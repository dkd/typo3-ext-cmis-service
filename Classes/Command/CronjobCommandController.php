<?php
namespace Dkd\CmisService\Command;

use Dkd\CmisService\Analysis\RecordAnalyzer;
use Dkd\CmisService\Analysis\TableConfigurationAnalyzer;
use Dkd\CmisService\Factory\QueueFactory;
use Dkd\CmisService\Factory\TaskFactory;
use Dkd\CmisService\Task\TaskInterface;
use Dkd\CmisService\Queue\QueueInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Cronjob Command Controller
 *
 * Handles scheduled
 */
class CronjobCommandController extends CommandController {

	/**
	 * Truncate Queue
	 *
	 * Used when the queue should be completely flushed
	 * of all pending Tasks, regardless of status.
	 *
	 * @return void
	 */
	public function truncateQueueCommand() {
		$this->getQueue()->flush();
	}

	/**
	 * Generate Indexing Tasks
	 *
	 * Generates indexing Tasks for all monitored content.
	 * Indexing tasks are then processed by pickTask() or
	 * pickTasks($num). No actual interaction with CMIS
	 * is done by this command - the execution of indexing
	 * Tasks performs this check and if no updates are
	 * required, skips further processing and marks the
	 * Task as successfully completed.
	 *
	 * @param string $table Table to index, or empty for all tables.
	 * @return void
	 */
	public function generateIndexingTasksCommand($table = NULL) {
		$tableAnalyzer = $this->getTableConfigurationAnalyzer();
		if (NULL === $table) {
			$tables = $tableAnalyzer->getIndexableTableNames();
		} elseif (FALSE !== strpos($table, ',')) {
			$tables = explode(',', $table);
			$tables = array_map('trim', $tables);
		} else {
			$tables = array($table);
		}
		$tasks = array();
		$queue = $this->getQueue();
		$taskFactory = $this->getTaskFactory();
		foreach ($tables as $table) {
			$records = $this->getAllEnabledRecordsFromTable($table);
			$added = 0;
			foreach ($records as $record) {
				$recordAnalyzer = $this->getRecordAnalyzer($table, $record);
				$fields = $recordAnalyzer->getIndexableColumnNames();
				$tasks[] = $taskFactory->createRecordIndexingTask($table, $record['uid'], $fields);
				++ $added;
			}
			$message = sprintf('Added %d indexing task%s for table %s', $added, (1 !== $added ? 's' : ''), $table);
			$this->response->setContent($message . PHP_EOL);
			$this->response->send();
		}
		$queue->addAll($tasks);
	}

	/**
	 * Pick and execute one (1) Task from the Queue
	 *
	 * Picks the next-in-line Task from the Queue and runs
	 * it, then exits.
	 *
	 * For multiple Tasks in one run, use pickTasks()
	 *
	 * @return void
	 */
	public function pickTaskCommand() {
		$this->pickTasksCommand(1);
	}

	/**
	 * Pick and execute one or more tasks from the Queue
	 *
	 * Pick the number of Tasks indicated in $tasks and run
	 * all of them in a single run.
	 *
	 * @param integer $tasks Number of tasks to pick and execute.
	 * @return void
	 */
	public function pickTasksCommand($tasks = 1) {
		/** @var TaskInterface[] $picked */
		$picked = array();
		$queue = $this->getQueue();
		while (0 <= --$tasks && ($task = $queue->pick())) {
			$task->getWorker()->execute($task);
		}
	}

	/**
	 * Reads the current queue status
	 *
	 * @return void
	 */
	public function statusCommand() {
		$queue = $this->getQueue();
		$count = $queue->count();
		$message = sprintf('%d job%s currently queued', $count, (1 !== $count ? 's' : ''));
		$this->response->setContent($message . PHP_EOL);
	}

	/**
	 * Get every record that is not deleted or disabled by
	 * TCA configuration, from $table.
	 *
	 * @param string $table
	 * @return array
	 */
	protected function getAllEnabledRecordsFromTable($table) {
		$pageRepository = $this->getPageRepository();
		// get an "enableFields" SQL condition, string starting with " AND ".
		$condition = $pageRepository->enableFields($table, 0, array(), TRUE);
		return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', $table, '1=1' . $condition);
	}

	/**
	 * Creates an instance of TaskFactory to create Tasks.
	 *
	 * @return TaskFactory
	 */
	protected function getTaskFactory() {
		return new TaskFactory();
	}

	/**
	 * Creates an instance of QueueFactory to create Queue instance.
	 *
	 * @return QueueFactory
	 */
	protected function getQueueFactory() {
		return new QueueFactory();
	}

	/**
	 * Gets an instance of the PageRepository which is used as
	 * support class to generate enableFields conditions.
	 *
	 * @return PageRepository
	 */
	protected function getPageRepository() {
		return new PageRepository();
	}

	/**
	 * Prepare an instance of the table configuration analyzer
	 * which reads and checks tables and fields for indexability.
	 *
	 * @return TableConfigurationAnalyzer
	 */
	protected function getTableConfigurationAnalyzer() {
		return new TableConfigurationAnalyzer();
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
	 * Gets the Queue containing Tasks.
	 *
	 * @return QueueInterface
	 */
	protected function getQueue() {
		return $this->getQueueFactory()->fetchQueue();
	}

}
