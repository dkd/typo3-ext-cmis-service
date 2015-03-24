<?php
namespace Dkd\CmisService\Tests\Unit\Task;

use Dkd\CmisService\Factory\TaskFactory;
use Dkd\CmisService\Task\InitializationTask;
use Dkd\CmisService\Task\TaskInterface;
use Dkd\CmisService\Tests\Fixtures\Task\DummyTask;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class InitializationTaskTest
 */
class InitializationTaskTest extends UnitTestCase {

	/**
	 * @var TaskFactory
	 */
	protected $factory;

	/**
	 * Setup before each test
	 *
	 * @return void
	 */
	public function setUp() {
		$this->factory = new TaskFactory();
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function resolveExecutionObjectReturnsCmisEvictionExecution() {
		$instance = $this->factory->createInitializationTask();
		$execution = $instance->resolveExecutionObject();
		$this->assertInstanceOf('Dkd\\CmisService\\Execution\\Cmis\\InitializationExecution', $execution);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @dataProvider getTaskFilterDataSet
	 * @param TaskInterface $otherTask
	 * @param boolean $expectation
	 * @return void
	 */
	public function matchesTaskFilter($otherTask, $expectation) {
		$task1 = $this->factory->createInitializationTask($table1, $uid1);
		$this->assertEquals($expectation, $task1->matches($otherTask));
	}

	/**
	 * @return array
	 */
	public function getTaskFilterDataSet() {
		$goodTask = new InitializationTask();
		$badTask = new DummyTask();
		return array(
			array($goodTask, TRUE),
			array($badTask, FALSE)
		);
	}

}
