<?php
namespace Dkd\CmisService\Factory;

use Dkd\CmisService\Configuration\Definitions\MasterConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\ObjectManagerInterface;

/**
 * Object Factory
 *
 * Wrapper for making instances of objects with constructor
 * arguments in a fashion that supports the host system's
 * object loader - in this default implementation using
 * Extbase's ObjectManager to create object instances.
 *
 * @package Dkd\CmisService\Factory
 */
class ObjectFactory {

	/**
	 * Make an instance of $className, if any additional parameters
	 * are present they will be used as constructor arguments.
	 *
	 * @param string $className
	 * @return mixed
	 */
	public function makeInstance($className) {
		/** @var ObjectManagerInterface $manager */
		$manager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$arguments = func_get_args();
		$instance = call_user_func_array(array($manager, 'get'), $arguments);
		return $instance;
	}

	/**
	 * Get the Master Configuration Definition which contains
	 * API methods to query every other configuration option.
	 *
	 * TYPO3 CMS specific information:
	 *
	 * In order to correctly bootstrap the configuration which is
	 * used for essential business logic of this package, the
	 * TypoScript information is read - but only key variables
	 * from _this_ reading of the TypoScript are used, namely
	 * the _configuration Reader and Writer and associated
	 * parameters_.
	 *
	 * If so configured, another configuration reader may read
	 * the TypoScript again, this time putting it into the API
	 * of the ConfigurationDefinition implementations. In the
	 * standard configuration this is the default approach, but
	 * other configuration may choose to use a static YAML file
	 * as only configuration source, ignoring any TypoScript
	 * except for these two key parameters for Reader and Writer.
	 *
	 * @return MasterConfiguration
	 */
	public function getConfiguration() {
		$configuration = new MasterConfiguration();
		return $configuration;
	}

}
