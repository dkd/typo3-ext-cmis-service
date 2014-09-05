<?php
namespace Dkd\CmisService\Queue;

use Dkd\CmisService\Execution\Result;
use Dkd\CmisService\Task\TaskInterface;

/**
 * Interface for Workers
 */
interface WorkerInterface {

	/**
	 * Tells this Worker to execute Task $task
	 *
	 * @param TaskInterface $task
	 * @return Result
	 */
	public function execute(TaskInterface $task);

}
