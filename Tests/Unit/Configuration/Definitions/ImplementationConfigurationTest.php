<?php
namespace Dkd\CmisService\Configuration\Definitions;

use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class ImplementationConfigurationTest
 *
 * @package Dkd\CmisService\Configuration\Definitions
 */
class ImplementationConfigurationTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function supportsInstanciationUsingNewKeyword() {
		$instance = new ImplementationConfiguration();
		$this->assertInstanceOf('Dkd\CmisService\Configuration\Definitions\ImplementationConfiguration', $instance);
	}

}
