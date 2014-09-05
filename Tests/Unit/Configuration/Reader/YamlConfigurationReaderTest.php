<?php
namespace Dkd\CmisService\Configuration\Reader;

use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class YamlConfigurationReaderTest
 *
 * @package Dkd\CmisService\Configuration\Reader
 */
class YamlConfigurationReaderTest extends UnitTestCase {

	/**
	 * SUPPORT FUNCTION: Gets a correct, existing fixture path
	 *
	 * @return string
	 */
	protected function getGoodFixturePath() {
		$fixture = 'Tests/Fixtures/Data/Dummy.yml';
		if (TRUE === defined('TYPO3_MODE')) {
			$fixture = ExtensionManagementUtility::extPath('cmis_service', $fixture);
		} else {
			$fixture = realpath($fixture);
		}
		return $fixture;
	}

	/**
	 * SUPPORT FUNCTION: Gets a bad, non-existing fixture path
	 *
	 * @return string
	 */
	protected function getBadFixturePath() {
		$fixture = 'Bad/Fixture/Path.yml';
		if (TRUE === defined('TYPO3_MODE')) {
			$fixture = ExtensionManagementUtility::extPath('cmis_service', $fixture);
		} else {
			$fixture = realpath($fixture);
		}
		return $fixture;
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function readCreatesExpectedDefinitionClassInstance() {
		$fixture = $this->getGoodFixturePath();
		$reader = new YamlConfigurationReader();
		$instance = $reader->read($fixture, 'Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyMasterConfiguration');
		$this->assertInstanceOf('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyMasterConfiguration', $instance);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function readThrowsExpectedExceptionOnInvalidDefinitionClassName() {
		$fixture = $this->getGoodFixturePath();
		$invalidAsConfigurationDefinitionButExistingClassName = 'Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyReader';
		$reader = new YamlConfigurationReader();
		$this->setExpectedException('RuntimeException', NULL, 1409923995);
		$reader->read($fixture, $invalidAsConfigurationDefinitionButExistingClassName);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function existsReturnsTrueIfResourceExists() {
		$fixture = $this->getGoodFixturePath();
		$reader = new YamlConfigurationReader();
		$this->assertTrue($reader->exists($fixture));
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function existsReturnsFalseIfResourceDoesNotExist() {
		$fixture = $this->getBadFixturePath();
		$reader = new YamlConfigurationReader();
		$this->assertFalse($reader->exists($fixture));
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function checksumReturnsSha1OfFilePath() {
		$fixture = $this->getGoodFixturePath();
		$reader = new YamlConfigurationReader();
		$sha1 = sha1($fixture);
		$this->assertEquals($sha1, $reader->checksum($fixture));
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function lastModifiedReturnsFileModificationDateTime() {
		$fixture = $this->getGoodFixturePath();
		$reader = new YamlConfigurationReader();
		$fileModified = \DateTime::createFromFormat('U', filemtime($fixture));
		$this->assertEquals($fileModified, $reader->lastModified($fixture));
	}

}
