<?php
namespace Dkd\CmisService\Configuration\Reader;

use Dkd\CmisService\Configuration\Definitions\ConfigurationDefinitionInterface;
use Dkd\CmisService\Factory\ObjectFactory;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * TypoScript Configuration Reader
 *
 * Reads ConfigurationDefinition instances from data
 * stored in TypoScript setup. By providing the read()
 * method with an argument that is a (dotted-path)
 * name of a setting inside the main TypoScript settings
 * array returns just that setting. This allows the
 * Reader to construct different ConfigurationDefinition
 * instances from each sub-array of the configuration.
 *
 * @package Dkd\CmisService\Configuration\Reader
 */
class TypoScriptConfigurationReader implements ConfigurationReaderInterface {

	/**
	 * Load the specified TypoScript object sub-path into
	 * the reader. Format is a string with a dotted path
	 * to the desired object, for example 'tables' to
	 * access the value of or array at location
	 * 'plugin.tx_cmisservice.settings.tables'. Use an
	 * empty value to read all settings.
	 *
	 * @param string $resourceIdentifier
	 * @param string $definitionClassName
	 * @return ConfigurationDefinitionInterface
	 */
	public function read($resourceIdentifier, $definitionClassName) {
		if (FALSE === is_a($definitionClassName, 'Dkd\\CmisService\\Configuration\\Definitions\\ConfigurationDefinitionInterface', TRUE)) {
			throw new \RuntimeException('Configuration definition class "' . $definitionClassName . '" must implement interface ' .
				'"Dkd\\CmisService\\Configuration\\Definitions\\ConfigurationDefinitionInterface"', 1409923995);
		}
		$typoScript = $this->getTypoScriptSettings();
		$typoScript = ObjectAccess::getPropertyPath($typoScript, $resourceIdentifier);
		/** @var ConfigurationDefinitionInterface $definition */
		$definition = new $definitionClassName();
		$definition->setDefinitions($typoScript);
		return $definition;
	}

	/**
	 * Returns TRUE if the TypoScript path identified in
	 * the argument is filled with any value other than
	 * NULL.
	 *
	 * @param string $resourceIdentifier
	 * @return boolean
	 */
	public function exists($resourceIdentifier) {
		$typoScript = $this->getTypoScriptSettings();
		$typoScript = ObjectAccess::getPropertyPath($typoScript, $resourceIdentifier);
		return (boolean) 0 < count($typoScript);
	}

	/**
	 * Performs a checksum calculation based on the contents
	 * of data fetched from the TypoScript path identified in
	 * the parameter, returning the checksum as a string.
	 *
	 * @param string $resourceIdentifier
	 * @return string
	 */
	public function checksum($resourceIdentifier) {
		$typoScript = $this->getTypoScriptSettings();
		$typoScript = ObjectAccess::getPropertyPath($typoScript, $resourceIdentifier);
		return sha1(serialize($typoScript));
	}

	/**
	 * Returns a DateTime instance reflecting the last
	 * modification date of any TypoScript record in the system.
	 * Modifying TypoScript (and clearing the cached value)
	 * automatically causes this stamp to update.
	 *
	 * @param string $resourceIdentifier
	 * @return \DateTime
	 */
	public function lastModified($resourceIdentifier) {
		$lastUpdatedRecord = $this->getLastUpdatedRecord();
		return \DateTime::createFromFormat('U', $lastUpdatedRecord['tstamp']);
	}

	/**
	 * @return array
	 */
	protected function getLastUpdatedRecord() {
		$lastUpdatedRecord = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
			'tstamp',
			'sys_template',
			'deleted = 0',
			'uid',
			'tstamp DESC'
		);
		return $lastUpdatedRecord;
	}

	/**
	 * Returns the TypoScript beneath plugin.tx_cmisservice.settings
	 *
	 * @return array
	 */
	protected function getTypoScriptSettings() {
		$objectFactory = new ObjectFactory();
		$typoScript = $objectFactory->getExtensionTypoScriptSettings();
		return $typoScript;
	}

}
