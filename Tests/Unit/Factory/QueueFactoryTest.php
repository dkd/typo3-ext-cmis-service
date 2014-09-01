<?php
namespace Dkd\CmisService\Factory;

use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class QueueFactoryTest
 *
 * @package Dkd\CmisService\Factory
 */
class QueueFactoryTest extends UnitTestCase {

	/**
	 * Setup
	 *
	 * @return void
	 */
	protected function setUp() {
		if (FALSE === defined('TYPO3_version')) {
			Bootstrap::getInstance()->baseSetup('vendor/typo3/cms/typo3');
		}
		parent::setUp();
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getConfiguredQueueClassNameReturnsValidClassName() {
		$factory = new QueueFactory();
		$className = $this->callInaccessibleMethod($factory, 'getConfiguredQueueClassName');
		$this->assertTrue(class_exists($className));
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function initializeQueueReturnsValidClassInstance() {
		$factory = new QueueFactory();
		$instance = $this->callInaccessibleMethod($factory, 'initializeQueue');
		$this->assertInstanceOf('Dkd\CmisService\Queue\QueueInterface', $instance);
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
		$factory = $this->getAccessibleMock('Dkd\CmisService\Factory\QueueFactory', array('initializeQueue'));
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
		$factory = $this->getAccessibleMock('Dkd\CmisService\Factory\QueueFactory', array('initializeQueue'));
		$factory->_setStatic('instance', $expectedQueueClassInstance);
		$factory->expects($this->never())->method('initializeQueue');
		$queue = $factory->fetchQueue();
		$this->assertInstanceOf($expectedQueueClassName, $queue);
		$this->assertSame($expectedQueueClassInstance, $queue);
	}

}
