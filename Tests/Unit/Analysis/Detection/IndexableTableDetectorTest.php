<?php
namespace Dkd\CmisService\Tests\Unit\Analysis\Detection;

use Dkd\CmisService\Analysis\Detection\IndexableTableDetector;
use Dkd\CmisService\Tests\Fixtures\Configuration\DummyMasterConfiguration;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class IndexableTableDetectorTest
 */
class IndexableTableDetectorTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getObjectFactoryReturnsObjectFactory() {
		$instance = new IndexableTableDetector();
		$result = $this->callInaccessibleMethod($instance, 'getObjectFactory');
		$this->assertInstanceOf('Dkd\\CmisService\\Factory\\ObjectFactory', $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getConfigurationReturnsObjectFactoryMasterConfiguration() {
		$dummyConfiguration = new DummyMasterConfiguration();
		$objectFactory = $this->getMock('Dkd\\CmisService\\Factory\\ObjectFactory', array('getConfiguration'));
		$objectFactory->expects($this->once())->method('getConfiguration')->will($this->returnValue($dummyConfiguration));
		$instance = $this->getMock('Dkd\\CmisService\\Analysis\\Detection\\IndexableTableDetector', array('getObjectFactory'));
		$instance->expects($this->once())->method('getObjectFactory')->will($this->returnValue($objectFactory));
		$result = $this->callInaccessibleMethod($instance, 'getConfiguration');
		$this->assertSame($dummyConfiguration, $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getEnabledTableNamesCallsExpectedMethodSequenceAndReturnsExpectedTableNames() {
		$tableConfiguration = $this->getMock(
			'Dkd\\CmisService\\Configuration\\Definitions\\TableConfiguration',
			array('getDefinitions')
		);
		$tableConfiguration->expects($this->once())->method('getDefinitions')->will($this->returnValue(array(
			'tt_content' => array('enabled' => '1'),
			'pages' => array('enabled' => '0')
		)));
		$masterConfiguration = $this->getMock(
			'Dkd\\CmisService\\Configuration\\Definitions\\MasterConfiguration',
			array('getTableConfiguration'),
			array(),
			'',
			FALSE
		);
		$masterConfiguration->expects($this->once())->method('getTableConfiguration')
			->will($this->returnValue($tableConfiguration));
		$objectFactory = $this->getMock('Dkd\\CmisService\\Factory\\ObjectFactory', array('getConfiguration'));
		$objectFactory->expects($this->once())->method('getConfiguration')->will($this->returnValue($masterConfiguration));
		$instance = $this->getMock(
			'Dkd\\CmisService\\Analysis\\Detection\\IndexableTableDetector',
			array('getObjectFactory', 'isTableEnabled')
		);
		$instance->expects($this->at(0))->method('getObjectFactory')->will($this->returnValue($objectFactory));
		$instance->expects($this->at(1))->method('isTableEnabled')->with('tt_content')->will($this->returnValue(TRUE));
		$instance->expects($this->at(2))->method('isTableEnabled')->with('pages')->will($this->returnValue(FALSE));
		$result = $instance->getEnabledTableNames();
		$this->assertEquals(array('tt_content'), $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function isTableEnabledWithoutDefinitionArgumentFetchesDefinitionFromConfiguration() {
		$tableConfiguration = $this->getMock('Dkd\\CmisService\\Configuration\\Definitions\\TableConfiguration', array('get'));
		$tableConfiguration->expects($this->once())->method('get')->with('foobar')->will($this->returnValue(array()));
		$masterConfiguration = $this->getMock(
			'Dkd\\CmisService\\Configuration\\Definitions\\MasterConfiguration',
			array('getTableConfiguration'),
			array(),
			'',
			FALSE
		);
		$masterConfiguration->expects($this->once())->method('getTableConfiguration')
			->will($this->returnValue($tableConfiguration));
		$objectFactory = $this->getMock('Dkd\\CmisService\\Factory\\ObjectFactory', array('getConfiguration'));
		$objectFactory->expects($this->once())->method('getConfiguration')->will($this->returnValue($masterConfiguration));
		$instance = $this->getMock('Dkd\\CmisService\\Analysis\\Detection\\IndexableTableDetector', array('getObjectFactory'));
		$instance->expects($this->once())->method('getObjectFactory')->will($this->returnValue($objectFactory));
		$result = $instance->isTableEnabled('foobar');
		$this->assertTrue($result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function isTableEnabledWithDefinitionDoesNotFetchDefinitionFromConfiguration() {
		$instance = $this->getMock('Dkd\\CmisService\\Analysis\\Detection\\IndexableTableDetector', array('getObjectFactory'));
		$instance->expects($this->never())->method('getObjectFactory');
		$result = $instance->isTableEnabled('foobar', array());
		$this->assertTrue($result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function isTableEnabledReturnsFalseIfDefinitionDisablesIt() {
		$instance = $this->getMock('Dkd\\CmisService\\Analysis\\Detection\\IndexableTableDetector', array('getObjectFactory'));
		$instance->expects($this->never())->method('getObjectFactory');
		$result = $instance->isTableEnabled('foobar', array('enabled' => 0));
		$this->assertFalse($result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function isTableEnabledReturnsBooleanValueOfDefinitionIfDefinitionIsNotArrayAndEvaluatesToTrue() {
		$instance = $this->getMock('Dkd\\CmisService\\Analysis\\Detection\\IndexableTableDetector', array('getObjectFactory'));
		$instance->expects($this->never())->method('getObjectFactory');
		$result = $instance->isTableEnabled('foobar', '1');
		$this->assertTrue($result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function isTableEnabledReturnsBooleanValueOfDefinitionIfDefinitionIsNotArrayAndEvaluatesToFalse() {
		$instance = $this->getMock('Dkd\\CmisService\\Analysis\\Detection\\IndexableTableDetector', array('getObjectFactory'));
		$instance->expects($this->never())->method('getObjectFactory');
		$result = $instance->isTableEnabled('foobar', '0');
		$this->assertFalse($result);
	}

}
