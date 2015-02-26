<?php
namespace Dkd\CmisService\Tests\Unit\Configuration;

use Dkd\CmisService\Configuration\ConfigurationManager;
use Dkd\CmisService\Tests\Fixtures\Configuration\DummyMasterConfiguration;
use Dkd\CmisService\Tests\Fixtures\Configuration\DummyReader;
use Dkd\CmisService\Tests\Fixtures\Configuration\DummyWriter;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ConfigurationManagerTest
 */
class ConfigurationManagerTest extends UnitTestCase {

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
	public function constructorSetsInternalPropertiesGivenOneArgument() {
		$dummyReader = new DummyReader();
		$manager = new ConfigurationManager($dummyReader);
		$this->assertAttributeSame($dummyReader, 'reader', $manager);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function constructorSetsInternalPropertiesGivenTwoArguments() {
		$dummyReader = new DummyReader();
		$dummyWriter = new DummyWriter();
		$manager = new ConfigurationManager($dummyReader, $dummyWriter);
		$this->assertAttributeSame($dummyReader, 'reader', $manager);
		$this->assertAttributeSame($dummyWriter, 'writer', $manager);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function constructorSetsInternalPropertiesGivenThreeArguments() {
		$dummyReader = new DummyReader();
		$dummyWriter = new DummyWriter();
		$dummyCache = new DummyReader();
		$manager = new ConfigurationManager($dummyReader, $dummyWriter, $dummyCache);
		$this->assertAttributeSame($dummyReader, 'reader', $manager);
		$this->assertAttributeSame($dummyWriter, 'writer', $manager);
		$this->assertAttributeSame($dummyCache, 'cache', $manager);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getMasterConfigurationReturnsLevelOneCacheIfSet() {
		$dummyWriter = new DummyWriter();
		$mockReader = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyReader', array('checksum'));
		$mockCache = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyReader', array('checksum'));
		$dummyConfiguration = new DummyMasterConfiguration();
		$manager = $this->getAccessibleMock(
			'Dkd\\CmisService\\Configuration\\ConfigurationManager',
			array('dummy'),
			array('reader' => $mockReader, 'writer' => $dummyWriter, 'cache' => $mockCache)
		);
		$mockCache->expects($this->never())->method('read');
		$mockReader->expects($this->never())->method('read');
		$manager->_set('masterConfiguration', $dummyConfiguration);
		$this->assertSame($dummyConfiguration, $manager->getMasterConfiguration());
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getMasterConfigurationReturnsCacheReadIfCacheSet() {
		$dummyWriter = new DummyWriter();
		$mockReader = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyReader', array('checksum'));
		$mockCache = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyReader', array('checksum', 'read'));
		$dummyConfiguration = new DummyMasterConfiguration();
		$manager = new ConfigurationManager($mockReader, $dummyWriter, $mockCache);
		$mockCache->expects($this->once())->method('read')
			->with(GeneralUtility::getFileAbsFileName(ConfigurationManager::CACHE_RESOURCE))
			->will($this->returnValue($dummyConfiguration));
		$mockReader->expects($this->never())->method('read');
		$this->assertSame($dummyConfiguration, $manager->getMasterConfiguration());
		$this->assertAttributeSame($dummyConfiguration, 'masterConfiguration', $manager);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getMasterConfigurationReturnsReaderReadAndSetsLevelOneCacheIfCacheNotSet() {
		$mockReader = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyReader', array('checksum', 'read'));
		$mockWriter = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyWriter', array('checksum'));
		$mockConfiguration = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyMasterConfiguration');
		$mockReader->expects($this->once())->method('read')
			->with(ConfigurationManager::MASTER_RESOURCE)
			->will($this->returnValue($mockConfiguration));
		$manager = new ConfigurationManager($mockReader, $mockWriter);
		$result = $manager->getMasterConfiguration();
		$this->assertSame($mockConfiguration, $result);
		$this->assertAttributeSame($mockConfiguration, 'masterConfiguration', $manager);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function exportThrowsExpectedRuntimeExceptionIfNoWriterSet() {
		$this->setExpectedException('RuntimeException', NULL, 1409181458);
		$mockReader = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyReader', array('checksum'));
		$manager = new ConfigurationManager($mockReader);
		$manager->export('voidResourceIdentifier');
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function exportCallsExpectedMethodSequenceWithExpectedParameters() {
		$mockConfiguration = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyMasterConfiguration');
		$mockReader = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyReader', array('checksum'));
		$mockWriter = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyWriter', array('checksum', 'write'));
		$mockWriter->expects($this->once())->method('write')
			->with($mockConfiguration, GeneralUtility::getFileAbsFileName('voidResourceIdentifier'))
			->will($this->returnValue(TRUE));
		$manager = $this->getMock(
			'Dkd\\CmisService\\Configuration\\ConfigurationManager',
			array('getMasterConfiguration'),
			array($mockReader, $mockWriter)
		);
		$manager->expects($this->once())->method('getMasterConfiguration')->will($this->returnValue($mockConfiguration));
		$result = $manager->export('voidResourceIdentifier');
		$this->assertTrue($result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function expireCachedDefinitionReturnsNullIfNoCacheSet() {
		$mockReader = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyReader', array('checksum'));
		$manager = $this->getMock(
			'Dkd\\CmisService\\Configuration\\ConfigurationManager',
			array('removeResource'),
			array($mockReader)
		);
		$manager->expects($this->never())->method('removeResource');
		$result = $manager->expireCachedDefinition();
		$this->assertNull($result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function expireCachedDefinitionRemovesFileAndReturnsNullIfCacheSet() {
		$mockReader = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyReader', array('checksum'));
		$mockWriter = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyWriter', array('checksum'));
		$mockCache = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyReader', array('checksum'));
		$manager = $this->getMock(
			'Dkd\\CmisService\\Configuration\\ConfigurationManager',
			array('removeResource'),
			array($mockReader, $mockWriter, $mockCache)
		);
		$manager->expects($this->once())->method('removeResource')
			->with(GeneralUtility::getFileAbsFileName(ConfigurationManager::CACHE_RESOURCE))
			->will($this->returnValue(TRUE));
		$result = $manager->expireCachedDefinition();
		$this->assertNull($result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function createOrUpdateCachedDefinitionReturnsEarlyNullIfNoWriterSet() {
		$mockReader = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyReader', array('checksum'));
		$mockReader->expects($this->never())->method('checksum');
		$manager = new ConfigurationManager($mockReader);
		$result = $this->callInaccessibleMethod($manager, 'createOrUpdateCachedDefinition');
		$this->assertNull($result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function createOrUpdateCachedDefinitionReturnsEarlyNullIfNoCacheSet() {
		$mockReader = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyReader', array('checksum'));
		$mockWriter = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyWriter', array('checksum'));
		$mockReader->expects($this->never())->method('checksum');
		$manager = new ConfigurationManager($mockReader, $mockWriter);
		$result = $this->callInaccessibleMethod($manager, 'createOrUpdateCachedDefinition');
		$this->assertNull($result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function createOrUpdateCachedDefinitionWritesCachedDefinitionAndReturnsTrueIfWriterAndCacheSetAndChecksumsDiffer() {
		$mockReader = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyReader', array('checksum'));
		$mockWriter = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyWriter', array('write'));
		$mockCache = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyReader', array('checksum', 'read'));
		$mockReader->expects($this->once())->method('checksum')
			->will($this->returnValue('foobarchecksum'));
		$mockCache->expects($this->once())->method('checksum')
			->will($this->returnValue('differentchecksum'));
		$mockCache->expects($this->never())->method('read');
		$manager = $this->getMock(
			'Dkd\\CmisService\\Configuration\\ConfigurationManager',
			array('expireCachedDefinition', 'export'),
			array($mockReader, $mockWriter, $mockCache)
		);
		$manager->expects($this->once())->method('expireCachedDefinition');
		$manager->expects($this->once())->method('export')
			->with(GeneralUtility::getFileAbsFileName(ConfigurationManager::CACHE_RESOURCE))
			->willReturn(TRUE);
		$result = $this->callInaccessibleMethod($manager, 'createOrUpdateCachedDefinition');
		$this->assertTrue($result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function createOrUpdateCachedDefinitionSkipsWritingAndReturnsNullIfWriterAndCacheSetAndChecksumsSame() {
		$mockReader = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyReader', array('checksum'));
		$mockWriter = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyWriter', array('write'));
		$mockCache = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyReader', array('checksum'));
		$mockReader->expects($this->once())->method('checksum')->will($this->returnValue('foobarchecksum'));
		$mockCache->expects($this->once())->method('checksum')->will($this->returnValue('foobarchecksum'));
		$mockWriter->expects($this->never())->method('write');
		$manager = $this->getMock(
			'Dkd\\CmisService\\Configuration\\ConfigurationManager',
			array('getMasterConfiguration'),
			array($mockReader, $mockWriter, $mockCache)
		);
		$manager->expects($this->never())->method('getMasterConfiguration');
		$result = $this->callInaccessibleMethod($manager, 'createOrUpdateCachedDefinition');
		$this->assertNull($result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function removeResourceUnlinksFile() {
		$mockReader = $this->getMock('Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyReader', array('checksum'));
		$manager = new ConfigurationManager($mockReader);
		$typo3tempFile = GeneralUtility::getFileAbsFileName('typo3temp/tempfile');
		$temporaryFile = TRUE === isset($GLOBALS['TYPO3_CONF_VARS']) ? $typo3tempFile : 'tempfile';
		$temporaryFile .= uniqid();
		touch($temporaryFile);
		$this->assertFileExists($temporaryFile);
		$result = $this->callInaccessibleMethod($manager, 'removeResource', $temporaryFile);
		$this->assertTrue($result);
		$this->assertFileNotExists($temporaryFile);
	}

}
