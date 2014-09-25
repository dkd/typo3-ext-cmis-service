<?php
namespace Dkd\CmisService\Tests\Unit\Hook;

use Dkd\CmisService\Hook\DataHandlerListener;
use Dkd\CmisService\Tests\Fixtures\Task\DummyTask;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class ListenerTest
 */
class DataHandlerListenerTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function afterDatabaseOperationsHookCallsHandleCommandWithPassthroughParameters() {
		$dataHandler = $this->getMock('TYPO3\\CMS\\Core\\DataHandling\\DataHandler', array(), array(), '', FALSE);
		$mock = $this->getMock('Dkd\\CmisService\\Hook\\DataHandlerListener', array('handleCommand'), array(), '', FALSE);
		$mock->expects($this->once())->method('handleCommand')->with('foo', 'bar', 123);
		$fields = array();
		$mock->processDatamap_afterDatabaseOperations('foo', 'bar', 123, $fields, $dataHandler);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function postProcessCommandMapCallsHandleCommandWithPassthroughParameters() {
		$dataHandler = $this->getMock('TYPO3\\CMS\\Core\\DataHandling\\DataHandler', array(), array(), '', FALSE);
		$mock = $this->getMock('Dkd\\CmisService\\Hook\\DataHandlerListener', array('handleCommand'), array(), '', FALSE);
		$mock->expects($this->once())->method('handleCommand')->with('foo', 'bar', 123);
		$fields = array();
		$command = 'foo';
		$mock->processCmdmap_postProcess($command, 'bar', 123, $fields, $dataHandler);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function handleCommandReturnsEarlyIfTableIsNotMonitored() {
		$mock = $this->getMock(
			'Dkd\\CmisService\\Hook\\DataHandlerListener',
			array('isTableMonitored', 'getQueue'),
			array(),
			'',
			FALSE
		);
		$mock->expects($this->once())->method('isTableMonitored')->with('foobar')->will($this->returnValue(FALSE));
		$mock->expects($this->never())->method('getQueue');
		$this->callInaccessibleMethod($mock, 'handleCommand', 'somecommand', 'foobar', 132);
	}

	/**
	 * Unit test
	 *
	 * @param string $command
	 * @param string $taskCreationMethodName
	 * @dataProvider getCommandAndExpectedTaskType
	 * @test
	 * @return void
	 */
	public function handleCommandLogicCreatesExpectedTaskForCommand($command, $taskCreationMethodName) {
		$mock = $this->getMock(
			'Dkd\\CmisService\\Hook\\DataHandlerListener',
			array('isTableMonitored', 'getQueue', 'getTaskFactory'),
			array(),
			'',
			FALSE
		);
		$mock->expects($this->once())->method('isTableMonitored')->will($this->returnValue(TRUE));
		$methods = array('createRecordIndexingTask', 'createEvictionTask');
		$taskFactory = $this->getMock(
			'Dkd\\CmisService\\Factory\\TaskFactory',
			$methods
		);
		$mock->expects($this->once())->method('getTaskFactory')->will($this->returnValue($taskFactory));
		if (NULL !== $taskCreationMethodName) {
			$queue = $this->getMock('Dkd\\CmisService\\Queue\\SimpleQueue', array('flushByFilter', 'add'));
			$task = new DummyTask();
			$taskFactory->expects($this->once())->method($taskCreationMethodName)->will($this->returnValue($task));
			$mock->expects($this->once())->method('getQueue')->will($this->returnValue($queue));
			$queue->expects($this->once())->method('flushByFilter')->with($task);
			$queue->expects($this->once())->method('add')->with($task);
		} else {
			$mock->expects($this->never())->method('getQueue');
			$mock->expects($this->never())->method($methods[0]);
			$mock->expects($this->never())->method($methods[1]);
		}
		$this->callInaccessibleMethod($mock, 'handleCommand', $command, 'foobar', 123);
	}

	/**
	 * @return array
	 */
	public function getCommandAndExpectedTaskType() {
		return array(
			array(DataHandlerListener::COMMAND_DELETE, 'createEvictionTask'),
			array(DataHandlerListener::COMMAND_HIDE, 'createEvictionTask'),
			array(DataHandlerListener::COMMAND_CREATE, 'createRecordIndexingTask'),
			array(DataHandlerListener::COMMAND_UPDATE, 'createRecordIndexingTask'),
			array(DataHandlerListener::COMMAND_TRANSLATE, 'createRecordIndexingTask'),
			array(DataHandlerListener::COMMAND_MOVE, 'createRecordIndexingTask'),
			array('unsupportedCommand', NULL)
		);
	}

}
