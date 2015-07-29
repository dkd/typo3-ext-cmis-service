<?php
namespace Dkd\CmisService\Execution\Cmis;

use Dkd\CmisService\Execution\AbstractExecution;
use Dkd\CmisService\Execution\ExecutionInterface;
use Dkd\CmisService\Execution\Result;
use Dkd\CmisService\Task\EvictionTask;
use Dkd\CmisService\Task\TaskInterface;

/**
 * Class EvictionExecution
 */
class EvictionExecution extends AbstractExecution implements ExecutionInterface {

	/**
	 * Contexts passed to Logger implementations when messages
	 * are dispatched from this class.
	 *
	 * @var array
	 */
	protected $logContexts = array('cmis_service', 'execution', 'cmis', 'eviction');

	/**
	 * @param TaskInterface $task
	 * @return boolean
	 */
	public function validate(TaskInterface $task) {
		if (FALSE === $task instanceof EvictionTask) {
			throw new \InvalidArgumentException(
				'Error in CMIS IndexExecution during Task validation. ' .
				'Task must be a Dkd\\CmisService\\Task\\EvictionTask or subclass; we received a ' . get_class($task));
		}
		return TRUE;
	}

	/**
	 * Evict a document from the index.
	 *
	 * @param EvictionTask $task
	 * @return Result
	 */
	public function execute(TaskInterface $task) {
		/** @var EvictionTask $task */
		$this->result = $this->createResultObject();
		// @TODO: fill function
		return $this->result;
	}

}
