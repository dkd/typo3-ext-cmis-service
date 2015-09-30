<?php
namespace Dkd\CmisService\Execution\Cmis;

use Dkd\CmisService\Analysis\Detection\ExtractionMethodDetector;
use Dkd\CmisService\Analysis\RecordAnalyzer;
use Dkd\CmisService\Analysis\TableConfigurationAnalyzer;
use Dkd\CmisService\Constants;
use Dkd\CmisService\Execution\Exception;
use Dkd\CmisService\Execution\ExecutionInterface;
use Dkd\CmisService\Execution\Result;
use Dkd\CmisService\Factory\ObjectFactory;
use Dkd\CmisService\Task\RecordIndexTask;
use Dkd\CmisService\Task\TaskInterface;
use Dkd\PhpCmis\CmisObject\CmisObjectInterface;
use Dkd\PhpCmis\Exception\CmisObjectNotFoundException;
use Dkd\PhpCmis\PropertyIds;

/**
 * Class IndexExecution
 */
class IndexExecution extends AbstractCmisExecution implements ExecutionInterface {

	/**
	 * Contexts passed to Logger implementations when messages
	 * are dispatched from this class.
	 *
	 * @var array
	 */
	protected $logContexts = array('cmis_service', 'execution', 'cmis', 'indexing');

	/**
	 * @param TaskInterface $task
	 * @return boolean
	 */
	public function validate(TaskInterface $task) {
		if (FALSE === $task instanceof RecordIndexTask) {
			throw new \InvalidArgumentException(
				'Error in CMIS IndexExecution during Task validation. ' .
				'Task must be a Dkd\\CmisService\\Task\\RecordIndexTask or subclass; we received a ' . get_class($task));
		}
		return TRUE;
	}

	/**
	 * Index a record, creating a document in the index.
	 *
	 * @param RecordIndexTask $task
	 * @return Result
	 * @throws Exception
	 */
	public function execute(TaskInterface $task) {
		$objectFactory = new ObjectFactory();
		/** @var RecordIndexTask $task */
		$this->result = $this->createResultObject();
		$fields = (array) $task->getParameter(RecordIndexTask::OPTION_FIELDS);
		$table = $task->getParameter(RecordIndexTask::OPTION_TABLE);
		$uid = $task->getParameter(RecordIndexTask::OPTION_UID);
		$record = $this->loadRecordFromDatabase($table, $uid, $fields);
		if (NULL === $record) {
			throw new Exception(
				sprintf(
					'Record %d from table %s not loaded. Record removed or invalid fields requested? (fields requested: %s)',
					$uid,
					$table,
					implode(', ', $fields)
				)
			);
		}
		$data = array();
		foreach ($fields as $fieldName) {
			$data[$fieldName] = $this->performTextExtraction($table, $uid, $fieldName, $record);
		}
		$recordAnalyzer = new RecordAnalyzer($table, $record);
		$cmisPropertyValues = $this->remapFieldsToDocumentProperties($data, $recordAnalyzer);
		$document = $this->getCmisService()->resolveObjectByTableAndUid($table, $uid);
		$document->updateProperties($cmisPropertyValues);
		if (TRUE === (boolean) $task->getParameter(RecordIndexTask::OPTION_RELATIONS)) {
			// The Task was configured to also index the relations from
			// this document to other CMIS documents (which have already
			// been indexed by a previous Task). We therefore now turn
			// all TYPO3 relations into CMIS relationships in a sync-type
			// manner; both creating and removing relationships as needed.
			$this->synchronizeRelationships($document, $recordAnalyzer, $data);
		}
		$this->result->setCode(Result::OK);
		$this->result->setMessage('Indexed record ' . $uid . ' from ' . $table);
		$this->result->setPayload($data);
		return $this->result;
	}

	/**
	 * Synchronizes the relationships between $document and other
	 * CMIS objects, as detected by the data that was extracted.
	 *
	 * @param CmisObjectInterface $document CMIS object to use in relationships
	 * @param RecordAnalyzer $recordAnalyzer An instance of RecordAnalyzer for record
	 * @param array $data The extracted data not yet mapped to CMIS properties.
	 * @return void
	 */
	protected function synchronizeRelationships(CmisObjectInterface $document, RecordAnalyzer $recordAnalyzer, array $data) {
		$tableConfigurationAnalyzer = new TableConfigurationAnalyzer();
		$objectFactory = $this->getObjectFactory();
		$table = $recordAnalyzer->getTable();
		$logger = $objectFactory->getLogger();
		foreach ($recordAnalyzer->getIndexableColumnNames() as $fieldName) {
			$columnAnalyzer = $tableConfigurationAnalyzer->getColumnAnalyzerForField($table, $fieldName);
			if ($columnAnalyzer->isFieldDatabaseRelation()) {
				$relationData = $recordAnalyzer->getRelationDataForColumn($fieldName);
				$targetUids = $relationData->getTargetUids();
				$targetTable = $relationData->getTargetTable();
				if ($objectFactory->getConfiguration()->getTableConfiguration()->isTableEnabled($targetTable)) {
					$logger->warning(
						sprintf(
							'Table %s is not configured for indexing; this relation cannot be indexed!',
							$table
						)
					);
					continue;
				}
				$session = $this->getCmisObjectFactory()->getSession();
				foreach ($targetUids as $targetUid) {
					try {
						$cmisObjectId = $objectFactory->getCmisService()->getUuidForLocalRecord($table, $targetUid);
						$foreignObject = $session->getObject($session->createObjectId($cmisObjectId));
						$session->createRelationship(array(
							PropertyIds::SOURCE_ID => $document->getId(),
							PropertyIds::TARGET_ID => $foreignObject->getId()
						));
					} catch (CmisObjectNotFoundException $error) {
						$logger->info(
							sprintf(
								'Record %d from table %s is not yet indexed by CMIS',
								$targetUid,
								$targetTable
							)
						);
					}
				}
			} elseif ($columnAnalyzer->isFieldLegacyFileReference()) {
				// @TODO: detect the already "imported" legacy file placed in CMIS
				// Extracted by LegacyFileReferenceExtractor which executed during the
				// first indexing step (without relations). We now need these as UUIDs
				// that can be used in relationships with the CMIS document.
			}
		}
	}

	/**
	 * Maps properties which require mapping, translating
	 * the name from the TYPO3 column name into a CMIS
	 * Document property name.
	 *
	 * @param array $data
	 * @param RecordAnalyzer $recordAnalyzer
	 * @return array
	 */
	protected function remapFieldsToDocumentProperties(array $data, RecordAnalyzer $recordAnalyzer) {
		$record = $recordAnalyzer->getRecord();
		$table = $recordAnalyzer->getTable();
		$uid = $record['uid'];
		$values = array(
			PropertyIds::NAME => $recordAnalyzer->getTitleForRecord(),
			Constants::CMIS_PROPERTY_RAWDATA => serialize($data)
		);
		$parentUid = $record['pid'];
		if (0 < $parentUid) {
			$values[PropertyIds::PARENT_ID] = $this->getCmisService()
				->resolveObjectByTableAndUid('pages', $parentUid)->getId();
		}
		$propertyMap = $this->getObjectFactory()->getConfiguration()->getTableConfiguration()->getSingleTableMapping($table);
		foreach ($propertyMap as $recordProperty => $cmisPropertyId) {
			$values[$cmisPropertyId] = $this->performTextExtraction($table, $uid, $recordProperty, $record);
		}
		$values[Constants::CMIS_PROPERTY_FULLTITLE] = $values[PropertyIds::NAME];
		$values[PropertyIds::NAME] = $this->getCmisService()->sanitizeTitle($values[PropertyIds::NAME], $table . '-' . $uid);
		return $values;
	}

	/**
	 * @param string $table
	 * @param integer $uid
	 * @param string $field
	 * @oaram array $record
	 * @return string
	 */
	protected function performTextExtraction($table, $uid, $field, $record) {
		return $this->getExtractionMethodDetector()->resolveExtractionForColumn($table, $field)->extract($record[$field]);
	}

	/**
	 * @return ExtractionMethodDetector
	 */
	protected function getExtractionMethodDetector() {
		return new ExtractionMethodDetector();
	}

}
