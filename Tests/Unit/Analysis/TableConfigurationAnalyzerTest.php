<?php
namespace Dkd\CmisService\Tests\Unit\Analysis;

use Dkd\CmisService\Analysis\TableConfigurationAnalyzer;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class TableConfigurationAnalyzerTest
 */
class TableConfigurationAnalyzerTest extends UnitTestCase {

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
	public function getIndexableTableNamesCallsExpectedMethodsAndReturnsExpectedResult() {
		$field = 'dummy';
		$instance = $this->getMock(
			'Dkd\\CmisService\\Analysis\\TableConfigurationAnalyzer',
			array('getAllTableNames', 'getAllFieldNamesOfTable')
		);
		$instance->expects($this->at(0))->method('getAllTableNames')->will($this->returnValue(array(self::TABLE)));
		$instance->expects($this->at(1))->method('getAllFieldNamesOfTable')
			->with(self::TABLE)->will($this->returnValue(array($field)));
		$return = $instance->getIndexableTableNames();
		$this->assertEquals(array(self::TABLE), $return);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getAllTablesContainsExpectedTable() {
		$instance = new TableConfigurationAnalyzer();
		$allTables = $instance->getAllTableNames();
		$this->assertContains(self::TABLE, $allTables);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getAllFieldNamesOfTablesContainsExpectedFields() {
		$instance = new TableConfigurationAnalyzer();
		$expectedFieldNames = array_keys($GLOBALS['TCA'][self::TABLE]['columns']);
		$fieldNames = $instance->getAllFieldNamesOfTable(self::TABLE);
		$this->assertSame($expectedFieldNames, $fieldNames);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getFieldTypeNameReturnsExpectedName() {
		$targetFieldName = key($GLOBALS['TCA'][self::TABLE]['columns']);
		$instance = new TableConfigurationAnalyzer();
		$expectedTypeName = $GLOBALS['TCA'][self::TABLE]['columns'][$targetFieldName]['config']['type'];
		$typeName = $this->callInaccessibleMethod($instance, 'getFieldTypeName', self::TABLE, $targetFieldName);
		$this->assertSame($expectedTypeName, $typeName);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getIndexableTypeNamesReturnsArrayOfStrings() {
		$instance = new TableConfigurationAnalyzer();
		$types = $instance->getIndexableFieldTypeNames();
		$this->assertThat($types, new \PHPUnit_Framework_Constraint_IsType(\PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY));
		foreach ($types as $type) {
			$this->assertThat($type, new \PHPUnit_Framework_Constraint_IsType(\PHPUnit_Framework_Constraint_IsType::TYPE_STRING));
		}
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getAllFieldNamesOfTableThrowsRuntimeExceptionOnUnrecognizedTableName() {
		$this->setExpectedException('RuntimeException', NULL, 1409091364);
		$analyzer = new TableConfigurationAnalyzer();
		$analyzer->getAllFieldNamesOfTable('thistabledoesnotexist');
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getFieldTypeNameThrowsRuntimeExceptionOnUnrecognizedTableNameAndFieldName() {
		$this->setExpectedException('RuntimeException', NULL, 1409091365);
		$analyzer = new TableConfigurationAnalyzer();
		$analyzer->getFieldTypeName('thistabledoesnotexist', 'thisfielddoesnotexist');
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getFieldTypeNameThrowsRuntimeExceptionOnRecognizedTableNameAndUnrecognizedFieldName() {
		$this->setExpectedException('RuntimeException', NULL, 1409091366);
		$analyzer = new TableConfigurationAnalyzer();
		$analyzer->getFieldTypeName(self::TABLE, 'thisfielddoesnotexist');

	}

}
