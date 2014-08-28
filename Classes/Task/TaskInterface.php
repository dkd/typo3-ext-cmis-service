<?php
namespace Dkd\CmisService\Task;

use Dkd\CmisService\Queue\WorkerInterface;
use Dkd\CmisService\Execution\ExecutionInterface;

/**
 * Interface TaskInterface
 *
 * @package Dkd\CmisService\Task
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
	 * Assigns a Worker which will execute this Task
	 *
	 * @param WorkerInterface $worker
	 * @return void
	 */
	public function assign(WorkerInterface $worker);

	/**
	 * Determine, instanciate and return an Execution
	 * befitting this Task, possibly conditioned by
	 * parameters defined in the Task.
	 *
	 * @return ExcecutionInterface
	 */
	public function resolveExecutionObject();

}
