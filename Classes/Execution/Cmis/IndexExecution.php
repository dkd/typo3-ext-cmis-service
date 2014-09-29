<?php
namespace Dkd\CmisService\Execution\Cmis;

use Dkd\CmisService\Analysis\Detection\ExtractionMethodDetector;
use Dkd\CmisService\Execution\AbstractExecution;
use Dkd\CmisService\Execution\ExecutionInterface;
use Dkd\CmisService\Task\RecordIndexTask;
use Dkd\CmisService\Task\TaskInterface;

/**
 * Class IndexExecution
 */
class IndexExecution extends AbstractExecution implements ExecutionInterface {

	/**
	 * Index a record, creating a document in the index.
	 *
	 * @param RecordIndexTask $task
	 * @return Result
	 */
	public function execute(TaskInterface $task) {
		/** @var RecordIndexTask $task */
		$this->result = $this->createResultObject();
		$fields = (array) $task->getParameter(RecordIndexTask::OPTION_FIELDS);
		$table = $task->getParameter(RecordIndexTask::OPTION_TABLE);
		$uid = $task->getParameter(RecordIndexTask::OPTION_UID);
		$record = $this->loadRecordFromDatabase($table, $uid, $fields);
		foreach ($fields as $fieldName) {
			$this->performTextExtraction($task, $uid, $fieldName, $record);
		}
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
