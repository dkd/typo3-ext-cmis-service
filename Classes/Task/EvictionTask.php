<?php
namespace Dkd\CmisService\Task;

use Dkd\CmisService\Execution\Cmis\EvictionExecution;
use Dkd\CmisService\Factory\ExecutionFactory;

/**
 * Class EvictionTask
 */
class EvictionTask extends AbstractTask implements TaskInterface, TaskFilterInterface {

	const OPTION_TABLE = 'table';
	const OPTION_UID = 'uid';

	/**
	 * Determine, instanciate and return an Execution
	 * befitting this Task, possibly conditioned by
	 * parameters defined in the Task.
	 *
	 * @return EvictionExecution
	 */
	public function resolveExecutionObject() {
		$executionFactory = new ExecutionFactory();
		return $executionFactory->createEvictionExecution();
	}

	/**
	 * Returns TRUE if this $task matches $this
	 *
	 * @param TaskInterface $task
	 * @return boolean
	 */
	public function matches(TaskInterface $task) {
		$matchesTable = $task->getParameter(self::OPTION_TABLE) === $this->getParameter(self::OPTION_TABLE);
		$matchesUid = $task->getParameter(self::OPTION_UID) === $this->getParameter(self::OPTION_UID);
		$matchesAllUids = NULL === $this->getParameter(self::OPTION_UID);
		return ($matchesTable && ($matchesUid || $matchesAllUids));
	}

	/**
	 * Returns the `table:uid` format identifying the
	 * record being evicted; or returns the table name
	 * only, if the eviction is targeting all records
	 * from that table.
	 *
	 * @return string
	 */
	public function getResourceId() {
		$table = $this->getParameter(self::OPTION_TABLE);
		$uid = $this->getParameter(self::OPTION_UID);
		return (empty($uid) ? $table : $table . ':' . $uid);
	}

}
