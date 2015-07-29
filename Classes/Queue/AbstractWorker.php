<?php
namespace Dkd\CmisService\Queue;

use Dkd\CmisService\Execution\Result;
use Dkd\CmisService\Factory\ObjectFactory;
use Dkd\CmisService\Task\TaskInterface;

/**
 * Worker base class
 *
 * Contains the most basic possible implementation of
 * a Task + Execution based task handling.
 */
abstract class AbstractWorker implements WorkerInterface {

	/**
	 * Contexts passed to Logger implementations when messages
	 * are dispatched from this class.
	 *
	 * @var array
	 */
	protected $logContexts = array('cmis_service', 'worker');

	/**
	 * Execute Task given in argument by internally resolving
	 * an Execution befitting the Task and then executing
	 * the Task via this Execution.
	 *
	 * @param TaskInterface $task
	 * @return Result
	 */
	public function execute(TaskInterface $task) {
		try {
			$execution = $task->resolveExecutionObject();
			$execution->validate($task);
			$result = $execution->execute($task);
		} catch (\InvalidArgumentException $error) {
			// we only catch misconfigured Tasks' errors
			// here and allow errors raised during execution
			// to bubble up, by not catching ->execute().
			$result = $this->createErrorResult($error);
		}
		$this->getObjectFactory()->getLogger()->log($result->getCode(), $result->getMessage(), $this->logContexts);
		return $result;
	}

	/**
	 * @param \Exception $error
	 * @return Result
	 */
	protected function createErrorResult(\Exception $error) {
		$result = new Result($error->getMessage(), Result::ERR, array($error));
		$result->setError($error);
		return $result;
	}

	/**
	 * @return ObjectFactory
	 */
	protected function getObjectFactory() {
		return new ObjectFactory();
	}

}
