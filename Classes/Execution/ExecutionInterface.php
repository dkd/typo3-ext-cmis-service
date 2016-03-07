<?php
namespace Dkd\CmisService\Execution;

use Dkd\CmisService\Task\TaskInterface;

/**
 * Interface for Executions
 *
 * Defines methods that must be implemented
 * by Execution implementations in order to
 * be usable in the system.
 */
interface ExecutionInterface {

	const EVENT_START = 'start';
	const EVENT_FINISH = 'finish';
	const EVENT_ERROR = 'error';
	const EVENT_VALIDATE = 'validate';
	const EVENT_VALID = 'valid';

	/**
	 * Returns the Result instance stored in this Execution
	 * after it has been executed.
	 *
	 * @return Result
	 */
	public function getResult();

	/**
	 * Run this Execution, returning the Result hereof.
	 *
	 * @param TaskInterface $task The task to be executed
	 * @return Result
	 */
	public function execute(TaskInterface $task);

	/**
	 * Validates parameters and type of Task, throwing a
	 * InvalidArgumentException if this Execution is
	 * unable to execute the Task due to Task's attributes.
	 *
	 * @param TaskInterface $task
	 * @return boolean
	 * @throws \InvalidArgumentException
	 */
	public function validate(TaskInterface $task);

	/**
	 * Called to trigger event listeners associated with
	 * the execution class or a parent class hereof.
	 *
	 * @param string $event
	 * @param TaskInterface|NULL $task
	 * @param array $data
	 * @return void
	 */
	public function event($event, TaskInterface $task = NULL, array $data = array());

	/**
	 * Adds a class implementing EventListenerInterface
	 * to be executed when event() is called.
	 *
	 * @param string $event
	 * @param string $listenerClassName
	 * @return void
	 */
	public static function addEventListener($event, $listenerClassName);

}
