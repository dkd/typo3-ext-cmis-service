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

	const SCOPE_IMPLEMENTATION = 'implementation';
	const SCOPE_TABLES = 'tables';
	const SCOPE_CMIS = 'cmis';
	const SCOPE_STANBOL = 'stanbol';

	/**
	 * @var ImplementationConfiguration
	 */
	protected $implementationConfiguration;

	/**
	 * @var TableConfiguration
	 */
	protected $tableConfiguration;

	/**
	 * @var CmisConfiguration
	 */
	protected $cmisConfiguration;

	/**
	 * @var StanbolConfiguration
	 */
	protected $stanbolConfiguration;

	/**
	 * Constructor - initializes the object with a set of
	 * blank configuration definitions.
	 */
	public function __construct() {
		$this->initialize(
			new ImplementationConfiguration(),
			new TableConfiguration(),
			new CmisConfiguration(),
			new StanbolConfiguration()
		);
	}

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
		CmisConfiguration $cmisConfiguration,
		StanbolConfiguration $stanbolConfiguration) {
		$this->implementationConfiguration = $implementationConfiguration;
		$this->tableConfiguration = $tableConfiguration;
		$this->cmisConfiguration = $cmisConfiguration;
		$this->stanbolConfiguration = $stanbolConfiguration;
	}

	/**
	 * Sets root-level definitions and delegates sub-definitions
	 * to each of the dedicated configuration types.
	 *
	 * @param array $definitions
	 * @return void
	 */
	public function setDefinitions(array $definitions) {
		$implementation = $tables = $cmis = $stanbol = array();
		if (TRUE === isset($definitions[self::SCOPE_IMPLEMENTATION])) {
			$implementation = (array) $definitions[self::SCOPE_IMPLEMENTATION];
			unset($definitions[self::SCOPE_IMPLEMENTATION]);
		}
		if (TRUE === isset($definitions[self::SCOPE_TABLES])) {
			$tables = (array) $definitions[self::SCOPE_TABLES];
			unset($definitions[self::SCOPE_TABLES]);
		}
		if (TRUE === isset($definitions[self::SCOPE_CMIS])) {
			$cmis = (array) $definitions[self::SCOPE_CMIS];
			unset($definitions[self::SCOPE_CMIS]);
		}
		if (TRUE === isset($definitions[self::SCOPE_STANBOL])) {
			$stanbol = (array) $definitions[self::SCOPE_STANBOL];
			unset($definitions[self::SCOPE_STANBOL]);
		}
		parent::setDefinitions($definitions);
		$this->implementationConfiguration->setDefinitions($implementation);
		$this->tableConfiguration->setDefinitions($tables);
		$this->cmisConfiguration->setDefinitions($cmis);
		$this->stanbolConfiguration->setDefinitions($stanbol);
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
