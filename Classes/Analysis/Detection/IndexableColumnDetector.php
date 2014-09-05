<?php
namespace Dkd\CmisService\Analysis\Detection;

use Dkd\CmisService\Analysis\TableConfigurationAnalyzer;

/**
 * Indexable Column Detector
 *
 * Analyzes TCA to find indexable columns in indexable
 * tables.
 */
class IndexableColumnDetector extends AbstractDetector implements DetectorInterface {

	/**
	 * Gets an array of fields which can be indexed in
	 * records coming from $table.
	 *
	 * @param string $table
	 * @return array
	 */
	public function getIndexableColumnNamesFromTable($table) {
		$tableAnalyzer = new TableConfigurationAnalyzer();
		$fields = $tableAnalyzer->getAllFieldNamesOfTable($table);
		$indexable = array();
		foreach ($fields as $fieldName) {
			if (FALSE === $this->isFieldPotentiallyIndexable($table, $fieldName)) {
				continue;
			}
			$indexable[] = $fieldName;
		}
		return $indexable;
	}

	/**
	 * Returns TRUE if a field is *potentially* indexable
	 * as determined by configuration-agnostic rules, for
	 * example returns TRUE for "pages" field "title" when
	 * "type" is zero (standard page) because a standard
	 * input field is indexable unless manually disabled
	 * or not specified in manual configuration.
	 *
	 * Any failed test for indexability returns FALSE,
	 * whether it's because the table is not indexable or
	 * the field does not exist or the field type is
	 * unknown or the record type configured in $type
	 * does not include the field.
	 *
	 * @param string $table
	 * @param string $field
	 * @return boolean
	 */
	public function isFieldPotentiallyIndexable($table, $field) {
		$tableAnalyzer = new TableConfigurationAnalyzer();
		$tables = $tableAnalyzer->getAllTableNames();
		if (FALSE === in_array($table, $tables)) {
			return FALSE;
		}
		$fields = $tableAnalyzer->getAllFieldNamesOfTable($table);
		if (FALSE === in_array($field, $fields)) {
			return FALSE;
		}
		$configuredType = $tableAnalyzer->getFieldTypeName($table, $field);
		$indexableTypes = $tableAnalyzer->getIndexableFieldTypeNames();
		if (FALSE === in_array($configuredType, $indexableTypes)) {
			return FALSE;
		}
		return TRUE;
	}

}
