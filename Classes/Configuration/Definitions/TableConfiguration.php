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

}
