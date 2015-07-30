<?php
namespace Dkd\CmisService\Task;

use Dkd\CmisService\Execution\ExecutionInterface;
use Dkd\CmisService\Queue\WorkerInterface;

/**
 * Interface TaskInterface
 */
interface TaskInterface {

	const STATUS_NONE = 0;
	const STATUS_QUEUED = 1;
	const STATUS_ASSIGNED = 2;
	const STATUS_RUNNING = 4;
	const STATUS_DONE = 8;
	const STATUS_ERROR = 32;

	/**
	 * Returns TRUE if the task has status RUNNING
	 *
	 * @return boolean
	 */
	public function isRunning();

	/**
	 * Returns TRUE if the task has status QUEUED
	 *
	 * @return boolean
	 */
	public function isQueued();

	/**
	 * Get parameters associated with this Task.
	 *
	 * @return mixed
	 */
	public function getParameters();

	/**
	 * Gets the value of Task parameter $name
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function getParameter($name);

	/**
	 * Sets the value of Task parameter $name to $value
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function setParameter($name, $value);

	/**
	 * Gets the ID of this Task instance
	 *
	 * @return string
	 */
	public function getId();

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
	 * Called when Task gets added to Queue
	 *
	 * @return void
	 */
	public function queue();

	/**
	 * Called when Task starts executing
	 *
	 * @return void
	 */
	public function start();

	/**
	 * Called when Task finishes executing
	 *
	 * @return void
	 */
	public function finish();

	/**
	 * Validate the arguments provided to the Task.
	 *
	 * @return boolean
	 * @throws \InvalidArgumentException
	 */
	public function validate();

	/**
	 * Assigns a Worker which will execute this Task
	 *
	 * @param WorkerInterface $worker
	 * @return void
	 */
	public function assign(WorkerInterface $worker);

	/**
	 * Gets the assigned Worker or NULL if one is not set.
	 *
	 * @return WorkerInterface
	 */
	public function getWorker();

	/**
	 * Determine, instanciate and return an Execution
	 * befitting this Task, possibly conditioned by
	 * parameters defined in the Task.
	 *
	 * @return ExecutionInterface
	 */
	public function resolveExecutionObject();

}
