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
	const OPTION_RELATIONS = 'relations';

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
		$matchesRelations = $task->getParameter(self::OPTION_RELATIONS) === $this->getParameter(self::OPTION_RELATIONS);
		return ($matchesTable && $matchesUid && $matchesFields && $matchesRelations);
	}

	/**
	 * Returns the `table:uid` format identifying the
	 * record being indexed.
	 *
	 * @return string
	 */
	public function getResourceId() {
		return $this->getParameter(self::OPTION_TABLE) . ':' . $this->getParameter(self::OPTION_UID);
	}

}
