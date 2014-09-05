<?php
namespace Dkd\CmisService\Tests\Unit\Analysis;

use Dkd\CmisService\Analysis\RecordAnalyzer;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class RecordAnalyzerTest
 */
class RecordAnalyzerTest extends UnitTestCase {

	const TABLE = 'dummytabledoesnotexist';

	/**
	 * @var array
	 */
	protected $records = array(
		array('dummy' => 'Record one'),
		array('dummy' => 'Record two')
	);

	/**
	 * Setup
	 *
	 * @return void
	 */
	protected function setUp() {
		$GLOBALS['TCA'][self::TABLE] = array(
			'columns' => array(
				'dummy' => array(
					'config' => array(
						'type' => 'input'
					)
				),
				'dummy2' => array(
					'config' => array(
						'type' => 'passthrough'
					)
				),
			)
		);
		parent::setUp();
	}

	/**
	 * Teardown
	 *
	 * @return void
	 */
	protected function tearDown() {
		unset($GLOBALS['TCA'][self::TABLE]);
		parent::tearDown();
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function instanciationSetsInternalProperties() {
		$analyzer = new RecordAnalyzer(self::TABLE, $this->records[0]);
		$this->assertAttributeEquals(self::TABLE, 'table', $analyzer);
		$this->assertAttributeEquals($this->records[0], 'record', $analyzer);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getIndexableColumnNamesUsesInternalTablePropertyAndDelegatesToIndexableColumnDetector() {
		$analyzer = new RecordAnalyzer(self::TABLE, $this->records[0]);
		$names = $analyzer->getIndexableColumnNames();
		$this->assertEquals(array('dummy'), $names);
	}

}
