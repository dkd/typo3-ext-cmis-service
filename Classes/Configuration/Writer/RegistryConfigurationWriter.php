<?php
namespace Dkd\CmisService\Configuration\Writer;

use Dkd\CmisService\Configuration\Definitions\ConfigurationDefinitionInterface;
use Dkd\CmisService\Configuration\Reader\RegistryConfigurationReader;

/**
 * TYPO3 Registry-based Configuration Writer
 *
 * Writes a ConfigurationDefinition to the TYPO3 registry.
 * Is a subclass of the RegistryConfigurationReader but with
 * a write() method and implementing the Writer interface.
 */
class RegistryConfigurationWriter extends RegistryConfigurationReader implements ConfigurationWriterInterface {

	/**
	 * Writes the definitions array from $configuration into
	 * the TYPO3 Registry.
	 *
	 * @param ConfigurationDefinitionInterface $configuration
	 * @param string $resourceIdentifier
	 * @return boolean
	 */
	public function write(ConfigurationDefinitionInterface $configuration, $identifier) {
		$array = $configuration->getDefinitions();
		$array[self::CHANGE_DATE_KEY] = time();
		$this->getRegistry()->set(self::REGISTRY_NAMESPACE, $identifier, $array);
		return true;
	}

    /**
     * @param string $identifier
     */
	public function remove($identifier) {
        $this->getRegistry()->remove(self::REGISTRY_NAMESPACE, $identifier);
    }
}
