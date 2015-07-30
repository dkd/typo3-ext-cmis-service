<?php
namespace Dkd\CmisService\Task;

use Dkd\CmisService\Queue\WorkerInterface;

/**
 * Abstract: Task
 *
 * A shared base class for Tasks. Contains methods
 * and properties shared by Tasks which get processed
 * via the queue and workers. Task classes extending
 * this base class also implement TaskInterface.
 */
abstract class AbstractTask implements TaskInterface {

	/**
	 * Automatically generated ID of this task, preserved
	 * upon serializing the task.
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * When a Worker is processing this Task, a reference
	 * to the instance is stored in this property.
	 *
	 * @var WorkerInterface
	 */
	protected $worker = NULL;

	/**
	 * Optional, additional parameters for this Task.
	 *
	 * @var array
	 */
	protected $parameters = array();

	/**
	 * @var integer
	 */
	protected $status = TaskInterface::STATUS_NONE;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
		$this->id = uniqid('cmis-task-');
	}

	/**
	 * If the resource used by this Task has an identifier,
	 * for example table records can be identified by `table:uid`,
	 * CMIS objects can be identified by their UUID, files by
	 * their filename, etc.
	 *
	 * Returning a value here allows checking for duplicate Tasks
	 * (for example the same Task running on the same record).
	 *
	 * This default implementation always returns NULL and must be
	 * implemented if a Task operates on an identified resource.
	 *
	 * @return mixed
	 */
	public function getResourceId() {
		return NULL;
	}

	/**
	 * Returns the unique, automatically generated ID
	 * of this Task instance.
	 *
	 * @return string
	 * @api
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Updates the internal status to "Running".
	 *
	 * @return void
	 * @api
	 */
	public function start() {
		$this->status = TaskInterface::STATUS_RUNNING;
	}

	/**
	 * Updates the internal status to "Queued".
	 *
	 * @return void
	 * @api
	 */
	public function queue() {
		$this->status = TaskInterface::STATUS_QUEUED;
	}

	/**
	 * Validate the arguments provided to the Task.
	 *
	 * @return boolean
	 * @throws \InvalidArgumentException
	 */
	public function validate() {
		return TRUE;
	}

	/**
	 * Finishes the task - unassigns the Worker and updates the
	 * status to "Done". Task execution implies that if an error
	 * has occurred, this method is not reached.
	 *
	 * @return void
	 * @api
	 */
	public function finish() {
		$this->worker = NULL;
		$this->status = TaskInterface::STATUS_DONE;
	}

	/**
	 * Assigns a Worker to handle this Task. Internally, updates
	 * the status to "Assigned" and sets the Task as a reference
	 * on the Worker instance to relate them 1:1 with references
	 * in each direction.
	 *
	 * @param WorkerInterface $worker
	 * @return void
	 * @api
	 */
	public function assign(WorkerInterface $worker) {
		$this->worker = $worker;
		$this->status = TaskInterface::STATUS_ASSIGNED;
	}

	/**
	 * Gets the assigned Worker or NULL if one is not set.
	 *
	 * @return WorkerInterface
	 */
	public function getWorker() {
		return $this->worker;
	}

	/**
	 * Returns TRUE if the Task is currently running.
	 *
	 * @return boolean
	 * @api
	 */
	public function isRunning() {
		return TaskInterface::STATUS_RUNNING === $this->status;
	}

	/**
	 * Returns TRUE if the Task is queued but not assigned.
	 *
	 * @return boolean
	 * @api
	 */
	public function isQueued() {
		return TaskInterface::STATUS_QUEUED === $this->status;
	}

	/**
	 * Returns TRUE if the Task is currently assigned to a Worker.
	 *
	 * @return boolean
	 * @api
	 */
	public function isAssigned() {
		return TaskInterface::STATUS_ASSIGNED === $this->status;
	}

	/**
	 * Gets a parameter from the internal storage.
	 *
	 * @param string $name
	 * @return mixed
	 * @api
	 */
	public function getParameter($name) {
		return TRUE === isset($this->parameters[$name]) ? $this->parameters[$name] : NULL;
	}

	/**
	 * Get parameters associated with this Task. We sort the
	 * parameters by name so the resulting array always has the
	 * same order and always appear the same way when serialized,
	 * debugged, passed between methods, used in vsprintf etc.
	 *
	 * @return array
	 */
	public function getParameters() {
		$parameters = $this->parameters;
		ksort($parameters);
		return $parameters;
	}

	/**
	 * Sets optional parameters for this Task and/or the execution
	 * of this Task.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 * @api
	 */
	public function setParameter($name, $value) {
		$this->parameters[$name] = $value;
	}

}
