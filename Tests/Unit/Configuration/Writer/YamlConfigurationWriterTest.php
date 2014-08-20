<?php
namespace Dkd\CmisService\Configuration\Writer;

use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class YamlConfigurationReaderTest
 *
 * @package Dkd\CmisService\Configuration\Writer
 */
class YamlConfigurationReaderTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function supportsInstanciationUsingNewKeyword() {
		$instance = new YamlConfigurationWriter();
		$this->assertInstanceOf('Dkd\CmisService\Configuration\Writer\YamlConfigurationWriter', $instance);
	}

}
