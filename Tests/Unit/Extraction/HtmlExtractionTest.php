<?php
namespace Dkd\CmisService\Tests\Unit\Extraction;

use Dkd\CmisService\Extraction\HtmlExtraction;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class HtmlExtractionTest
 */
class HtmlExtractionTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function extractsPlainTextFromHtml() {
		$instance = new HtmlExtraction();
		$html = '<b><em>Nice test</em>';
		$expected = strip_tags($html);
		$result = $instance->extract($html);
		$this->assertEquals($expected, $result);
	}

}
