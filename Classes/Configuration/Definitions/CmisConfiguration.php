<?php
namespace Dkd\CmisService\Configuration\Definitions;

use Dkd\PhpCmis\Enum\BindingType;
use Dkd\PhpCmis\SessionParameter;

/**
 * CMIS Configuration Definition
 */
class CmisConfiguration extends AbstractConfigurationDefinition implements ConfigurationDefinitionInterface {

	const ID = 'id';
	const URL = 'url';
	const USERNAME = 'username';
	const PASSWORD = 'password';
	const BINDINGTYPE = 'bindingType';

	/**
	 * @var array
	 */
	protected $defaults = array(
		self::BINDINGTYPE => BindingType::BROWSER
	);

	/**
	 * @return array
	 */
	public function getSessionParameters() {
		return array(
			SessionParameter::BROWSER_URL => $this->get(self::URL),
			SessionParameter::BINDING_TYPE => $this->get(self::BINDINGTYPE),
			SessionParameter::REPOSITORY_ID => $this->get(self::ID),
			SessionParameter::USER => $this->get(self::USERNAME),
			SessionParameter::PASSWORD => $this->get(self::PASSWORD)
		);
	}

}
