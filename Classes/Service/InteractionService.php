<?php
namespace Dkd\CmisService\Service;

use Dkd\CmisService\Analysis\RecordAnalyzer;
use Dkd\CmisService\Configuration\Definitions\CmisConfiguration;
use Dkd\CmisService\Execution\Result;
use Dkd\CmisService\Factory\CmisObjectFactory;
use Dkd\CmisService\Factory\ObjectFactory;
use Dkd\CmisService\Factory\QueueFactory;
use Dkd\CmisService\Factory\TaskFactory;
use Dkd\CmisService\Factory\WorkerFactory;
use Dkd\CmisService\Queue\QueueInterface;
use Dkd\CmisService\SingletonInterface;
use Dkd\PhpCmis\Exception\CmisRuntimeException;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Class InteractionService
 *
 * Contains methods to be called from controllers/commands
 * which execute the same business logic.
 */
class InteractionService implements SingletonInterface {

	/**
	 * Contexts passed to Logger implementations when messages
	 * are dispatched from this class.
	 *
	 * @var array
	 */
	protected $logContexts = array('cmis_service', 'service_interaction');

	/**
	 * @param string $tableName
	 * @return array
	 */
	public function getTableConfigurationByTableName($tableName) {
		return $this->getObjectFactory()->getConfiguration()->getTableConfiguration()->getSingleTableConfiguration($tableName);
	}

	/**
	 * @return array
	 */
	public function getConfiguredServerNames() {
		return $this->getObjectFactory()->getConfiguration()->getCmisConfigurationNames();
	}

	/**
	 * @return array
	 */
	public function getActiveServerName() {
		return $this->getObjectFactory()->getConfiguration()->getActiveConfigurationName();
	}

	/**
	 * @param string $serverName
	 * @return CmisConfiguration
	 */
	public function getServerConfigurationByServerName($serverName) {
		return $this->getObjectFactory()->getConfiguration()->getCmisConfiguration($serverName);
	}

	/**
	 * @param string $serverName
	 * @return Result
	 */
	public function checkServerConnection($serverName) {
		try {
			$rootFolderId = $this->getCmisObjectFactory()->getSession($serverName)->getRootFolder()->getId();
			$message = sprintf('Server "%s" is okay - root folder ID: "%s"', $serverName, $rootFolderId);
			$code = Result::OK;
		} catch (CmisRuntimeException $error) {
			$message = sprintf('Connection error: "%s"', $error->getMessage());
			$code = Result::ERR;
		}
		return new Result($message, $code);
	}

	/**
	 * @return integer
	 */
	public function countQueue() {
		return $this->getQueue()->count();
	}

	/**
	 * @return integer
	 */
	public function countIdentities() {
		return (integer) $this->getDatabaseConnection()->exec_SELECTcountRows('uid', 'tx_cmisservice_identity');
	}

	/**
	 * @return Result
	 */
	public function pickTask() {
		return reset($this->pickTasks(1));
	}

	/**
	 * @param integer $tasks
	 * @return Result[]
	 */
	public function pickTasks($tasks = 1) {
		$requestedTasks = $tasks;
		$queue = $this->getQueue();
		$results = array();
		while (0 <= --$tasks && ($task = $queue->pick())) {
			$results[] = $task->getWorker()->execute($task);
		}
		$this->getObjectFactory()->getLogger()->info(sprintf('Executed %d Worker task(s)', $requestedTasks), $this->logContexts);
		return $results;
	}

	/**
	 * @return Result
	 */
	public function readQueueStatus() {
		$queue = $this->getQueue();
		$count = $queue->count();
		$message = sprintf('%d job%s currently queued', $count, (1 !== $count ? 's' : ''));
		$message = sprintf('Status: %s', $message);
		$this->getObjectFactory()->getLogger()->debug($message, $this->logContexts);
		return new Result($message);
	}

	/**
	 * @return Result
	 */
	public function truncateQueue() {
		$message = 'Truncate queue command executed';
		$this->getQueue()->flush();
		$this->getObjectFactory()->getLogger()->debug($message, $this->logContexts);
		return new Result($message);
	}

	/**
	 * @return Result
	 */
	public function truncateIdentities() {
		$this->getDatabaseConnection()->exec_TRUNCATEquery('tx_cmisservice_identity');
		return new Result('Identities truncated');
	}

	/**
	 * @return \Dkd\CmisService\Execution\Result
	 */
	public function initializeRepository() {
		$taskFactory = $this->getTaskFactory();
		$initializationTask = $taskFactory->createInitializationTask();
		$worker = $this->getWorkerFactory()->createWorker();
		$result = $worker->execute($initializationTask);
		$this->getObjectFactory()->getLogger()->info('Initialization performed', $this->logContexts);
		return $result;
	}

	/**
	 * Generate all required indexing tasks for table
	 *
	 * @param string $table
	 * @return Result
	 */
	public function createAndAddIndexingTasks($table) {
		$indexingTasks = array();
		$records = $this->getAllEnabledRecordsFromTable($table);
		foreach ($records as $record) {
			$indexingTasks[] = $this->createRecordIndexingTask($table, $record);
			$indexingTasks[] = $this->createRecordIndexingTask($table, $record, TRUE);
		}
		$this->getQueue()->addAll($indexingTasks);
		$message = sprintf('Added %d indexing task(s) for table %s', count($indexingTasks), $table);
		$this->getObjectFactory()->getLogger()->info($message, $this->logContexts);
		return new Result($message);
	}

	/**
	 * @return array
	 */
	public function getMonitoredTableNames() {
		return $this->getObjectFactory()->getConfiguration()->getTableConfiguration()->getConfiguredTableNames();
	}

	/**
	 * Get every record that is not deleted or disabled by
	 * TCA configuration, from $table.
	 *
	 * @param string $table
	 * @return array
	 */
	public function getAllEnabledRecordsFromTable($table) {
		$pageRepository = $this->getPageRepository();
		// get an "enableFields" SQL condition, string starting with " AND ".
		$condition = $pageRepository->enableFields($table, 0, array(), TRUE);
		return $this->getDatabaseConnection()->exec_SELECTgetRows('*', $table, '1=1' . $condition);
	}

	/**
	 * Count every record that is not deleted or disabled by
	 * TCA configuration, from $table.
	 *
	 * @param string $table
	 * @return integer
	 */
	public function countAllEnabledRecordsFromTable($table) {
		$pageRepository = $this->getPageRepository();
		// get an "enableFields" SQL condition, string starting with " AND ".
		$condition = $pageRepository->enableFields($table, 0, array(), TRUE);
		return $this->getDatabaseConnection()->exec_SELECTcountRows('uid', $table, '1=1' . $condition);
	}

	/**
	 * @param string $table
	 * @param array $record
	 * @param boolean $includeRelations
	 * @return TaskInterface
	 */
	protected function createRecordIndexingTask($table, $record, $includeRelations = FALSE) {
		$taskFactory = $this->getTaskFactory();
		$recordAnalyzer = $this->getRecordAnalyzer($table, $record);
		$fields = $recordAnalyzer->getIndexableColumnNames();
		return $taskFactory->createRecordIndexingTask($table, $record['uid'], $fields, $includeRelations);
	}

	/**
	 * Prepare an instance of the record analyzer.
	 *
	 * @param string $table
	 * @param array $record
	 * @return RecordAnalyzer
	 */
	protected function getRecordAnalyzer($table, $record) {
		return new RecordAnalyzer($table, $record);
	}

	/**
	 * Gets an instance of the PageRepository which is used as
	 * support class to generate enableFields conditions.
	 *
	 * @codeCoverageIgnore
	 * @return PageRepository
	 */
	protected function getPageRepository() {
		return new PageRepository();
	}

	/**
	 * Creates an instance of ObjectFactory to create new objects.
	 *
	 * @codeCoverageIgnore
	 * @return ObjectFactory
	 */
	protected function getObjectFactory() {
		return new ObjectFactory();
	}

	/**
	 * Creates an instance of CmisObjectFactory to create session instances.
	 *
	 * @codeCoverageIgnore
	 * @return CmisObjectFactory
	 */
	protected function getCmisObjectFactory() {
		return new CmisObjectFactory();
	}

	/**
	 * Creates an instance of QueueFactory to create Queue instance.
	 *
	 * @codeCoverageIgnore
	 * @return QueueFactory
	 */
	protected function getQueueFactory() {
		return new QueueFactory();
	}

	/**
	 * Creates an instance of WorkerFactory to create new workers.
	 *
	 * @codeCoverageIgnore
	 * @return WorkerFactory
	 */
	protected function getWorkerFactory() {
		return new WorkerFactory();
	}

	/**
	 * Creates an instance of TaskFactory to create Tasks.
	 *
	 * @codeCoverageIgnore
	 * @return TaskFactory
	 */
	protected function getTaskFactory() {
		return new TaskFactory();
	}

	/**
	 * Gets the Queue containing Tasks.
	 *
	 * @codeCoverageIgnore
	 * @return QueueInterface
	 */
	protected function getQueue() {
		return $this->getQueueFactory()->fetchQueue();
	}

	/**
	 * @return DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}
}
