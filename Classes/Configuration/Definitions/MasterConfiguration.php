<?php
namespace Dkd\CmisService\Configuration\Definitions;

/**
 * Master Configuration Definition
 *
 * Contains definitions for main system parameters such
 * as names of classes to use for handling the bootstrap
 * process of configuration parameters. Also contains
 * API methods to access every other type of configuration.
 */
class MasterConfiguration extends AbstractConfigurationDefinition implements ConfigurationDefinitionInterface {

	const OBJECT_CLASS_CONFIGURATION_READER = 'objects.configuration.reader.className';
	const OBJECT_CLASS_CONFIGURATION_WRITER = 'objects.configuration.writer.className';

	protected $defaults = array(
		self::OBJECT_CLASS_CONFIGURATION_READER => 'Dkd\\CmisService\\Configuration\\Reader\\YamlConfigurationReader',
		self::OBJECT_CLASS_CONFIGURATION_WRITER => 'Dkd\\CmisService\\Configuration\\Writer\\YamlConfigurationWriter'
	);

	/**
	 * @var ImplementationConfiguration
	 */
	protected $implementationConfiguration;

	/**
	 * @var TableConfiguration
	 */
	protected $tableConfiguration;

	/**
	 * @var NetworkConfiguration
	 */
	protected $networkConfiguration;

	/**
	 * @var CmisConfiguration
	 */
	protected $cmisConfiguration;

	/**
	 * @var StanbolConfiguration
	 */
	protected $stanbolConfiguration;

	/**
	 * Initialize this instance with sub-instances of
	 * ConfigurationDefinitions for specific contexts.
	 *
	 * @param ImplementationConfiguration $implementationConfiguration
	 * @param TableConfiguration $tableConfiguration
	 * @param NetworkConfiguration $networkConfiguration
	 * @param CmisConfiguration $cmisConfiguration
	 * @param StanbolConfiguration $stanbolConfiguration
	 * @return void
	 */
	public function initialize(
		ImplementationConfiguration $implementationConfiguration,
		TableConfiguration $tableConfiguration,
		NetworkConfiguration $networkConfiguration,
		CmisConfiguration $cmisConfiguration,
		StanbolConfiguration $stanbolConfiguration) {
		$this->implementationConfiguration = $implementationConfiguration;
		$this->tableConfiguration = $tableConfiguration;
		$this->networkConfiguration = $networkConfiguration;
		$this->cmisConfiguration = $cmisConfiguration;
		$this->stanbolConfiguration = $stanbolConfiguration;
	}

	/**
	 * Get the ConfigurationDefinition describing class implementations
	 *
	 * @return ImplementationConfiguration
	 */
	public function getImplementationConfiguration() {
		return $this->implementationConfiguration;
	}

	/**
	 * Get the ConfigurationDefinition describing CMIS integration
	 *
	 * @return CmisConfiguration
	 */
	public function getCmisConfiguration() {
		return $this->cmisConfiguration;
	}

	/**
	 * Get the ConfigurationDefinition describing network parameters
	 *
	 * @return NetworkConfiguration
	 */
	public function getNetworkConfiguration() {
		return $this->networkConfiguration;
	}

	/**
	 * Get the ConfigurationDefinition describing Stanbol integration
	 *
	 * @return StanbolConfiguration
	 */
	public function getStanbolConfiguration() {
		return $this->stanbolConfiguration;
	}

	/**
	 * Get the ConfigurationDefinition describing table configurations
	 *
	 * @return TableConfiguration
	 */
	public function getTableConfiguration() {
		return $this->tableConfiguration;
	}

}
