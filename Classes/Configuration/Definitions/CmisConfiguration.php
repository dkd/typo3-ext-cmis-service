<?php
namespace Dkd\CmisService\Configuration\Definitions;

use Dkd\PhpCmis\Enum\BindingType;
use Dkd\PhpCmis\SessionParameter;
use GuzzleHttp\Client;

/**
 * CMIS Configuration Definition
 */
class CmisConfiguration extends AbstractConfigurationDefinition implements ConfigurationDefinitionInterface {

	const ID = 'contentRepositoryId';
	const URL = 'url';
	const USERNAME = 'username';
	const PASSWORD = 'password';
	const BINDINGTYPE = 'bindingType';
	const ROOT_UUID = 'root';

	/**
	 * @var array
	 */
	protected $defaults = array(
		self::BINDINGTYPE => BindingType::BROWSER
	);

	/**
	 * @param string $username
	 * @param string $password
	 * @return Client
	 */
	protected function createHttpInvoker($username, $password) {
		return new Client(
			array(
				'auth' => array(
					$username,
					$password
				)
			)
		);
	}

	/**
	 * @return array
	 */
	public function getSessionParameters() {
		return array(
			SessionParameter::BROWSER_URL => $this->get(self::URL),
			SessionParameter::BINDING_TYPE => $this->get(self::BINDINGTYPE),
			SessionParameter::REPOSITORY_ID => $this->get(self::ID),
			SessionParameter::BROWSER_SUCCINCT => FALSE,
			SessionParameter::HTTP_INVOKER_OBJECT => $this->createHttpInvoker(
				$this->get(self::USERNAME),
				$this->get(self::PASSWORD)
			)
		);
	}

}
