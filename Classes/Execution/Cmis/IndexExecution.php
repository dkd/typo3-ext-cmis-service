<?php
namespace Dkd\CmisService\Execution\Cmis;

use Dkd\CmisService\Analysis\Detection\ExtractionMethodDetector;
use Dkd\CmisService\Analysis\RecordAnalyzer;
use Dkd\CmisService\Constants;
use Dkd\CmisService\Execution\ExecutionInterface;
use Dkd\CmisService\Execution\Result;
use Dkd\CmisService\Factory\ObjectFactory;
use Dkd\CmisService\Task\RecordIndexTask;
use Dkd\CmisService\Task\TaskInterface;
use Dkd\PhpCmis\CmisObject\CmisObjectInterface;
use Dkd\PhpCmis\PropertyIds;

/**
 * Class IndexExecution
 */
class IndexExecution extends AbstractCmisExecution implements ExecutionInterface {

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
	 */
	public function execute(TaskInterface $task) {
		$objectFactory = new ObjectFactory();
		/** @var RecordIndexTask $task */
		$this->result = $this->createResultObject();
		$fields = (array) $task->getParameter(RecordIndexTask::OPTION_FIELDS);
		$table = $task->getParameter(RecordIndexTask::OPTION_TABLE);
		$uid = $task->getParameter(RecordIndexTask::OPTION_UID);
		$record = $this->loadRecordFromDatabase($table, $uid, $fields);
		$data = array();
		foreach ($fields as $fieldName) {
			$data[$fieldName] = $this->performTextExtraction($table, $uid, $fieldName, $record);
		}
		$recordAnalyzer = new RecordAnalyzer($table, $record);
		$cmisPropertyValues = $this->remapFieldsToDocumentProperties($data, $recordAnalyzer);
		$document = $this->resolveCmisDocumentByTableAndUid($table, $uid);
		$document->updateProperties($cmisPropertyValues);
		if (TRUE === (boolean) $task->getParameter(RecordIndexTask::OPTION_RELATIONS)) {
			// The Task was configured to also index the relations from
			// this document to other CMIS documents (which have already
			// been indexed by a previous Task). We therefore now turn
			// all TYPO3 relations into CMIS relationships in a sync-type
			// manner; both creating and removing relationships as needed.
			$this->synchronizeRelationships($document, $fields, $record, $data);
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
	 * @param array $fields Array of field names to process
	 * @param array $record The original TYPO3 record, untouched
	 * @param array $data The extracted data not yet mapped to CMIS properties.
	 * @return void
	 */
	protected function synchronizeRelationships(CmisObjectInterface $document, array $fields, array $record, array $data) {
		// @TODO: implement method
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
		$cmisPropertyValues = array(
			PropertyIds::NAME => $recordAnalyzer->getTitleForRecord(),
			Constants::CMIS_PROPERTY_RAWDATA => $data
		);
		return $cmisPropertyValues;
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
