<?php
namespace Dkd\CmisService\Tests\Unit\Execution;

use Dkd\CmisService\Execution\Exception;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class ExceptionTest
 */
class ExceptionTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function canBeThrownAsException() {
		$exception = new Exception('Message', 123);
		$this->setExpectedException('Dkd\\CmisService\\Execution\\Exception', 'Message', 123);
		throw $exception;
	}

}
