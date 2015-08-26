<?php
namespace Dkd\CmisService\Execution\Cmis;

use Dkd\CmisService\Analysis\Detection\ExtractionMethodDetector;
use Dkd\CmisService\Analysis\Detection\IndexableColumnDetector;
use Dkd\CmisService\Analysis\RecordAnalyzer;
use Dkd\CmisService\Configuration\Definitions\CmisConfiguration;
use Dkd\CmisService\Constants;
use Dkd\CmisService\Execution\AbstractExecution;
use Dkd\CmisService\Factory\CmisObjectFactory;
use Dkd\CmisService\Factory\ObjectFactory;
use Dkd\CmisService\Resolving\UUIDResolver;
use Dkd\PhpCmis\CmisObject\CmisObjectInterface;
use Dkd\PhpCmis\Data\DocumentInterface;
use Dkd\PhpCmis\Data\ObjectIdInterface;
use Dkd\PhpCmis\Data\ObjectTypeInterface;
use Dkd\PhpCmis\DataObjects\DocumentTypeDefinition;
use Dkd\PhpCmis\DataObjects\FolderTypeDefinition;
use Dkd\PhpCmis\Exception\CmisObjectNotFoundException;
use Dkd\PhpCmis\Exception\CmisRuntimeException;
use Dkd\PhpCmis\PropertyIds;
use Dkd\PhpCmis\SessionInterface;
use Maroschik\Identity\IdentityMap;
use TYPO3\CMS\Core\Utility\ClassNamingUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;

/**
 * Class AbstractCmisExecution
 *
 * Base class with helper functions relevant for
 * executions using CMIS documents/service.
 */
abstract class AbstractCmisExecution extends AbstractExecution {

	/**
	 * Contexts passed to Logger implementations when messages
	 * are dispatched from this class.
	 *
	 * @var array
	 */
	protected $logContexts = array('cmis_service', 'execution', 'cmis');

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
	 *
	 * Works recursively on pages but does so from the
	 * child and upwards until every required parent
	 * exists; then continues with the children.
	 *
	 * @param string $table
	 * @param integer $uid
	 * @return DocumentInterface
	 */
	protected function resolveCmisDocumentByTableAndUid($table, $uid) {
		$session = $this->getCmisObjectFactory()->getSession();
		try {
			$uuid = $this->getCmisUuidForLocalRecord($table, $uid);
			$document = $this->resolveCmisObjectByUuid($session, $uuid);
		} catch (CmisObjectNotFoundException $error) {
			$fields = $this->resolveStructuralFieldsForTable($table);
			$record = $this->loadRecordFromDatabase($table, $uid, $fields);
			$parentPageUid = (integer) $record['pid'];
			if (0 === $parentPageUid) {
				$configuredRootUuid = $this->getObjectFactory()->getConfiguration()
					->getCmisConfiguration()->get(CmisConfiguration::ROOT_UUID);
				if (TRUE === empty($configuredRootUuid)) {
					$parentFolder = $session->getRootFolder();
				} else {
					$parentFolder = $session->getObject($session->createObjectId($configuredRootUuid));
				}
			} else {
				$parentFolder = $this->resolveCmisDocumentByTableAndUid('pages', $parentPageUid);
			}
			$type = $this->resolveCmisObjectTypeForTableAndUid($table, $uid);
			$document = $this->createCmisDocument($uuid, $type, $parentFolder, $table, $uid);
		}
		return $document;
	}

	/**
	 * @param SessionInterface $session
	 * @param string $uuid
	 * @return CmisObjectInterface
	 */
	protected function resolveCmisObjectByUuid(SessionInterface $session, $uuid) {
		$object = $session->getObject($session->createObjectId($uuid));
		$this->getObjectFactory()->getLogger()->info(
			sprintf('CMIS Document retrieved, ID: %s', $object->getId()),
			$this->logContexts
		);
		return $object;
	}

	/**
	 * Resolves a CMIS object type, either native to CMIS
	 * or specific to TYPO3, which fits documents stored
	 * from TYPO3 records. Accepts $uid in preparation for
	 * multiple types of records stored in the same table,
	 * making it possible to return a different CMIS object
	 * type depending on the record's type designation.
	 *
	 * @param string $table
	 * @param integer $uid
	 * @return ObjectTypeInterface
	 */
	protected function resolveCmisObjectTypeForTableAndUid($table, $uid) {
		$definitionType = Constants::CMIS_DOCUMENT_TYPE_ARBITRARY;
		if ('pages' === $table) {
			$definitionType = Constants::CMIS_DOCUMENT_TYPE_PAGES;
		} elseif ('tt_content' === $table) {
			$definitionType = Constants::CMIS_DOCUMENT_TYPE_CONTENT;
		}
		return $this->getCmisObjectFactory()->getSession()->getTypeDefinition($definitionType);
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
	 * @return DocumentInterface
	 */
	protected function extractPropertiesForTableAndUid($table, $uid) {
		$values = $this->readDefaultPropertyValuesForTableFromConfiguration($table);
		$columnDetector = new IndexableColumnDetector();
		$columns = $columnDetector->getIndexableColumnNamesFromTable($table);
		$record = $this->loadRecordFromDatabase($table, $uid, $columns);
		$recordAnalyzer = new RecordAnalyzer($table, $record);
		$values[PropertyIds::NAME] = $recordAnalyzer->getTitleForRecord();
		return $values;
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
	 * Creates a CMIS Document instance filled with defaults
	 * based on TCA table.
	 *
	 * @param string $uuid The UUID intended for the object
	 * @param ObjectTypeInterface $type Document type to create
	 * @param ObjectIdInterface $folder Parent folder for new document
	 * @param string $table The name of the table in TCA
	 * @param integer $uid The UID of the record on the TYPO3 side
	 * @return ObjectIdInterface
	 */
	protected function createCmisDocument($uuid, ObjectTypeInterface $type, ObjectIdInterface $folder, $table, $uid) {
		$session = $this->getCmisObjectFactory()->getSession();
		$properties = $this->extractPropertiesForTableAndUid($table, $uid);
		$properties[Constants::CMIS_PROPERTY_TYPO3TABLE] = $table;
		$properties[Constants::CMIS_PROPERTY_TYPO3UUID] = $this->getObjectFactory()
			->getIdentityMap()->getIdentifierForResourceLocation($table, $uid);
		$properties[PropertyIds::OBJECT_TYPE_ID] = $type->getId();
		$properties[PropertyIds::SECONDARY_OBJECT_TYPE_IDS] = array(
			$this->getCmisObjectFactory()->getSession()->getTypeDefinition(Constants::CMIS_DOCUMENT_TYPE_MAIN_ASPECT)->getId()
		);
		if (TRUE === $type instanceof FolderTypeDefinition) {
			$objectId = $session->createFolder($properties, $folder);
		} else {
			$objectId = $session->createDocument($properties, $folder);
		}
		$this->getObjectFactory()->getLogger()->info(
			sprintf('New CMIS Object (%s) created, ID: %s', $type->getDisplayName(), $objectId),
			$this->logContexts
		);
		$this->storeCmisUuidLocallyForRecord($table, $uid, $objectId);
		// @TODO: store generic resources for which there is no type, inside else {}
		return $session->getObject($objectId);
	}

	/**
	 * @param string $table
	 * @param integer $uid
	 * @return string|NULL
	 */
	protected function getCmisUuidForLocalRecord($table, $uid) {
		$record = $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
			'cmis_uuid',
			'sys_identity',
			sprintf("foreign_uid = %d AND foreign_tablename = '%s'", $uid, $table)
		);
		if (empty($record['cmis_uuid'])) {
			throw new CmisObjectNotFoundException('Local UUID not detected, no CMIS document can be loaded');
		}
		return $record['cmis_uuid'];
	}

	/**
	 * @param string $table
	 * @param integer $uid
	 * @param string $objectId
	 * @return void
	 */
	protected function storeCmisUuidLocallyForRecord($table, $uid, $objectId) {
		$versionNeedle = strpos($objectId, ';');
		if (FALSE !== $versionNeedle) {
			$uuid = substr($objectId, 0, $versionNeedle);
		} else {
			$uuid = $objectId;
		}
		$this->getDatabaseConnection()->exec_UPDATEquery(
			'sys_identity',
			sprintf("foreign_uid = %d AND foreign_tablename = '%s'", $uid, $table),
			array(
				'cmis_uuid' => $uuid
			)
		);
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
	 * @return CmisObjectFactory
	 * @codeCoverageIgnore
	 */
	protected function getCmisObjectFactory() {
		return new CmisObjectFactory();
	}

	/**
	 * @return DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

}
