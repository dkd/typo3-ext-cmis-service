<?php
namespace Dkd\CmisService\Configuration\Reader;

use Dkd\CmisService\Configuration\Definitions\ConfigurationDefinitionInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Registry Configuration Reader
 *
 * Reads Configuration Definitions from data stored
 * in the TYPO3 registry.
 */
class RegistryConfigurationReader implements ConfigurationReaderInterface {

    const REGISTRY_NAMESPACE = 'cmis_service';
    const CHANGE_DATE_KEY = 'changeDate';

	/**
	 * @param string $resourceIdentifier
	 * @param string $definitionClassName
	 * @return ConfigurationDefinitionInterface
	 */
	public function read($resourceIdentifier, $definitionClassName) {
		if (FALSE === is_a($definitionClassName, 'Dkd\\CmisService\\Configuration\\Definitions\\ConfigurationDefinitionInterface', TRUE)) {
			throw new \RuntimeException('Configuration definition class "' . $definitionClassName . '" must implement interface ' .
				'"Dkd\\CmisService\\Configuration\\Definitions\\ConfigurationDefinitionInterface"', 1409923995);
		}

		$data = $this->getRegistry()->get(self::REGISTRY_NAMESPACE, $resourceIdentifier);
		/** @var ConfigurationDefinitionInterface $definition */
		$definition = new $definitionClassName();
		$definition->setDefinitions($data);
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
		return [] !== $this->getRegistry()->get(self::REGISTRY_NAMESPACE, $resourceIdentifier, []);
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
        $data = $this->getRegistry()->get(self::REGISTRY_NAMESPACE, $resourceIdentifier);
        if (!empty($data[self::CHANGE_DATE_KEY])) {
            $date = $data[self::CHANGE_DATE_KEY];
        } else {
            $date = time() - 31557600;
        }
        return \DateTime::createFromFormat('U', $date);
	}

	/**
	 * @return \TYPO3\CMS\Core\Registry
	 */
	protected function getRegistry() {
		return GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Registry');
	}

}
