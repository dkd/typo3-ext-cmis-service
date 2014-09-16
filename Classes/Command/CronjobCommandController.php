<?php
namespace Dkd\CmisService\Command;

use Dkd\CmisService\Analysis\TableConfigurationAnalyzer;
use Dkd\CmisService\Factory\QueueFactory;
use Dkd\CmisService\Queue\QueueInterface;
use Dkd\CmisService\Task\TaskInterface;
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
		$analyzer = $this->getTableConfigurationAnalyzer();
		if (NULL === $table) {
			$tables = $analyzer->getIndexableTableNames();
		} else {
			$tables = array($table);
		}
		// @TODO: fill actual tasks
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
		do {
			$task = $queue->pick();
			$task->getWorker()->execute($task);
		} while (0 < --$tasks);
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
	 * Gets the Queue containing Tasks.
	 *
	 * @return QueueInterface
	 */
	protected function getQueue() {
		$queueFactory = new QueueFactory();
		$queue = $queueFactory->fetchQueue();
		return $queue;
	}

}
