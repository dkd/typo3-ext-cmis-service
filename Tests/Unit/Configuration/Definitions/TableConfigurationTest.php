<?php
namespace Dkd\CmisService\Configuration\Definitions;

use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class TableConfigurationTest
 *
 * @package Dkd\CmisService\Configuration\Definitions
 */
class TableConfigurationTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function supportsInstanciationUsingNewKeyword() {
		$instance = new TableConfiguration();
		$this->assertInstanceOf('Dkd\CmisService\Configuration\Definitions\TableConfiguration', $instance);
	}

}
