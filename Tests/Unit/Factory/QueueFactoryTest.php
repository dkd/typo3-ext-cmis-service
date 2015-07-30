<?php
namespace Dkd\CmisService\Tests\Unit\Factory;

use Dkd\CmisService\Configuration\Definitions\ImplementationConfiguration;
use Dkd\CmisService\Factory\QueueFactory;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class QueueFactoryTest
 */
class QueueFactoryTest extends UnitTestCase {

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
	public function getConfiguredQueueClassNameFetchesClassNameFromImplementationConfigurationViaObjectFactory() {
		$implementationConfiguration = $this->getMock(
			'Dkd\\CmisService\\Configuration\\Definitions\\MasterConfiguration',
			array('get')
		);
		$implementationConfiguration->expects($this->once())->method('get')
			->with(ImplementationConfiguration::OBJECT_CLASS_QUEUE)
			->will($this->returnValue('foobar'));
		$masterConfiguration = $this->getMock(
			'Dkd\\CmisService\\Configuration\\Definitions\\MasterConfiguration',
			array('getImplementationConfiguration')
		);
		$masterConfiguration->expects($this->once())->method('getImplementationConfiguration')
			->will($this->returnValue($implementationConfiguration));
		$objectFactory = $this->getMock('Dkd\\CmisService\\Factory\\QueueFactory', array('getConfiguration'));
		$objectFactory->expects($this->once())->method('getConfiguration')->will($this->returnValue($masterConfiguration));
		$factory = $this->getMock('Dkd\\CmisService\\Factory\\QueueFactory', array('getObjectFactory'));
		$factory->expects($this->once())->method('getObjectFactory')->will($this->returnValue($objectFactory));
		$className = $this->callInaccessibleMethod($factory, 'getConfiguredQueueClassName');
		$this->assertEquals('foobar', $className);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function initializeQueueReturnsValidClassInstance() {
		$factory = $this->getAccessibleMock('Dkd\\CmisService\\Factory\\QueueFactory', array('getConfiguredQueueClassName'));
		$factory->expects($this->once())->method('getConfiguredQueueClassName')
			->will($this->returnValue(QueueFactory::DEFAULT_QUEUE_CLASS));
		$instance = $this->callInaccessibleMethod($factory, 'initializeQueue');
		$this->assertInstanceOf('Dkd\\CmisService\\Queue\\QueueInterface', $instance);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function fetchQueueCallsExpectedMethodsAndReturnsValidClassInstance() {
		$expectedQueueClassName = QueueFactory::DEFAULT_QUEUE_CLASS;
		$expectedQueueClassInstance = new $expectedQueueClassName();
		$factory = $this->getAccessibleMock(
			'Dkd\\CmisService\\Factory\\QueueFactory',
			array('initializeQueue', 'getConfiguredQueueClassName')
		);
		$factory->_setStatic('instance', NULL);
		$factory->expects($this->once())->method('initializeQueue')->will($this->returnValue($expectedQueueClassInstance));
		$queue = $factory->fetchQueue();
		$this->assertInstanceOf($expectedQueueClassName, $queue);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function fetchQueueReturnsPreparedInstanceOnSubsequentUseIfInternalInstanceIsSet() {
		$expectedQueueClassName = QueueFactory::DEFAULT_QUEUE_CLASS;
		$expectedQueueClassInstance = new $expectedQueueClassName();
		$factory = $this->getAccessibleMock('Dkd\\CmisService\\Factory\\QueueFactory', array('initializeQueue'));
		$factory->_setStatic('instance', $expectedQueueClassInstance);
		$factory->expects($this->never())->method('initializeQueue');
		$queue = $factory->fetchQueue();
		$this->assertInstanceOf($expectedQueueClassName, $queue);
		$this->assertSame($expectedQueueClassInstance, $queue);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getObjectFactoryReturnsObjectFactoryInstance() {
		$queueFactory = new QueueFactory();
		$result = $this->callInaccessibleMethod($queueFactory, 'getObjectFactory');
		$this->assertInstanceOf('Dkd\\CmisService\\Factory\\ObjectFactory', $result);
	}

}
