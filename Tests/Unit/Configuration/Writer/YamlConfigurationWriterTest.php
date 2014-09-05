<?php
namespace Dkd\CmisService\Tests\Unit\Configuration\Writer;

use Dkd\CmisService\Configuration\Writer\YamlConfigurationWriter;
use Dkd\CmisService\Tests\Fixtures\Configuration\DummyMasterConfiguration;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class YamlConfigurationReaderTest
 */
class YamlConfigurationWriterTest extends UnitTestCase {

	/**
	 * Setup. Create a VFS filesystem for testing writes.
	 *
	 * Fakes a Windows OS during tests if the framework is not
	 * loaded - this avoids numerous FS-dependent calls during
	 * writing to VFS. No other involved code depends on this
	 * constant so it should be safe to set this even if OS is
	 * not actually a Windows system.
	 *
	 * @return void
	 */
	public function setUp() {
		vfsStreamWrapper::register();
		vfsStreamWrapper::setRoot(new vfsStreamDirectory('temp'));
		if (FALSE === defined('TYPO3_OS')) {
			define('TYPO3_OS', 'WIN');
		}
		parent::setUp();
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function writesYamlFiles() {
		$definition = new DummyMasterConfiguration();
		$definition->setDefinitions(array('foo' => array('bar' => TRUE)));
		$writer = new YamlConfigurationWriter();
		$virtualFile = vfsStream::url('temp/cmis-service-test-yaml.yml');
		$writer->write($definition, $virtualFile);
		$this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('cmis-service-test-yaml.yml'));
		$thawedDefinition = $writer->read(
			$virtualFile,
			'Dkd\\CmisService\\Tests\\Fixtures\\Configuration\\DummyMasterConfiguration'
		);
		$this->assertEquals($definition->getDefinitions(), $thawedDefinition->getDefinitions());
	}

}
