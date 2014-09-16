<?php
namespace Dkd\CmisService\Task;

/**
 * Task Filter Interface
 *
 * Implemented by classes which can filter a Queue
 * by analysing each Task it contains. Can be implemented
 * by any class type, including Tasks, Executions, etc.
 */
interface TaskFilterInterface {

	/**
	 * Returns TRUE if this Filter matches the Task
	 *
	 * @param TaskInterface $task
	 * @return boolean
	 */
	public function matches(TaskInterface $task);

}
