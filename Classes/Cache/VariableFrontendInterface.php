<?php
namespace Dkd\CmisService\Cache;

/**
 * Interface for classes capable of delegating
 * cache operations (save, get, has) to a
 * supported implementation provided by the
 * host system.
 *
 * @package Dkd\CmisService\Cache
 */
interface VariableFrontendInterface {

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function get($name);

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return mixed
	 */
	public function set($name, $value);

	/**
	 * @param string $name
	 * @return boolean
	 */
	public function has($name);

}
