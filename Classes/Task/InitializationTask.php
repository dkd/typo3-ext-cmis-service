<?php
namespace Dkd\CmisService\Task;

use Dkd\CmisService\Factory\ExecutionFactory;
use Dkd\CmisService\Execution\ExecutionInterface;

/**
 * Class InitializationTask
 */
class InitializationTask extends AbstractTask implements TaskInterface, TaskFilterInterface {

	const OPTION_MODELPATHANDFILENAME = 'modelPathAndFilename';

	/**
	 * Returns an Execution object for indexing the
	 * record as configured by Task's options.
	 *
	 * @return ExecutionInterface
	 */
	public function resolveExecutionObject() {
		$executionFactory = new ExecutionFactory();
		return $executionFactory->createInitializationExecution();
	}

	/**
	 * Returns TRUE if this Task matches $task
	 *
	 * @param TaskInterface $task
	 * @return boolean
	 */
	public function matches(TaskInterface $task) {
		return ($task instanceof $this);
	}

}
