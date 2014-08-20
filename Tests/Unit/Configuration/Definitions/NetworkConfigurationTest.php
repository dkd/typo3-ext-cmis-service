<?php
namespace Dkd\CmisService\Configuration\Definitions;

use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class NetworkConfigurationTest
 *
 * @package Dkd\CmisService\Configuration\Definitions
 */
class NetworkConfigurationTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function supportsInstanciationUsingNewKeyword() {
		$instance = new NetworkConfiguration();
		$this->assertInstanceOf('Dkd\CmisService\Configuration\Definitions\NetworkConfiguration', $instance);
	}

}
