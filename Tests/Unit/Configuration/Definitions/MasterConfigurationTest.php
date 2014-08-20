<?php
namespace Dkd\CmisService\Configuration\Definitions;

use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class MasterConfigurationTest
 *
 * @package Dkd\CmisService\Configuration\Definitions
 */
class MasterConfigurationTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function supportsInstanciationUsingNewKeyword() {
		$instance = new MasterConfiguration();
		$this->assertInstanceOf('Dkd\CmisService\Configuration\Definitions\MasterConfiguration', $instance);
	}

}
