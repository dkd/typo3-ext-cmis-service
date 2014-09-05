<?php
namespace Dkd\CmisService\Configuration\Writer;

use Dkd\CmisService\Configuration\Definitions\ConfigurationDefinitionInterface;
use Dkd\CmisService\Configuration\Reader\YamlConfigurationReader;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * YAML-based Configuration Writer
 *
 * Writes a ConfigurationDefinition to a YAML file target.
 * Is a subclass of the YamlConfigurationReader but with
 * a write() method and implementing the Writer interface.
 */
class YamlConfigurationWriter extends YamlConfigurationReader implements ConfigurationWriterInterface {

	/**
	 * Writes the definitions array from $configuration into
	 * the YAML file at $resourceIdentifier.
	 *
	 * @param ConfigurationDefinitionInterface $configuration
	 * @param string $resourceIdentifier
	 * @return boolean
	 */
	public function write(ConfigurationDefinitionInterface $configuration, $resourceIdentifier) {
		$array = $configuration->getDefinitions();
		$yamlFileContents = Yaml::dump($array);
		return GeneralUtility::writeFile($resourceIdentifier, $yamlFileContents);
	}

}
