<?php
namespace Dkd\CmisService\Factory;

use Dkd\CmisService\Configuration\Definitions\CmisConfiguration;
use Dkd\CmisService\Configuration\Definitions\MasterConfiguration;
use Dkd\PhpCmis\Session;

/**
 * Class CmisObjectFactory
 */
class CmisObjectFactory {

	/**
	 * @var Session[]
	 */
	private static $sessions = array();

	/**
	 * Resolves or initialises and then returns
	 * the currently active CMIS Session which can
	 * be used to communicate with the repository.
	 *
	 * @param string $serverName configuration name in array of server configurations
	 * @return Session
	 */
	public function getSession($serverName = MasterConfiguration::CMIS_DEFAULT_SERVER) {
		if (FALSE === self::$sessions[$serverName] instanceof Session) {
			$parameters = $this->getSessionParameters($serverName);
			self::$sessions[$serverName] = $this->createSessionObject($parameters);
		}
		return self::$sessions[$serverName];
	}

	/**
	 * Wrapper to easily fetch configured server names
	 * from the master configuration without having
	 * to create another factory.
	 *
	 * @return CmisConfiguration
	 */
	public function getConfiguredServerNames() {
		return $this->getObjectFactory()->getConfiguration()->getCmisConfigurationNames();
	}

	/**
	 * @param string $serverName configuration name in array of server configurations
	 * @return array
	 */
	protected function getSessionParameters($serverName) {
		$cmisConfiguration = $this->getObjectFactory()->getConfiguration()->getCmisConfiguration($serverName);
		return $cmisConfiguration->getSessionParameters();
	}

	/**
	 * @return ObjectFactory
	 */
	protected function getObjectFactory() {
		return new ObjectFactory();
	}

	/**
	 * @codeCoverageIgnore
	 * @param array $parameters
	 * @return Session
	 */
	protected function createSessionObject(array $parameters) {
		return new Session($parameters);
	}

}
