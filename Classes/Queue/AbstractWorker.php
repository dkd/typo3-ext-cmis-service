<?php
namespace Dkd\CmisService\Queue;

use Dkd\CmisService\Task\TaskInterface;

/**
 * Worker base class
 *
 * Contains the most basic possible implementation of
 * a Task + Execution based task handling.
 *
 * @package Dkd\CmisService\Queue
 */
abstract class AbstractWorker implements WorkerInterface {

	/**
	 * Execute Task given in argument by internally resolving
	 * an Execution befitting the Task and then executing
	 * the Task via this Execution.
	 *
	 * @param TaskInterface $task
	 * @return Result
	 */
	public function execute(TaskInterface $task) {
		return $task->resolveExecutionObject()->execute($task);
	}

}
