<?php
namespace Dkd\CmisService\Tests\Unit\Configuration\Definitions;

use Dkd\CmisService\Configuration\Definitions\CmisConfiguration;
use Dkd\PhpCmis\SessionParameter;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class CmisConfigurationTest
 */
class CmisConfigurationTest extends UnitTestCase {

	/**
	 * @dataProvider getSettingTestValues
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function testGetSettingReturnsDefinedValue($name, $value) {
		$instance = new CmisConfiguration();
		$instance->setDefinitions(array(
			$name => $value
		));
		$this->assertEquals($value, $instance->get($name));
	}

	/**
	 * @return array
	 */
	public function getSettingTestValues() {
		return array(
			array(CmisConfiguration::URL, 'localhost'),
			array(CmisConfiguration::ID, 'id'),
			array(CmisConfiguration::USERNAME, 'username'),
			array(CmisConfiguration::PASSWORD, 'password')
		);
	}

	/**
	 * @dataProvider getSessionParametersTestValues
	 * @param array $definitions
	 * @param array $expected
	 * @return void
	 */
	public function testGetSessionParametersReturnsDefinedSessionParameters(array $definitions, array $expected) {
		$instance = new CmisConfiguration();
		$instance->setDefinitions($definitions);
		$result = $instance->getSessionParameters();
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return array
	 */
	public function getSessionParametersTestValues() {
		return array(
			array(
				array(
					CmisConfiguration::URL => 'url',
					CmisConfiguration::ID => 'id',
					CmisConfiguration::USERNAME => 'username',
					CmisConfiguration::PASSWORD => 'password',
					CmisConfiguration::BINDINGTYPE => 'binding'
				),
				array(
					SessionParameter::BROWSER_URL => 'url',
					SessionParameter::BINDING_TYPE => 'binding',
					SessionParameter::REPOSITORY_ID => 'id',
					SessionParameter::USER => 'username',
					SessionParameter::PASSWORD => 'password'
				)
			)
		);
	}

}
