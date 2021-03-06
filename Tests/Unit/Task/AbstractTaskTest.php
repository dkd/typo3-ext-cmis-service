<?php
namespace Dkd\CmisService\Tests\Unit\Task;

use Dkd\CmisService\Task\TaskInterface;
use Dkd\CmisService\Tests\Fixtures\Queue\DummyWorker;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class AbstractTaskTest
 */
class AbstractTaskTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function generatesIdInConstructor() {
		$task = $this->getAccessibleMockForAbstractClass('Dkd\\CmisService\\Task\\AbstractTask');
		$this->assertNotEmpty($task->getId());
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function startSetsCorrectStatus() {
		$task = $this->getAccessibleMockForAbstractClass('Dkd\\CmisService\\Task\\AbstractTask');
		$task->start();
		$this->assertEquals(TaskInterface::STATUS_RUNNING, $task->_get('status'));
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function queueSetsCorrectStatus() {
		$task = $this->getAccessibleMockForAbstractClass('Dkd\\CmisService\\Task\\AbstractTask');
		$task->queue();
		$this->assertEquals(TaskInterface::STATUS_QUEUED, $task->_get('status'));
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function isQueuedRespondsTrueIfQueued() {
		$task = $this->getAccessibleMockForAbstractClass('Dkd\\CmisService\\Task\\AbstractTask');
		$task->_set('status', TaskInterface::STATUS_QUEUED);
		$this->assertTrue($task->isQueued());
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function isQueuedRespondsFalseIfNotQueued() {
		$task = $this->getAccessibleMockForAbstractClass('Dkd\\CmisService\\Task\\AbstractTask');
		$task->_set('status', TaskInterface::STATUS_NONE);
		$this->assertFalse($task->isQueued());
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function isRunningRespondsTrueIfRunning() {
		$task = $this->getAccessibleMockForAbstractClass('Dkd\\CmisService\\Task\\AbstractTask');
		$task->_set('status', TaskInterface::STATUS_RUNNING);
		$this->assertTrue($task->isRunning());
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function isRunningRespondsFalseIfNotRunning() {
		$task = $this->getAccessibleMockForAbstractClass('Dkd\\CmisService\\Task\\AbstractTask');
		$task->_set('status', TaskInterface::STATUS_NONE);
		$this->assertFalse($task->isRunning());
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function isAssignedRespondsTrueIfAssigned() {
		$task = $this->getAccessibleMockForAbstractClass('Dkd\\CmisService\\Task\\AbstractTask');
		$task->_set('status', TaskInterface::STATUS_ASSIGNED);
		$this->assertTrue($task->isAssigned());
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function isAssignedRespondsFalseIfNotAssigned() {
		$task = $this->getAccessibleMockForAbstractClass('Dkd\\CmisService\\Task\\AbstractTask');
		$task->_set('status', TaskInterface::STATUS_NONE);
		$this->assertFalse($task->isAssigned());
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function finishCallsExpectedMethodsAndSetsExpectedStatus() {
		$task = $this->getAccessibleMockForAbstractClass('Dkd\\CmisService\\Task\\AbstractTask');
		$worker = new DummyWorker();
		$task->assign($worker);
		$this->assertSame($worker, $task->_get('worker'));
		$task->finish();
		$this->assertEquals(TaskInterface::STATUS_DONE, $task->_get('status'));
		$this->assertNull($task->_get('worker'));
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function setParameterSetsParameter() {
		$task = $this->getAccessibleMockForAbstractClass('Dkd\\CmisService\\Task\\AbstractTask');
		$task->setParameter('foobar', 'test');
		$this->assertContains('test', $task->_get('parameters'));
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getParameterReturnsParameterValue() {
		$task = $this->getAccessibleMockForAbstractClass('Dkd\\CmisService\\Task\\AbstractTask');
		$task->_set('parameters', array('foobar' => 'test'));
		$this->assertEquals('test', $task->getParameter('foobar'));
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getParameterReturnsNullForUnknownParameters() {
		$task = $this->getAccessibleMockForAbstractClass('Dkd\\CmisService\\Task\\AbstractTask');
		$task->_set('parameters', array('foobar' => 'test'));
		$this->assertNull($task->getParameter('invalidname'));
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function defaultValidationReturnsTrue() {
		$task = $this->getMockForAbstractClass('Dkd\\CmisService\\Task\\AbstractTask');
		$result = $task->validate();
		$this->assertTrue($result);
	}

}
