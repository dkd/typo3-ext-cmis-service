<?php
namespace Dkd\CmisService\Configuration\Reader;

use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class YamlConfigurationReaderTest
 *
 * @package Dkd\CmisService\Configuration\Reader
 */
class YamlConfigurationReaderTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function supportsInstanciationUsingNewKeyword() {
		$instance = new YamlConfigurationReader();
		$this->assertInstanceOf('Dkd\CmisService\Configuration\Reader\YamlConfigurationReader', $instance);
	}

}
