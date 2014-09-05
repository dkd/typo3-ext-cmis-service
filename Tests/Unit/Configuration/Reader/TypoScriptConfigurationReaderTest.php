<?php
namespace Dkd\CmisService\Tests\Unit\Configuration\Reader;

use Dkd\CmisService\Configuration\Reader\TypoScriptConfigurationReader;
use Dkd\CmisService\Factory\ObjectFactory;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class TypoScriptConfigurationReaderTest
 */
class TypoScriptConfigurationReaderTest extends UnitTestCase {

	/**
	 * Setup
	 *
	 * Checks the very first constant defined by the TYPO3 CMS
	 * initialization "Bootstrap" class. If the constant is not
	 * defined we assume a minimal environment must be created:
	 *
	 * - to define constants some TYPO3 CMS classes need
	 * - to initialize the TYPO3 CMS class loader _caches_
	 * - to unregister the actual class loader from TYPO3 CMS,
	 *   falling back to the Composer autoloading.
	 *
	 * In this case the initial object for $GLOBALS['TYPO3_DB']
	 * is also added which makes the tests E_NOTICE safe.
	 *
	 * @return void
	 */
	protected function setUp() {
		if (FALSE === defined('TYPO3_version')) {
			define('PATH_thisScript', realpath('vendor/typo3/cms/typo3/index.php'));
			$bootstrap = Bootstrap::getInstance()->baseSetup('typo3/')
				->disableCoreAndClassesCache()->initializeClassLoader()
				->unregisterClassLoader();
		}
		parent::setUp();
	}

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

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getLastUpdatedRecordDelegatesToGlobalConnection() {
		$reader = new TypoScriptConfigurationReader();
		$backup = $GLOBALS['TYPO3_DB'];
		$GLOBALS['TYPO3_DB'] = $this->getMock('TYPO3\\CMS\\Core\\Database\\DatabaseConnection', array('exec_SELECTgetSingleRow'));
		$GLOBALS['TYPO3_DB']->expects($this->once())->method('exec_SELECTgetSingleRow')
			->with('tstamp', 'sys_template', 'deleted = 0', 'uid', 'tstamp DESC')
			->will($this->returnValue(array('foo' => 'bar')));
		$result = $this->callInaccessibleMethod($reader, 'getLastUpdatedRecord');
		$GLOBALS['TYPO3_DB'] = $backup;
		$this->assertEquals(array('foo' => 'bar'), $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function readsTypoScriptPathFromGlobalTypoScript() {
		$typoScript = array('foo' => 'bar');
		$reader = $this->getMock(
			'Dkd\\CmisService\\Configuration\\Reader\\TypoScriptConfigurationReader',
			array('getObjectFactory')
		);
		$objectFactory = $this->getMock('Dkd\\CmisService\\Factory\\ObjectFactory', array('getExtensionTypoScriptSettings'));
		$objectFactory->expects($this->once())->method('getExtensionTypoScriptSettings')->will($this->returnValue($typoScript));
		$reader->expects($this->once())->method('getObjectFactory')->will($this->returnValue($objectFactory));
		$result = $this->callInaccessibleMethod($reader, 'getTypoScriptSettings');
		$this->assertSame($typoScript, $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getObjectFactoryReturnsObjectFactory() {
		$reader = new TypoScriptConfigurationReader();
		$objectFactory = $this->callInaccessibleMethod($reader, 'getObjectFactory');
		$this->assertInstanceOf('Dkd\\CmisService\\Factory\\ObjectFactory', $objectFactory);
	}

}
