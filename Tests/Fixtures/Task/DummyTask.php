<?php
namespace Dkd\CmisService\Tests\Fixtures\Task;

use Dkd\CmisService\Task\AbstractTask;
use Dkd\CmisService\Task\ExcecutionInterface;
use Dkd\CmisService\Task\TaskFilterInterface;
use Dkd\CmisService\Task\TaskInterface;
use Dkd\CmisService\Tests\Fixtures\Execution\DummyExecution;

/**
 * Class DummyTask
 */
class DummyTask extends AbstractTask implements TaskInterface, TaskFilterInterface {

	/**
	 * Determine, instanciate and return an Execution
	 * befitting this Task, possibly conditioned by
	 * parameters defined in the Task.
	 *
	 * @return ExcecutionInterface
	 */
	public function resolveExecutionObject() {
		return new DummyExecution();
	}

	/**
	 * Returns TRUE if this Filter matches the Task
	 *
	 * @param TaskInterface $task
	 * @return boolean
	 */
	public function matches(TaskInterface $task) {
		return TRUE;
	}

}
