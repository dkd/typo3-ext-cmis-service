<?php
namespace Dkd\CmisService\Task;

use Dkd\CmisService\Factory\ExecutionFactory;

/**
 * Class RecordIndexTask
 */
class RecordIndexTask extends AbstractTask implements TaskInterface, TaskFilterInterface {

	const OPTION_TABLE = 'table';
	const OPTION_UID = 'uid';
	const OPTION_FIELDS = 'fields';

	/**
	 * Returns an Execution object for indexing the
	 * record as configured by Task's options.
	 *
	 * @return ExcecutionInterface
	 */
	public function resolveExecutionObject() {
		$executionFactory = new ExecutionFactory();
		return $executionFactory->createIndexExecution();
	}

	/**
	 * Returns TRUE if this Task matches $task
	 *
	 * @param TaskInterface $task
	 * @return boolean
	 */
	public function matches(TaskInterface $task) {
		$matchesTable = $task->getParameter(self::OPTION_TABLE) === $this->getParameter(self::OPTION_TABLE);
		$matchesUid = $task->getParameter(self::OPTION_UID) === $this->getParameter(self::OPTION_UID);
		$matchesFields = $task->getParameter(self::OPTION_FIELDS) === $this->getParameter(self::OPTION_FIELDS);
		return ($matchesTable && $matchesUid && $matchesFields);
	}

}
