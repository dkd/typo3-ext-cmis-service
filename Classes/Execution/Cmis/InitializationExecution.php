<?php
namespace Dkd\CmisService\Execution\Cmis;

use Dkd\CmisService\Execution\AbstractExecution;
use Dkd\CmisService\Execution\ExecutionInterface;
use Dkd\CmisService\Execution\Result;
use Dkd\CmisService\Task\InitializationTask;
use Dkd\CmisService\Task\TaskInterface;

/**
 * Class InitializationExecution
 */
class InitializationExecution extends AbstractExecution implements ExecutionInterface {

	/**
	 * Validates that this Task is an instance of
	 * the expected and supported class.
	 *
	 * @param TaskInterface $task
	 * @return boolean
	 * @throws \InvalidArgumentException
	 */
	public function validate(TaskInterface $task) {
		if (FALSE === $task instanceof InitializationTask) {
			throw new \InvalidArgumentException(
				'Error in CMIS IndexExecution during Task validation. ' .
				'Task must be a Dkd\\CmisService\\Task\\InitializationTask or subclass; we received a ' . get_class($task));
		}
		return TRUE;
	}

	/**
	 * Evict a document from the index.
	 *
	 * @param InitializationTask $task
	 * @return Result
	 */
	public function execute(TaskInterface $task) {
		/** @var EvictionTask $task */
		$this->result = $this->createResultObject();
		$this->result->setMessage('CMIS Repository initialized!');
		return $this->result;
	}

}
