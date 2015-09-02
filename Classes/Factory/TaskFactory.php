<?php
namespace Dkd\CmisService\Factory;
use Dkd\CmisService\Task\EvictionTask;
use Dkd\CmisService\Task\InitializationTask;
use Dkd\CmisService\Task\RecordIndexTask;

/**
 * Class TaskFactory
 */
class TaskFactory {

	/**
	 * Creates a Task to index one record from a table.
	 *
	 * @param string $table
	 * @param integer $uid
	 * @param array|NULL fields
	 * @param boolean $includeRelations
	 * @return RecordIndexTask
	 */
	public function createRecordIndexingTask($table, $uid, $fields = NULL, $includeRelations = FALSE) {
		$task = new RecordIndexTask();
		$task->setParameter(RecordIndexTask::OPTION_TABLE, (string) $table);
		$task->setParameter(RecordIndexTask::OPTION_UID, (integer) $uid);
		$task->setParameter(RecordIndexTask::OPTION_FIELDS, (array) $fields);
		$task->setParameter(RecordIndexTask::OPTION_RELATIONS, (boolean) $includeRelations);
		return $task;
	}

	/**
	 * Creates a Task to evict one or all record(s) from the index.
	 *
	 * @param string $table
	 * @param integer|NULL $uid
	 * @return EvictionTask
	 */
	public function createEvictionTask($table, $uid = NULL) {
		$task = new EvictionTask();
		$task->setParameter(EvictionTask::OPTION_TABLE, (string) $table);
		$task->setParameter(EvictionTask::OPTION_UID, (integer) $uid);
		return $task;
	}

	/**
	 * Creates a Task to initialize the CMIS data storage.
	 *
	 * @return InitializationTask
	 */
	public function createInitializationTask() {
		return new InitializationTask();
	}

}
