<?php
namespace Dkd\CmisService\Tests\Fixtures\Queue;

use Dkd\CmisService\Execution\Result;
use Dkd\CmisService\Queue\AbstractWorker;
use Dkd\CmisService\Queue\WorkerInterface;
use Dkd\CmisService\Task\TaskInterface;

/**
 * Class DummyWorker
 */
class DummyWorker extends AbstractWorker implements WorkerInterface {

	/**
	 * Mock
	 *
	 * @param TaskInterface $task
	 * @return Result
	 */
	public function execute(TaskInterface $task) {
		$result = $task->resolveExecutionObject()->execute($task);
		return $result;
	}

}
