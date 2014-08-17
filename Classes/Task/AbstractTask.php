<?php
namespace Dkd\CmisService\Task;

class AbstractTask {

	/**
	 * @var WorkerInterface
	 */
	protected $worker;

	/**
	 * @var array
	 */
	protected $parameters = array();

	/**
	 * @var integer
	 */
	protected $status = self::STATUS_NONE;

	/**
	 * @return boolean
	 */
	public function isRunning() {
		return TaskInterface::STATUS_RUNNING === $this->status;
	}

	/**
	 * @return boolean
	 */
	public function isQueued() {
		return TaskInterface::STATUS_QUEUED === $this->status;
	}

	/**
	 * @return boolean
	 */
	public function isAssigned() {
		return TaskInterface::STATUS_ASSIGNED === $this->status;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function getParameter($name) {
		return TRUE === isset($this->parameters[$name]) ? $this->parameters[$name] : NULL;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function setParameter($name, $value) {
		$this->parameters[$name] = $value;
	}

}
