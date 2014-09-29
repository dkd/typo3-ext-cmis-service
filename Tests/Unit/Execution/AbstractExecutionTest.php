<?php
namespace Dkd\CmisService\Tests\Unit\Execution;

use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class AbstractExecutionTest
 */
class AbstractExecutionTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function createsResultObjects() {
		$execution = $this->getAccessibleMockForAbstractClass('Dkd\\CmisService\\Execution\\AbstractExecution');
		$result = $this->callInaccessibleMethod($execution, 'createResultObject');
		$this->assertInstanceOf('Dkd\\CmisService\\Execution\\Result', $result);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function storesResultAfterExecution() {
		$task = $this->getAccessibleMock('Dkd\\CmisService\\Tests\\Fixtures\\Task\\DummyTask');
		$execution = $this->getAccessibleMockForAbstractClass('Dkd\\CmisService\\Execution\\AbstractExecution');
		$executionResult = $execution->execute($task);
		$this->assertInstanceOf('Dkd\\CmisService\\Execution\\Result', $execution->getResult());
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function loadRecordFromDatabaseDelegatesToGlobalDatabaseConnection() {
		$backup = isset($GLOBALS['TYPO3_DB']) ? $GLOBALS['TYPO3_DB'] : NULL;
		$fields = array('foo', 'bar');
		$table = 'baz';
		$uid = 123;
		$GLOBALS['TYPO3_DB'] = $this->getMock('TYPO3\\CMS\\Core\\Database\\DatabaseConnection', array('exec_SELECTgetSingleRow'));
		$GLOBALS['TYPO3_DB']->expects($this->once())->method('exec_SELECTGetSingleRow')
			->with(implode(',', $fields), $table, "uid = '" . $uid . "'")
			->will($this->returnValue('foobar'));

		$execution = $this->getAccessibleMockForAbstractClass('Dkd\\CmisService\\Execution\\AbstractExecution');
		$this->callInaccessibleMethod($execution, 'loadRecordFromDatabase', $table, $uid, $fields);
		unset($GLOBALS['TYPO3_DB']);
	}
}
