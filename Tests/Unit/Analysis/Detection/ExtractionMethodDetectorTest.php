<?php
namespace Dkd\CmisService\Tests\Unit\Analysis\Detection;

use Dkd\CmisService\Analysis\Detection\ExtractionMethodDetector;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class ExtractionMethodDetectorTest
 */
class ExtractionMethodDetectorTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function resolveExtractionByTypeNameOrClassNameSupportsShortName() {
		$detector = new ExtractionMethodDetector();
		$shortName = ExtractionMethodDetector::METHOD_PASSTHROUGH;
		$result = $detector->resolveExtractionByTypeNameOrClassName($shortName);
		$this->assertInstanceOf('Dkd\\CmisService\\Extraction\\ExtractionInterface', $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function resolveExtractionByTypeNameOrClassNameSupportsFullClassName() {
		$detector = new ExtractionMethodDetector();
		$shortName = 'Dkd\\CmisService\\Extraction\\PassthroughExtraction';
		$result = $detector->resolveExtractionByTypeNameOrClassName($shortName);
		$this->assertInstanceOf('Dkd\\CmisService\\Extraction\\ExtractionInterface', $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function resolveExtractionByTypeNameOrClassNameThrowsRuntimeExceptionOnIncorrectInterface() {
		$detector = new ExtractionMethodDetector();
		$existingButInvalidClass = '\\DateTime';
		$this->setExpectedException('RuntimeException', NULL, 1410960261);
		$detector->resolveExtractionByTypeNameOrClassName($existingButInvalidClass);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function resolveExtractionByTypeNameOrClassNameThrowsRuntimeExceptionOnNonexistingClass() {
		$detector = new ExtractionMethodDetector();
		$existingButInvalidClass = 'ClassDoesNotExist';
		$this->setExpectedException('RuntimeException', NULL, 1413286569);
		$detector->resolveExtractionByTypeNameOrClassName($existingButInvalidClass);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function resolveExtractionForColumnCallsExpectedMethodSequence() {
		$mock = $this->getMock(
			'Dkd\\CmisService\\Analysis\\Detection\\ExtractionMethodDetector',
			array('resolveTableConfigurationForField', 'determineExtractionMethod', 'resolveExtractionByTypeNameOrClassName')
		);
		$mock->expects($this->at(0))->method('resolveTableConfigurationForField')
			->with('table', 'field')
			->will($this->returnValue('foobar'));
		$mock->expects($this->at(1))->method('determineExtractionMethod')
			->with('foobar')
			->will($this->returnValue('baz'));
		$mock->expects($this->at(2))->method('resolveExtractionByTypeNameOrClassName')
			->with('baz')
			->will($this->returnValue('foo'));
		$result = $mock->resolveExtractionForColumn('table', 'field');
		$this->assertEquals('foo', $result);
	}

	/**
	 * Unit test
	 *
	 * @param array $configuration
	 * @param string $expectedType
	 * @test
	 * @return void
	 * @dataProvider getConfigurationsAndExpectedExtractionTypes
	 */
	public function determineExtractionMethodReturnsExpectedType(array $configuration, $expectedType) {
		$detector = new ExtractionMethodDetector();
		$result = $this->callInaccessibleMethod($detector, 'determineExtractionMethod', $configuration);
		$this->assertEquals($expectedType, $result);
	}

	/**
	 * @return array
	 */
	public function getConfigurationsAndExpectedExtractionTypes() {
		return array(
			// sane defaults
			array(
				array(),
				ExtractionMethodDetector::DEFAULT_METHOD
			),
			array(
				array('config' => array('type' => 'unsupported')),
				ExtractionMethodDetector::DEFAULT_METHOD
			),

			// Rich Text field detection for "text" field types
			array(
				array('config' => array('type' => 'text'), 'defaultExtras' => 'rte_only'),
				ExtractionMethodDetector::METHOD_RICHTEXT
			),
			array(
				array('config' => array('type' => 'text'), 'defaultExtras' => 'rte_transform[options]'),
				ExtractionMethodDetector::METHOD_RICHTEXT
			),
			array(
				array('config' => array('type' => 'select', 'items' => array())),
				ExtractionMethodDetector::METHOD_MULTIVALUE
			),

			// relations WITH maximum size EQUAL one expect SingleRelation
			array(
				array('config' => array('type' => 'select', 'table' => 'foobar', 'size' => 1)),
				ExtractionMethodDetector::METHOD_SINGLERELATION
			),
			array(
				array('config' => array('type' => 'select', 'table' => 'foobar', 'maxitems' => 1)),
				ExtractionMethodDetector::METHOD_SINGLERELATION
			),
			array(
				array('config' => array('type' => 'group', 'internal_type' => 'db', 'foreign_table' => 'foobar', 'size' => 1)),
				ExtractionMethodDetector::METHOD_SINGLERELATION
			),
			array(
				array('config' => array('type' => 'group', 'internal_type' => 'db', 'foreign_table' => 'foobar', 'maxitems' => 1)),
				ExtractionMethodDetector::METHOD_SINGLERELATION
			),

			// relations WITH maximum size ABOVE one expect MultiRelation
			array(
				array('config' => array('type' => 'select', 'table' => 'foobar', 'size' => 10)),
				ExtractionMethodDetector::METHOD_MULTIRELATION
			),
			array(
				array('config' => array('type' => 'select', 'table' => 'foobar', 'maxitems' => 10)),
				ExtractionMethodDetector::METHOD_MULTIRELATION
			),
			array(
				array('config' => array('type' => 'group', 'internal_type' => 'db', 'foreign_table' => 'foobar', 'size' => 10)),
				ExtractionMethodDetector::METHOD_MULTIRELATION
			),
			array(
				array('config' => array('type' => 'group', 'internal_type' => 'db', 'foreign_table' => 'foobar', 'maxitems' => 10)),
				ExtractionMethodDetector::METHOD_MULTIRELATION
			),

			// relations WITHOUT maximum size expect MultiRelation
			array(
				array('config' => array('type' => 'select', 'table' => 'foobar')),
				ExtractionMethodDetector::METHOD_MULTIRELATION
			),
			array(
				array('config' => array('type' => 'select', 'table' => 'foobar')),
				ExtractionMethodDetector::METHOD_MULTIRELATION
			),
			array(
				array('config' => array('type' => 'group', 'internal_type' => 'db', 'foreign_table' => 'foobar')),
				ExtractionMethodDetector::METHOD_MULTIRELATION
			),
			array(
				array('config' => array('type' => 'group', 'internal_type' => 'db', 'foreign_table' => 'foobar')),
				ExtractionMethodDetector::METHOD_MULTIRELATION
			),
		);
	}

}
