<?php
namespace Dkd\CmisService\Tests\Unit\Extraction;

use Dkd\CmisService\Extraction\PassthroughExtraction;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class PassthroughExtractionTest
 */
class PassthroughExtractionTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function returnsUntouchedContent() {
		$instance = new PassthroughExtraction();
		$original = 'Nice test';
		$result = $instance->extract($original);
		$this->assertEquals($original, $result);
	}

}
