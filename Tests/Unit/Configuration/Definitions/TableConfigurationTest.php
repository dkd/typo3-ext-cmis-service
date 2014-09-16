<?php
namespace Dkd\CmisService\Tests\Unit\Configuration\Definitions;

use Dkd\CmisService\Configuration\Definitions\TableConfiguration;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class TableConfigurationTest
 */
class TableConfigurationTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getConfiguredTableNamesReturnsArrayKeysOfDefinitions() {
		$instance = new TableConfiguration();
		$instance->setDefinitions(array(
			'foo' => array(),
			'bar' => array()
		));
		$this->assertEquals(array('foo', 'bar'), $instance->getConfiguredTableNames());
	}

}
