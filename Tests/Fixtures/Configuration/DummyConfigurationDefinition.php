<?php
namespace Dkd\CmisService\Tests\Fixtures\Configuration;

use Dkd\CmisService\Configuration\Definitions\ConfigurationDefinitionInterface;

/**
 * Class DummyConfigurationDefinition
 */
class DummyConfigurationDefinition implements ConfigurationDefinitionInterface {

	/**
	 * @var array
	 */
	protected $definitions = array();

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
	 * Sets $this->definitions
	 *
	 * @param array $definitions
	 * @return void
	 */
	public function setDefinitions(array $definitions) {
		$this->definitions = $definitions;
	}

	/**
	 * Always returns $this->definitions
	 *
	 * @return array
	 */
	public function getDefinitions() {
		return $this->definitions;
	}

}
