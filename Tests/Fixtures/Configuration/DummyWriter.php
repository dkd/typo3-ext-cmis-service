<?php
namespace Dkd\CmisService\Tests\Fixtures\Configuration;

use Dkd\CmisService\Configuration\Definitions\ConfigurationDefinitionInterface;
use Dkd\CmisService\Configuration\Writer\ConfigurationWriterInterface;

/**
 * Class DummyWriter
 */
class DummyWriter implements ConfigurationWriterInterface {

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

	/**
	 * Always returns NULL unless mocked.
	 *
	 * @param string $resourceIdentifier
	 * @return mixed
	 */
	public function read($resourceIdentifier) {
		return NULL;
	}

	/**
	 * Always returns FALSE unless mocked.
	 *
	 * @param string $resourceIdentifier
	 * @return boolean
	 */
	public function exists($resourceIdentifier) {
		return FALSE;
	}

	/**
	 * Always returns same value as $resourceIdentifier
	 * unless mocked.
	 *
	 * @param string $resourceIdentifier
	 * @return string
	 */
	public function checksum($resourceIdentifier) {
		return $resourceIdentifier;
	}

	/**
	 * Always returns a UNIXTIME zero DateTime.
	 *
	 * @param string $resourceIdentifier
	 * @return \DateTime
	 */
	public function lastModified($resourceIdentifier) {
		return \DateTime::createFromFormat('U', '0');
	}

}
