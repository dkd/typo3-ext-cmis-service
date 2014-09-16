<?php
namespace Dkd\CmisService\Factory;

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

}
