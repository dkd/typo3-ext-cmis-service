<?php
namespace Dkd\CmisService\Queue;

use Dkd\CmisService\Factory\WorkerFactory;
use Dkd\CmisService\Task\TaskFilterInterface;
use Dkd\CmisService\Task\TaskInterface;

/**
 * Class DatabaseTableQueue
 */
class DatabaseTableQueue implements QueueInterface {

	const QUERY_FLUSH_ALL = 'DELETE FROM tx_cmisservice_queue';
	const QUERY_FLUSH_FILTER = 'DELETE FROM tx_cmisservice_queue WHERE task_class = \'%s\' AND parameters = \'%s\'';
	const QUERY_ADD_CHECK = 'SELECT uid FROM tx_cmisservice_queue WHERE task_class = \'%s\' AND parameters = \'%s\' AND resource_identifier = \'%s\'';
	const QUERY_ADD = 'INSERT INTO tx_cmisservice_queue SET task_class = \'%s\', parameters = \'%s\', resource_identifier = \'%s\'';
	const QUERY_PICK = 'SELECT * FROM tx_cmisservice_queue ORDER BY uid ASC LIMIT 0,1';
	const QUERY_DELETE = 'DELETE FROM tx_cmisservice_queue WHERE uid = %d';
	const QUERY_COUNT = 'SELECT uid FROM tx_cmisservice_queue';

	/**
	 * @param TaskInterface[] $task
	 * @return void
	 */
	public function addAll(array $tasks) {
		foreach ($tasks as $task) {
			$this->add($task);
		}
	}

	/**
	 * @param TaskInterface $task
	 * @return void
	 */
	public function add(TaskInterface $task) {
		$queryParameters = array(addslashes(get_class($task)), serialize($task->getParameters()), $task->getResourceId());
		if (FALSE === $this->performDatabaseQuery(self::QUERY_ADD_CHECK, $queryParameters)) {
			$this->performDatabaseQuery(self::QUERY_ADD, $queryParameters);
		}
	}

	/**
	 * Recreates a Task based on the information stored in
	 * the queue. We don't store a fully serialized representation;
	 * instead, each queue record contains the type of task and the
	 * parameters it had when originally added to the queue.
	 *
	 * @return TaskInterface|NULL
	 */
	public function pick() {
		$data = $this->performDatabaseQuery(self::QUERY_PICK);
		if ($data) {
			// data loaded; delete the queue record immediately.
			$this->performDatabaseQuery(self::QUERY_DELETE, array($data['uid']));
		} else {
			return NULL;
		}
		$class = $data['task_class'];
		if (!is_a($class, 'Dkd\\CmisService\\Task\\TaskInterface', TRUE)) {
			throw new \RuntimeException(
				'Security issue detected; Task queued in DatabaseTableQueue is not a valid TaskInterface implementation. ' .
				'The provided class name was ' . $class
			);
		}
		$factory = new WorkerFactory();
		$worker = $factory->createWorker();
		$parameters = (array) unserialize($data['parameters']);
		/** @var TaskInterface $task */
		$task = new $class();
		foreach ($parameters as $parameter => $value) {
			$task->setParameter($parameter, $value);
		}
		$task->assign($worker);
		return $task;
	}

	/**
	 * @return integer
	 */
	public function count() {
		return count($this->performDatabaseQuery(self::QUERY_COUNT));
	}

	/**
	 * @return void
	 */
	public function flush() {
		$this->performDatabaseQuery(self::QUERY_FLUSH_ALL);
	}

	/**
	 * @param TaskFilterInterface $filter
	 * @return void
	 */
	public function flushByFilter(TaskFilterInterface $filter) {
		$this->performDatabaseQuery(self::QUERY_FLUSH_FILTER, array(get_class($filter), serialize($filter->getParameters())));
	}

	/**
	 * Wrapper to execute SQL queries; allowing those queries
	 * to be dispatched through the desired API as well as
	 * mocking of expected queries in unit tests. Return type
	 * is mixed and the specific type must be checked by those
	 * methods using this wrapper; just like they would when
	 * using standard MySQL functions in PHP.
	 *
	 * The `$query` parameter should be one of the QUERY_*
	 * constants on this class and `$parameters` must contain
	 * the number and order of parameters required by the query.
	 *
	 * @codeCoverageIgnore
	 * @param string $query
	 * @param array $parameters
	 * @return mixed
	 */
	protected function performDatabaseQuery($query, array $parameters = array()) {
		$query = vsprintf($query, $parameters);
		$result = $GLOBALS['TYPO3_DB']->sql_query($query);
		if ($result ) {
			if (is_object($result)) {
				return $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
			}
			return $result;
		}
		return FALSE;
	}

}
