<?php
namespace Dkd\CmisService\Tests\Fixtures\Configuration;

use Dkd\CmisService\Configuration\Definitions\ConfigurationDefinitionInterface;
use Dkd\CmisService\Configuration\Writer\ConfigurationWriterInterface;

/**
 * Class DummyWriter
 */
class DummyWriter extends DummyReader implements ConfigurationWriterInterface {

	/**
	 * DummyWriter never writes anything and always
	 * responds TRUE for success unless mocked.
	 *
	 * @param ConfigurationDefinitionInterface $configuration
	 * @param string $resourceIdentifier
	 * @return boolean
	 */
	public function write(ConfigurationDefinitionInterface $configuration, $resourceIdentifier) {
		return TRUE;
	}

}
