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

	/**
	 * If the resource used by this Task has an identifier,
	 * for example table records can be identified by `table:uid`,
	 * CMIS objects can be identified by their UUID, files by
	 * their filename, etc.
	 *
	 * Returning a value here allows checking for duplicate Tasks
	 * (for example the same Task running on the same record).
	 *
	 * @return mixed
	 */
	public function getResourceId();

	/**
	 * Get parameters associated with this filter.
	 *
	 * @return mixed
	 */
	public function getParameters();

}
