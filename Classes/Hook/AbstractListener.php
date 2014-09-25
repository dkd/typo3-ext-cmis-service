<?php
namespace Dkd\CmisService\Hook;

use Dkd\CmisService\Analysis\Detection\IndexableTableDetector;
use Dkd\CmisService\Factory\ObjectFactory;
use Dkd\CmisService\Factory\QueueFactory;
use Dkd\CmisService\Factory\TaskFactory;
use Dkd\CmisService\Queue\QueueInterface;
use Dkd\CmisService\SingletonInterface;

/**
 * Abstract Listener
 *
 * Base class for classes acting as Listeners,
 * reacting to records being saved, deleted,
 * created, moved etc.
 *
 * Contains shared utility methods to interact
 * with the Queue - allowing new record indexing
 * tasks to be created and existing tasks to be
 * flushed if they match a TaskFilter.
 */
abstract class AbstractListener implements SingletonInterface {

	/**
	 * @var array
	 */
	protected static $monitoredTables = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->initializeMonitoredTables();
	}

	/**
	 * Initialize the L1 cache of monitored table names
	 * for better performance on lookups.
	 *
	 * @return void
	 */
	protected function initializeMonitoredTables() {
		$detector = $this->getIndexableTableDetector();
		self::$monitoredTables = $detector->getEnabledTableNames();
	}

	/**
	 * Returns TRUE if the table is currently monitored
	 * according to configuration and column composition.
	 *
	 * @param string $table
	 * @return boolean
	 */
	protected function isTableMonitored($table) {
		return in_array($table, self::$monitoredTables);
	}

	/**
	 * Creates and queues an indexing task for $table:$uid
	 *
	 * @param string $table
	 * @param integer $uid
	 * @return void
	 */
	protected function createAndQueueIndexingTask($table, $uid) {
		$task = $this->getTaskFactory()->createRecordIndexingTask($table, $uid);
		$queue = $this->getQueue();
		$queue->flushByFilter($task);
		$queue->add($task);
	}

	/**
	 * @param string $table
	 * @param integer|NULL $uid
	 * @return void
	 */
	protected function createAndQueueEvictionTask($table, $uid = NULL) {
		$task = $this->getTaskFactory()->createEvictionTask($table, $uid);
		$queue = $this->getQueue();
		$queue->flushByFilter($task);
		$queue->add($task);
	}

	/**
	 * Removes all currently stored indexing tasks for $table:$uid
	 *
	 * @param string $table
	 * @param integer $uid
	 * @return void
	 */
	protected function removeAllIndexingTasksForTableAndUid($table, $uid) {
		$task = $this->getTaskFactory()->createRecordIndexingTask($table, $uid);
		$this->getQueue()->flushByFilter($task);
	}

	/**
	 * Gets an instance of the indexable table detector.
	 *
	 * @return IndexableTableDetector
	 */
	protected function getIndexableTableDetector() {
		return new IndexableTableDetector();
	}

	/**
	 * Gets the Queue instance used by the system.
	 *
	 * @return QueueInterface
	 */
	protected function getQueue() {
		return $this->getQueueFactory()->fetchQueue();
	}

	/**
	 * Gets an instance of the QueueFactory
	 *
	 * @return QueueFactory
	 */
	protected function getQueueFactory() {
		return new QueueFactory();
	}

	/**
	 * Gets an instance of the TaskFactory
	 *
	 * @return TaskFactory
	 */
	protected function getTaskFactory() {
		return new TaskFactory();
	}

	/**
	 * Gets an instance of the ObjectFactory
	 *
	 * @return ObjectFactory
	 */
	protected function getObjectFactory() {
		return new ObjectFactory();
	}

}
