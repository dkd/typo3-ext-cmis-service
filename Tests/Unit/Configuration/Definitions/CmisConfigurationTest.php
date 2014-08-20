<?php
namespace Dkd\CmisService\Configuration\Definitions;

use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class CmisConfigurationTest
 *
 * @package Dkd\CmisService\Configuration\Definitions
 */
class CmisConfigurationTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function supportsInstanciationUsingNewKeyword() {
		$instance = new CmisConfiguration();
		$this->assertInstanceOf('Dkd\CmisService\Configuration\Definitions\CmisConfiguration', $instance);
	}

}
