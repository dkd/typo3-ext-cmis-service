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
	 * @var CmisConfiguration[]
	 */
	protected $cmisConfigurations = array();

	/**
	 * @var StanbolConfiguration
	 */
	protected $stanbolConfiguration;

	/**
	 * @var string
	 */
	protected $activeCmisServerConfigurationName = self::CMIS_DEFAULT_SERVER;

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
		$this->cmisConfigurations = array(
			self::CMIS_DEFAULT_SERVER => $cmisConfiguration
		);
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
		$implementation = $tables = $cmisServerConfiguration = $cmisServerConfigurations = $cmis = $stanbol = array();
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
			foreach ($cmis[self::CMIS_OPTION_SERVERS] as $serverName => $cmisServerConfiguration) {
				$configurationObject = new CmisConfiguration();
				$configurationObject->setDefinitions((array) $cmisServerConfiguration);
				$this->cmisConfigurations[$serverName] = $configurationObject;
			}
			if (TRUE === isset($cmis[self::CMIS_OPTION_SERVER])) {
				$this->activeCmisServerConfigurationName = $cmis[self::CMIS_OPTION_SERVER];
			}
		} elseif (TRUE === isset($definitions[self::SCOPE_CMIS])) {
			$configurationObject = new CmisConfiguration();
			$configurationObject->setDefinitions((array) $definitions[self::SCOPE_CMIS]);
			$this->cmisConfigurations[self::CMIS_DEFAULT_SERVER] = $configurationObject;
		}
		if (TRUE === isset($definitions[self::SCOPE_STANBOL])) {
			$stanbol = (array) $definitions[self::SCOPE_STANBOL];
		}
		$this->implementationConfiguration->setDefinitions($implementation);
		$this->tableConfiguration->setDefinitions($tables);
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
	 * Get the specific CMIS server connection definition identified by
	 * name - or if no name is provided, the one configured as currently
	 * active/default server.
	 *
	 * @param string $serverName Optional name of server configuration to get
	 * @return CmisConfiguration
	 */
	public function getCmisConfiguration($serverName = NULL) {
		if (NULL === $serverName) {
			$serverName = $this->activeCmisServerConfigurationName;
		}
		if (TRUE === isset($this->cmisConfigurations[$serverName])) {
			return $this->cmisConfigurations[$serverName];
		}
		return $this->cmisConfigurations[self::CMIS_DEFAULT_SERVER];
	}

	/**
	 * Get the names of all CMIS sub-definitions contained in this
	 * MasterConfiguration. The names can then be passed to
	 * getCmisConfiguration($name) to retrieve a specific CMIS
	 * server connection configuration.
	 *
	 * @return array
	 */
	public function getCmisConfigurationNames() {
		return array_keys($this->cmisConfigurations);
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
	 * Get all child definitions of this MasterConfiguration, resulting
	 * in an array structure that can be serialized and passed to
	 * setDefinitions() to recreate the state this MasterConfiguration
	 * was in when it was extracted.
	 *
	 * @return array
	 */
	public function getDefinitions() {
		return array(
			self::SCOPE_TABLES => $this->getTableConfiguration()->getDefinitions(),
			self::SCOPE_IMPLEMENTATION => $this->getImplementationConfiguration()->getDefinitions(),
			self::SCOPE_STANBOL => $this->getStanbolConfiguration()->getDefinitions(),
			self::SCOPE_CMIS => array(
				self::CMIS_OPTION_SERVER => $this->activeCmisServerConfigurationName,
				self::CMIS_OPTION_SERVERS => $this->extractAllDefinitions($this->cmisConfigurations)
			),
		);
	}

}
