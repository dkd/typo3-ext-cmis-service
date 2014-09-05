<?php
namespace Dkd\CmisService\Execution;

/**
 * Base class for Executions
 */
abstract class AbstractExecution implements ExecutionInterface {

	/**
	 * @var Result
	 */
	protected $result;

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
	 * Run this execution, returning the Result hereof.
	 *
	 * @return Result
	 */
	public function execute() {
		return $this->result = $this->createResultObject();
	}

}
