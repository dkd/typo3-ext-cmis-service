<?php
namespace Dkd\CmisService\Tests\Unit\Execution\Cmis;

use Dkd\CmisService\Execution\Cmis\EvictionExecution;
use Dkd\CmisService\Execution\Result;
use Dkd\CmisService\Task\EvictionTask;
use Dkd\CmisService\Tests\Fixtures\Task\DummyTask;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class EvictionExecutionTest
 */
class EvictionExecutionTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function validateReturnsTrueGivenExpectedTaskType() {
		$execution = new EvictionExecution();
		$goodTask = new EvictionTask();
		$result = $execution->validate($goodTask);
		$this->assertTrue($result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function validateOnInvalidTaskThrowsInvalidArgumentExceptionIncludingBothPassedAndRequiredTaskTypesInMessage() {
		$execution = new EvictionExecution();
		$badTask = new DummyTask();
		$goodTask = new EvictionTask();
		$expectedMessage = 'Error in CMIS IndexExecution during Task validation. ' .
			'Task must be a ' . get_class($goodTask) . ' or subclass; we received a ' . get_class($badTask);
		$this->setExpectedException('InvalidArgumentException', $expectedMessage);
		$execution->validate($badTask);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function executeCreatesResultObjectAndStoresAsProperty() {
		$result = new Result();
		$instance = $this->getMock('Dkd\\CmisService\\Execution\\Cmis\\EvictionExecution', array('createResultObject'));
		$instance->expects($this->once())->method('createResultObject')->will($this->returnValue($result));
		$task = new DummyTask();
		$outputResult = $instance->execute($task);
		$this->assertAttributeEquals($result, 'result', $instance);
		$this->assertSame($outputResult, $result);
	}

}
