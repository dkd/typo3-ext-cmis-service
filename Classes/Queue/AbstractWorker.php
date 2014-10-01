<?php
namespace Dkd\CmisService\Queue;

use Dkd\CmisService\Execution\Result;
use Dkd\CmisService\Task\TaskInterface;

/**
 * Worker base class
 *
 * Contains the most basic possible implementation of
 * a Task + Execution based task handling.
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
		$execution = $task->resolveExecutionObject();
		try {
			$execution->validate($task);
		} catch (\InvalidArgumentException $error) {
			// we only catch misconfigured Tasks' errors
			// here and allow errors raised during execution
			// to bubble up, by not catching ->execute().
			return $this->createErrorResult($error);
		}
		return $execution->execute($task);
	}

	/**
	 * @param \Exception $error
	 * @return Result
	 */
	protected function createErrorResult(\Exception $error) {
		$result = new Result($error->getMessage(), Result::ERR, array($error));
		return $result;
	}

}
