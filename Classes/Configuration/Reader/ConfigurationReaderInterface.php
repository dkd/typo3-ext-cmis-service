<?php
namespace Dkd\CmisService\Configuration\Reader;

use Dkd\CmisService\Configuration\ConfigurationResourceConsumerInterface;
use Dkd\CmisService\Configuration\Definitions\ConfigurationDefinitionInterface;

/**
 * Configuration Reader Interface
 *
 * Implemented by classes which are capable of reading
 * configuration parameters or collections of parameters
 * identified by a string for example a stream protocul
 * URL or other location identifier.
 *
 * @package Dkd\CmisService\Configuration\Reader
 */
interface ConfigurationReaderInterface extends ConfigurationResourceConsumerInterface {

	/**
	 * Load the specified resource into the reader.
	 * Note that all Reader implementations may not
	 * support every possible stream/record identification
	 * format - consult the documentation for each Reader
	 * implementation for a list of supported streams.
	 *
	 * Developer note: this method must be kept in perfect
	 * sync with ConfigurationResourceConsumerInterface::read
	 * and normal practice is for a Reader to also implement
	 * the Consumer interface to let it serve a dual purpose
	 * of reading as well as stat'ing configurations by their
	 * identifier name.
	 *
	 * @param string $resourceIdentifier
	 * @return ConfigurationDefinitionInterface
	 */
	public function read($resourceIdentifier);

}
