<?php
namespace Dkd\CmisService\Tests\Unit\Analysis\Detection;

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

}
