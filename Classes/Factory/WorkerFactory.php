<?php
namespace Dkd\CmisService\Factory;

use Dkd\CmisService\Queue\SimpleWorker;
use Dkd\CmisService\Queue\WorkerInterface;

/**
 * Factory to create new Worker instances.
 */
class WorkerFactory {

	/**
	 * Creates a Worker
	 *
	 * @return WorkerInterface
	 */
	public function createWorker() {
		$worker = new SimpleWorker();
		return $worker;
	}

}
