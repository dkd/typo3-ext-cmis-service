<?php
namespace Dkd\CmisService\Configuration\Reader;

use Dkd\CmisService\Configuration\Definitions\ConfigurationDefinitionInterface;

interface ConfigurationReaderInterface {

	/**
	 * @param string $filePathAndFilename
	 * @return ConfigurationDefinitionInterface
	 */
	public function read($filePathAndFilename);

}
