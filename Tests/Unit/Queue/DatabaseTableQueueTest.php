<?php
namespace Dkd\CmisService\Tests\Unit\Queue;

use Dkd\CmisService\Queue\DatabaseTableQueue;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class DatabaseTableQueueTest
 */
class DatabaseTableQueueTest extends UnitTestCase {

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function countCountsResultOfPerformedQuery() {
		$queue = $this->getMock('Dkd\\CmisService\\Queue\\DatabaseTableQueue', array('performDatabaseQuery'));
		$queue->expects($this->once())->method('performDatabaseQuery')
			->with(DatabaseTableQueue::QUERY_COUNT)->willReturn(array('foo', 'bar'));
		$this->assertEquals(2, $queue->count());
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function flushPerformsExpectedQuery() {
		$queue = $this->getMock('Dkd\\CmisService\\Queue\\DatabaseTableQueue', array('performDatabaseQuery'));
		$queue->expects($this->once())->method('performDatabaseQuery')->with(DatabaseTableQueue::QUERY_FLUSH_ALL);
		$queue->flush();
	}

}
