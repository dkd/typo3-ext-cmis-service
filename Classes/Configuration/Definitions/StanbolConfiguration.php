<?php
namespace Dkd\CmisService\Configuration\Definitions;

/**
 * Stanbol Configuration Definition
 */
class StanbolConfiguration extends AbstractConfigurationDefinition implements ConfigurationDefinitionInterface {

	const HOSTNAME = 'hostname';
	const PORT = 'port';

	/**
	 * @var array
	 */
	protected $defaults = array(
		self::HOSTNAME => 'localhost',
		self::PORT => 9090
	);

}
