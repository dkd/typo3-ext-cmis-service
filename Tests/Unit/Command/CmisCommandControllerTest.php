<?php
namespace Dkd\CmisService\Tests\Unit\Command;

use Dkd\CmisService\Command\CmisCommandController;
use Dkd\CmisService\Tests\Fixtures\Queue\DummyWorker;
use Dkd\CmisService\Tests\Fixtures\Task\DummyTask;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class CmisCommandControllerTest
 */
class CmisCommandControllerTest extends UnitTestCase {

	/**
	 * Setup
	 *
	 * Checks the very first constant defined by the TYPO3 CMS
	 * initialization "Bootstrap" class. If the constant is not
	 * defined we assume a minimal environment must be created:
	 *
	 * - to define constants some TYPO3 CMS classes need
	 * - to initialize the TYPO3 CMS class loader _caches_
	 * - to unregister the actual class loader from TYPO3 CMS,
	 *   falling back to the Composer autoloading.
	 *
	 * The whole thing is initialized by setting PATH_thisScript
	 * since this constant is the one TYPO3's Bootstrap uses
	 * when detecting the starting point for other path constants.
	 *
	 * @return void
	 */
	protected function setUp() {
		if (FALSE === defined('TYPO3_version')) {
			define('PATH_thisScript', realpath('vendor/typo3/cms/typo3/index.php'));
			$bootstrap = Bootstrap::getInstance()->baseSetup('typo3/')->initializeClassLoader()
				->unregisterClassLoader();
		}
		parent::setUp();
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getQueueFetchesQueue() {
		$queueFactory = $this->getMock('Dkd\\CmisService\\Factory\\QueueFactory', array('fetchQueue'));
		$queueFactory->expects($this->once())->method('fetchQueue')->will($this->returnValue('foobar'));
		$commandController = $this->getAccessibleMock(
			'Dkd\\CmisService\\Command\\CmisCommandController',
			array('getQueueFactory')
		);
		$commandController->expects($this->once())->method('getQueueFactory')->will($this->returnValue($queueFactory));
		$result = $commandController->_call('getQueue');
		$this->assertEquals('foobar', $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function truncateQueueCallsFlush() {
		$commandController = $this->getMock('Dkd\\CmisService\\Command\\CmisCommandController', array('getQueue'));
		$queue = $this->getMock('Dkd\\CmisService\\Queue\\SimpleQueue', array('flush'));
		$queue->expects($this->once())->method('flush');
		$commandController->expects($this->once())->method('getQueue')->will($this->returnValue($queue));
		$commandController->truncateQueueCommand();
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function pickTaskDelegatesToPickTasksWithOneAsArgument() {
		$commandController = $this->getMock('Dkd\\CmisService\\Command\\CmisCommandController', array('pickTasksCommand'));
		$commandController->expects($this->once())->method('pickTasksCommand')->with(1);
		$commandController->pickTaskCommand();
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function pickTasksRunsExpectedLoop() {
		$dummyTask = new DummyTask();
		$dummyWorker = new DummyWorker();
		$dummyTask->assign($dummyWorker);
		$queue = $this->getMock('Dkd\\CmisService\\Queue\\SimpleQueue', array('pick'));
		$queue->expects($this->exactly(3))->method('pick')->will($this->returnValue($dummyTask));
		$response = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\Request\\Response', array('appendContent', 'send'));
		$response->expects($this->exactly(6))->method('appendContent');
		$response->expects($this->once())->method('send');
		$commandController = $this->getAccessibleMock('Dkd\\CmisService\\Command\\CmisCommandController', array('getQueue'));
		$commandController->_set('response', $response);
		$commandController->expects($this->once())->method('getQueue')->will($this->returnValue($queue));
		$commandController->pickTasksCommand(3);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function statusCountsQueue() {
		$queue = $this->getMock('Dkd\\CmisService\\Queue\\SimpleQueue', array('count'));
		$queue->expects($this->once())->method('count')->will($this->returnValue(2));
		$response = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\Request\\Response', array('setContent', 'send'));
		$response->expects($this->atLeastOnce())->method('setContent');
		$commandController = $this->getAccessibleMock('Dkd\\CmisService\\Command\\CmisCommandController', array('getQueue'));
		$commandController->_set('response', $response);
		$commandController->expects($this->once())->method('getQueue')->will($this->returnValue($queue));
		$commandController->statusCommand();
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getTableConfigurationAnalyzerReturnsTableConfigurationAnalyzerInstance() {
		$commandController = new CmisCommandController();
		$result = $this->callInaccessibleMethod($commandController, 'getTableConfigurationAnalyzer');
		$this->assertInstanceOf('Dkd\\CmisService\\Analysis\\TableConfigurationAnalyzer', $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getRecordAnalyzerReturnsRecordAnalyzerInstance() {
		$commandController = new CmisCommandController();
		$result = $this->callInaccessibleMethod($commandController, 'getRecordAnalyzer', 'tt_content', array('uid' => 123));
		$this->assertInstanceOf('Dkd\\CmisService\\Analysis\\RecordAnalyzer', $result);
		$this->assertAttributeEquals('tt_content', 'table', $result);
		$this->assertAttributeEquals(array('uid' => 123), 'record', $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getAllEnabledRecordsFromTableDelegatesToGlobalConnectionAndAppliesEnableFields() {
		$pageRepository = $this->getMock('TYPO3\\CMS\\Frontend\\Page\\PageRepository', array('enableFields'));
		$pageRepository->expects($this->once())->method('enableFields')->with('dummytable', 0, array(), TRUE);
		$commandController = $this->getMock('Dkd\\CmisService\\Command\\CmisCommandController', array('getPageRepository'));
		$commandController->expects($this->once())->method('getPageRepository')->will($this->returnValue($pageRepository));
		$backup = $GLOBALS['TYPO3_DB'];
		$GLOBALS['TYPO3_DB'] = $this->getMock('TYPO3\\CMS\\Core\\Database\\DatabaseConnection', array('exec_SELECTgetRows'));
		$GLOBALS['TYPO3_DB']->expects($this->once())->method('exec_SELECTgetRows')->with('*', 'dummytable', '1=1');
		$this->callInaccessibleMethod($commandController, 'getAllEnabledRecordsFromTable', 'dummytable');
		$GLOBALS['TYPO3_DB'] = $backup;
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function generateIndexingTasksCommandWithoutTableCallsTableConfigurationAnalyzerToGetAllTables() {
		$backup = $GLOBALS['TYPO3_DB'];
		$GLOBALS['TYPO3_DB'] = $this->getMock(
			'TYPO3\\CMS\\Core\\Database\\DatabaseConnection',
			array('connectDB', 'fullQuoteStr', 'exec_SELECTgetRows')
		);
		$GLOBALS['TYPO3_DB']->expects($this->once())->method('exec_SELECTgetRows')->will($this->returnValue(array()));
		$GLOBALS['TCA']['foobar'] = array(
			'columns' => array(),
			'ctrl' => array()
		);
		$queue = $this->getMock('Dkd\\CmisService\\Queue\\SimpleQueue', array('addAll'));
		$queue->expects($this->once())->method('addAll')->will($this->returnValue(TRUE));
		$response = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\Request\\Response', array('setContent', 'send'));
		$response->expects($this->atLeastOnce())->method('setContent');
		$commandController = $this->getAccessibleMock(
			'Dkd\\CmisService\\Command\\CmisCommandController',
			array('getTableConfigurationAnalyzer', 'getQueue')
		);
		$commandController->_set('response', $response);
		$commandController->expects($this->once())->method('getQueue')->will($this->returnValue($queue));
		$tableConfigurationAnalyzer = $this->getMock(
			'Dkd\\CmisService\\Analysis\\TableConfigurationAnalyzer',
			array('getIndexableTableNames')
		);
		$commandController->expects($this->once())->method('getTableConfigurationAnalyzer')
			->will($this->returnValue($tableConfigurationAnalyzer));
		$tableConfigurationAnalyzer->expects($this->once())->method('getIndexableTableNames')
			->will($this->returnValue(array('foobar')));
		$commandController->generateIndexingTasksCommand();
		$GLOBALS['TYPO3_DB'] = $backup;
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function generateIndexingTasksCommandWithTableOnlyProcessesSpecifiedTable() {
		$backup = $GLOBALS['TYPO3_DB'];
		$GLOBALS['TYPO3_DB'] = $this->getMock(
			'TYPO3\\CMS\\Core\\Database\\DatabaseConnection',
			array('connectDB', 'fullQuoteStr', 'exec_SELECTgetRows')
		);
		$records = array(array('uid' => 123), array('uid' => 321));
		$GLOBALS['TYPO3_DB']->expects($this->once())->method('exec_SELECTgetRows')->will($this->returnValue($records));
		$GLOBALS['TCA']['foobar'] = array(
			'columns' => array(),
			'ctrl' => array()
		);
		$queue = $this->getMock('Dkd\\CmisService\\Queue\\SimpleQueue', array('addAll'));
		$queue->expects($this->once())->method('addAll')->will($this->returnValue(TRUE));
		$response = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\Request\\Response', array('setContent', 'send'));
		$response->expects($this->atLeastOnce())->method('setContent');
		$commandController = $this->getAccessibleMock(
			'Dkd\\CmisService\\Command\\CmisCommandController',
			array('getTableConfigurationAnalyzer', 'getQueue', 'createRecordIndexingTask')
		);
		$commandController->_set('response', $response);
		$commandController->expects($this->once())->method('getQueue')->will($this->returnValue($queue));
		$commandController->expects($this->exactly(4))->method('createRecordIndexingTask');
		$commandController->generateIndexingTasksCommand('foobar');
		$GLOBALS['TYPO3_DB'] = $backup;
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function generateIndexingTasksCommandWithTablesOnlyProcessesSpecifiedTables() {
		$backup = $GLOBALS['TYPO3_DB'];
		$GLOBALS['TYPO3_DB'] = $this->getMock(
			'TYPO3\\CMS\\Core\\Database\\DatabaseConnection',
			array('connectDB', 'fullQuoteStr', 'exec_SELECTgetRows')
		);
		$records = array(array('uid' => 123), array('uid' => 321));
		$GLOBALS['TYPO3_DB']->expects($this->exactly(2))->method('exec_SELECTgetRows')->will($this->returnValue($records));
		$GLOBALS['TCA']['foobar'] = array(
			'columns' => array(),
			'ctrl' => array()
		);
		$GLOBALS['TCA']['foobaz'] = array(
			'columns' => array(),
			'ctrl' => array()
		);
		$queue = $this->getMock('Dkd\\CmisService\\Queue\\SimpleQueue', array('addAll'));
		$queue->expects($this->once())->method('addAll')->will($this->returnValue(TRUE));
		$response = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\Request\\Response', array('setContent', 'send'));
		$response->expects($this->atLeastOnce())->method('setContent');
		$commandController = $this->getAccessibleMock(
			'Dkd\\CmisService\\Command\\CmisCommandController',
			array('getTableConfigurationAnalyzer', 'getQueue', 'createRecordIndexingTask')
		);
		$commandController->_set('response', $response);
		$commandController->expects($this->once())->method('getQueue')->will($this->returnValue($queue));
		$commandController->expects($this->exactly(8))->method('createRecordIndexingTask');
		$commandController->generateIndexingTasksCommand('foobar,foobaz');
		$GLOBALS['TYPO3_DB'] = $backup;
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function createRecordIndexingTaskCallsExpectedMethodSequence() {
		$taskFactory = $this->getMock('Dkd\\CmisService\\Factory\\TaskFactory', array('createRecordIndexingTask'));
		$taskFactory->expects($this->once())->method('createRecordIndexingTask')
			->with('foobar', 123, array('uid'))
			->will($this->returnValue('baz'));
		$recordAnalyzer = $this->getMock(
			'Dkd\\CmisService\\Analysis\\RecordAnalyzer',
			array('getIndexableColumnNames'),
			array(),
			'',
			FALSE
		);
		$recordAnalyzer->expects($this->once())->method('getIndexableColumnNames')->will($this->returnValue(array('uid')));
		$commandController = $this->getMock(
			'Dkd\\CmisService\\Command\\CmisCommandController',
			array('getTaskFactory', 'getRecordAnalyzer')
		);
		$commandController->expects($this->at(0))->method('getTaskFactory')->will($this->returnValue($taskFactory));
		$commandController->expects($this->at(1))->method('getRecordAnalyzer')->will($this->returnValue($recordAnalyzer));
		$result = $this->callInaccessibleMethod($commandController, 'createRecordIndexingTask', 'foobar', array('uid' => 123));
		$this->assertEquals('baz', $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getPageRepositoryReturnsPageRepository() {
		$commandController = new CmisCommandController();
		$result = $this->callInaccessibleMethod($commandController, 'getPageRepository');
		$this->assertInstanceOf('TYPO3\\CMS\\Frontend\\Page\\PageRepository', $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getTaskFactoryReturnsTaskFactoryInstance() {
		$commandController = new CmisCommandController();
		$result = $this->callInaccessibleMethod($commandController, 'getTaskFactory');
		$this->assertInstanceOf('Dkd\\CmisService\\Factory\\TaskFactory', $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getQueueFactoryReturnsQueueFactoryInstance() {
		$commandController = new CmisCommandController();
		$result = $this->callInaccessibleMethod($commandController, 'getQueueFactory');
		$this->assertInstanceOf('Dkd\\CmisService\\Factory\\QueueFactory', $result);
	}

}
