<?php
namespace Dkd\CmisService\Service;

use Dkd\CmisService\Analysis\Detection\IndexableColumnDetector;
use Dkd\CmisService\Analysis\RecordAnalyzer;
use Dkd\CmisService\Configuration\Definitions\CmisConfiguration;
use Dkd\CmisService\Configuration\Definitions\MasterConfiguration;
use Dkd\CmisService\Constants;
use Dkd\CmisService\Error\DatabaseCallException;
use Dkd\CmisService\Error\RecordNotFoundException;
use Dkd\CmisService\Execution\Exception;
use Dkd\CmisService\Factory\CmisObjectFactory;
use Dkd\CmisService\Factory\ObjectFactory;
use Dkd\CmisService\SingletonInterface;
use Dkd\PhpCmis\CmisObject\CmisObjectInterface;
use Dkd\PhpCmis\Data\DocumentInterface;
use Dkd\PhpCmis\Data\FolderInterface;
use Dkd\PhpCmis\Data\ObjectIdInterface;
use Dkd\PhpCmis\DataObjects\DocumentTypeDefinition;
use Dkd\PhpCmis\DataObjects\FolderTypeDefinition;
use Dkd\PhpCmis\Definitions\TypeDefinitionInterface;
use Dkd\PhpCmis\Exception\CmisContentAlreadyExistsException;
use Dkd\PhpCmis\Exception\CmisObjectNotFoundException;
use Dkd\PhpCmis\PropertyIds;
use Dkd\PhpCmis\SessionInterface;
use GuzzleHttp\Stream\Stream;
use TYPO3\CMS\Core\Database\DatabaseConnection;

/**
 * CMIS Service
 *
 * Contains the master API for other code to interact
 * or acquire other interaction APIs for CMIS.
 */
class CmisService implements SingletonInterface {

	/**
	 * Contexts passed to Logger implementations when messages
	 * are dispatched from this class.
	 *
	 * @var array
	 */
	protected $logContexts = array('cmis_service', 'service', 'cmis');

	/**
	 * @param string $table
	 * @param integer $uid
	 * @return array|NULL
	 */
	protected function getIdentityStorageRecord($table, $uid) {
		return $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
			'cmis_uuid',
			'tx_cmisservice_identity',
			sprintf("foreign_uid = %d AND foreign_tablename = '%s'", $uid, $table)
		);
	}

	/**
	 * Get the CMIS-generated UUID for a local record,
	 * pulled from local storage. Save UUIDs using the
	 * storeUuidLocallyForRecord() method.
	 *
	 * @param string $table
	 * @param integer $uid
	 * @return string|NULL
	 */
	public function getUuidForLocalRecord($table, $uid) {
		$record = $this->getIdentityStorageRecord($table, $uid);
		if (empty($record['cmis_uuid'])) {
			throw new CmisObjectNotFoundException('Local UUID not detected, no CMIS document can be loaded');
		}
		return $record['cmis_uuid'];
	}

	/**
	 * Returns the record associated with the CMIS object
	 * or NULL if no record can be found. If second argument
	 * is TRUE the specific version of the CMIS object must
	 * be matched or NULL is returned (indicating the CMIS
	 * object needs to be imported - importing then is capable
	 * of merging the existing record with new CMIS data).
	 *
	 * @param string $objectId
	 * @param boolean $matchVersion
	 * @return array|NULL
	 */
	public function getRecordForCmisUuid($objectId, $matchVersion = FALSE) {
		if (strpos($objectId, ';')) {
			list ($uuid, $version) = explode(';', $objectId);
		} else {
			$uuid = $objectId;
			$version = '';
		}
		if ($matchVersion) {
			$clause = sprintf("cmis_uuid = '%s' AND cmis_version = '%s'", $uuid, $version);
		} else {
			$clause = sprintf("cmis_uuid = '%s'", $uuid);
		}
		$identityRecord = $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
			'foreign_tablename,foreign_uid',
			'tx_cmisservice_identity',
			$clause
		);
		if (!$identityRecord) {
			// Object is not yet mapped - early return NULL since we can't look for any original record next
			return NULL;
		}
		return $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
			'*',
			$identityRecord['foreign_tablename'],
			sprintf('uid = %d', $identityRecord['foreign_uid'])
		);
	}

	/**
	 * Save a CMIS-generated UUID to the local storage.
	 * The UUID can then be retrieved using getUuidForLocalRecord().
	 *
	 * @param string $table
	 * @param integer $uid
	 * @param string $objectId
	 * @return void
	 */
	public function storeUuidLocallyForRecord($table, $uid, $objectId) {
		if (strpos($objectId, ';')) {
			list ($uuid, $version) = explode(';', $objectId);
		} else {
			$uuid = $objectId;
			$version = '';
		}
		$record = $this->getIdentityStorageRecord($table, $uid);
		if ($record) {
			$this->getDatabaseConnection()->exec_UPDATEquery(
				'tx_cmisservice_identity',
				sprintf("foreign_uid = %d AND foreign_tablename = '%s'", $uid, $table),
				array(
					'cmis_uuid' => $uuid,
					'cmis_version' => $version
				)
			);
		} else {
			$this->getDatabaseConnection()->exec_INSERTquery(
				'tx_cmisservice_identity',
				array(
					'cmis_uuid' => $uuid,
					'cmis_version' => $version,
					'foreign_uid' => $uid,
					'foreign_tablename' => $table
				)
			);
		}
	}

	/**
	 * Returns a single, primary type definition which
	 * applies to $table. The $uid parameter is accepted
	 * to allow switching the returned type based on the
	 * selected record's type designation.
	 *
	 * Throws a \RuntimeException if the table has no
	 * primary type configured in TypoScript.
	 *
	 * @param string $table
	 * @param string $uid
	 * @return TypeDefinitionInterface
	 * @throws \RuntimeException
	 */
	public function resolvePrimaryObjectTypeForTableAndUid($table, $uid) {
		$typeId = $this->getObjectFactory()->getConfiguration()->getTableConfiguration()->getSinglePrimaryType($table);
		if (TRUE === empty($typeId)) {
			throw new \RuntimeException(
				sprintf(
					'Table "%s" does not appear to be configured with a primary CMIS type, please check your settings',
					$table
				)
			);
		}
		return $this->getCmisObjectFactory()->getSession()->getTypeDefinition($typeId);
	}

	/**
	 * Resolves secondary object types, either native to CMIS
	 * or specific to TYPO3, which fits documents stored
	 * from TYPO3 records. Accepts $uid in preparation for
	 * multiple types of records stored in the same table,
	 * making it possible to return different CMIS object
	 * types depending on the record's type designation.
	 *
	 * @param string $table
	 * @param integer $uid Currently unused; planned usage: read type from record property, lookup types by record type
	 * @return array
	 */
	public function resolveSecondaryObjectTypesForTableAndUid($table, $uid) {
		$types = $this->getObjectFactory()->getConfiguration()->getTableConfiguration()->getSingleSecondaryTypes($table);
		array_unshift($types, Constants::CMIS_DOCUMENT_TYPE_MAIN_ASPECT);
		return $types;
	}

	/**
	 * Extracts properties for a CMIS Document/Folder
	 * based on table and UID. Is a shortcut alternative
	 * to manually creating Detector/Extractor/Analyzer
	 * instances, applying default values and extracting
	 * the properties required for saving in CMIS without
	 * causing errors about missing values.
	 *
	 * @param string $table
	 * @param integer $uid
	 * @return array
	 */
	public function resolvePropertiesForTableAndUid($table, $uid) {
		$columnDetector = new IndexableColumnDetector();
		$columns = $columnDetector->getIndexableColumnNamesFromTable($table);
		$record = $this->loadRecordFromDatabase($table, $uid, $columns);
		$recordAnalyzer = new RecordAnalyzer($table, $record);
		$title = $recordAnalyzer->getTitleForRecord();
		$properties = $this->readDefaultPropertyValuesForTableFromConfiguration($table);
		$properties[PropertyIds::OBJECT_TYPE_ID] = $this->resolvePrimaryObjectTypeForTableAndUid($table, $uid)->getId();
		$properties[Constants::CMIS_PROPERTY_FULLTITLE] = $title;
		$properties[PropertyIds::NAME] = $this->sanitizeTitle($title, $table . $uid);
		$properties[Constants::CMIS_PROPERTY_TYPO3TABLE] = $table;
		$properties[Constants::CMIS_PROPERTY_TYPO3UID] = (integer) $uid;
		$properties[PropertyIds::SECONDARY_OBJECT_TYPE_IDS] = $this->resolveSecondaryObjectTypesForTableAndUid($table, $uid);
		return $properties;
	}

	/**
	 * Returns an instance of a CMIS document that is
	 * related to the record identified by $uid from
	 * $table. If a CMIS document does not exist in the
	 * repository, one is created and stored before
	 * being returned. The return value can therefore
	 * be trusted to always return a correct, relevant
	 * document instance unless errors occur.
	 *
	 * Catches a single type of Exception from CMIS,
	 * the CmisObjectNotFound Exception which if caught,
	 * causes the method to return a fresh Document.
	 * All other Exceptions are allowed to pass through.
	 * If creating a page and the page contains a
	 * `sys_domain` record and no CMIS top page has been
	 * configured in TypoScript (all three must be true)
	 * then the method will resolve and create if missing,
	 * a virtual container folder underneath the "Sites"
	 * folder in CMIS in which the page will be placed.
	 *
	 * This causes one separate "Site" with that site's
	 * page tree to be created in CMIS.
	 *
	 * This also means that several TYPO3 sites can share
	 * and synchronise to/from the same "Site" folder as
	 * long as those sites use the same domain name or
	 * manually configure which CMIS folder to use as top.
	 *
	 * Works recursively on pages but does so from the
	 * child and upwards until every required parent
	 * exists; then continues with the children.
	 *
	 * @param string $table
	 * @param integer $uid
	 * @return CmisObjectInterface
	 */
	public function resolveObjectByTableAndUid($table, $uid) {
		$session = $this->getCmisObjectFactory()->getSession();
		try {
			$uuid = $this->getUuidForLocalRecord($table, $uid);
			$document = $this->resolveObjectByUuid($uuid);
		} catch (CmisObjectNotFoundException $error) {
			$configuredRootUuid = $this->getObjectFactory()->getConfiguration()
				->getCmisConfiguration()->get(CmisConfiguration::ROOT_UUID);
			$fields = $this->resolveStructuralFieldsForTable($table);
			$record = $this->loadRecordFromDatabase($table, $uid, $fields);
			$parentPageUid = (integer) $record['pid'];
			if ('pages' === $table) {
				$domainRecord = $this->resolvePrimaryDomainRecordForPageUid($uid);
			} else {
				$domainRecord = NULL;
			}
			if ('sys_domain' === $table) {
				// Domains are *always* created in the same place. Always. One level only.
				$parentFolder = $this->resolveCmisSitesParentFolder();
				$resolvedUsingMethod = 'Sites parent folder';
			} elseif (NULL !== $domainRecord) {
				// Domain record detected; has priority. Store CMIS object in Site folder.
				$parentFolder = $this->resolveCmisSiteFolderByPageUid($domainRecord['pid']);
				$resolvedUsingMethod = 'Sites folder by page UID';
			} elseif (0 < $parentPageUid) {
				// Standard record without domain and with parent; store under parent page.
				$parentFolder = $this->resolveObjectByTableAndUid('pages', $parentPageUid);
				$resolvedUsingMethod = 'Page by page UID';
			} elseif (FALSE === empty($configuredRootUuid)) {
				// Page UID is zero; page has no domain; a top point is configured. Use it.
				$parentFolder = $session->getObject($session->createObjectId($configuredRootUuid));
				$resolvedUsingMethod = 'Configured root folder UUID';
			} else {
				// Page UID is zero; page has no domain; no top point is configured. Resolve
				// hostname or IP and use as site folder.
				$parentFolder = $this->getAndAutoCreateDefaultSiteFolder();
				$resolvedUsingMethod = 'Auto-created sites folder';
			}
			if (!$parentFolder instanceof FolderInterface) {
				throw new \RuntimeException(
					sprintf(
						'Object resolved (via %s) as parent for new object for %s:%s is not a folder - actual type: %s. ID: %s.',
						$resolvedUsingMethod,
						$table,
						$uid,
						$parentFolder->getType()->getId(),
						$parentFolder->getId()
					)
				);
			}

			$existingObject = $this->resolveChildByTableAndUid($parentFolder, $table, $uid);
			if ($existingObject) {
				$this->storeUuidLocallyForRecord($table, $uid, $existingObject->getId());
				return $existingObject;
			}

			$document = $this->createCmisObject($parentFolder, $table, $uid);
		}
		return $document;
	}

	/**
	 * Gets, and creates if missing, a default Site
	 * folder based on the hostname of the current host.
	 *
	 * @return FolderInterface|NULL
	 */
	public function getAndAutoCreateDefaultSiteFolder() {
		$session = $this->getCmisObjectFactory()->getSession();
		$hostname = $this->resolveHostname();
		$sitesParentFolder = $this->resolveCmisSitesParentFolder();
		$parentFolder = NULL;
		foreach ($sitesParentFolder->getChildren() as $site) {
			if ($site->getName() === $hostname) {
				$parentFolder = $site;
				break;
			}
		}
		if (NULL === $parentFolder) {
			$createdFolder = $session->createFolder(array(
				PropertyIds::NAME => $hostname,
				PropertyIds::DESCRIPTION => 'Global records from page UID zero on host ' . $hostname,
				PropertyIds::OBJECT_TYPE_ID => Constants::CMIS_DOCUMENT_TYPE_SITE,
				PropertyIds::SECONDARY_OBJECT_TYPE_IDS => $this->resolveSecondaryObjectTypesForTableAndUid('sys_domain', 0)
			), $sitesParentFolder);
			/** @var FolderInterface $parentFolder */
			$parentFolder = $session->getObject($createdFolder);
		}
		return $parentFolder;
	}

	/**
	 * @return string
	 */
	protected function resolveHostname() {
		return !empty($GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']) ? $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'] : trim(shell_exec('hostname'));
	}

	/**
	 * Returns the `sys_domain` which highest sorting priority
	 * on $pageUid, or NULL if no domain records exist.
	 *
	 * @param integer $pageUid
	 * @return array|NULL
	 */
	public function resolvePrimaryDomainRecordForPageUid($pageUid) {
		$record = $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
			'uid, domainName, pid',
			'sys_domain',
			"pid = '" . $pageUid . "'",
			'',
			'sorting ASC'
		);
		return (TRUE === empty($record) ? NULL : $record);
	}

	/**
	 * IMPORTANT: Do not use this CMIS Folder as parameter
	 * for other commands or as parent page of folders you
	 * create. It is intended for internal use only.
	 *
	 * Returns the CMIS "Site"-type folder that will contain
	 * or already contains the page identified by $pageUid.
	 * Creates the "Site" folder if it does not already exist.
	 *
	 * Uses `sys_domain` records to identify each "Site" that
	 * is expected to exist, but only considers the topmost
	 * domain record.
	 *
	 * Works recursively: $pageUid does not have to be a direct
	 * descendant of the page that has the domain record.
	 *
	 * NB: The returned "Site" folder acts as virtual container;
	 * meaning that the page that contains the `sys_domain`
	 * record will become the first and only child of the "Site"
	 * folder. To illustrate, an example structure:
	 *
	 * - CMIS ROOT
	 * - Shared
	 * + Sites
	 *   - swdp
	 *   + my.domain.com
	 *     + Top page of my.domain.com
	 *       - First subpage
	 *       - Second subpage
	 *   - my.otherdomain.com
	 *   - my.thirddomain.com
	 *
	 * And so on. This virtual container can then be targeted
	 * by type and name; and the top page of the domain can be
	 * targeted because it is the only expected child page.
	 *
	 * @param integer $pageUid
	 * @return FolderInterface
	 */
	public function resolveCmisSiteFolderByPageUid($pageUid) {
		$searchPageUid = $pageUid;
		$domainRecord = NULL;
		while (NULL === $domainRecord && $searchPageUid > 0) {
			$domainRecord = $this->resolvePrimaryDomainRecordForPageUid($searchPageUid);
			$searchPageUid = (integer) reset($this->loadRecordFromDatabase('pages', $searchPageUid, array('pid')));
		}
		if (NULL === $domainRecord) {
			return $this->getAndAutoCreateDefaultSiteFolder();
		}
		try {
			$uuid = $this->getUuidForLocalRecord('sys_domain', $domainRecord['uid']);
			$folder = $this->resolveObjectByUuid($uuid);
		} catch (RecordNotFoundException $error) {
			$folder = $this->getAndAutoCreateDefaultSiteFolder();
		} catch (CmisObjectNotFoundException $error) {
			$folder = $this->getAndAutoCreateDefaultSiteFolder();
		}
		return $folder;
	}

	/**
	 * Returns the parent folder in which to store Site
	 * folders created by the system. If no Sites container
	 * exists, the root folder is returned instead.
	 *
	 * @return FolderInterface
	 */
	public function resolveCmisSitesParentFolder() {
		$session = $this->getCmisObjectFactory()->getSession();
		$root = $session->getRootFolder();
		foreach ($root->getChildren() as $child) {
			if ($child->getType()->getId() === Constants::CMIS_DOCUMENT_TYPE_SITES) {
				return $child;
			}
		}
		return $root;
	}

	/**
	 * Main UUID-based resolving method; loads a single
	 * object based on the CMIS UUID.
	 *
	 * @param string $uuid
	 * @return CmisObjectInterface
	 */
	public function resolveObjectByUuid($uuid) {
		$session = $this->getCmisObjectFactory()->getSession();
		$object = $session->getObject($session->createObjectId($uuid));
		$this->getObjectFactory()->getLogger()->info(
			sprintf('CMIS Document retrieved, ID: %s', $object->getId()),
			$this->logContexts
		);
		return $object;
	}

	/**
	 * Queues an importing task to get create a TYPO3 record
	 * based on a CMIS object. Resolves parent folder also
	 * based on parent CMIS ID - which means that any object
	 * you attempt to import *must* exist within the site root
	 * associated with this TYPO3 site.
	 *
	 * Importing an object causes the record and object to
	 * become associated and indexed by TYPO3 - so editing the
	 * record in TYPO3 afterwards also updates the CMIS object.
	 *
	 * @param CmisObjectInterface $cmisObject
	 * @return void
	 */
	public function queueObjectImport(CmisObjectInterface $cmisObject) {

	}

	/**
	 * Creates a CMIS Document instance filled with defaults
	 * based on TCA table.
	 *
	 * @param FolderInterface $folder Parent folder for new document
	 * @param string $table The name of the table in TCA
	 * @param integer $uid The UID of the record on the TYPO3 side
	 * @param array $properties Properties to set; if NULL resolved from table/uid
	 * @return CmisObjectInterface
	 */
	public function createCmisObject(FolderInterface $folder, $table, $uid, array $properties = NULL) {
		if (NULL === $properties) {
			$properties = $this->resolvePropertiesForTableAndUid($table, $uid);
		}
		$existingChild = $this->resolveChildByTableAndUid($folder, $table, $uid);
		if ($existingChild) {
			$this->storeUuidLocallyForRecord($table, $uid, $existingChild->getId());
			return $existingChild;
		}

		$primaryType = $this->resolvePrimaryObjectTypeForTableAndUid($table, $uid);
		$properties[PropertyIds::NAME] = $this->sanitizeTitle($properties[PropertyIds::NAME], $table . '-' . $uid);

		try {
			$objectId = $this->createByTypeInFolder($folder, $primaryType, $properties);
		} catch (CmisContentAlreadyExistsException $error) {
			$properties[PropertyIds::NAME] = $table . '-' . $uid;
			$objectId = $this->createByTypeInFolder($folder, $primaryType, $properties);
		}

		$this->getObjectFactory()->getLogger()->info(
			sprintf('New CMIS Object (%s) created, ID: %s', $primaryType->getId(), $objectId),
			$this->logContexts
		);
		$this->storeUuidLocallyForRecord($table, $uid, $objectId);
		return $this->getCmisSession()->getObject($objectId);
	}

	/**
	 * @param FolderInterface $folder
	 * @param TypeDefinitionInterface $primaryType
	 * @param array $properties
     * @return ObjectIdInterface
	 */
	protected function createByTypeInFolder(FolderInterface $folder, TypeDefinitionInterface $primaryType, array $properties) {
		$session = $this->getCmisObjectFactory()->getSession();
		if (TRUE === $primaryType instanceof FolderTypeDefinition) {
			$objectId = $session->createFolder($properties, $folder);
		} elseif (TRUE === $primaryType instanceof DocumentTypeDefinition) {
			$objectId = $session->createDocument($properties, $folder);
		} else {
			$objectId = $session->createItem($properties, $folder);
		}
		return $objectId;
	}

	/**
	 * @param FolderInterface $folder
	 * @param string $name
	 * @return CmisObjectInterface|NULL
	 */
	public function resolveChildByName(FolderInterface $folder, $name) {
		foreach ($folder->getChildren() as $child) {
			if ($child->getName() === $name) {
				// Object using the exact same name was detected.
				return $child;
			}
		}
		return NULL;
	}

	/**
	 * @param FolderInterface $folder
	 * @param string $table
	 * @param integer $uid
	 * @return CmisObjectInterface|NULL
	 */
	public function resolveChildByTableAndUid(FolderInterface $folder, $table, $uid) {
		foreach ($folder->getChildren() as $child) {
			if (
				$child->getPropertyValue(Constants::CMIS_PROPERTY_TYPO3TABLE) === $table
				&& $child->getPropertyValue(Constants::CMIS_PROPERTY_TYPO3UID) === $uid
			) {
				return $child;
			}
		}
		return NULL;
	}

	/**
	 * Uploads a (new or existing) model schema to the
	 * CMIS repository and activates it, causing the
	 * repository to load all definitions from the schema.
	 *
	 * Can be executed multiple times without problems
	 * but will obviously replace the schema with the version
	 * you provide as argument, always.
	 *
	 * Note that your uploaded model files are identified by
	 * base name only, meaning that uploading multiple files
	 * named for example "mymodel.xml" will override that
	 * model every time also if the file comes from different
	 * folders on the local file system.
	 *
	 * @param string $modelDefinitionPathAndFilename
	 * @return DocumentInterface
	 */
	public function uploadModelDefinition($modelDefinitionPathAndFilename) {
		if (!file_exists($modelDefinitionPathAndFilename)) {
			throw new Exception(
				sprintf(
					'Model definition file "%s" does not exist - please provide a valid path',
					$modelDefinitionPathAndFilename
				)
			);
		}
		$session = $this->getCmisSession();
		/** @var FolderInterface $dictionaryModelFolder */
		$dictionaryModelFolder = $session->getObjectByPath('/Data Dictionary/Models');
		$modelDefinitionBaseName = pathinfo($modelDefinitionPathAndFilename, PATHINFO_BASENAME);
		/** @var DocumentInterface|NULL $modelDefinitionObject */
		$modelDefinitionObject = NULL;
		foreach ($dictionaryModelFolder->getChildren() as $existingModelDefinitionObject) {
			if ($existingModelDefinitionObject->getName() === $modelDefinitionBaseName) {
				$modelDefinitionObject = $existingModelDefinitionObject;
				break;
			}
		}
		$contentStream = Stream::factory(fopen($modelDefinitionPathAndFilename, 'r'));
		if ($modelDefinitionObject) {
			$modelDefinitionObjectId = $modelDefinitionObject->setContentStream($contentStream, TRUE);
		} else {
			$modelDefinitionObjectId = $session->createDocument(
				array(
					PropertyIds::OBJECT_TYPE_ID => Constants::CMIS_DOCUMENT_TYPE_MODEL,
					PropertyIds::SECONDARY_OBJECT_TYPE_IDS => array(
						'P:cm:titled',
						'P:cm:author',
						'P:sys:localized'
					),
					PropertyIds::NAME => $modelDefinitionBaseName,
					Constants::CMIS_PROPERTY_MODELDESCRIPTION => 'Imported TYPO3 model definition',
				),
				$dictionaryModelFolder,
				$contentStream
			);
		}
		return $session->getObject($modelDefinitionObjectId);
	}

	/**
	 * Activate a model definition definition in repository.
	 *
	 * @param CmisObjectInterface $model
	 * @return CmisObjectInterface
	 */
	public function activateModelDefinition(CmisObjectInterface $model) {
		return $model->updateProperties(array(
			Constants::CMIS_PROPERTY_MODELACTIVE => TRUE
		));
	}

	/**
	 * Deactivate a model definition document in repository.
	 *
	 * @param CmisObjectInterface $model
	 * @return CmisObjectInterface
	 */
	public function deactivateModelDefinition(CmisObjectInterface $model) {
		return $model->updateProperties(array(
			Constants::CMIS_PROPERTY_MODELACTIVE => FALSE
		));
	}

	/**
	 * Gets the default or a named CMIS session. Wrapper
	 * with public access for easy use of CMIS session in
	 * classes implementing CmisService.
	 *
	 * @param string $serverName
	 * @return SessionInterface
	 */
	public function getCmisSession($serverName = MasterConfiguration::CMIS_DEFAULT_SERVER) {
		return $this->getCmisObjectFactory()->getSession($serverName);
	}

	/**
	 * @param string $title
	 * @param string $default
	 * @return string
	 */
	public function sanitizeTitle($title, $default) {
		$title = strip_tags($title);
		$title = iconv('UTF-8', 'ASCII//IGNORE//TRANSLIT', $title);
		$title = trim($title);
		$title = preg_replace('/[^a-z0-9\\s_\\-]+/i', '', $title);
		if (strlen($title) > 255) {
			$title = substr($title, 0, 252);
		} elseif (empty($title)) {
			$title = $default;
		}
		return $title;
	}

	/**
	 * Returns an array of fields which are considered
	 * structural in nature: fields like "pid", "hidden",
	 * "sys_language_uid" "label" etc. which determine how
	 * and whether or not each record should be treated.
	 *
	 * @param string $table
	 * @return array
	 */
	protected function resolveStructuralFieldsForTable($table) {
		$fields = array('pid');
		if (TRUE === isset($GLOBALS['TCA'][$table]['ctrl']['label'])) {
			$fields[] = $GLOBALS['TCA'][$table]['ctrl']['label'];
		}
		// @TODO: include additional fields from TCA "ctrl" specs
		return $fields;
	}

	/**
	 * Gets default column values for a table as configured
	 * in the TableConfiguration.
	 *
	 * @param string $table
	 * @return array
	 */
	protected function readDefaultPropertyValuesForTableFromConfiguration($table) {
		$configuration = $this->getObjectFactory()->getConfiguration()->getTableConfiguration();
		if ($configuration->isTableConfigured($table)) {
			return $configuration->getSingleTableDefaultValues($table);
		}
		return array();
	}

	/**
	 * Wrapper function to load a single database record,
	 * identified by $uid. Throws a CmisService-specific
	 * Exception if the record could not be loaded - this
	 * method is only used to load existing records and
	 * a failure indicates a failure in table name, uid
	 * or field names.
	 *
	 * @param string $table
	 * @param integer $uid
	 * @param array $fields
	 * @return array
	 * @throws RecordNotFoundException
	 * @throws DatabaseCallException
	 */
	protected function loadRecordFromDatabase($table, $uid, array $fields) {
		$fieldList = implode(',', $fields);
		$database = $this->getDatabaseConnection();
		$result = $database->exec_SELECTgetSingleRow($fieldList, $table, "uid = '" . $uid . "'");
		if (NULL === $result) {
			throw new DatabaseCallException($database->sql_error(), 1442925222);
		} elseif (FALSE === $result) {
			throw new RecordNotFoundException(sprintf('Record %d from table %s could not be loaded', $uid, $table), 1442925279);
		}
		return $result;
	}

	/**
	 * @return DatabaseConnection
	 * @codeCoverageIgnore
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * @return CmisObjectFactory
	 * @codeCoverageIgnore
	 */
	protected function getCmisObjectFactory() {
		return new CmisObjectFactory();
	}

	/**
	 * @return ObjectFactory
	 * @codeCoverageIgnore
	 */
	protected function getObjectFactory() {
		return new ObjectFactory();
	}

}
