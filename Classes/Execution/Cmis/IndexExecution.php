<?php
namespace Dkd\CmisService\Execution\Cmis;

use Dkd\CmisService\Analysis\Detection\ExtractionMethodDetector;
use Dkd\CmisService\Execution\ExecutionInterface;
use Dkd\CmisService\Execution\Result;
use Dkd\CmisService\Factory\ObjectFactory;
use Dkd\CmisService\Task\RecordIndexTask;
use Dkd\CmisService\Task\TaskInterface;

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
		$document = $this->resolveCmisDocumentByTableAndUid($table, $uid);
		foreach ($fields as $fieldName) {
			$data[$fieldName] = $this->performTextExtraction($task, $uid, $fieldName, $record);
		}
		$this->result->setCode(Result::OK);
		$this->result->setMessage('SIMULATED: Indexed record ' . $uid . ' from ' . $table);
		$this->result->setPayload($data);
		return $this->result;
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
