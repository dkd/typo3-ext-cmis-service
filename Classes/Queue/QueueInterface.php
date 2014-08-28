<?php
namespace Dkd\CmisService\Queue;

use Dkd\CmisService\Task\TaskInterface;

/**
 * Queue Interface
 *
 * Must be implemented by system-configured Queue class(es).
 *
 * @package Dkd\CmisService\Queue
 */
interface QueueInterface {

	/**
	 * @param TaskInterface[] $task
	 * @return void
	 */
	public function addAll(array $tasks);

	/**
	 * @param TaskInterface $task
	 * @return void
	 */
	public function add(TaskInterface $task);

	/**
	 * @return TaskInterface
	 */
	public function pick();

	/**
	 * @return void
	 */
	public function save();

	/**
	 * @return void
	 */
	public function load();

}
