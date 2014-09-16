<?php
namespace Dkd\CmisService\Factory;

use Dkd\CmisService\Execution\Cmis\EvictionExecution;
use Dkd\CmisService\Execution\Cmis\IndexExecution;

/**
 * Class ExecutionFactory
 */
class ExecutionFactory {

	/**
	 * @return IndexExecution
	 */
	public function createIndexExecution() {
		return new IndexExecution();
	}

	/**
	 * @return EvictionExecution
	 */
	public function createEvictionExecution() {
		return new EvictionExecution();
	}

}
