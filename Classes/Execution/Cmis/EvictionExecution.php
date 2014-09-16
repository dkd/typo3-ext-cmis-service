<?php
namespace Dkd\CmisService\Execution\Cmis;

use Dkd\CmisService\Execution\AbstractExecution;
use Dkd\CmisService\Execution\ExecutionInterface;
use Dkd\CmisService\Execution\Result;

/**
 * Class EvictionExecution
 */
class EvictionExecution extends AbstractExecution implements ExecutionInterface {

	/**
	 * Evict a document from the index.
	 *
	 * @return Result
	 */
	public function execute() {
		$this->result = $this->createResultObject();
		return $this->result;
	}

}
