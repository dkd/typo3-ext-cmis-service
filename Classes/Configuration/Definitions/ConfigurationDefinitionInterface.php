<?php
namespace Dkd\CmisService\Configuration\Definitions;

/**
 * Configuration Definition Interface
 *
 * Methods to be implemented in classes supporting
 * ConfigurationDefinition behavior.
 *
 * @package Dkd\CmisService\Configuration\Definitions
 */
interface ConfigurationDefinitionInterface {

	/**
	 * API
	 *
	 * Gets a stored configuration parameter by path.
	 *
	 * If path contains dots, the internal definitions
	 * array is depth-traversed using the dots as path
	 * separators, e.g. reading $array['name1']['name2']
	 * if given a path value of 'name1.name2'.
	 *
	 * @param string $path
	 * @return mixed
	 * @api
	 */
	public function get($path);

	/**
	 * Non-API!
	 *
	 * Set the definitions that will be read through the
	 * other API methods.
	 *
	 * @param array $definitions
	 * @return void
	 */
	public function setDefinitions(array $definitions);

	/**
	 * Non-API!
	 *
	 * Gets the master definitions array in full. Reserved
	 * for usage by ConfigurationWriter implementations that
	 * require all values of the configuration definition.
	 *
	 * @return array
	 */
	public function getDefinitions();

}
