<?php
namespace Dkd\CmisService\Execution;

use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class AbstractExecutionTest
 *
 * @package Dkd\CmisService\Execution
 */
class AbstractExecutionTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function createsResultObjects() {
		$execution = $this->getAccessibleMockForAbstractClass('Dkd\CmisService\Execution\AbstractExecution');
		$result = $this->callInaccessibleMethod($execution, 'createResultObject');
		$this->assertInstanceOf('Dkd\CmisService\Execution\Result', $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function storesResultAfterExecution() {
		$task = $this->getAccessibleMock('Dkd\CmisService\Tests\Fixtures\Task\DummyTask');
		$execution = $this->getAccessibleMockForAbstractClass('Dkd\CmisService\Execution\AbstractExecution');
		$executionResult = $execution->execute($task);
		$this->assertInstanceOf('Dkd\CmisService\Execution\Result', $execution->getResult());
	}

}
