<?php
namespace Dkd\CmisService\Hook;

use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * DataHandler Listener
 *
 * Listens for record changes, additions and
 * deletions using the DataHandler hooks in
 * TYPO3 CMS.
 */
class DataHandlerListener extends AbstractListener {

	const COMMAND_CREATE = 'create';
	const COMMAND_HIDE = 'hide';
	const COMMAND_UPDATE = 'update';
	const COMMAND_DELETE = 'delete';
	const COMMAND_MOVE = 'move';
	const COMMAND_TRANSLATE = 'localize';

	/**
	 * Treat a record after it has been initialised by TYPO3. Creates
	 * indexing tasks for the record.
	 *
	 * @param string $status The TCEmain operation status, fx. 'update'
	 * @param string $table The table TCEmain is currently processing
	 * @param string $id The records id (if any)
	 * @param array $fieldArray The field names and their values to be processed
	 * @param DataHandler $reference Reference to the parent object (TCEmain)
	 * @return void
	 */
	public function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, &$reference) {
		$this->handleCommand($status, $table, $id);
	}

	/**
	 * Executed after a record has been modified in database
	 * using traditional methods of record updates/deletions/additions.
	 *
	 * @param string $command
	 * @param string $table
	 * @param mixed $id
	 * @param array $fieldArray
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler
	 * @return void
	 */
	public function processDatamap_afterDatabaseOperations($command, $table, $id, array &$fieldArray, DataHandler &$reference) {
		$this->handleCommand($command, $table, $id);
	}

	/**
	 * Executes after commands which for example move records
	 * to other pages, hides a record, creates a translation etc.
	 *
	 * @param string $command
	 * @param string $table
	 * @param string $id
	 * @param array $relativeTo
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $reference
	 * @return void
	 */
	public function processCmdmap_postProcess(&$command, $table, $id, &$relativeTo, DataHandler &$reference) {
		$this->handleCommand($command, $table, $id);
	}

	/**
	 * Handles a monitored command on a monitored table or
	 * return early on unmonitored tables.
	 *
	 * @param string $command
	 * @param string $table
	 * @param integer $uid
	 * @return void
	 */
	protected function handleCommand($command, $table, $uid) {
		if (FALSE === $this->isTableMonitored($table)) {
			return;
		}
		$task = NULL;
		$taskFactory = $this->getTaskFactory();
		switch ($command) {
			case self::COMMAND_DELETE:
				// fallthrough
			case self::COMMAND_HIDE:
				$task = $taskFactory->createEvictionTask($table, $uid);
				break;
			case self::COMMAND_MOVE:
				// fallthrough
			case self::COMMAND_UPDATE:
				// fallthrough
			case self::COMMAND_TRANSLATE:
				// fallthrough
			case self::COMMAND_CREATE:
				$task = $taskFactory->createRecordIndexingTask($table, $uid);
				break;
			default:
		}
		if (NULL !== $task) {
			// Queueing: flushByFilter() to remove currently queued
			// Tasks which are duplicate of $task (parameters identical).
			$queue = $this->getQueue();
			$queue->flushByFilter($task);
			$queue->add($task);
		}
	}

}
