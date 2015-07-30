<?php
namespace Dkd\CmisService\Factory;

use Dkd\CmisService\Configuration\Definitions\ImplementationConfiguration;
use Dkd\CmisService\Queue\QueueCachableInterface;
use Dkd\CmisService\Queue\QueueInterface;

/**
 * Queue Factory
 *
 * Fetches an instance of the Queue implementation the
 * host system has configured as active, preparing it
 * for use.
 */
class QueueFactory {

	const DEFAULT_QUEUE_CLASS = 'Dkd\\CmisService\\Queue\\DatabaseTableQueue';

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
		$queueFactory = new QueueFactory();
		self::$instance = new $className();
		if (TRUE === self::$instance instanceof QueueCachableInterface) {
			$cacheFactory = new CacheFactory();
			$queueCache = $cacheFactory->fetchCache($className::CACHE_IDENTITY);
			self::$instance->setCache($queueCache);
			self::$instance->load();
		}
		return self::$instance;
	}

	/**
	 * Gets the currently configured Queue implementation class
	 * name or, if none is defined, the default implementation.
	 *
	 * @return string
	 */
	protected function getConfiguredQueueClassName() {
		$className = $this->getObjectFactory()
			->getConfiguration()
			->getImplementationConfiguration()
			->get(ImplementationConfiguration::OBJECT_CLASS_QUEUE);
		return $className;
	}

	/**
	 * @return ObjectFactory
	 */
	protected function getObjectFactory() {
		return new ObjectFactory();
	}

}
