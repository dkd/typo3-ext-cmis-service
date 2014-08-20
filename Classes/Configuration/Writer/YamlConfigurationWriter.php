<?php
namespace Dkd\CmisService\Configuration\Writer;

use Dkd\CmisService\Configuration\ConfigurationResourceConsumerInterface;
use Dkd\CmisService\Configuration\Definitions\ConfigurationDefinitionInterface;

/**
 * YAML-based Configuration Writer
 *
 * Writes a ConfigurationDefinition to a YAML file target.
 *
 * @package Dkd\CmisService\Configuration\Writer
 */
class YamlConfigurationWriter implements ConfigurationWriterInterface, ConfigurationResourceConsumerInterface {

	/**
	 * @param ConfigurationDefinitionInterface $configuration
	 * @return boolean
	 */
	public function write(ConfigurationDefinitionInterface $configuration) {

	}

	/**
	 * Load the specified YAML file and parse it into
	 * memory.
	 *
	 * @param string $resourceIdentifier
	 * @return mixed
	 */
	public function read($resourceIdentifier) {

	}

	/**
	 * Returns TRUE if the resource YAML file exists
	 *
	 * @param string $resourceIdentifier
	 * @return boolean
	 */
	public function exists($resourceIdentifier) {

	}

	/**
	 * Performs a checksum calculation of the resource
	 * identifier (file checksum of YAML file)
	 *
	 * @param string $resourceIdentifier
	 * @return string
	 */
	public function checksum($resourceIdentifier) {

	}

	/**
	 * Returns a DateTime instance reflecting the last
	 * modification date of the resource identified in
	 * the argument.
	 *
	 * @param string $resourceIdentifier
	 * @return \DateTime
	 */
	public function modified($resourceIdentifier) {

	}

}
