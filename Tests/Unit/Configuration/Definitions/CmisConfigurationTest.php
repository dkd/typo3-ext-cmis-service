<?php
namespace Dkd\CmisService\Tests\Unit\Configuration\Definitions;

use Dkd\CmisService\Configuration\Definitions\CmisConfiguration;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class CmisConfigurationTest
 */
class CmisConfigurationTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function supportsHostnameSetting() {
		$instance = new CmisConfiguration();
		$instance->setDefinitions(array(
			CmisConfiguration::HOSTNAME => 'foobar'
		));
		$this->assertEquals('foobar', $instance->get(CmisConfiguration::HOSTNAME));
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function supportsPortSetting() {
		$instance = new CmisConfiguration();
		$instance->setDefinitions(array(
			CmisConfiguration::PORT => 'foobar'
		));
		$this->assertEquals('foobar', $instance->get(CmisConfiguration::PORT));
	}

}
