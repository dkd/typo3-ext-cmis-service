<?php
namespace Dkd\CmisService\Tests\Unit\Command;

use Dkd\CmisService\Command\CronjobCommandController;
use Dkd\CmisService\Tests\Fixtures\Queue\DummyWorker;
use Dkd\CmisService\Tests\Fixtures\Task\DummyTask;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class CronjobCommandControllerTest
 */
class CronjobCommandControllerTest extends UnitTestCase {

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
		$commandController = new CronjobCommandController();
		$result = $this->callInaccessibleMethod($commandController, 'getQueue');
		$this->assertInstanceOf('Dkd\\CmisService\\Queue\\QueueInterface', $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function truncateQueueCallsFlush() {
		$commandController = $this->getMock('Dkd\\CmisService\\Command\\CronjobCommandController', array('getQueue'));
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
		$commandController = $this->getMock('Dkd\\CmisService\\Command\\CronjobCommandController', array('pickTasksCommand'));
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
		$commandController = $this->getMock('Dkd\\CmisService\\Command\\CronjobCommandController', array('getQueue'));
		$commandController->expects($this->once())->method('getQueue')->will($this->returnValue($queue));
		$commandController->pickTasksCommand(3);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getTableConfigurationAnalyzerReturnsTableConfigurationAnalyzerInstance() {
		$commandController = new CronjobCommandController();
		$result = $this->callInaccessibleMethod($commandController, 'getTableConfigurationAnalyzer');
		$this->assertInstanceOf('Dkd\\CmisService\\Analysis\\TableConfigurationAnalyzer', $result);
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
		$commandController = $this->getMock('Dkd\\CmisService\\Command\\CronjobCommandController', array('getPageRepository'));
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
		$commandController = $this->getMock(
			'Dkd\\CmisService\\Command\\CronjobCommandController',
			array('getTableConfigurationAnalyzer')
		);
		$tableConfigurationAnalyzer = $this->getMock(
			'Dkd\\CmisService\\Analysis\\TableConfigurationAnalyzer',
			array('getIndexableTableNames')
		);
		$commandController->expects($this->once())->method('getTableConfigurationAnalyzer')
			->will($this->returnValue($tableConfigurationAnalyzer));
		$tableConfigurationAnalyzer->expects($this->once())->method('getIndexableTableNames')
			->will($this->returnValue(array(
				array('uid' => 1),
				array('uid' => 2)
			)));
		$commandController->generateIndexingTasksCommand();
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function generateIndexingTasksCommandWithTableOnlyProcessesSpecifiedTable() {
		$commandController = $this->getMock(
			'Dkd\\CmisService\\Command\\CronjobCommandController',
			array('getTableConfigurationAnalyzer')
		);
		$tableConfigurationAnalyzer = $this->getMock(
			'Dkd\\CmisService\\Analysis\\TableConfigurationAnalyzer',
			array('getIndexableTableNames')
		);
		$commandController->expects($this->once())->method('getTableConfigurationAnalyzer')
			->will($this->returnValue($tableConfigurationAnalyzer));
		$tableConfigurationAnalyzer->expects($this->never())->method('getIndexableTableNames');
		$commandController->generateIndexingTasksCommand('foobar');
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getPageRepositoryReturnsPageRepository() {
		$commandController = new CronjobCommandController();
		$result = $this->callInaccessibleMethod($commandController, 'getPageRepository');
		$this->assertInstanceOf('TYPO3\\CMS\\Frontend\\Page\\PageRepository', $result);
	}

}
