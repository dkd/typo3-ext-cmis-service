<?php
namespace Dkd\CmisService\Analysis;

use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class TableConfigurationAnalyzerTest
 *
 * @package Dkd\CmisService\Analysis
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
		$field = 'dummyfield';
		$instance = $this->getMock(
			'Dkd\\CmisService\\Analysis\\TableConfigurationAnalyzer',
			array('getAllTableNames', 'getAllFieldNamesOfTable', 'isFieldPotentiallyIndexable')
		);
		$instance->expects($this->at(0))->method('getAllTableNames')->will($this->returnValue(array(self::TABLE)));
		$instance->expects($this->at(1))->method('getAllFieldNamesOfTable')
			->with(self::TABLE)->will($this->returnValue(array($field)));
		$instance->expects($this->at(2))->method('isFieldPotentiallyIndexable')
			->with(self::TABLE, $field)->will($this->returnValue(TRUE));
		$return = $instance->getIndexableTableNames();
		$this->assertEquals(array(self::TABLE), $return);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function isFieldPotentiallyIndexableCallsExpectedMethodsAndReturnsEarlyFalseForUnknownTable() {
		$instance = $this->getMock(
			'Dkd\\CmisService\Analysis\\TableConfigurationAnalyzer',
			array('getAllTableNames', 'getAllFieldNamesOfTable', 'getFieldTypeName', 'getIndexableFieldTypeNames')
		);
		$instance->expects($this->at(0))->method('getAllTableNames')->will($this->returnValue(array('unsupportedtable')));
		$instance->expects($this->never())->method('getAllFieldNamesOfTable');
		$instance->expects($this->never())->method('getFieldTypeName');
		$instance->expects($this->never())->method('getIndexableFieldTypeNames');
		$return = $this->callInaccessibleMethod($instance, 'isFieldPotentiallyIndexable', 'invalidtablename', 'invalidfieldname');
		$this->assertFalse($return);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function isFieldPotentiallyIndexableCallsExpectedMethodsAndReturnsEarlyFalseForUnknownFieldInKnownTable() {
		$validFieldName = key($GLOBALS['TCA'][self::TABLE]['columns']);
		$instance = $this->getMock(
			'Dkd\\CmisService\Analysis\\TableConfigurationAnalyzer',
			array('getAllTableNames', 'getAllFieldNamesOfTable', 'getFieldTypeName', 'getIndexableFieldTypeNames')
		);
		$instance->expects($this->at(0))->method('getAllTableNames')->will($this->returnValue(array(self::TABLE)));
		$instance->expects($this->at(1))->method('getAllFieldNamesOfTable')
			->with(self::TABLE)->will($this->returnValue(array($validFieldName)));
		$instance->expects($this->never())->method('getFieldTypeName');
		$instance->expects($this->never())->method('getIndexableFieldTypeNames');
		$return = $this->callInaccessibleMethod($instance, 'isFieldPotentiallyIndexable', self::TABLE, 'invalidfieldname');
		$this->assertFalse($return);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function isFieldPotentiallyIndexableCallsExpectedMethodsAndReturnsEarlyFalseForUnindexedFieldTypeInKnownTableAndKnownField() {
		$validFieldName = key($GLOBALS['TCA'][self::TABLE]['columns']);
		$validTypeName = $GLOBALS['TCA'][self::TABLE]['columns'][$validFieldName]['config']['type'];
		$instance = $this->getMock(
			'Dkd\\CmisService\Analysis\\TableConfigurationAnalyzer',
			array('getAllTableNames', 'getAllFieldNamesOfTable', 'getFieldTypeName', 'getIndexableFieldTypeNames')
		);
		$instance->expects($this->at(0))->method('getAllTableNames')->will($this->returnValue(array(self::TABLE)));
		$instance->expects($this->at(1))->method('getAllFieldNamesOfTable')
			->with(self::TABLE)->will($this->returnValue(array($validFieldName)));
		$instance->expects($this->at(2))->method('getFieldTypeName')
			->with(self::TABLE, $validFieldName)->will($this->returnValue('unsupportedtype'));
		$instance->expects($this->at(3))->method('getIndexableFieldTypeNames')->will($this->returnValue(array($validTypeName)));
		$return = $this->callInaccessibleMethod($instance, 'isFieldPotentiallyIndexable', self::TABLE, $validFieldName);
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
		$instance = $this->getMock(
			'Dkd\\CmisService\\Analysis\\TableConfigurationAnalyzer',
			array('getAllTableNames', 'getAllFieldNamesOfTable', 'getFieldTypeName', 'getIndexableFieldTypeNames')
		);
		$instance->expects($this->at(0))->method('getAllTableNames')->will($this->returnValue(array(self::TABLE)));
		$instance->expects($this->at(1))->method('getAllFieldNamesOfTable')
			->with(self::TABLE)->will($this->returnValue(array($validFieldName)));
		$instance->expects($this->at(2))->method('getFieldTypeName')
			->with(self::TABLE, $validFieldName)->will($this->returnValue($validTypeName));
		$instance->expects($this->at(3))->method('getIndexableFieldTypeNames')->will($this->returnValue(array($validTypeName)));
		$return = $this->callInaccessibleMethod($instance, 'isFieldPotentiallyIndexable', self::TABLE, $validFieldName);
		$this->assertTrue($return);
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getAllTablesContainsExpectedTable() {
		$instance = new TableConfigurationAnalyzer();
		$allTables = $this->callInaccessibleMethod($instance, 'getAllTableNames');
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
		$fieldNames = $this->callInaccessibleMethod($instance, 'getAllFieldNamesOfTable', self::TABLE);
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
		$analyzer = $this->getMock('Dkd\\CmisService\\Analysis\\TableConfigurationAnalyzer');
		$this->callInaccessibleMethod($analyzer, 'getAllFieldNamesOfTable', 'thistabledoesnotexist');
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getFieldTypeNameThrowsRuntimeExceptionOnUnrecognizedTableNameAndFieldName() {
		$this->setExpectedException('RuntimeException', NULL, 1409091365);
		$analyzer = $this->getMock('Dkd\\CmisService\\Analysis\\TableConfigurationAnalyzer');
		$this->callInaccessibleMethod($analyzer, 'getFieldTypeName', 'thistabledoesnotexist', 'thisfielddoesnotexist');
	}

	/**
	 * Unit test
	 *
	 * @test
	 * @return void
	 */
	public function getFieldTypeNameThrowsRuntimeExceptionOnRecognizedTableNameAndUnrecognizedFieldName() {
		$this->setExpectedException('RuntimeException', NULL, 1409091366);
		$analyzer = $this->getMock('Dkd\\CmisService\\Analysis\\TableConfigurationAnalyzer');
		$this->callInaccessibleMethod($analyzer, 'getFieldTypeName', self::TABLE, 'thisfielddoesnotexist');
	}

}
