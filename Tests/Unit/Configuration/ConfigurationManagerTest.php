<?php
namespace Dkd\CmisService\Configuration;

use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class ConfigurationManagerTest
 *
 * @package Dkd\CmisService\Configuration
 */
class ConfigurationManagerTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function supportsInstanciationUsingNewKeyword() {
		$instance = new ConfigurationManager();
		$this->assertInstanceOf('Dkd\CmisService\Configuration\ConfigurationManager', $instance);
	}

}
