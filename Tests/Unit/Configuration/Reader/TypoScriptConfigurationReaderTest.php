<?php
namespace Dkd\CmisService\Configuration\Reader;

use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class TypoScriptConfigurationReaderTest
 *
 * @package Dkd\CmisService\Configuration\Reader
 */
class TypoScriptConfigurationReaderTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function supportsInstanciationUsingNewKeyword() {
		$instance = new TypoScriptConfigurationReader();
		$this->assertInstanceOf('Dkd\CmisService\Configuration\Reader\TypoScriptConfigurationReader', $instance);
	}

}
