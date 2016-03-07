<?php
namespace Dkd\CmisService\Execution;

use Dkd\CmisService\Task\TaskInterface;

/**
 * Interface EventListenerInterface
 */
interface EventListenerInterface {

	/**
	 * @param string $event
	 * @param ExecutionInterface $execution
	 * @param TaskInterface $task
	 * @param array $data
	 * @return void
	 */
	public function event($event, ExecutionInterface $execution, TaskInterface $task = NULL, $data = array());

}
