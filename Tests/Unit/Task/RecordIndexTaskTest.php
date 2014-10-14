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
	 * @param array $parameters1
	 * @param array $parameters2
	 * @param boolean $expectation
	 * @return void
	 */
	public function matchesTaskFilter(array $parameters1, array $parameters2, $expectation) {
		list ($table1, $uid1, $fields1, $relations1) = $parameters1;
		list ($table2, $uid2, $fields2, $relations2) = $parameters2;
		$task1 = $this->factory->createRecordIndexingTask($table1, $uid1, $fields1, $relations1);
		$task2 = $this->factory->createRecordIndexingTask($table2, $uid2, $fields2, $relations2);
		$this->assertEquals($expectation, $task1->matches($task2));
	}

	/**
	 * @return array
	 */
	public function getTaskFilterDataSet() {
		return array(
			array(
				array('tt_content', 123, array('foo', 'bar'), TRUE),
				array('tt_content', 123, array('foo', 'bar'), TRUE),
				TRUE
			),
			array(
				array('tt_content', 123, array('foo', 'bar'), TRUE),
				array('tt_content', 123, array('foo', 'bar'), FALSE),
				FALSE
			),
			array(
				array('tt_content', 123, array('foo', 'bar'), FALSE),
				array('tt_content', 123, array('bar', 'foo'), FALSE),
				FALSE
			),
			array(
				array('tt_content', 123, array('foo', 'bar'), FALSE),
				array('tt_content', 321, array('foo', 'bar'), FALSE),
				FALSE
			),
			array(
				array('tt_content', 123, array('foo', 'bar'), FALSE),
				array('tt_content', 321, array('bar', 'foo'), FALSE),
				FALSE
			),
			array(
				array('tt_content', 123, array('foo', 'bar'), FALSE),
				array('pages', 123, array('foo', 'bar'), FALSE),
				FALSE
			),
			array(
				array('tt_content', 123, array('foo', 'bar'), FALSE),
				array('pages', 123, array('bar', 'foo'), FALSE),
				FALSE
			),
			array(
				array('tt_content', 123, array('foo', 'bar'), FALSE),
				array('pages', 321, array('foo', 'bar'), FALSE),
				FALSE
			),
			array(
				array('tt_content', 123, array('foo', 'bar'), FALSE),
				array('pages', 321, array('bar', 'foo'), FALSE),
				FALSE
			),
		);
	}

}
