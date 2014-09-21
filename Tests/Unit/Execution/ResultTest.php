<?php
namespace Dkd\CmisService\Tests\Unit\Execution;

use Dkd\CmisService\Execution\Result;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class ResultTest
 */
class ResultTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function setsInternalPropertiesFromConstructor() {
		$result = new Result('message', Result::ERR, array('foo' => 'bar'));
		$this->assertAttributeEquals('message', 'message', $result);
		$this->assertAttributeEquals(Result::ERR, 'code', $result);
		$this->assertAttributeEquals(array('foo' => 'bar'), 'payload', $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function canGetAndSetMessage() {
		$result = new Result();
		$result->setMessage('message');
		$this->assertAttributeEquals('message', 'message', $result);
		$this->assertEquals('message', $result->getMessage());
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function canGetAndSetCode() {
		$result = new Result();
		$result->setCode(Result::ERR);
		$this->assertAttributeEquals(Result::ERR, 'code', $result);
		$this->assertEquals(Result::ERR, $result->getCode());
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function canGetAndSetPayload() {
		$result = new Result();
		$result->setPayload(array('foo' => 'bar'));
		$this->assertAttributeEquals(array('foo' => 'bar'), 'payload', $result);
		$this->assertEquals(array('foo' => 'bar'), $result->getPayload());
	}

}
