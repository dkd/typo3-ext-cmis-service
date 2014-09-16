<?php
namespace Dkd\CmisService\Analysis\Detection;

use Dkd\CmisService\Configuration\Definitions\MasterConfiguration;
use Dkd\CmisService\Factory\ObjectFactory;

/**
 * Class IndexableTableDetector
 */
class IndexableTableDetector extends AbstractDetector implements DetectorInterface {

	/**
	 * Gets the names of all tables which are included in
	 * configuration but not explicitly disabled by it.
	 *
	 * @return array
	 */
	public function getEnabledTableNames() {
		$configuration = $this->getConfiguration();
		$tableConfiguration = $configuration->getTableConfiguration();
		$definitions = $tableConfiguration->getDefinitions();
		$tableNames = array();
		foreach ($definitions as $tableName => $tableDefinition) {
			$enabled = $this->isTableEnabled($tableName, $tableDefinition);
			if (TRUE === $enabled) {
				$tableNames[] = $tableName;
			}
		}
		return $tableNames;
	}

	/**
	 * Asserts if table exists according to definition but
	 * is not explicitly disabled by it. If the $definition
	 * argument is provided, only it is used when making
	 * the judgement, otherwise this definition is loaded
	 * from the master configuration's table definitions.
	 *
	 * @param string $table
	 * @param array|NULL $definition
	 * @return boolean
	 */
	public function isTableEnabled($table, $definition = NULL) {
		if (NULL === $definition) {
			$configuration = $this->getConfiguration();
			$tableConfiguration = $configuration->getTableConfiguration();
			$definition = $tableConfiguration->get($table);
		}
		if (FALSE === is_array($definition)) {
			$definition = array('enabled' => (boolean) $definition);
		}
		return (FALSE === isset($definition['enabled']) || TRUE === (boolean) $definition['enabled']);
	}

	/**
	 * Gets the currently active MasterConfiguration
	 *
	 * @return MasterConfiguration
	 */
	protected function getConfiguration() {
		return $this->getObjectFactory()->getConfiguration();
	}

	/**
	 * Gets the ObjectFactory used by the system.
	 *
	 * @return ObjectFactory
	 */
	protected function getObjectFactory() {
		return new ObjectFactory();
	}

}
