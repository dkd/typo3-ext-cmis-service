<?php
namespace Dkd\CmisService\Queue;

use Dkd\CmisService\Execution\Result;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class AbstractWorkerTest
 *
 * @package Dkd\CmisService\Queue
 */
class AbstractWorkerTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function handlesExecution() {
		$result = new Result();
		$worker = $this->getAccessibleMockForAbstractClass('Dkd\CmisService\Queue\AbstractWorker');
		$task = $this->getAccessibleMock('Dkd\CmisService\Tests\Fixtures\Task\DummyTask', array('resolveExecutionObject'));
		$execution = $this->getAccessibleMock('Dkd\CmisService\Tests\Fixtures\Execution\DummyExecution', array('execute'));
		$task->expects($this->once())->method('resolveExecutionObject')->will($this->returnValue($execution));
		$execution->expects($this->once())->method('execute')->with($task)->will($this->returnValue($result));
		$output = $worker->execute($task);
		$this->assertSame($result, $output);
	}

}
