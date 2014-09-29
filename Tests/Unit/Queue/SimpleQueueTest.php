<?php
namespace Dkd\CmisService\Tests\Unit\Queue;

use Dkd\CmisService\Queue\SimpleQueue;
use Dkd\CmisService\Tests\Fixtures\Task\ErroringTask;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Locking\Locker;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class SimpleQueueTest
 */
class SimpleQueueTest extends UnitTestCase {

	/**
	 * Setup
	 *
	 * Checks the very first constant defined by the TYPO3 CMS
	 * initialization "Bootstrap" class. If the constant is not
	 * defined we assume a minimal environment must be created:
	 *
	 * - to define constants some TYPO3 CMS classes need
	 * - to initialize the TYPO3 CMS class loader _caches_
	 * - to unregister the actual class loader from TYPO3 CMS,
	 *   falling back to the Composer autoloading.
	 *
	 * The whole thing is initialized by setting PATH_thisScript
	 * since this constant is the one TYPO3's Bootstrap uses
	 * when detecting the starting point for other path constants.
	 *
	 * @return void
	 */
	protected function setUp() {
		if (FALSE === defined('TYPO3_version')) {
			define('PATH_thisScript', realpath('vendor/typo3/cms/typo3/index.php'));
			$bootstrap = Bootstrap::getInstance()->baseSetup('typo3/')->initializeClassLoader()
				->unregisterClassLoader();
		}
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['lockingMode'] = Locker::LOCKING_METHOD_DISABLED;
		parent::setUp();
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function countCountsInternalArray() {
		$queue = $this->getAccessibleMock('Dkd\\CmisService\\Queue\\SimpleQueue', array('save'));
		$queue->_set('queue', array('foo' => 'bar'));
		$result = $queue->count();
		$this->assertEquals(1, $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function countCountsInternalArrayWithMultipleItems() {
		$queue = $this->getAccessibleMock('Dkd\\CmisService\\Queue\\SimpleQueue', array('save'));
		$queue->_set('queue', array('foo' => 'bar', 'abc' => 'def', '123' => '456'));
		$result = $queue->count();
		$this->assertEquals(3, $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function savingCallsSetOnCacheFrontend() {
		$queue = $this->getMock('Dkd\\CmisService\\Queue\\SimpleQueue', array('lock', 'release'), array(), '', FALSE);
		$cache = $this->getMock(
			'Dkd\\CmisService\\Tests\\Fixtures\\Cache\\DummyVariableFrontend',
			array('set'),
			array(),
			'',
			FALSE
		);
		$cache->expects($this->at(0))->method('set')->will($this->returnValue(TRUE));
		$queue->expects($this->at(0))->method('lock')->will($this->returnValue(TRUE));
		$queue->expects($this->at(1))->method('release')->will($this->returnValue(TRUE));
		$queue->setCache($cache);
		$queue->save();
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function loadCallsExpectedMethodSequence() {
		$queue = $this->getMock('Dkd\\CmisService\\Queue\\SimpleQueue', array('lock', 'release'), array(), '', FALSE);
		$cache = $this->getMock(
			'Dkd\\CmisService\\Tests\\Fixtures\\Cache\\DummyVariableFrontend',
			array('has', 'get'),
			array(),
			'',
			FALSE
		);
		$cache->expects($this->at(0))->method('has')->with(SimpleQueue::CACHE_IDENTITY)->will($this->returnValue(TRUE));
		$cache->expects($this->at(1))->method('get')->with(SimpleQueue::CACHE_IDENTITY)->will($this->returnValue(array()));
		$queue->setCache($cache);
		$queue->load();
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function pickReturnsEarlyNullIfQueueIsEmpty() {
		$queue = $this->getMock('Dkd\\CmisService\\Queue\\SimpleQueue', array('lock'), array(), '', FALSE);
		$queue->expects($this->never())->method('lock');
		$picked = $queue->pick();
		$this->assertNull($picked);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function pickReturnsTaskAndExecutesExpectedMethodSequenceIfQueueNotEmpty() {
		$queue = $this->getAccessibleMock('Dkd\\CmisService\\Queue\\SimpleQueue', array('lock', 'save', 'release'), array(), '', FALSE);
		$task = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Task\\DummyTask', array('assign'));
		$task->expects($this->at(0))->method('assign');
		$queue->expects($this->at(0))->method('lock')->will($this->returnValue(TRUE));
		$queue->expects($this->at(1))->method('save')->will($this->returnValue(TRUE));
		$queue->expects($this->at(2))->method('release')->will($this->returnValue(TRUE));
		$queue->_set('queue', array(
			'dummy-task' => $task
		));
		$picked = $queue->pick();
		$this->assertSame($picked, $task);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function addTaskWithErrorsCausesExceptionBeforeAnyOtherAction() {
		$queue = $this->getMock('Dkd\\CmisService\\Queue\\SimpleQueue', array('lock'), array(), '', FALSE);
		$queue->expects($this->never())->method('lock');
		$errorTask = new ErroringTask();
		$this->setExpectedException('InvalidArgumentException');
		$queue->add($errorTask);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function addAllTasksWithErrorsCausesExceptionBeforeAnyOtherAction() {
		$queue = $this->getMock('Dkd\\CmisService\\Queue\\SimpleQueue', array('lock'), array(), '', FALSE);
		$queue->expects($this->never())->method('lock');
		$errorTasks = array(
			$errorTask = new ErroringTask(),
			$errorTask = new ErroringTask(),
		);
		$this->setExpectedException('InvalidArgumentException');
		$queue->addAll($errorTasks);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function addCallsExpectedMethodSequence() {
		$queue = $this->getMock('Dkd\\CmisService\\Queue\\SimpleQueue', array('lock', 'save', 'release'), array(), '', FALSE);
		$task = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Task\\DummyTask', array('getId', 'queue', 'validate'));
		$task->expects($this->at(0))->method('validate')->will($this->returnValue(TRUE));
		$task->expects($this->at(1))->method('getId')->will($this->returnValue('dummy-task'));
		$task->expects($this->at(2))->method('queue');
		$queue->expects($this->at(0))->method('lock')->will($this->returnValue(TRUE));
		$queue->expects($this->at(1))->method('save')->will($this->returnValue(TRUE));
		$queue->expects($this->at(2))->method('release')->will($this->returnValue(TRUE));
		$queue->add($task);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function addAllCallsExpectedMethodSequence() {
		$queue = $this->getMock('Dkd\\CmisService\\Queue\\SimpleQueue', array('lock', 'save', 'release'), array(), '', FALSE);
		$task1 = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Task\\DummyTask', array('getId', 'queue', 'validate'));
		$task1->expects($this->at(0))->method('validate')->will($this->returnValue(TRUE));
		$task1->expects($this->at(1))->method('getId')->will($this->returnValue('dummy-task-1'));
		$task1->expects($this->at(2))->method('queue');
		$task2 = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Task\\DummyTask', array('getId', 'queue', 'validate'));
		$task2->expects($this->at(0))->method('validate')->will($this->returnValue(TRUE));
		$task2->expects($this->at(1))->method('getId')->will($this->returnValue('dummy-task-2'));
		$task2->expects($this->at(2))->method('queue');
		$tasks = array($task1, $task2);
		$queue->expects($this->at(0))->method('lock')->will($this->returnValue(TRUE));
		$queue->expects($this->at(1))->method('save')->will($this->returnValue(TRUE));
		$queue->expects($this->at(2))->method('release')->will($this->returnValue(TRUE));
		$queue->addAll($tasks);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function lockMethodDelegatesToLocker() {
		$locker = $this->getMock('TYPO3\\CMS\\Core\\Locking\\Locker', array('acquireExclusiveLock'), array(), '', FALSE);
		$locker->expects($this->once())->method('acquireExclusiveLock')->will($this->returnValue(TRUE));
		$queue = $this->getMock('Dkd\\CmisService\\Queue\\SimpleQueue', array('getLocker'), array(), '', FALSE);
		$queue->expects($this->once())->method('getLocker')->will($this->returnValue($locker));
		$this->callInaccessibleMethod($queue, 'lock');
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function releaseMethodDelegatesToLocker() {
		$locker = $this->getMock('TYPO3\\CMS\\Core\\Locking\\Locker', array('release'), array(), '', FALSE);
		$locker->expects($this->once())->method('release');
		$queue = $this->getMock('Dkd\\CmisService\\Queue\\SimpleQueue', array('getLocker'), array(), '', FALSE);
		$queue->expects($this->once())->method('getLocker')->will($this->returnValue($locker));
		$this->callInaccessibleMethod($queue, 'release');
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function isLockedMethodDelegatesToLocker() {
		$locker = $this->getMock('TYPO3\\CMS\\Core\\Locking\\Locker', array('isLocked'), array(), '', FALSE);
		$locker->expects($this->once())->method('isLocked');
		$queue = $this->getMock('Dkd\\CmisService\\Queue\\SimpleQueue', array('getLocker'), array(), '', FALSE);
		$queue->expects($this->once())->method('getLocker')->will($this->returnValue($locker));
		$this->callInaccessibleMethod($queue, 'isLocked');
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getLockerSetsInternalPropertyAndReturnsInstance() {
		$queue = $this->getMock('Dkd\\CmisService\\Queue\\SimpleQueue', array(), array(), '', FALSE);
		$this->assertAttributeEquals(NULL, 'locker', $queue);
		$locker = $this->callInaccessibleMethod($queue, 'getLocker');
		$this->assertAttributeSame($locker, 'locker', $queue);
		$this->assertInstanceOf('TYPO3\\CMS\\Core\\Locking\\Locker', $locker);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function flushSetsEmptyArrayAndCallsSave() {
		$queue = $this->getMock('Dkd\\CmisService\\Queue\\SimpleQueue', array('save'), array(), '', FALSE);
		$queue->expects($this->once())->method('save');
		$queue->flush();
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function flushByFilterCallsExpectedMethodSequence() {
		$filterTask = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Task\\DummyTask');
		$task1 = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Task\\DummyTask', array('matches'));
		$task1->expects($this->once())->method('matches')->with($filterTask)->will($this->returnValue(TRUE));
		$task2 = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Task\\DummyTask', array('matches'));
		$task2->expects($this->once())->method('matches')->with($filterTask)->will($this->returnValue(FALSE));
		$queue = $this->getAccessibleMock('Dkd\\CmisService\\Queue\\SimpleQueue', array('save'));
		$queue->expects($this->once())->method('save');
		$queue->_set('queue', array('foo' => $task1, 'bar' => $task2));
		$queue->flushByFilter($filterTask);
		$this->assertAttributeEquals(array('bar' => $task2), 'queue', $queue);
	}

}
