<?php
namespace Dkd\CmisService\Tests\Unit\Execution\Cmis;

use Dkd\CmisService\Execution\Cmis\InitializationExecution;
use Dkd\CmisService\Task\InitializationTask;
use Dkd\CmisService\Tests\Fixtures\Task\DummyTask;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class InitializationExecutionTest
 */
class InitializationExecutionTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function validateReturnsTrueGivenExpectedTaskType() {
		$execution = new InitializationExecution();
		$goodTask = new InitializationTask();
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
		$execution = new InitializationExecution();
		$badTask = new DummyTask();
		$goodTask = new InitializationTask();
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
	public function executeReturnsResultObject() {
		$task = new DummyTask();
		$execution = $this->getMock(
			'Dkd\\CmisService\\Execution\\Cmis\\InitializationExecution',
			array('validatePresenceOfCustomCmisTypes', 'createCmisSitesForFirstDomainOfAllRootPages')
		);
		$execution->expects($this->once())->method('validatePresenceOfCustomCmisTypes');
		$execution->expects($this->once())->method('createCmisSitesForFirstDomainOfAllRootPages');
		$result = $execution->execute($task);
		$this->assertInstanceOf('Dkd\\CmisService\\Execution\\Result', $result);
	}

}
