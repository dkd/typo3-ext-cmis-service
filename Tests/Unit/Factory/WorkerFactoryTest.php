<?php
namespace Dkd\CmisService\Factory;

/**
 * Class WorkerFactoryTest
 *
 * @package Dkd\CmisService\Factory
 */
class WorkerFactoryTest {

	/**
	 * Tests that this factory creates Worker instances
	 *
	 * @test
	 * @return void
	 */
	public function createsWorkers() {
		$factory = $this->getMock('Dkd\CmisService\Factory\WorkerFactory');
		$worker = $factory->createWorker();
		$this->assertInstanceof('Dkd\CmisService\Queue\WorkerInterface', $worker);
	}

}
