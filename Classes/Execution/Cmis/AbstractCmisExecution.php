<?php
namespace Dkd\CmisService\Execution\Cmis;

use Dkd\CmisService\Analysis\Detection\ExtractionMethodDetector;
use Dkd\CmisService\Analysis\Detection\IndexableColumnDetector;
use Dkd\CmisService\Analysis\RecordAnalyzer;
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
use Dkd\PhpCmis\PropertyIds;
use Dkd\PhpCmis\SessionInterface;
use Maroschik\Identity\IdentityMap;
use TYPO3\CMS\Core\Utility\ClassNamingUtility;

/**
 * Class AbstractCmisExecution
 *
 * Base class with helper functions relevant for
 * executions using CMIS documents/service.
 */
abstract class AbstractCmisExecution extends AbstractExecution {

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
		$identityMap = $this->getObjectFactory()->getIdentityMap();
		$uuid = $identityMap->getIdentifierForResourceLocation($table, $uid);
		try {
			$document = $this->resolveCmisObjectByUuid($session, $uuid);
		} catch (CmisObjectNotFoundException $error) {
			$fields = $this->resolveStructuralFieldsForTable($table);
			$record = $this->loadRecordFromDatabase($table, $uid, $fields);
			$parentPageUid = (integer) $record['pid'];
			if (0 === $parentPageUid) {
				$parentFolder = $session->getRootFolder();
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
		$typeId = $session->getRootFolder()->getBaseType()->getId();
		$objects = $session->queryObjects($typeId, Constants::CMIS_PROPERTY_TYPO3UUID . '=' . $uuid);
		if (0 === count($objects)) {
			throw new CmisObjectNotFoundException();
		}
		return reset($objects);
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
		$session = $this->getCmisObjectFactory()->getSession();
		if ('pages' === $table) {
			return $session->getTypeDefinition('cmis:folder');
		}
		return $session->getTypeDefinition('cmis:document');
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
		$extractionDetector = new ExtractionMethodDetector();
		$record = $this->loadRecordFromDatabase($table, $uid, $columns);
		$recordAnalyzer = new RecordAnalyzer($table, $record);
		foreach ($record as $column => $value) {
			if (FALSE === array_key_exists($column, $values)) {
				$values[$column] = $extractionDetector->resolveExtractionForColumn($table, $column)->extract($value);
			}
		}
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
		$properties = $this->extractPropertiesForTableAndUid($table);
		$properties[Constants::CMIS_PROPERTY_TYPO3TABLE] = $table;
		$properties[Constants::CMIS_PROPERTY_TYPO3UUID] = $uuid;
		$properties[PropertyIds::OBJECT_TYPE_ID] = $type->getId();
		if (TRUE === $type instanceof FolderTypeDefinition) {
			$objectId = $session->createFolder($properties, $folder);
		} elseif (TRUE === $type instanceof DocumentTypeDefinition) {
			$objectId = $session->createDocument($properties, $folder);
		}
		// @TODO: store generic resources for which there is no type, inside else {}
		return $session->getObject($objectId);
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

}
