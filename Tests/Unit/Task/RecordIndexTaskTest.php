<?php
namespace Dkd\CmisService\Tests\Unit\Task;

use Dkd\CmisService\Factory\TaskFactory;
use Dkd\CmisService\Task\RecordIndexTask;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class RecordIndexTaskTest
 */
class RecordIndexTaskTest extends UnitTestCase {

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
	public function resolveExecutionObjectReturnsCmisIndexExecution() {
		$instance = new RecordIndexTask();
		$execution = $instance->resolveExecutionObject();
		$this->assertInstanceOf('Dkd\\CmisService\\Execution\\Cmis\\IndexExecution', $execution);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @dataProvider getTaskFilterDataSet
	 * @param string $table1
	 * @param integer $uid1
	 * @oaran array $fields1
	 * @param string $table2
	 * @param integer $uid2
	 * @oaran array $fields2
	 * @param boolean $expectation
	 * @return void
	 */
	public function matchesTaskFilter($table1, $uid1, $fields1, $table2, $uid2, $fields2, $expectation) {
		$task1 = $this->factory->createRecordIndexingTask($table1, $uid1, $fields1);
		$task2 = $this->factory->createRecordIndexingTask($table2, $uid2, $fields2);
		$this->assertEquals($expectation, $task1->matches($task2));
	}

	/**
	 * @return array
	 */
	public function getTaskFilterDataSet() {
		return array(
			array('tt_content', 123, array('foo', 'bar'), 'tt_content', 123, array('foo', 'bar'), TRUE),
			array('tt_content', 123, array('foo', 'bar'), 'tt_content', 123, array('bar', 'foo'), FALSE),
			array('tt_content', 123, array('foo', 'bar'), 'tt_content', 321, array('foo', 'bar'), FALSE),
			array('tt_content', 123, array('foo', 'bar'), 'tt_content', 321, array('bar', 'foo'), FALSE),
			array('tt_content', 123, array('foo', 'bar'), 'pages', 123, array('foo', 'bar'), FALSE),
			array('tt_content', 123, array('foo', 'bar'), 'pages', 123, array('bar', 'foo'), FALSE),
			array('tt_content', 123, array('foo', 'bar'), 'pages', 321, array('foo', 'bar'), FALSE),
			array('tt_content', 123, array('foo', 'bar'), 'pages', 321, array('bar', 'foo'), FALSE),
		);
	}

}
