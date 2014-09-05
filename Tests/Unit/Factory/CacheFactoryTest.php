<?php
namespace Dkd\CmisService\Tests\Unit\Factory;

use Dkd\CmisService\Factory\CacheFactory;
use Dkd\CmisService\Queue\SimpleQueue;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class CacheFactoryTest
 */
class CacheFactoryTest extends UnitTestCase {

	/**
	 * Setup - initialize a very basic set of TYPO3 constants
	 * and include the ext_localconf.php file to generate the
	 * necessary cache definitions.
	 *
	 * @return void
	 */
	protected function setUp() {
		if (FALSE === defined('TYPO3_version')) {
			define('PATH_thisScript', realpath('vendor/typo3/cms/typo3/index.php'));
			Bootstrap::getInstance()->baseSetup('typo3/')->initializeClassLoader()
				->unregisterClassLoader();
			$GLOBALS['EXEC_TIME'] = time();
		}
		parent::setUp();
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function fetchCacheCallsExpectedMethodsWhenCacheExists() {
		$instance = $this->getMock('Dkd\\CmisService\\Factory\\CacheFactory', array('getCacheManager'), array(), '', FALSE);
		$cacheManager = $this->getMock('TYPO3\\CMS\\Core\\Cache\\CacheManager', array('getCache', 'hasCache'));
		$cacheManager->expects($this->at(0))->method('hasCache')
			->with(CacheFactory::CACHE_PREFIX . SimpleQueue::CACHE_IDENTITY)
			->will($this->returnValue(TRUE));
		$cacheManager->expects($this->at(1))->method('getCache')
			->with(CacheFactory::CACHE_PREFIX . SimpleQueue::CACHE_IDENTITY)
			->will($this->returnValue('marker'));
		$instance->expects($this->at(0))->method('getCacheManager')->will($this->returnValue($cacheManager));
		$return = $instance->fetchCache(SimpleQueue::CACHE_IDENTITY);
	}

}
