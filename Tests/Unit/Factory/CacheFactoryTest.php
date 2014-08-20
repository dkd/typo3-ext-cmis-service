<?php
namespace Dkd\CmisService\Factory;

use Dkd\CmisService\Queue\SimpleQueue;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class CacheFactoryTest
 *
 * @package Dkd\CmisService\Factory
 */
class CacheFactoryTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function fetchCacheCallsExpectedMethods() {
		$instance = $this->getMock('Dkd\CmisService\Factory\CacheFactory', array('getCacheManager'), array(), '', FALSE);
		$cacheManager = $this->getMock('TYPO3\CMS\Core\Cache\CacheManager', array('getCache'));
		$cacheManager->expects($this->at(0))->method('getCache')->with(CacheFactory::CACHE_PREFIX . SimpleQueue::CACHE_IDENTITY)->will($this->returnValue('marker'));
		$instance->expects($this->at(0))->method('getCacheManager')->will($this->returnValue($cacheManager));
		$return = $instance->fetchCache(SimpleQueue::CACHE_IDENTITY);
	}

}
