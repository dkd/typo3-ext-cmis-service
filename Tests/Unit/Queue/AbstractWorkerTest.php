<?php
namespace Dkd\CmisService\Tests\Unit\Queue;

use Dkd\CmisService\Execution\Result;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class AbstractWorkerTest
 */
class AbstractWorkerTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function handlesExecution() {
		$logger = $this->getMock('foo', array('log'));
		$factory = $this->getMock('Dkd\\CmisService\\Factory\\ObjectFactory', array('getLogger'));
		$factory->expects($this->any())->method('getLogger')->willReturn($logger);
		$result = new Result();
		$worker = $this->getAccessibleMock('Dkd\\CmisService\\Queue\\AbstractWorker', array('getObjectFactory'));
		$task = $this->getAccessibleMock('Dkd\\CmisService\\Tests\\Fixtures\\Task\\DummyTask', array('resolveExecutionObject'));
		$execution = $this->getAccessibleMock('Dkd\\CmisService\\Tests\\Fixtures\\Execution\\DummyExecution', array('execute'));
		$task->expects($this->once())->method('resolveExecutionObject')->will($this->returnValue($execution));
		$execution->expects($this->once())->method('execute')->with($task)->will($this->returnValue($result));
		$worker->expects($this->any())->method('getObjectFactory')->will($this->returnValue($factory));
		$output = $worker->execute($task);
		$this->assertSame($result, $output);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function handlesExecutionWithArgumentErrorsReturningErrorResult() {
		$logger = $this->getMock('foo', array('log'));
		$factory = $this->getMock('Dkd\\CmisService\\Factory\\ObjectFactory', array('getLogger'));
		$factory->expects($this->any())->method('getLogger')->willReturn($logger);
		$error = new \InvalidArgumentException('Argument foo is not bar-like', 123);
		$result = new Result($error->getMessage(), Result::ERR, array($error));
		$result->setError($error);
		$worker = $this->getAccessibleMock('Dkd\\CmisService\\Queue\\AbstractWorker', array('getObjectFactory'));
		$task = $this->getAccessibleMock('Dkd\\CmisService\\Tests\\Fixtures\\Task\\DummyTask', array('resolveExecutionObject'));
		$execution = $this->getAccessibleMock('Dkd\\CmisService\\Tests\\Fixtures\\Execution\\DummyExecution', array('validate'));
		$task->expects($this->once())->method('resolveExecutionObject')->will($this->returnValue($execution));
		$execution->expects($this->once())->method('validate')->with($task)->will($this->throwException($error));
		$worker->expects($this->any())->method('getObjectFactory')->will($this->returnValue($factory));
		$output = $worker->execute($task);
		$this->assertEquals($result, $output);
	}

}
