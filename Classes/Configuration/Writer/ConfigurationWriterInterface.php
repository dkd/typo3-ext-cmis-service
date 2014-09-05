<?php
namespace Dkd\CmisService\Configuration\Writer;

use Dkd\CmisService\Configuration\ConfigurationResourceConsumerInterface;
use Dkd\CmisService\Configuration\Definitions\ConfigurationDefinitionInterface;

/**
 * Configuration Writer Interface
 *
 * Methods implemented by Configuration Writer classes.
 */
interface ConfigurationWriterInterface extends ConfigurationResourceConsumerInterface {

	/**
	 * Writes the definition to the resource.
	 *
	 * @param ConfigurationDefinitionInterface $configuration
	 * @param string $resourceIdentifier
	 * @return boolean
	 */
	public function write(ConfigurationDefinitionInterface $configuration, $resourceIdentifier);

}
