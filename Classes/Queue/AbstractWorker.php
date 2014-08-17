<?php
namespace Dkd\CmisService\Queue;

use Dkd\CmisService\Task\TaskInterface;

class AbstractWorker {

	/**
	 * @var TaskInterface
	 */
	protected $task;

	/**
	 * @param TaskInterface $task
	 * @return void
	 */
	public function setTask(TaskInterface $task) {
		$this->task = $task;
	}

	/**
	 * @return TaskInterface
	 */
	public function getTask() {
		return $this->task;
	}

}
