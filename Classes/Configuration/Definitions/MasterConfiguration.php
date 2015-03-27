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

	const CMIS_OPTION_SERVER = 'server';
	const CMIS_OPTION_SERVERS = 'servers';
	const CMIS_DEFAULT_SERVER = 'default';
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
		$implementation = $tables = $cmisServerConfiguration = $cmis = $stanbol = array();
		if (TRUE === isset($definitions[self::SCOPE_IMPLEMENTATION])) {
			$implementation = (array) $definitions[self::SCOPE_IMPLEMENTATION];
		}
		if (TRUE === isset($definitions[self::SCOPE_TABLES])) {
			$tables = (array) $definitions[self::SCOPE_TABLES];
		}
		if (TRUE === isset($definitions[self::SCOPE_CMIS][self::CMIS_OPTION_SERVERS])) {
			// pick the currently selected CMIS server by checking the
			// plugin.tx_cmisservice.settings.cmis.server value. The
			// value set in this position must be the name of a key as
			// plugin.tx_cmisservice.settings.cmis.servers.KEYNAME in
			// which all the CMIS server options must be defined.
			$cmis = (array) $definitions[self::SCOPE_CMIS];
			if (FALSE === isset($cmis[self::CMIS_OPTION_SERVER])) {
				$serverConfigurationKey = self::CMIS_DEFAULT_SERVER;
			} else {
				$serverConfigurationKey = $cmis[self::CMIS_OPTION_SERVER];
			}
			$cmisServerConfiguration = $cmis[self::CMIS_OPTION_SERVERS][$serverConfigurationKey];
		} elseif (TRUE === isset($definitions[self::SCOPE_CMIS])) {
			$cmisServerConfiguration = (array) $definitions[self::SCOPE_CMIS];
		}
		if (TRUE === isset($definitions[self::SCOPE_STANBOL])) {
			$stanbol = (array) $definitions[self::SCOPE_STANBOL];
		}
		$this->implementationConfiguration->setDefinitions($implementation);
		$this->tableConfiguration->setDefinitions($tables);
		$this->cmisConfiguration->setDefinitions($cmisServerConfiguration);
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

	/**
	 * @return array
	 */
	public function getDefinitions() {
		return array(
			self::SCOPE_TABLES => $this->getTableConfiguration()->getDefinitions(),
			self::SCOPE_IMPLEMENTATION => $this->getImplementationConfiguration()->getDefinitions(),
			self::SCOPE_STANBOL => $this->getStanbolConfiguration()->getDefinitions(),
			self::SCOPE_CMIS => $this->getCmisConfiguration()->getDefinitions()
		);
	}

}
