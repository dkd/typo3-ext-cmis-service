<?php
namespace Dkd\CmisService\Configuration\Definitions;

use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Base class for Configuration Definitions
 *
 * Contains methods used by any implementation of a
 * ConfigurationDefinition.
 */
abstract class AbstractConfigurationDefinition implements ConfigurationDefinitionInterface {

	/**
	 * Defaults - can be overridden in implementation to
	 * contain a set of default values which can be used
	 * if system's configuration defines no value for a
	 * configurattion parameter. The defaults can be a
	 * multidepth array or a flat array indexed by the
	 * dotted path names of all parameters.
	 *
	 * @var array
	 * @api
	 */
	protected $defaults = array();

	/**
	 * @var array
	 */
	protected $definitions = array();

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
	public function get($path) {
		// Note: in order to allow values `0` and `'0'` to be used
		// without being overridden by defaults, we check these weak
		// empty types in addition to checking empty().

		// first read: deep arrays accessed by dotted.path
		$value = ObjectAccess::getPropertyPath($this->definitions, $path);
		// second read: flat array indexed by 'dotted.path'
		if (TRUE === empty($value) && 0 !== $value && '0' !== $value && TRUE === isset($this->definitions[$path])) {
			$value = $this->definitions[$path];
		}
		// third attempt: read the default using index 'dotted.path'
		if (TRUE === empty($value) && 0 !== $value && '0' !== $value && TRUE === isset($this->defaults[$path])) {
			$value = $this->defaults[$path];
		}
		// final attempt: read default from deep array by dotted.path
		if (TRUE === empty($value) && 0 !== $value && '0' !== $value) {
			$value = ObjectAccess::getPropertyPath($this->defaults, $path);
		}
		return $value;
	}

	/**
	 * Non-API!
	 *
	 * Set the definitions that will be read through the
	 * other API methods.
	 *
	 * @param array $definitions
	 * @return void
	 */
	public function setDefinitions(array $definitions) {
		$this->definitions = $definitions;
	}

	/**
	 * Non-API!
	 *
	 * Gets the master definitions array in full. Reserved
	 * for usage by ConfigurationWriter implementations that
	 * require all values of the configuration definition.
	 *
	 * @return array
	 */
	public function getDefinitions() {
		return $this->definitions;
	}

}
