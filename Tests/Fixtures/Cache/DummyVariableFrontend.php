<?php
namespace Dkd\CmisService\Tests\Fixtures\Cache;

use Dkd\CmisService\Cache\VariableFrontendInterface;

/**
 * Class DummyVariableFrontend
 */
class DummyVariableFrontend implements VariableFrontendInterface {

	/**
	 * Mock
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function get($name) {
		return NULL;
	}

	/**
	 * Mock
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return mixed
	 */
	public function set($name, $value) {
		return NULL;
	}

	/**
	 * Mock
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function has($name) {
		return FALSE;
	}

}
