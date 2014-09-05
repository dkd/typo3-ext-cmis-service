<?php
namespace Dkd\CmisService;

use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Initialization unit test case
 */
class InitializationTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function startCallsExpectedMethodSequenceIfNotInitialized() {
		$initialization = $this->getMock('Dkd\\CmisService\\Initialization', array('isInitialized', 'initialize', 'finish'));
		$initialization->expects($this->at(0))->method('isInitialized')->will($this->returnValue(FALSE));
		$initialization->expects($this->at(1))->method('initialize');
		$initialization->expects($this->at(2))->method('finish');
		$initialization->start();
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function startCallsExpectedMethodSequenceIfInitialized() {
		$initialization = $this->getMock('Dkd\\CmisService\\Initialization', array('isInitialized', 'initialize', 'finish'));
		$initialization->expects($this->at(0))->method('isInitialized')->will($this->returnValue(TRUE));
		$initialization->expects($this->at(1))->method('finish');
		$initialization->expects($this->never())->method('initialize');
		$initialization->start();
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function finishSetsInitializedStatusTrue() {
		$initialization = $this->getAccessibleMock('Dkd\\CmisService\\Initialization');
		$this->callInaccessibleMethod($initialization, 'reset');
		$initialized = $this->callInaccessibleMethod($initialization, 'isInitialized');
		$this->assertFalse($initialized);
		$this->callInaccessibleMethod($initialization, 'finish');
		$initialized = $this->callInaccessibleMethod($initialization, 'isInitialized');
		$this->assertTrue($initialized);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function resetSetsInitializedStatusFalse() {
		$initialization = $this->getAccessibleMock('Dkd\\CmisService\\Initialization');
		$this->callInaccessibleMethod($initialization, 'finish');
		$initialized = $this->callInaccessibleMethod($initialization, 'isInitialized');
		$this->assertTrue($initialized);
		$this->callInaccessibleMethod($initialization, 'reset');
		$initialized = $this->callInaccessibleMethod($initialization, 'isInitialized');
		$this->assertFalse($initialized);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function initializeReturnsNull() {
		$initialization = $this->getAccessibleMock('Dkd\\CmisService\\Initialization');
		$output = $this->callInaccessibleMethod($initialization, 'initialize');
		$this->assertNull($output);
	}

}
