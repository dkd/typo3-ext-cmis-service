<?php
namespace Dkd\CmisService\Factory;

use Dkd\CmisService\Queue\QueueInterface;
use Dkd\CmisService\Queue\SimpleQueue;

/**
 * Queue Factory
 *
 * Fetches an instance of the Queue implementation the
 * host system has configured as active, preparing it
 * for use.
 *
 * @package Dkd\CmisService\Factory
 */
class QueueFactory {

	const DEFAULT_QUEUE_CLASS = 'Dkd\\CmisService\\Queue\\SimpleQueue';

	/**
	 * @var QueueInterface
	 */
	protected static $instance;

	/**
	 * Fetches the Queue implementation configured as
	 * active by the host system. Queue instance is
	 * treated as single instance and instance is
	 * reused on subsequent fetches.
	 *
	 * @return QueueInterface
	 */
	public function fetchQueue() {
		if (TRUE === self::$instance instanceof QueueInterface) {
			return self::$instance;
		}
		return $this->initializeQueue();
	}

	/**
	 * Initialize a Queue implementation and return it.
	 *
	 * @return QueueInterface
	 */
	protected function initializeQueue() {
		$className = $this->getConfiguredQueueClassName();
		$objectFactory = new ObjectFactory();
		$cacheFactory = new CacheFactory();
		$queueFactory = new QueueFactory();
		$queueCache = $cacheFactory->fetchCache(SimpleQueue::CACHE_IDENTITY);
		self::$instance = new $className();
		self::$instance->setCache($queueCache);
		return self::$instance;
	}

	/**
	 * Gets the currently configured Queue implementation class
	 * name or, if none is defined, the default implementation.
	 *
	 * @return string
	 */
	protected function getConfiguredQueueClassName() {
		return self::DEFAULT_QUEUE_CLASS;
	}

}
