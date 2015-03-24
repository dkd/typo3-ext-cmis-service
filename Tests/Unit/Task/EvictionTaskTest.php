<?php
namespace Dkd\CmisService\Tests\Unit\Task;

use Dkd\CmisService\Factory\TaskFactory;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class EvictionTaskTest
 */
class EvictionTaskTest extends UnitTestCase {

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
		$instance = $this->factory->createEvictionTask('tt_content', 123);
		$execution = $instance->resolveExecutionObject();
		$this->assertInstanceOf('Dkd\\CmisService\\Execution\\Cmis\\EvictionExecution', $execution);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @dataProvider getTaskFilterDataSet
	 * @param string $table1
	 * @param integer $uid1
	 * @param string $table2
	 * @param integer $uid2
	 * @param boolean $expectation
	 * @return void
	 */
	public function matchesTaskFilter($table1, $uid1, $table2, $uid2, $expectation) {
		$task1 = $this->factory->createEvictionTask($table1, $uid1);
		$task2 = $this->factory->createEvictionTask($table2, $uid2);
		$this->assertEquals($expectation, $task1->matches($task2));
	}

	/**
	 * @return array
	 */
	public function getTaskFilterDataSet() {
		return array(
			array('tt_content', 123, 'tt_content', 123, TRUE),
			array('tt_content', 123, 'tt_content', 321, FALSE),
			array('tt_content', 123, 'pages', 123, FALSE),
			array('tt_content', 123, 'pages', 321, FALSE),
		);
	}

}
