<?php
namespace Dkd\CmisService\Tests\Unit\Factory;

use Dkd\CmisService\Factory\WorkerFactory;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class WorkerFactoryTest
 */
class WorkerFactoryTest extends UnitTestCase {

	/**
	 * Tests that this factory creates Worker instances
	 *
	 * @test
	 * @return void
	 */
	public function createsWorkers() {
		$factory = new WorkerFactory();
		$worker = $factory->createWorker();
		$this->assertInstanceof('Dkd\\CmisService\\Queue\\WorkerInterface', $worker);
	}

}
