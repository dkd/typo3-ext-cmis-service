<?php
namespace Dkd\CmisService\Tests\Unit\Hook;

use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class ListenerTest
 */
class DatabaseListenerTest extends UnitTestCase {

	/**
	 * @return void
	 */
	public function testSelectPostProcessDoesNothing() {
		$connection = $this->getMock('TYPO3\\CMS\\Core\\Database\\DatabaseConnection', array('connect'), array(), '', FALSE);
		$listener = $this->getMock('Dkd\\CmisService\\Hook\\DatabaseListener', array('dummy'), array(), '', FALSE);
		$fields = '';
		$table = 'void';
		$where = 'void';
		$groupBy = 'void';
		$orderBy = 'void';
		$limit = 0;
		$result = $listener->exec_SELECTquery_postProcessAction($fields, $table, $where, $groupBy, $orderBy, $limit, $connection);
		$this->assertNull($result);
	}

	/**
	 * @return void
	 */
	public function testInsertMultipleRowsPostProcessDoesNothing() {
		$connection = $this->getMock('TYPO3\\CMS\\Core\\Database\\DatabaseConnection', array('connect'), array(), '', FALSE);
		$listener = $this->getMock('Dkd\\CmisService\\Hook\\DatabaseListener', array('dummy'), array(), '', FALSE);
		$table = 'void';
		$fields = array();
		$rows = array();
		$noQuoteFields = array();
		$result = $listener->exec_INSERTmultipleRows_postProcessAction($table, $fields, $rows, $noQuoteFields, $connection);
		$this->assertNull($result);
	}

	/**
	 * @return void
	 */
	public function testDeletePostProcessDoesNothing() {
		$connection = $this->getMock('TYPO3\\CMS\\Core\\Database\\DatabaseConnection', array('connect'), array(), '', FALSE);
		$listener = $this->getMock('Dkd\\CmisService\\Hook\\DatabaseListener', array('dummy'), array(), '', FALSE);
		$table = 'void';
		$where = 'void';
		$result = $listener->exec_DELETEquery_postProcessAction($table, $where, $connection);
		$this->assertNull($result);
	}

	/**
	 * @return void
	 */
	public function testTruncatePostProcessCreatesEvictionTasks() {
		$connection = $this->getMock('TYPO3\\CMS\\Core\\Database\\DatabaseConnection', array('connect'), array(), '', FALSE);
		$listener = $this->getMock(
			'Dkd\\CmisService\\Hook\\DatabaseListener',
			array('createAndQueueEvictionTask'),
			array(), '', FALSE
		);
		$table = 'void';
		$listener->expects($this->once())->method('createAndQueueEvictionTask')->with($table);
		$result = $listener->exec_TRUNCATEquery_postProcessAction($table, $connection);
		$this->assertNull($result);
	}

}
