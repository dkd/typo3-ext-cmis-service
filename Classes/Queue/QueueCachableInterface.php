<?php
namespace Dkd\CmisService\Queue;

use Dkd\CmisService\Cache\VariableFrontendInterface;

/**
 * Queue Cachable Interface
 *
 * Must be implemented if the Queue implementation also
 * should receive an instance of a VariableFrontend to
 * implement caching-based features.
 */
interface QueueCachableInterface {

	/**
	 * @param VariableFrontendInterface $frontend
	 * @return void
	 */
	public function setCache(VariableFrontendInterface $frontend);

}
