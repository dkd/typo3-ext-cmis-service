<?php
namespace Dkd\CmisService\Service;

use Dkd\CmisService\Analysis\Detection\IndexableColumnDetector;
use Dkd\CmisService\Analysis\RecordAnalyzer;
use Dkd\CmisService\Configuration\Definitions\CmisConfiguration;
use Dkd\CmisService\Configuration\Definitions\MasterConfiguration;
use Dkd\CmisService\Constants;
use Dkd\CmisService\Factory\CmisObjectFactory;
use Dkd\CmisService\Factory\ObjectFactory;
use Dkd\CmisService\SingletonInterface;
use Dkd\CmisService\Error\RecordNotFoundException;
use Dkd\CmisService\Error\DatabaseCallException;
use Dkd\PhpCmis\CmisObject\CmisObjectInterface;
use Dkd\PhpCmis\Data\FolderInterface;
use Dkd\PhpCmis\DataObjects\DocumentTypeDefinition;
use Dkd\PhpCmis\DataObjects\FolderTypeDefinition;
use Dkd\PhpCmis\Definitions\TypeDefinitionInterface;
use Dkd\PhpCmis\Exception\CmisObjectNotFoundException;
use Dkd\PhpCmis\PropertyIds;
use Dkd\PhpCmis\SessionInterface;
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
	 * Save a CMIS-generated UUID to the local storage.
	 * The UUID can then be retrieved using getUuidForLocalRecord().
	 *
	 * @param string $table
	 * @param integer $uid
	 * @param string $objectId
	 * @return void
	 */
	public function storeUuidLocallyForRecord($table, $uid, $objectId) {
		$versionNeedle = strpos($objectId, ';');
		if (FALSE !== $versionNeedle) {
			$uuid = substr($objectId, 0, $versionNeedle);
		} else {
			$uuid = $objectId;
		}
		$record = $this->getIdentityStorageRecord($table, $uid);
		if ($record) {
			$this->getDatabaseConnection()->exec_UPDATEquery(
				'tx_cmisservice_identity',
				sprintf("foreign_uid = %d AND foreign_tablename = '%s'", $uid, $table),
				array(
					'cmis_uuid' => $uuid
				)
			);
		} else {
			$this->getDatabaseConnection()->exec_INSERTquery(
				'tx_cmisservice_identity',
				array(
					'cmis_uuid' => $uuid,
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
	 * @param string $table
	 * @param string $uid
	 * @return TypeDefinitionInterface
	 */
	public function resolvePrimaryObjectTypeForTableAndUid($table, $uid) {
		$typeId = $this->getObjectFactory()->getConfiguration()->getTableConfiguration()->getSinglePrimaryType($table);
		if (TRUE === empty($typeId)) {
			if ('pages' === $table) {
				$typeId = Constants::CMIS_DOCUMENT_TYPE_PAGES;
			} elseif ('tt_content' === $table) {
				$typeId = Constants::CMIS_DOCUMENT_TYPE_CONTENT;
			} elseif ('sys_domain' === $table) {
				$typeId = Constants::CMIS_DOCUMENT_TYPE_SITE;
			} else {
				$typeId = Constants::CMIS_DOCUMENT_TYPE_ARBITRARY;
			}
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
		$session = $this->getCmisObjectFactory()->getSession();
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
	 * @param CmisObjectInterface $document
	 * @param string $table
	 * @param integer $uid
	 * @return array
	 */
	public function resolvePropertiesForTableAndUid($table, $uid) {
		$columnDetector = new IndexableColumnDetector();
		$columns = $columnDetector->getIndexableColumnNamesFromTable($table);
		$record = $this->loadRecordFromDatabase($table, $uid, $columns);
		$recordAnalyzer = new RecordAnalyzer($table, $record);
		$properties = $this->readDefaultPropertyValuesForTableFromConfiguration($table);
		$properties[PropertyIds::OBJECT_TYPE_ID] = $this->resolvePrimaryObjectTypeForTableAndUid($table, $uid)->getId();
		$properties[PropertyIds::NAME] = $recordAnalyzer->getTitleForRecord();
		$properties[Constants::CMIS_PROPERTY_TYPO3TABLE] = $table;
		$properties[Constants::CMIS_PROPERTY_TYPO3UID] = (integer) $uid;
		$properties[PropertyIds::SECONDARY_OBJECT_TYPE_IDS] = $this->resolveSecondaryObjectTypesForTableAndUid($table, $uid);
		if ('sys_domain' === $table) {
			$properties['cm:title'] = $properties[PropertyIds::NAME];
			//TODO: discuss inclusion of a configurable "TYPO3 site preset" in Alfresco repository;
			//$properties['st:sitePreset'] = 'typo3-site';
		}
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
	 * @return DocumentInterface
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
			} elseif (NULL !== $domainRecord) {
				// Domain record detected; has priority. Store CMIS object in Site folder.
				$parentFolder = $this->resolveCmisSiteFolderByPageUid($parentPageUid);
			} elseif (0 < $parentPageUid) {
				// Standard record without domain and with parent; store under parent page.
				$parentFolder = $this->resolveObjectByTableAndUid('pages', $parentPageUid);
			} elseif (FALSE === empty($configuredRootUuid)) {
				// Page UID is zero; page has no domain; a top point is configured. Use it.
				$parentFolder = $session->getObject($session->createObjectId($configuredRootUuid));
			} else {
				// Page UID is zero; page has no domain; no top point is configured. Resolve
				// hostname or IP and use as site folder.
				$parentFolder = $this->getAndAutoCreateDefaultSiteFolder();
			}
			$document = $this->createCmisObject($parentFolder, $table, $uid);
		}
		return $document;
	}

	/**
	 * Gets, and creates if missing, a default Site
	 * folder based on the hostname of the current host.
	 *
	 * @return CmisObjectInterface|NULL
	 */
	protected function getAndAutoCreateDefaultSiteFolder() {
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
				PropertyIds::SECONDARY_OBJECT_TYPE_IDS => array(
					$session->getTypeDefinition(Constants::CMIS_DOCUMENT_TYPE_MAIN_ASPECT)->getId(),
					$session->getTypeDefinition('P:cm:titled')->getId(),
					$session->getTypeDefinition('P:cm:ownable')->getId(),
					$session->getTypeDefinition('P:sys:undeletable')->getId()
				)
			), $sitesParentFolder);
			$parentFolder = $session->getObject($createdFolder);
		}
		return $parentFolder;
	}

	/**
	 * @return string
	 */
	protected function resolveHostname() {
		return trim(shell_exec('hostname'));
	}

	/**
	 * Returns the `sys_domain` which highest sorting priority
	 * on $pageUid, or NULL if no domain records exist.
	 *
	 * @param integer $pageUid
	 * @return array|NULL
	 */
	protected function resolvePrimaryDomainRecordForPageUid($pageUid) {
		$record = $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
			'uid, domainName, pid',
			'sys_domain',
			"pid = '" . $pageUid . "'",
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
		if (0 === (integer) $pageUid) {
			return $this->getAndAutoCreateDefaultSiteFolder();
		}
		$searchPageUid = $pageUid;
		$domainRecord = NULL;
		try {
			while (NULL === $domainRecord && $searchPageUid > 0) {
				$domainRecord = $this->resolvePrimaryDomainRecordForPageUid($searchPageUid);
				$searchPageUid = (integer) reset($this->loadRecordFromDatabase('pages', $searchPageUid, array('pid')));
			}
			$uuid = $this->getUuidForLocalRecord('sys_domain', $domainRecord['uid']);
			$folder = $this->resolveObjectByUuid($uuid);
		} catch (RecordNotFoundException $error) {
			$folder = $this->getAndAutoCreateDefaultSiteFolder();
		} catch (CmisObjectNotFoundException $error) {
			$parent = $this->resolveCmisSitesParentFolder();
			$folder = $this->createCmisObject($parent, 'sys_domain', $domainRecord['uid']);
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
	protected function resolveCmisSitesParentFolder() {
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
	 * Creates a CMIS Document instance filled with defaults
	 * based on TCA table.
	 *
	 * @param FolderInterface $folder Parent folder for new document
	 * @param string $table The name of the table in TCA
	 * @param integer $uid The UID of the record on the TYPO3 side
	 * @param array $properties Properties to set; if NULL resolved from table/uid
	 * @return ObjectIdInterface
	 */
	public function createCmisObject(FolderInterface $folder, $table, $uid, array $properties = NULL) {
		if (NULL === $properties) {
			$properties = $this->resolvePropertiesForTableAndUid($table, $uid);
		}

		foreach ($folder->getChildren() as $child) {
			if ($child->getName() === $properties[PropertyIds::NAME]) {
				$objectId = $child->getId();
				$this->storeUuidLocallyForRecord($table, $uid, $objectId);
				$this->getObjectFactory()->getLogger()->info(
					sprintf(
						'Existing CMIS Object (%s) used instead of creating new, ID: %s',
						$type,
						$objectId
					),
					$this->logContexts
				);
				return $child;
			}
		}

		$session = $this->getCmisObjectFactory()->getSession();
		$primaryType = $this->resolvePrimaryObjectTypeForTableAndUid($table, $uid);

		$properties[PropertyIds::OBJECT_TYPE_ID] = $primaryType->getId();
		if (TRUE === $primaryType instanceof FolderTypeDefinition) {
			$objectId = $session->createFolder($properties, $folder);
		} elseif (TRUE === $primaryType instanceof DocumentTypeDefinition) {
			$objectId = $session->createDocument($properties, $folder);
		} else {
			$objectId = $session->createItem($properties, $folder);
		}

		$this->getObjectFactory()->getLogger()->info(
			sprintf('New CMIS Object (%s) created, ID: %s', $type, $objectId),
			$this->logContexts
		);
		$this->storeUuidLocallyForRecord($table, $uid, $objectId);
		return $session->getObject($objectId);
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
