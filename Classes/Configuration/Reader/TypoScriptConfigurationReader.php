<?php
namespace Dkd\CmisService\Configuration\Reader;

use Dkd\CmisService\Configuration\Definitions\ConfigurationDefinitionInterface;

/**
 * TypoScript Configuration Reader
 *
 * Reads ConfigurationDefinition instances from data
 * stored in TypoScript setup.
 *
 * @package Dkd\CmisService\Configuration\Reader
 */
class TypoScriptConfigurationReader implements ConfigurationReaderInterface {

	/**
	 * Load the specified TypoScript object path into
	 * the reader. Format is a string with a dotted path
	 * to the desired object, for example
	 * 'plugin.tx_cmisservice.settings.indexing'
	 *
	 * @param string $resourceIdentifier
	 * @return ConfigurationDefinitionInterface
	 */
	public function read($resourceIdentifier) {

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

	}

}
