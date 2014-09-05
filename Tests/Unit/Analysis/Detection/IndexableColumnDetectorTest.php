<?php
namespace Dkd\CmisService\Tests\Unit\Analysis\Detection;

use Dkd\CmisService\Analysis\Detection\IndexableColumnDetector;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class IndexableColumnDetectorTest
 */
class IndexableColumnDetectorTest extends UnitTestCase {

	const TABLE = 'dummytabledoesnotexist';

	/**
	 * Setup
	 *
	 * @return void
	 */
	protected function setUp() {
		parent::setUp();
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
				)
			)
		);
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
	public function isFieldPotentiallyIndexableCallsExpectedMethodsAndReturnsEarlyFalseForUnknownFieldInKnownTable() {
		$validFieldName = 'dummy';
		$instance = new IndexableColumnDetector();
		$return = $instance->isFieldPotentiallyIndexable(self::TABLE, 'invalidfieldname');
		$this->assertFalse($return);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function isFieldPotentiallyIndexableCallsExpectedMethodsAndReturnsEarlyFalseForUnindexedFieldTypeInKnownTableAndKnownField() {
		$validFieldName = 'dummy2';
		$instance = new IndexableColumnDetector();
		$return = $instance->isFieldPotentiallyIndexable(self::TABLE, $validFieldName);
		$this->assertFalse($return);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function isFieldPotentiallyIndexableCallsExpectedMethodsAndReturnsFinalTrueForKnownTableAndKnownFieldAndIndexableFieldType() {
		$validFieldName = key($GLOBALS['TCA'][self::TABLE]['columns']);
		$validTypeName = $GLOBALS['TCA'][self::TABLE]['columns'][$validFieldName]['config']['type'];
		$instance = new IndexableColumnDetector();
		$return = $instance->isFieldPotentiallyIndexable(self::TABLE, $validFieldName);
		$this->assertTrue($return);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function isFieldPotentiallyIndexableCallsExpectedMethodsAndReturnsEarlyFalseForUnknownTable() {
		$instance = new IndexableColumnDetector();
		$return = $instance->isFieldPotentiallyIndexable('thistabledoesnotexist', 'thisfielddoesnotexist');
		$this->assertFalse($return);
	}

}
