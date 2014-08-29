<?php
namespace Dkd\CmisService\Tests\Fixtures\Configuration;

use Dkd\CmisService\Configuration\Definitions\ConfigurationDefinitionInterface;

/**
 * Class DummyConfigurationDefinition
 */
class DummyConfigurationDefinition implements ConfigurationDefinitionInterface {

	/**
	 * DummyConfigurationDefinition always returns name of
	 * variable passed as argument-
	 *
	 * @param string $path
	 * @return mixed
	 * @api
	 */
	public function get($path) {
		return $path;
	}

	/**
	 * Does nothing with $definitions
	 *
	 * @param array $definitions
	 * @return void
	 */
	public function setDefinitions(array $definitions) {
	}

	/**
	 * Always returns array('foo' => 'bar')
	 *
	 * @return array
	 */
	public function getDefinitions() {
		return array('foo' => 'bar');
	}

}
