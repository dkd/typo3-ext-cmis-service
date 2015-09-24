<?php
namespace Dkd\CmisService\Analysis;

use Dkd\CmisService\Analysis\Detection\IndexableColumnDetector;
use Dkd\CmisService\Factory\ObjectFactory;

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
	 * @var array
	 */
	protected static $excludedTableNames = array(
		'sys_log',
		'sys_history',
		'sys_file',
		'sys_file_metadata',
		'sys_file_storage',
		'tx_extensionmanager_domain_model_extension',
		'tx_extensionmanager_domain_model_repository'
	);

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
		$columnDetector = new IndexableColumnDetector();
		$tables = $this->getAllTableNames();
		$indexable = array();
		foreach ($tables as $table) {
			if (TRUE === in_array($table, self::$excludedTableNames)) {
				continue;
			} elseif (FALSE === $this->getObjectFactory()->getConfiguration()->getTableConfiguration()->isTableEnabled($table)) {
				continue;
			}
			$fields = $this->getAllFieldNamesOfTable($table);
			foreach ($fields as $field) {
				if (TRUE === $columnDetector->isFieldPotentiallyIndexable($table, $field)) {
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
			'input', 'checkbox', 'text', 'file', 'select', 'inline', 'group'
		);
	}

	/**
	 * Gets the names-only of every recognized table
	 * which are present in the system.
	 *
	 * @return array
	 */
	public function getAllTableNames() {
		return array_keys($GLOBALS['TCA']);
	}

	/**
	 * Gets an array of column names which may contain
	 * title values for records from $table.
	 *
	 * @param string $table
	 * @return array
	 */
	public function getLabelFieldListFromTable($table) {
		$control = $GLOBALS['TCA'][$table]['ctrl'];
		return explode(',', trim($control['label'] . ',' . $control['label_alt'], ','));
	}

	/**
	 * Gets the names-only of every recognized field
	 * on $table, whether or not that field is indexable
	 *
	 * @param string $table
	 * @return array
	 * @throws \RuntimeException
	 */
	public function getAllFieldNamesOfTable($table) {
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
	public function getFieldTypeName($table, $field) {
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
	 * @param string $table
	 * @param string $field
	 * @return ColumnAnalyzer
	 */
	public function getColumnAnalyzerForField($table, $field) {
		$configuration = (array) $this->getConfigurationForField($table, $field);
		return new ColumnAnalyzer($configuration);
	}

	/**
	 * Returns the configuration array which describes
	 * how a specific field is supposed to be edited. We
	 * need the location above the "config" array rather
	 * than the "config" array itself - since the RTE
	 * is enabled by a flag outside this array.
	 *
	 * Returns NULL if the field is unknown or has no
	 * configuration which indicates a setup problem, e.g.
	 * some TCA relation pointing to a table or field that's
	 * unknown, misspelled, missing, incorrect case, etc.
	 *
	 * It is up to the consumer to react to a NULL value
	 * and either ignore or dispatch errors accordingly.
	 *
	 * @param string $table
	 * @param string $field
	 * @return array|NULL
	 */
	public function getConfigurationForField($table, $field) {
		if (isset($GLOBALS['TCA'][$table]['columns'][$field]) && is_array($GLOBALS['TCA'][$table]['columns'][$field])) {
			return $GLOBALS['TCA'][$table]['columns'][$field];
		}
		return NULL;
	}

	/**
	 * Returns the configured "uploadFolder" from the field
	 * TCA, or NULL if no "uploadFolder" is defined.
	 *
	 * @param string $table
	 * @param string $field
	 * @return array
	 */
	public function getUploadFolderForField($table, $field) {
		$configuration = $this->getConfigurationForField($table, $field);
		return TRUE === isset($configuration['uploadfolder']) ? $configuration['uploadfolder'] : NULL;
	}

	/**
	 * @return ObjectFactory
	 */
	protected function getObjectFactory() {
		return new ObjectFactory();
	}

}

