<?php
namespace Dkd\CmisService\Factory;

use Dkd\CmisService\Cache\VariableFrontendInterface;
use TYPO3\CMS\Core\Cache\CacheManager;

/**
 * Cache Factory
 *
 * Fetches (with auto-creation) cache instances
 * by name, each cache instance implementing the
 * VariableFrontendInterface.
 *
 * This Factory uses both the caching Frontend and
 * Backend features of the host system whereas all
 * other parts of the CMIS Service extension uses
 * only the frontend (which has an interface
 * native to this extension in order to allow it
 * to be replaced with other implementations,
 * something that is not necessary to do with the
 * backend since a host system may not need one).
 *
 * @package Dkd\CmisService\Factory
 */
class CacheFactory {

	const CACHE_PREFIX = 'CmisService';
	const DEFAULT_FRONTEND = 'Dkd\CmisService\Cache\CmsVariableFrontend';
	const DEFAULT_BACKEND = 'TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend';

	/**
	 * Fetch (and create if not found) a Cache
	 * instance identified by $name.
	 *
	 * @param string $name
	 * @return VariableFrontendInterface
	 */
	public function fetchCache($name) {
		$cacheManager = $this->getCacheManager();
		$instance = $cacheManager->getCache(self::CACHE_PREFIX . $name);
		return $instance;
	}

	/**
	 * TYPO3 CMS specific: Get a CacheManager-implementation
	 * which can load Caching Framework caches - the default
	 * strategy used to expose a VariableFrontendInterface
	 * implementation with API methods to get/check/store
	 * persisted cache data.
	 *
	 * @return CacheManager
	 */
	protected function getCacheManager() {
		$objectFactory = new ObjectFactory();
		/** @var CacheManager $cacheManager */
		$cacheManager = $objectFactory->makeInstance('TYPO3\CMS\Core\Cache\CacheManager');
		return $cacheManager;
	}

}
