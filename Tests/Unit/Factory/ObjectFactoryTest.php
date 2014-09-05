<?php
namespace Dkd\CmisService\Tests\Unit\Factory;

use Dkd\CmisService\Factory\ObjectFactory;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Class ObjectFactoryTest
 */
class ObjectFactoryTest extends UnitTestCase {

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
	 * The whole thing is initialized by setting PATH_thisScript
	 * since this constant is the one TYPO3's Bootstrap uses
	 * when detecting the starting point for other path constants.
	 *
	 * @return void
	 */
	protected function setUp() {
		if (FALSE === defined('TYPO3_version')) {
			define('PATH_thisScript', realpath('vendor/typo3/cms/typo3/index.php'));
			$bootstrap = Bootstrap::getInstance()->baseSetup('typo3/')->initializeClassLoader()
				->unregisterClassLoader();
		}
		parent::setUp();
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getConfigurationCallsGetConfigurationManager() {
		$mockConfiguration = $this->getMock('Dkd\\CmisService\\Configuration\\Definitions\\MasterConfiguration');
		$mockReader = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyReader', array('checksum'));
		$manager = $this->getMock(
			'Dkd\\CmisService\\Configuration\\ConfigurationManager',
			array('getMasterConfiguration'),
			array($mockReader)
		);
		$factory = $this->getMock('Dkd\\CmisService\\Factory\\ObjectFactory', array('getConfigurationManager'));
		$factory->expects($this->once())->method('getConfigurationManager')->will($this->returnValue($manager));
		$manager->expects($this->once())->method('getMasterConfiguration')->will($this->returnValue($mockConfiguration));
		$configuration = $factory->getConfiguration();
		$this->assertSame($mockConfiguration, $configuration);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getExtensionTypoScriptSettingsCallsExpectedMethodSequence() {
		$expectedTypoScript = array('foo' => 'bar');
		$mockExtbaseConfigurationManager = $this->getMock(
			'TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager',
			array('getConfiguration')
		);
		$mockExtbaseConfigurationManager->expects($this->once())->method('getConfiguration')
			->with(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT, 'Dkd.CmisService')
			->will($this->returnValue($expectedTypoScript));
		$factory = $this->getMock('Dkd\\CmisService\\Factory\\ObjectFactory', array('makeInstance'));
		$factory->expects($this->once())->method('makeInstance')
			->with('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager')
			->will($this->returnValue($mockExtbaseConfigurationManager));
		$result = $factory->getExtensionTypoScriptSettings();
		$this->assertSame($expectedTypoScript, $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getConfigurationManagerCallsExpectedMethodSequenceWithoutWriterAndCache() {
		$factory = $this->getMock('Dkd\\CmisService\\Factory\\ObjectFactory',
			array(
				'getConfigurationReaderClassName',
				'getConfigurationWriterClassName',
				'getConfigurationReaderCacheClassName',
				'makeInstance'
			)
		);
		$factory->expects($this->at(0))->method('getConfigurationReaderClassName')
			->will($this->returnValue('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyReader'));
		$factory->expects($this->at(1))->method('getConfigurationWriterClassName')->will($this->returnValue(NULL));
		$factory->expects($this->at(2))->method('getConfigurationReaderCacheClassName')->will($this->returnValue(NULL));
		$factory->expects($this->exactly(2))->method('makeInstance');
		$factory->getConfigurationManager();
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getConfigurationManagerCallsExpectedMethodSequenceWithWriterAndWithoutCache() {
		$factory = $this->getMock('Dkd\\CmisService\\Factory\\ObjectFactory',
			array(
				'getConfigurationReaderClassName',
				'getConfigurationWriterClassName',
				'getConfigurationReaderCacheClassName',
				'makeInstance'
			)
		);
		$factory->expects($this->at(0))->method('getConfigurationReaderClassName')
			->will($this->returnValue('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyReader'));
		$factory->expects($this->at(1))->method('getConfigurationWriterClassName')
			->will($this->returnValue('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyWriter'));
		$factory->expects($this->at(2))->method('getConfigurationReaderCacheClassName')->will($this->returnValue(NULL));
		$factory->expects($this->exactly(3))->method('makeInstance');
		$factory->getConfigurationManager();
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getConfigurationManagerCallsExpectedMethodSequenceWithWriterAndCache() {
		$factory = $this->getMock('Dkd\\CmisService\\Factory\\ObjectFactory',
			array(
				'getConfigurationReaderClassName',
				'getConfigurationWriterClassName',
				'getConfigurationReaderCacheClassName',
				'makeInstance'
			)
		);
		$factory->expects($this->at(0))->method('getConfigurationReaderClassName')
			->will($this->returnValue('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyReader'));
		$factory->expects($this->at(1))->method('getConfigurationWriterClassName')
			->will($this->returnValue('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyWriter'));
		$factory->expects($this->at(2))->method('getConfigurationReaderCacheClassName')
			->will($this->returnValue('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyReader'));
		$factory->expects($this->exactly(4))->method('makeInstance');
		$factory->getConfigurationManager();
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getLoggerCallsExpectedMethodSequence() {
		$factory = $this->getMock('Dkd\\CmisService\\Factory\\ObjectFactory', array('makeInstance'));
		$mockLogManager = $this->getMock('TYPO3\\CMS\\Core\\Log\\LogManager', array('getLogger'));
		$mockLogManager->expects($this->once())->method('getLogger')->will($this->returnValue('foobarloggerinstance'));
		$factory->expects($this->once())->method('makeInstance')
			->with('TYPO3\\CMS\\Core\\Log\\LogManager')
			->will($this->returnValue($mockLogManager));
		$result = $factory->getLogger();
		$this->assertSame('foobarloggerinstance', $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getLoggerReturnsStoredInstanceIfSet() {
		$factory = $this->getAccessibleMock('Dkd\\CmisService\\Factory\\ObjectFactory', array('makeInstance'));
		$factory->expects($this->never())->method('makeInstance');
		$logger = $this->getMock('TYPO3\\CMS\\Core\\Log\\Logger', array(), array('dkd.cmisservice.test'));
		$factory->_setStatic('logger', $logger);
		$result = $factory->getLogger();
		$this->assertSame($logger, $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getConfigurationManagerClassNameReturnsValidClassName() {
		$factory = new ObjectFactory();
		$result = $this->callInaccessibleMethod($factory, 'getConfigurationManagerClassName');
		$this->assertTrue(class_exists($result));
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getConfigurationReaderClassNameReturnsValidClassName() {
		$factory = new ObjectFactory();
		$result = $this->callInaccessibleMethod($factory, 'getConfigurationReaderClassName');
		$this->assertTrue(class_exists($result));
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getConfigurationWriterClassNameReturnsValidClassName() {
		$factory = new ObjectFactory();
		$result = $this->callInaccessibleMethod($factory, 'getConfigurationWriterClassName');
		$this->assertTrue(class_exists($result));
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getConfigurationReaderCacheClassNameReturnsValidClassName() {
		$factory = new ObjectFactory();
		$result = $this->callInaccessibleMethod($factory, 'getConfigurationReaderCacheClassName');
		$this->assertTrue(class_exists($result));
	}

}
