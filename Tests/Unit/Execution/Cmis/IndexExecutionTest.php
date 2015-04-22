<?php
namespace Dkd\CmisService\Tests\Unit\Execution\Cmis;

use Dkd\CmisService\Execution\Cmis\IndexExecution;
use Dkd\CmisService\Execution\Result;
use Dkd\CmisService\Task\RecordIndexTask;
use Dkd\CmisService\Tests\Fixtures\Task\DummyTask;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class IndexExecutionTest
 */
class IndexExecutionTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function validateReturnsTrueGivenExpectedTaskType() {
		$execution = new IndexExecution();
		$goodTask = new RecordIndexTask();
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
		$execution = new IndexExecution();
		$badTask = new DummyTask();
		$goodTask = new RecordIndexTask();
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
		$instance = $this->getMock(
			'Dkd\\CmisService\\Execution\\Cmis\\IndexExecution',
			array('createResultObject', 'loadRecordFromDatabase', 'performTextExtraction', 'resolveCmisDocumentByTableAndUid')
		);
		$document = $this->getMock('Dkd\\PhpCmis\\DataObjects\\Document', array(), array(), '', FALSE);
		$instance->expects($this->once())->method('createResultObject')->will($this->returnValue($result));
		$instance->expects($this->once())->method('resolveCmisDocumentByTableAndUid')->will($this->returnValue($document));
		$task = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Task\\DummyTask', array('getParameter'));
		$task->expects($this->at(0))->method('getParameter')
			->with(RecordIndexTask::OPTION_FIELDS)
			->will($this->returnValue(array('uid', 'pid')));
		$task->expects($this->at(1))->method('getParameter')
			->with(RecordIndexTask::OPTION_TABLE)
			->will($this->returnValue('tt_content'));
		$task->expects($this->at(2))->method('getParameter')
			->with(RecordIndexTask::OPTION_UID)
			->will($this->returnValue(123));
		$instance->expects($this->once())->method('loadRecordFromDatabase')
			->with('tt_content', 123, array('uid', 'pid'))
			->will($this->returnValue(array('uid' => 123, 'pid' => 123)));
		$instance->expects($this->exactly(2))->method('performTextExtraction');
		$outputResult = $instance->execute($task);
		$this->assertAttributeEquals($result, 'result', $instance);
		$this->assertSame($outputResult, $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getExtractionMethodDetectorReturnsExtractionMethodDetectorInstance() {
		$instance = new IndexExecution();
		$detector = $this->callInaccessibleMethod($instance, 'getExtractionMethodDetector');
		$this->assertInstanceOf('Dkd\\CmisService\\Analysis\\Detection\\ExtractionMethodDetector', $detector);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function performTextExtractionCallsExpectedMethodsAndReturnsExpectedValue() {
		$table = 'tt_content';
		$field = 'uid';
		$uid = 123;
		$record = array($field => $uid);
		$mockExtraction = $this->getMock('Dkd\\CmisService\\Extraction\\PassthroughExtraction', array('extract'));
		$mockExtraction->expects($this->once())->method('extract')->with($record[$field])->will($this->returnValue('barfoo'));
		$mockDetector = $this->getMock(
			'Dkd\\CmisService\\Analys\\Detection\\ExtractionMethodDetector',
			array('resolveExtractionForColumn')
		);
		$mockDetector->expects($this->once())->method('resolveExtractionForColumn')
			->with($table, $field)
			->will($this->returnValue($mockExtraction));
		$instance = $this->getMock('Dkd\\CmisService\\Execution\\Cmis\\IndexExecution', array('getExtractionMethodDetector'));
		$instance->expects($this->once())->method('getExtractionMethodDetector')->will($this->returnValue($mockDetector));
		$result = $this->callInaccessibleMethod($instance, 'performTextExtraction', $table, $uid, $field, $record);
		$this->assertEquals('barfoo', $result);
	}

}
