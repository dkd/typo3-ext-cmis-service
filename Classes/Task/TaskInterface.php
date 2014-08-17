<?php
namespace Dkd\CmisService\Task;

use Dkd\CmisService\Queue\WorkerInterface;
use Dkd\CmisService\Execution\ExecutionInterface;

interface TaskInterface {

	const STATUS_NONE = 0;
	const STATUS_QUEUED = 1;
	const STATUS_ASSIGNED = 2;
	const STATUS_RUNNING = 4;
	const STATUS_DONE = 8;
	const STATUS_ERROR = 32;

	/**
	 * If return value is TRUE,
	 *
	 * @return boolean
	 */
	public function isDeferred();

	/**
	 * @return boolean
	 */
	public function isRunning();

	/**
	 * @return boolean
	 */
	public function isQueued();

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function getParameter($name);

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function setParameter($name, $value);

}
