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

}
