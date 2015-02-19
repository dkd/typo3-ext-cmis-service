<?php
namespace Dkd\CmisService\Factory;

use Dkd\PhpCmis\Session;

/**
 * Class CmisObjectFactory
 */
class CmisObjectFactory {

	/**
	 * @var Session
	 */
	private static $session;

	/**
	 * Resolves or initialises and then returns
	 * the currently active CMIS Session which can
	 * be used to communicate with the repository.
	 *
	 * @return Session
	 */
	public function getSession() {
		if (FALSE === self::$session instanceof Session) {
			$parameters = $this->getSessionParameters();
			self::$session = $this->createSessionObject($parameters);
		}
		return self::$session;
	}

	/**
	 * @return array
	 */
	protected function getSessionParameters() {
		$cmisConfiguration = $this->getObjectFactory()->getConfiguration()->getCmisConfiguration();
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
