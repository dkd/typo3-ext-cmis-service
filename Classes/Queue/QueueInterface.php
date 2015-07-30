<?php
namespace Dkd\CmisService\Queue;

use Dkd\CmisService\Task\TaskFilterInterface;
use Dkd\CmisService\Task\TaskInterface;

/**
 * Queue Interface
 *
 * Must be implemented by system-configured Queue class(es).
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
	 * @return integer
	 */
	public function count();

	/**
	 * @return void
	 */
	public function flush();

	/**
	 * @param TaskFilterInterface $filter
	 * @return void
	 */
	public function flushByFilter(TaskFilterInterface $filter);

}
