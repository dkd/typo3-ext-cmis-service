<?php
namespace Dkd\CmisService\Analysis;

/**
 * Table Configuration Analyzer
 *
 * The responsibilities of this class are:
 *
 * - Determining the composition of tables on the system
 * - Determining which of these tables are indexable[1]
 * - Returning key configuration aspects about tables
 *   and fields contained in current system configuration.
 *
 * [1] Indexability determined by built-in rules alone,
 * letting this class deliver values to user interfaces
 * which toggle indexing options for tables. Additional
 * API methods must be called to determine if the active
 * configuration has disabled the table or disabled enough
 * field and/or field types that the table or field no
 * longer is a candidate for indexing.
 */
class TableConfigurationAnalyzer {

	/**
	 * Fast method for returning names-only of tables
	 * which contain at least one potentially indexed
	 * field. System configuration of individual fields
	 * is not taken into consideration; names returned
	 * from this method may or may not contain enabled
	 * indexable fields.
	 *
	 * @api
	 * @return array
	 */
	public function getIndexableTableNames() {
		$tables = $this->getAllTableNames();
		$indexable = array();
		foreach ($tables as $table) {
			$fields = $this->getAllFieldNamesOfTable($table);
			foreach ($fields as $field) {
				if (TRUE === $this->isFieldPotentiallyIndexable($table, $field)) {
					$indexable[] = $table;
					break;
				}
			}
		}
		return $indexable;
	}

	/**
	 * Returns an array of indexable field types as
	 * determined by core system rules without taking
	 * into consideration if configuration has disabled
	 * the field type.
	 *
	 * @api
	 * @return array
	 */
	public function getIndexableFieldTypeNames() {
		return array(
			'input', 'text'
		);
	}

	/**
	 * Gets the names-only of every recognized table
	 * which are present in the system.
	 *
	 * @return array
	 */
	protected function getAllTableNames() {
		return array_keys($GLOBALS['TCA']);
	}

	/**
	 * Gets the names-only of every recognized field
	 * on $table, whether or not that field is indexable
	 *
	 * @param string $table
	 * @return array
	 * @throws \RuntimeException
	 */
	protected function getAllFieldNamesOfTable($table) {
		if (FALSE === isset($GLOBALS['TCA'][$table]['columns'])) {
			throw new \RuntimeException('Table "' . $table . '" is either not defined in TCA or has no columns array', 1409091364);
		}
		return array_keys($GLOBALS['TCA'][$table]['columns']);
	}

	/**
	 * Gets the type-name of a field on a specific table.
	 * The returned type name values of this implementation
	 * are valid TCA type names.
	 *
	 * @param string $table
	 * @param string $field
	 * @return string
	 * @throws RuntimeException
	 */
	protected function getFieldTypeName($table, $field) {
		if (FALSE === isset($GLOBALS['TCA'][$table])) {
			throw new \RuntimeException('Table "' . $table . '" is not defined in TCA', 1409091365);
		}
		if (FALSE === isset($GLOBALS['TCA'][$table]['columns'][$field]['config']['type'])) {
			throw new \RuntimeException(
				'Field "' . $field . '" on table "' . $table . '" is either not defined or has no field type',
				1409091366
			);
		}
		return $GLOBALS['TCA'][$table]['columns'][$field]['config']['type'];
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
	protected function isFieldPotentiallyIndexable($table, $field) {
		$tables = $this->getAllTableNames();
		if (FALSE === in_array($table, $tables)) {
			return FALSE;
		}
		$fields = $this->getAllFieldNamesOfTable($table);
		if (FALSE === in_array($field, $fields)) {
			return FALSE;
		}
		$configuredType = $this->getFieldTypeName($table, $field);
		$indexableTypes = $this->getIndexableFieldTypeNames();
		if (FALSE === in_array($configuredType, $indexableTypes)) {
			return FALSE;
		}
		return TRUE;
	}

}
