<?php
namespace Dkd\CmisService\Tests\Unit\Analysis\Detection;

use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class AbstractDetectorTest
 */
class AbstractDetectorTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function resolveTableConfigurationForFieldReturnsTcaValues() {
		$GLOBALS['TCA']['foobar']['columns']['barfoo'] = 'baz';
		$instance = $this->getMockForAbstractClass('Dkd\\CmisService\\Analysis\\Detection\\AbstractDetector');
		$result = $this->callInaccessibleMethod($instance, 'resolveTableConfigurationForField', 'foobar', 'barfoo');
		$this->assertEquals('baz', $result);
		unset($GLOBALS['TCA']['foobar']);
	}

}
