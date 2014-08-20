<?php
namespace Dkd\CmisService\Configuration\Definitions;

use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class StanbolConfigurationTest
 *
 * @package Dkd\CmisService\Configuration\Definitions
 */
class StanbolConfigurationTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function supportsInstanciationUsingNewKeyword() {
		$instance = new StanbolConfiguration();
		$this->assertInstanceOf('Dkd\CmisService\Configuration\Definitions\StanbolConfiguration', $instance);
	}

}
