<?php
namespace Dkd\CmisService\Configuration\Writer;

use Dkd\CmisService\Configuration\Definitions\ConfigurationDefinitionInterface;

/**
 * Configuration Writer Interface
 *
 * Methods implemented by Configuration Writer classes.
 *
 * @package Dkd\CmisService\Configuration\Definitions
 */
interface ConfigurationWriterInterface {

	/**
	 * @param ConfigurationDefinitionInterface $configuration
	 * @return boolean
	 */
	public function write(ConfigurationDefinitionInterface $configuration);

}
