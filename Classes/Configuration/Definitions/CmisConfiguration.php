<?php
namespace Dkd\CmisService\Configuration\Definitions;

/**
 * CMIS Configuration Definition
 */
class CmisConfiguration extends AbstractConfigurationDefinition implements ConfigurationDefinitionInterface {

	const HOSTNAME = 'hostname';
	const PORT = 'port';

	/**
	 * @var array
	 */
	protected $defaults = array(
		self::HOSTNAME => 'localhost',
		self::PORT => 8080
	);

}
