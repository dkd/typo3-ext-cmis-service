<?php
namespace Dkd\CmisService\Tests\Unit\Extraction;

use Dkd\CmisService\Extraction\MultiValueExtraction;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class MultiValueExtractionTest
 */
class MultiValueExtractionTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @param mixed $input
	 * @param array $expected
	 * @dataProvider getExtractTestValues
	 * @test
	 * @return void
	 */
	public function testExtract($input, array $expected) {
		$extraction = new MultiValueExtraction();
		$result = $extraction->extract($input);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return array
	 */
	public function getExtractTestValues() {
		return array(
			array('1,2,3', array(1, 2, 3)),
			array('1, 2, 3', array(1, 2, 3))
		);
	}

}
