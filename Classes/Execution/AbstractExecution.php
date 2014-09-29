<?php
namespace Dkd\CmisService\Execution;

use Dkd\CmisService\Task\TaskInterface;

/**
 * Base class for Executions
 */
abstract class AbstractExecution implements ExecutionInterface {

	/**
	 * @var Result
	 */
	protected $result;

	/**
	 * Returns the Result stored in this Execution
	 * after it has been executed.
	 *
	 * @return Result
	 */
	public function getResult() {
		return $this->result;
	}

	/**
	 * Creates a pre-configured instance of Result
	 * which can be post-processed and returned after
	 * execution has ended.
	 *
	 * @return Result
	 */
	protected function createResultObject() {
		$result = new Result();
		return $result;
	}

	/**
	 * @param string $table
	 * @param integer $uid
	 * @param array $fields
	 * @return array|FALSE
	 */
	protected function loadRecordFromDatabase($table, $uid, array $fields) {
		$fieldList = implode(',', $fields);
		return $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow($fieldList, $table, "uid = '" . $uid . "'");
	}

	/**
	 * Run this execution, returning the Result hereof.
	 *
	 * @param TaskInterface $task
	 * @return Result
	 */
	public function execute(TaskInterface $task) {
		return $this->result = $this->createResultObject();
	}

}
