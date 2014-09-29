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
	 * Evict a document from the index.
	 *
	 * @param EvictionTask $task
	 * @return Result
	 */
	public function execute(TaskInterface $task) {
		/** @var EvictionTask $task */
		$this->result = $this->createResultObject();
		return $this->result;
	}

}
