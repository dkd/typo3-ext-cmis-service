<?php
namespace Dkd\CmisService\Queue;

use Dkd\CmisService\Cache\VariableFrontendInterface;
use Dkd\CmisService\Factory\WorkerFactory;
use Dkd\CmisService\Task\TaskFilterInterface;
use Dkd\CmisService\Task\TaskInterface;
use TYPO3\CMS\Core\Locking\Locker;

/**
 * Simple Queue
 * ============
 *
 * Implementation of a Queue based on a cached list of
 * Tasks which will be serialized and implementing
 * locking of write operations to preserve integrity.
 *
 * Any consumer can "pick" a Task which is then assigned
 * a Worker instance and returned. The execution of the
 * task is then handled in the consumer's context using
 * an Execution instance.
 *
 * Once "picked" the Task is removed from the Queue.
 *
 * Usage example
 * =============
 *
 * // ADDING TASKS (record indexing Task as example):
 * $taskFactory = new TaskFactory();
 * $queueFactory = new QueueFactory();
 * $task = $taskFactory->createRecordIndexingTask();
 * $task->setParameter('foobar', 'baz');
 * $queue = $queueFactory->fetchQueue();
 * $queue->add($task);
 * // alternative: create array of Tasks and use addAll($tasks).
 *
 *
 * // PICKING AND EXECUTING TASKS:
 * $factory = new QueueFactory();
 * $queue = $factory->fetchQueue();
 * $task = $queue->pick();
 *
 * // dummy example execution without ANY handling.
 * // $result is a Dkd\CmisService\Execution\Result
 * $result = $task->getWorker()->execute($task);
 */
class SimpleQueue implements QueueInterface, QueueCachableInterface {

	// Constants from Locker::FOOBAR possible, empty string for sys default
	const LOCK_METHOD = '';
	const LOCK_ID = 'dkd.cmisservice.queueLock';
	const MAX_LOCK_WAIT = 120;
	const CACHE_IDENTITY = 'SimpleQueue';

	/**
	 * @var TaskInterface[]
	 */
	protected $queue = array();

	/**
	 * @var VariableFrontendInterface
	 */
	protected $cache;

	/**
	 * @var Locker
	 */
	protected $locker;

	/**
	 * Prepare this instance with an instance of a "Cache" which
	 * supports saving a serialized state of the internal data
	 * storage to a persistent storage, by default a DB table.
	 *
	 * @api
	 * @param VariableFrontendInterface $frontend
	 * @return void
	 */
	public function setCache(VariableFrontendInterface $frontend) {
		$this->cache = $frontend;
	}

	/**
	 * Adds a Task to the Queue. Internally, locks the Queue
	 * for write operation and stores the updated (cached)
	 * value, then releases the lock.
	 *
	 * @api
	 * @param TaskInterface $task
	 * @return void
	 */
	public function add(TaskInterface $task) {
		$id = $task->getId();
		$this->lock();
		$task->queue();
		$this->queue[$id] = $task;
		$this->save();
		$this->release();
	}

	/**
	 * Adds all Tasks in $tasks as a bulk operation, locking
	 * the write operation before adding all Tasks to the
	 * Queue and saving, then releasing.
	 *
	 * @api
	 * @param TaskInterface[] $task
	 * @return void
	 */
	public function addAll(array $tasks) {
		$this->lock();
		foreach ($tasks as $task) {
			$id = $task->getId();
			$task->queue();
			$this->queue[$id] = $task;
		}
		$this->save();
		$this->release();
	}

	/**
	 * Picks (for execution) the first-added (FIFO principle)
	 * Task from the Queue, removing it from the internal data
	 * storage in the process. Assigns a Worker instance to
	 * the Task which is then prepared for actual execution
	 * via the Execution instance returned from the Task.
	 *
	 * @api
	 * @return TaskInterface|NULL
	 */
	public function pick() {
		if (0 === count($this->queue)) {
			return NULL;
		}
		$this->lock();
		/** @var TaskInterface $task */
		$task = array_shift($this->queue);
		$this->save();
		$this->release();
		$factory = new WorkerFactory();
		$worker = $factory->createWorker();
		$task->assign($worker);
		return $task;
	}

	/**
	 * Saves the current state of the Queue by delegating to
	 * a "Cache" implementation, by default utilizing a DB
	 * table storage in TYPO3 CMS.
	 *
	 * @api
	 * @return void
	 */
	public function save() {
		$this->lock();
		$this->cache->set(self::CACHE_IDENTITY, $this->queue);
		$this->release();
	}

	/**
	 * Loads the cached state of the Queue into this instance's
	 * internal storage. Subsequent calls will re-load the data
	 * and overwrite what is currently loaded.
	 *
	 * @api
	 * @return void
	 */
	public function load() {
		if (TRUE === $this->cache->has(self::CACHE_IDENTITY)) {
			$this->queue = $this->cache->get(self::CACHE_IDENTITY);
		}
	}

	/**
	 * @return integer
	 */
	public function count() {
		return count($this->queue);
	}

	/**
	 * Flushes every Task from the Queue.
	 *
	 * @api
	 * @return void
	 */
	public function flush() {
		$this->queue = array();
		$this->save();
	}

	/**
	 * @param TaskFilterInterface $filter
	 * @return void
	 */
	public function flushByFilter(TaskFilterInterface $filter) {
		foreach ($this->queue as $index => $task) {
			if (TRUE === $task->matches($filter)) {
				unset($this->queue[$index]);
			}
		}
		$this->save();
	}

	/**
	 * Gets the Locker used by the Queue
	 *
	 * @return Locker
	 */
	protected function getLocker() {
		if (FALSE === $this->locker instanceof Locker) {
			$this->locker = new Locker(self::LOCK_ID, self::LOCK_METHOD, self::MAX_LOCK_WAIT);
		}
		return $this->locker;
	}

	/**
	 * Lock the queue, protecting it from updates
	 *
	 * @return boolean
	 */
	protected function lock() {
		return $this->getLocker()->acquireExclusiveLock();
	}

	/**
	 * Release an obtained lock
	 *
	 * @return boolean
	 */
	protected function release() {
		return $this->getLocker()->release();
	}

	/**
	 * Returns TRUE if the queue is currently write-locked
	 *
	 * @return boolean
	 */
	protected function isLocked() {
		return $this->getLocker()->isLocked();
	}

}
