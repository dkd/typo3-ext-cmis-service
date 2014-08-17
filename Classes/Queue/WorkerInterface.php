<?php
namespace Dkd\CmisService\Queue;

use Dkd\CmisService\Task\TaskInterface;

interface WorkerInterface {

	/**
	 * @param TaskInterface $task
	 * @return void
	 */
	public function execute(TaskInterface $task);

	/**
	 * @param TaskInterface $task
	 * @return void
	 */
	public function setTask(TaskInterface $task);

	/**
	 * @return TaskInterface|NULL
	 */
	public function getTask();

}
