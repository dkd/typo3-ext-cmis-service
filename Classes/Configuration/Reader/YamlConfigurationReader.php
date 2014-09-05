<?php
namespace Dkd\CmisService\Configuration\Reader;

use Dkd\CmisService\Configuration\Definitions\ConfigurationDefinitionInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * YAML Configuration Reader
 *
 * Reads Configuration Definitions from data stored
 * in a YAML file.
 */
class YamlConfigurationReader implements ConfigurationReaderInterface {

	/**
	 * Load the specified resource into the reader.
	 * Note that all Reader implementations may not
	 * support every possible stream/record identification
	 * format - consult the documentation for each Reader
	 * implementation for a list of supported streams.
	 *
	 * Developer note: this method must be kept in perfect
	 * sync with ConfigurationResourceConsumerInterface::read
	 * and normal practice is for a Reader to also implement
	 * the Consumer interface to let it serve a dual purpose
	 * of reading as well as stat'ing configurations by their
	 * identifier name.
	 *
	 * @param string $resourceIdentifier
	 * @return ConfigurationDefinitionInterface
	 */
	public function read($resourceIdentifier, $definitionClassName) {
		if (FALSE === is_a($definitionClassName, 'Dkd\\CmisService\\Configuration\\Definitions\\ConfigurationDefinitionInterface', TRUE)) {
			throw new \RuntimeException('Configuration definition class "' . $definitionClassName . '" must implement interface ' .
				'"Dkd\\CmisService\\Configuration\\Definitions\\ConfigurationDefinitionInterface"', 1409923995);
		}
		$yamlArray = Yaml::parse($resourceIdentifier);
		$definition = new $definitionClassName();
		$definition->setDefinitions($yamlArray);
		return $definition;
	}

	/**
	 * Returns TRUE if the resource identified by the
	 * argument exists, FALSE if it does not.
	 *
	 * @param string $resourceIdentifier
	 * @return boolean
	 */
	public function exists($resourceIdentifier) {
		return TRUE === file_exists($resourceIdentifier);
	}

	/**
	 * Performs a checksum calculation of the resource
	 * identifier (optionally incorporating additional
	 * factors depending on the implementation).
	 *
	 * @param string $resourceIdentifier
	 * @return string
	 */
	public function checksum($resourceIdentifier) {
		return sha1($resourceIdentifier);
	}

	/**
	 * Returns a DateTime instance reflecting the last
	 * modification date of the resource identified in
	 * the argument.
	 *
	 * @param string $resourceIdentifier
	 * @return \DateTime
	 */
	public function lastModified($resourceIdentifier) {
		return \DateTime::createFromFormat('U', filemtime($resourceIdentifier));
	}

}
