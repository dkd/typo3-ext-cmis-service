<?php
namespace Dkd\CmisService\Tests\Unit\Extraction;

use Dkd\CmisService\Extraction\CommandBasedExtraction;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class CommandBasedExtractionTest
 */
class CommandBasedExtractionTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function executionReturnsNull() {
		// placeholder: will fail when extraction class is completed
		$extraction = new CommandBasedExtraction();
		$result = $extraction->extract('Some content');
		$this->assertNull($result);
	}

}
