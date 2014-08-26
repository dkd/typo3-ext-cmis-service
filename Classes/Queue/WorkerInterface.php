<?php
namespace Dkd\CmisService\Queue;

use Dkd\CmisService\Task\TaskInterface;
use Dkd\CmisService\Execution\Result;

/**
 * Interface for Workers
 *
 * @package Dkd\CmisService\Queue
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
