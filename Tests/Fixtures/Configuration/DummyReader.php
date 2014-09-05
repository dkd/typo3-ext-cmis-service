<?php
namespace Dkd\CmisService\Tests\Fixtures\Configuration;

use Dkd\CmisService\Configuration\Definitions\ConfigurationDefinitionInterface;
use Dkd\CmisService\Configuration\Reader\ConfigurationReaderInterface;

/**
 * Class DummyReader
 */
class DummyReader implements ConfigurationReaderInterface {

	/**
	 * DummyReader always returns a DummyConfigurationDefinition
	 *
	 * @param string $resourceIdentifier
	 * @param string $definitionClassName
	 * @return ConfigurationDefinitionInterface
	 */
	public function read($resourceIdentifier, $definitionClassName) {
		return new DummyConfigurationDefinition();
	}

	/**
	 * Always returns TRUE unless mocked.
	 *
	 * @param string $resourceIdentifier
	 * @return boolean
	 */
	public function exists($resourceIdentifier) {
		return TRUE;
	}

	/**
	 * Always returns value of $resourceIdentifier
	 * unless mocked.
	 *
	 * @param string $resourceIdentifier
	 * @return string
	 */
	public function checksum($resourceIdentifier) {
		return $resourceIdentifier;
	}

	/**
	 * Always returns a UNIXTIME zero DateTime
	 *
	 * @param string $resourceIdentifier
	 * @return \DateTime
	 */
	public function lastModified($resourceIdentifier) {
		return \DateTime::createFromFormat('U', '0');
	}

}
