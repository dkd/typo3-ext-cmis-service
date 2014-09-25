<?php
namespace Dkd\CmisService\Tests\Unit\Hook;

use Dkd\CmisService\Queue\SimpleQueue;
use Dkd\CmisService\Tests\Fixtures\Task\DummyTask;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use Dkd\CmisService\Hook\AbstractListener;

/**
 * Class AbstractListenerTest
 */
class AbstractListenerTest extends UnitTestCase {

	/**
	 * @var AbstractListener|\PHPUnit_Framework_MockObject
	 */
	protected $mock;

	/**
	 * Setup before each test
	 *
	 * @return void
	 */
	public function setUp() {
		$this->mock = $this->getMock(
			'Dkd\\CmisService\\Hook\\AbstractListener',
			array('initializeMonitoredTables'),
			array(),
			'',
			FALSE
		);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function constructorCallsInitializeMonitoredTables() {
		$mock = $this->getMockForAbstractClass(
			'Dkd\\CmisService\\Hook\\AbstractListener',
			array(),
			'',
			FALSE,
			FALSE,
			TRUE,
			array('initializeMonitoredTables')
		);
		$mock->expects($this->once())->method('initializeMonitoredTables');
		$mock->__construct();
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function initializeMonitoredTablesCallsIndexableTableDetectorToResolveTableNames() {
		$mockDetector = $this->getMock(
			'Dkd\\CmisService\\Analysis\\Detection\\IndexableTableDetector',
			array('getEnabledTableNames')
		);
		$mockDetector->expects($this->once())->method('getEnabledTableNames')->will($this->returnValue('foobar'));
		$mock = $this->getMockForAbstractClass(
			'Dkd\\CmisService\\Hook\\AbstractListener',
			array(),
			'',
			FALSE,
			FALSE,
			TRUE,
			array('getIndexableTableDetector')
		);
		$mock->expects($this->once())->method('getIndexableTableDetector')->will($this->returnValue($mockDetector));
		$this->callInaccessibleMethod($mock, 'initializeMonitoredTables');
		$this->assertObjectHasAttribute('monitoredTables', $mock);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function isTableMonitoredChecksInternalArray() {
		$mock = $this->getAccessibleMockForAbstractClass('Dkd\\CmisService\\Hook\\AbstractListener', array(), '', FALSE);
		$mock->_setStatic('monitoredTables', array('foobartable'));
		$this->assertTrue($mock->_call('isTableMonitored', 'foobartable'));
		$this->assertFalse($mock->_call('isTableMonitored', 'baztable'));
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getObjectFactoryReturnsObjectFactoryInstance() {
		$instance = $this->callInaccessibleMethod($this->mock, 'getObjectFactory');
		$this->assertInstanceOf('Dkd\\CmisService\\Factory\\ObjectFactory', $instance);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getTaskFactoryReturnsTaskFactoryInstance() {
		$instance = $this->callInaccessibleMethod($this->mock, 'getTaskFactory');
		$this->assertInstanceOf('Dkd\\CmisService\\Factory\\TaskFactory', $instance);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getQueueFactoryReturnsQueueFactoryInstance() {
		$instance = $this->callInaccessibleMethod($this->mock, 'getQueueFactory');
		$this->assertInstanceOf('Dkd\\CmisService\\Factory\\QueueFactory', $instance);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getQueueCallsGetQueueFactoryToFetchQueue() {
		$queue = new SimpleQueue();
		$mockQueueFactory = $this->getMock('Dkd\\CmisService\\Factory\\QueueFactory', array('fetchQueue'));
		$mockQueueFactory->expects($this->once())->method('fetchQueue')->will($this->returnValue($queue));
		$mock = $this->getMockForAbstractClass(
			'Dkd\\CmisService\\Hook\\AbstractListener',
			array(),
			'',
			FALSE,
			FALSE,
			TRUE,
			array('getQueueFactory')
		);
		$mock->expects($this->once())->method('getQueueFactory')->will($this->returnValue($mockQueueFactory));
		$result = $this->callInaccessibleMethod($mock, 'getQueue');
		$this->assertSame($queue, $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getIndexableTableDetectorReturnsIndexableTableDetectorInstance() {
		$instance = $this->callInaccessibleMethod($this->mock, 'getIndexableTableDetector');
		$this->assertInstanceOf('Dkd\\CmisService\\Analysis\\Detection\\IndexableTableDetector', $instance);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function removeAllIndexingTasksForTableAndUidFiltersQueueByParameters() {
		$table = 'tt_content';
		$uid = 123;
		$mockTask = new DummyTask();
		$mockTaskFactory = $this->getMock('Dkd\\CmisService\\Factory\\TaskFactory', array('createRecordIndexingTask'));
		$mockQueue = $this->getMock('Dkd\\CmisService\\Queue\\SimpleQueue', array('flushByFilter'));
		$mockQueue->expects($this->once())->method('flushByFilter')->with($mockTask);
		$mockTaskFactory->expects($this->once())->method('createRecordIndexingTask')
			->with($table, $uid)
			->will($this->returnValue($mockTask));
		$mock = $this->getMockForAbstractClass(
			'Dkd\\CmisService\\Hook\\AbstractListener',
			array(),
			'',
			FALSE,
			FALSE,
			TRUE,
			array('getTaskFactory', 'getQueue')
		);
		$mock->expects($this->once())->method('getTaskFactory')->will($this->returnValue($mockTaskFactory));
		$mock->expects($this->once())->method('getQueue')->will($this->returnValue($mockQueue));
		$this->callInaccessibleMethod($mock, 'removeAllIndexingTasksForTableAndUid', $table, $uid);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function createAndQueueEvictionTaskFlushesCurrentTasksAndQueuesNewTask() {
		$table = 'tt_content';
		$uid = 123;
		$mockTask = new DummyTask();
		$mockTaskFactory = $this->getMock('Dkd\\CmisService\\Factory\\TaskFactory', array('createEvictionTask'));
		$mockQueue = $this->getMock('Dkd\\CmisService\\Queue\\SimpleQueue', array('flushByFilter', 'add'));
		$mockQueue->expects($this->once())->method('flushByFilter')->with($mockTask);
		$mockQueue->expects($this->once())->method('add')->with($mockTask);
		$mockTaskFactory->expects($this->once())->method('createEvictionTask')
			->with($table, $uid)
			->will($this->returnValue($mockTask));
		$mock = $this->getMockForAbstractClass(
			'Dkd\\CmisService\\Hook\\AbstractListener',
			array(),
			'',
			FALSE,
			FALSE,
			TRUE,
			array('getTaskFactory', 'getQueue')
		);
		$mock->expects($this->once())->method('getTaskFactory')->will($this->returnValue($mockTaskFactory));
		$mock->expects($this->once())->method('getQueue')->will($this->returnValue($mockQueue));
		$this->callInaccessibleMethod($mock, 'createAndQueueEvictionTask', $table, $uid);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function createAndQueueIndexingTaskFlushesCurrentTasksAndQueuesNewTask() {
		$table = 'tt_content';
		$uid = 123;
		$mockTask = new DummyTask();
		$mockTaskFactory = $this->getMock('Dkd\\CmisService\\Factory\\TaskFactory', array('createRecordIndexingTask'));
		$mockQueue = $this->getMock('Dkd\\CmisService\\Queue\\SimpleQueue', array('flushByFilter', 'add'));
		$mockQueue->expects($this->once())->method('flushByFilter')->with($mockTask);
		$mockQueue->expects($this->once())->method('add')->with($mockTask);
		$mockTaskFactory->expects($this->once())->method('createRecordIndexingTask')
			->with($table, $uid)
			->will($this->returnValue($mockTask));
		$mock = $this->getMockForAbstractClass(
			'Dkd\\CmisService\\Hook\\AbstractListener',
			array(),
			'',
			FALSE,
			FALSE,
			TRUE,
			array('getTaskFactory', 'getQueue')
		);
		$mock->expects($this->once())->method('getTaskFactory')->will($this->returnValue($mockTaskFactory));
		$mock->expects($this->once())->method('getQueue')->will($this->returnValue($mockQueue));
		$this->callInaccessibleMethod($mock, 'createAndQueueIndexingTask', $table, $uid);
	}

}
