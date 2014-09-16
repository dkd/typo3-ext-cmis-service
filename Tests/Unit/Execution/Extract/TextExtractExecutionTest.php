<?php
namespace Dkd\CmisService\Tests\Unit\Execution\Extract;

use Dkd\CmisService\Execution\Result;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class TextExtractionTest
 */
class TextExtractionTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function executeCreatesResultObjectAndStoresAsProperty() {
		$result = new Result();
		$instance = $this->getMock('Dkd\\CmisService\\Execution\\Extract\\TextExtractExecution', array('createResultObject'));
		$instance->expects($this->once())->method('createResultObject')->will($this->returnValue($result));
		$outputResult = $instance->execute();
		$this->assertAttributeEquals($result, 'result', $instance);
		$this->assertSame($outputResult, $result);
	}

}
