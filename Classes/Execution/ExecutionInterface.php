<?php
namespace Dkd\CmisService\Execution;

use Dkd\CmisService\Task\TaskInterface;

/**
 * Interface for Executions
 *
 * Defines methods that must be implemented
 * by Execution implementations in order to
 * be usable in the system.
 */
interface ExecutionInterface {

	/**
	 * Returns the Result instance stored in this Execution
	 * after it has been executed.
	 *
	 * @return Result
	 */
	public function getResult();

	/**
	 * Run this Execution, returning the Result hereof.
	 *
	 * @param TaskInterface $task The task to be executed
	 * @return Result
	 */
	public function execute(TaskInterface $task);

}
