<?php
namespace Dkd\CmisService\Tests\Unit\Task;

use Dkd\CmisService\Task\EvictionTask;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class RecordIndexTaskTest
 */
class EvictionTaskTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function resolveExecutionObjectReturnsCmisEvictionExecution() {
		$instance = new EvictionTask();
		$execution = $instance->resolveExecutionObject();
		$this->assertInstanceOf('Dkd\\CmisService\\Execution\\Cmis\\EvictionExecution', $execution);
	}

}
