<?php
namespace Dkd\CmisService\Configuration;

/**
 * Configuration Manager
 *
 * Handles reading and writing of configuration.
 *
 * Configuration writing is, if enabled, implemented
 * as a pseudo-cache; writing to the configured Writer
 * if the configuration resource identity is different
 * from the one used by the Reader. This results in a
 * behavior where configuration can be read from, for
 * example, a database table or XML file and stored as
 * a YAML file. The YAML file is then used as source
 * only if it exists, making it behave like a cached
 * representation of whichever configuration is live.
 *
 * In other words: the Configuration Manager supports
 * a total of TWO potentially different Reader and
 * Writer resource identifiers and providing a different
 * resource identifier to the Writer also makes the
 * Reader use that resource if it exists.
 *
 * @package Dkd\CmisService\Configuration
 */
class ConfigurationManager {

	/**
	 * @var ConfigurationReaderInterface
	 */
	protected $reader;

	/**
	 * @var ConfigurationWriterInterface
	 */
	protected $writer;

}
