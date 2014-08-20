<?php
namespace Dkd\CmisService\Factory;

use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class ObjectFactoryTest
 *
 * @package Dkd\CmisService\Factory
 */
class ObjectFactoryTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getConfigurationReturnsInstanceOfMasterConfiguration() {
		$factory = new ObjectFactory();
		$configuration = $factory->getConfiguration();
		$this->assertInstanceOf('Dkd\CmisService\Configuration\Definitions\MasterConfiguration', $configuration);
	}

}
