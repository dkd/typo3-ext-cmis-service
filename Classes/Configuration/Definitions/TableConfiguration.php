<?php
namespace Dkd\CmisService\Configuration\Definitions;

/**
 * Table Configuration Definition
 */
class TableConfiguration extends AbstractConfigurationDefinition implements ConfigurationDefinitionInterface {

	/**
	 * Gets the names of all tables currently configured
	 *
	 * @return array
	 */
	public function getConfiguredTableNames() {
		return array_keys($this->definitions);
	}

	/**
	 * @param string $table
	 * @return boolean
	 */
	public function isTableConfigured($table) {
		return in_array($table, $this->getConfiguredTableNames());
	}

	/**
	 * @param string $table
	 * @return boolean
	 */
	public function isTableEnabled($table) {
		return (boolean) $this->get($table . '.enabled');
	}

	/**
	 * @param string $table
	 * @return array
	 */
	public function getSingleTableConfiguration($table) {
		return (array) $this->get($table);
	}

	/**
	 * @param string $table
	 * @return array
	 */
	public function getSingleTableDefaultValues($table) {
		return (array) $this->get($table . '.defaults');
	}

	/**
	 * @param string $table
	 * @return string
	 */
	public function getSinglePrimaryType($table) {
		return (string) $this->get($table . '.primaryType');
	}

	/**
	 * @param string $table
	 * @return array
	 */
	public function getSingleSecondaryTypes($table) {
		return (array) $this->get($table . '.secondaryTypes');
	}

	/**
	 * @param string $table
	 * @return array
	 */
	public function getSingleTableMapping($table) {
		return (array) $this->get($table . '.mapping');
	}

}
