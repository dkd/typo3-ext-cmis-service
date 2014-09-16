<?php
namespace Dkd\CmisService\Tests\Unit\Factory;

use Dkd\CmisService\Factory\TaskFactory;
use Dkd\CmisService\Task\RecordIndexTask;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class TaskFactoryTest
 */
class TaskFactoryTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function createsCmisIndexTasks() {
		$factory = new TaskFactory();
		$task = $factory->createRecordIndexingTask('tt_content', 123, array('uid'));
		$this->assertInstanceOf('Dkd\\CmisService\\Task\\RecordIndexTask', $task);
		$this->assertEquals('tt_content', $task->getParameter(RecordIndexTask::OPTION_TABLE));
		$this->assertEquals(123, $task->getParameter(RecordIndexTask::OPTION_UID));
		$this->assertEquals(array('uid'), $task->getParameter(RecordIndexTask::OPTION_FIELDS));
	}

}
