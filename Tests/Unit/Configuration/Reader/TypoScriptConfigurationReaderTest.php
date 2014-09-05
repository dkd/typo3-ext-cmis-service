<?php
namespace Dkd\CmisService\Tests\Unit\Configuration\Reader;

use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class TypoScriptConfigurationReaderTest
 */
class TypoScriptConfigurationReaderTest extends UnitTestCase {

	/**
	 * Gets a correct, existing fixture path
	 *
	 * @return string
	 */
	protected function getGoodFixturePath() {
		return 'good.path';
	}

	/**
	 * Gets a correct, existing fixture path
	 *
	 * @return string
	 */
	protected function getBadFixturePath() {
		return 'bad.path';
	}

	/**
	 * @return array
	 */
	protected function getDummyTypoScriptSettings() {
		return array(
			'good' => array(
				'path' => array(
					'value' => TRUE
				)
			)
		);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function readCreatesExpectedDefinitionClassInstance() {
		$fixture = $this->getGoodFixturePath();
		$reader = $this->getMock(
			'Dkd\\CmisService\\Configuration\\Reader\\TypoScriptConfigurationReader',
			array('getTypoScriptSettings')
		);
		$reader->expects($this->once())->method('getTypoScriptSettings')
			->will($this->returnValue($this->getDummyTypoScriptSettings()));
		$instance = $reader->read($fixture, 'Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyMasterConfiguration');
		$this->assertInstanceOf('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyMasterConfiguration', $instance);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function existsReturnsTrueIfResourceExists() {
		$fixture = $this->getGoodFixturePath();
		$reader = $this->getMock(
			'Dkd\\CmisService\\Configuration\\Reader\\TypoScriptConfigurationReader',
			array('getTypoScriptSettings')
		);
		$typoScript = $this->getDummyTypoScriptSettings();
		$reader->expects($this->once())->method('getTypoScriptSettings')
			->will($this->returnValue($typoScript));
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
		$reader = $this->getMock(
			'Dkd\\CmisService\\Configuration\\Reader\\TypoScriptConfigurationReader',
			array('getTypoScriptSettings')
		);
		$typoScript = $this->getDummyTypoScriptSettings();
		$reader->expects($this->once())->method('getTypoScriptSettings')
			->will($this->returnValue($typoScript));
		$this->assertFalse($reader->exists($fixture));
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function checksumReturnsSha1OfSerializedTypoScript() {
		$fixture = $this->getGoodFixturePath();
		$reader = $this->getMock(
			'Dkd\\CmisService\\Configuration\\Reader\\TypoScriptConfigurationReader',
			array('getTypoScriptSettings')
		);
		$typoScript = $this->getDummyTypoScriptSettings();
		$reader->expects($this->once())->method('getTypoScriptSettings')
			->will($this->returnValue($typoScript));
		$expectedChecksum = sha1(serialize($typoScript['good']['path']));
		$this->assertEquals($expectedChecksum, $reader->checksum($fixture));
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function lastModifiedReturnsRecordModificationDateTime() {
		$fixture = $this->getGoodFixturePath();
		$reader = $this->getMock(
			'Dkd\\CmisService\\Configuration\\Reader\\TypoScriptConfigurationReader',
			array('getLastUpdatedRecord')
		);
		$time = 123456789;
		$recordModified = \DateTime::createFromFormat('U', $time);
		$reader->expects($this->once())->method('getLastUpdatedRecord')->will($this->returnValue(array('tstamp' => $time)));
		$this->assertEquals($recordModified, $reader->lastModified($fixture));
	}

}
