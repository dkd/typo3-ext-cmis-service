<?php
namespace Dkd\CmisService\Configuration\Writer;

use Dkd\CmisService\Configuration\Definitions\ConfigurationDefinitionInterface;

interface ConfigurationWriterInterface {

	/**
	 * @param ConfigurationDefinitionInterface $configuration
	 * @return boolean
	 */
	public function write(ConfigurationDefinitionInterface $configuration);

}
