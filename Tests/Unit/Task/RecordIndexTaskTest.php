<?php
namespace Dkd\CmisService\Tests\Unit\Task;

use Dkd\CmisService\Task\RecordIndexTask;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class RecordIndexTaskTest
 */
class RecordIndexTaskTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function resolveExecutionObjectReturnsCmisIndexExecution() {
		$instance = new RecordIndexTask();
		$execution = $instance->resolveExecutionObject();
		$this->assertInstanceOf('Dkd\\CmisService\\Execution\\Cmis\\IndexExecution', $execution);
	}

}
