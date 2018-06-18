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
	public function write(ConfigurationDefinitionInterface $configuration, $identifier) {
		$array = $configuration->getDefinitions();
		$yamlFileContents = Yaml::dump($array);
		return GeneralUtility::writeFile(GeneralUtility::getFileAbsFileName($identifier), $yamlFileContents);
	}

    /**
     * @param string $identifier
     */
	public function remove($identifier) {
        $this->removeResource(GeneralUtility::getFileAbsFileName($identifier));
    }

    /**
     * Removes a resource if it exists. Returns TRUE if the file
     * was removed or if it did not already exist.
     *
     * @param string $resourceIdentifier
     * @return boolean
     */
    protected function removeResource($resourceIdentifier) {
        $resourceIdentifier = GeneralUtility::getFileAbsFileName($resourceIdentifier);
        return file_exists($resourceIdentifier) ? unlink($resourceIdentifier) : TRUE;
    }

}
