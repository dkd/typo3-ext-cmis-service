<?php
namespace Dkd\CmisService\Execution;

/**
 * Interface for Executions
 *
 * Defines methods that must be implemented
 * by Execution implementations in order to
 * be usable in the system.
 *
 * @package Dkd\CmisService\Execution
 */
interface ExecutionInterface {

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
	 * @return Result
	 */
	public function execute();

}
