<?php
namespace Dkd\CmisService\Tests\Unit\Configuration\Definitions;

use Dkd\CmisService\Configuration\Definitions\StanbolConfiguration;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class StanbolConfigurationTest
 */
class StanbolConfigurationTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function supportsHostnameSetting() {
		$instance = new StanbolConfiguration();
		$instance->setDefinitions(array(
			StanbolConfiguration::HOSTNAME => 'foobar'
		));
		$this->assertEquals('foobar', $instance->get(StanbolConfiguration::HOSTNAME));
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function supportsPortSetting() {
		$instance = new StanbolConfiguration();
		$instance->setDefinitions(array(
			StanbolConfiguration::PORT => 'foobar'
		));
		$this->assertEquals('foobar', $instance->get(StanbolConfiguration::PORT));
	}

}
