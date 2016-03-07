<?php
namespace Dkd\CmisService\Execution;

use Dkd\CmisService\Error\DatabaseCallException;
use Dkd\CmisService\Error\RecordNotFoundException;
use Dkd\CmisService\Factory\ObjectFactory;
use Dkd\CmisService\Task\TaskInterface;

/**
 * Base class for Executions
 */
abstract class AbstractExecution implements ExecutionInterface {

	/**
	 * @var Result
	 */
	protected $result;

	/**
	 * Contexts passed to Logger implementations when messages
	 * are dispatched from this class.
	 *
	 * @var array
	 */
	protected $logContexts = array('cmis_service', 'execution');

	/**
	 * @var array
	 */
	protected static $eventListeners = array();

	/**
	 * Returns the Result stored in this Execution
	 * after it has been executed.
	 *
	 * @return Result
	 */
	public function getResult() {
		return $this->result;
	}

	/**
	 * Creates a pre-configured instance of Result
	 * which can be post-processed and returned after
	 * execution has ended.
	 *
	 * @return Result
	 */
	protected function createResultObject() {
		$result = new Result();
		return $result;
	}

	/**
	 * @param string $table
	 * @param integer $uid
	 * @param array $fields
	 * @return array
	 * @throws RecordNotFoundException
	 * @throws DatabaseCallException
	 */
	protected function loadRecordFromDatabase($table, $uid, array $fields) {
		if (empty($fields)) {
			$fieldList = '*';
		} else {
			$fields[] = 'uid';
			$fields[] = 'pid';
			$fieldList = implode(',', $fields);
		}
		$database = $this->getDatabaseConnection();
		$result = $database->exec_SELECTgetSingleRow($fieldList, $table, "uid = '" . $uid . "'");
		if (NULL === $result) {
			throw new DatabaseCallException($database->sql_error(), 1442925435);
		} elseif (FALSE === $result) {
			throw new RecordNotFoundException(sprintf('Record %d from table %s could not be loaded', $uid, $table), 1442925436);
		}
		return $result;
	}

	/**
	 * @return DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * Run this execution, returning the Result hereof.
	 *
	 * @param TaskInterface $task
	 * @return Result
	 */
	public function execute(TaskInterface $task) {
		return $this->result = $this->createResultObject();
	}

	/**
	 * Validates parameters and type of Task, throwing a
	 * InvalidArgumentException if this Execution is
	 * unable to execute the Task due to Task's attributes.
	 *
	 * Default implementation: no validation.
	 *
	 * @param TaskInterface $task
	 * @return boolean
	 * @throws \InvalidArgumentException
	 */
	public function validate(TaskInterface $task) {
		return TRUE;
	}

	/**
	 * Adds a class implementing EventListenerInterface
	 * to be executed when event() is called.
	 *
	 * @param string $event
	 * @param string $listenerClassName
	 * @return void
	 */
	public static function addEventListener($event, $listenerClassName) {
		if (!is_a($listenerClassName, EventListenerInterface::class, TRUE)) {
			throw new \RuntimeException(
				sprintf(
					'Invalid CMIS Service EventListener: %s must implement %s',
					$listenerClassName,
					EventListenerInterface::class
				)
			);
		}
		static::$eventListeners[get_called_class()][$event][] = $listenerClassName;
	}

	/**
	 * Called to trigger event listeners associated with
	 * the execution class or a parent class hereof.
	 *
	 * @param string $event
	 * @param TaskInterface|NULL $task
	 * @param array $data
	 * @return void
	 */
	public function event($event, TaskInterface $task = NULL, array $data = array()) {
		foreach (static::$eventListeners as $subscribedOrigin => $listenersAndEvents) {
			foreach ($listenersAndEvents as $subscribedEvent => $listenerClassNames) {
				foreach ($listenerClassNames as $listenerClassName) {
					/** @var EventListenerInterface $listener */
					$listener = new $listenerClassName();
					$listener->event($event, $this, $task, $data);
				}
			}
		}
	}

	/**
	 * @return ObjectFactory
	 * @codeCoverageIgnore
	 */
	protected function getObjectFactory() {
		return new ObjectFactory();
	}

}
