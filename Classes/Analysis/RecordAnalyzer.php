<?php
namespace Dkd\CmisService\Analysis;

use Dkd\CmisService\Analysis\Detection\IndexableColumnDetector;

/**
 * Record Analyzer
 *
 * Detects indexing ability and parameters for a
 * record. Detection happens based on system's
 * built-in rules for which tables and records are
 * allowed to be indexed, combined with configuration
 * options from the `tables` settings scope.
 */
class RecordAnalyzer {

	/**
	 * @var string
	 */
	protected $table;

	/**
	 * @var array
	 */
	protected $record;

	/**
	 * Constructor
	 *
	 * @param string $table
	 * @param array $record
	 */
	public function __construct($table, array $record) {
		$this->table = (string) $table;
		$this->record = $record;
	}

	/**
	 * @return string
	 */
	public function getTable() {
		return $this->table;
	}

	/**
	 * @return array
	 */
	public function getRecord() {
		return $this->record;
	}

	/**
	 * Gets the names of all columns in record which
	 * can be indexed according to rules.
	 *
	 * @return array
	 */
	public function getIndexableColumnNames() {
		$columnDetector = new IndexableColumnDetector();
		return $columnDetector->getIndexableColumnNamesFromTable($this->table);
	}

	/**
	 * Gets an always-filled title for the record being
	 * analysed. If no title can be resolved based on the
	 * record's columns and TCA configuration for label,
	 * a default $table:$uid format is returned.
	 *
	 * @return string
	 */
	public function getTitleForRecord() {
		$tableConfigurationAnalyzer = new TableConfigurationAnalyzer();
		$labelFields = $tableConfigurationAnalyzer->getLabelFieldListFromTable($this->table);
		foreach ($labelFields as $labelFieldName) {
			if (FALSE === empty($this->record[$labelFieldName])) {
				return $this->record[$labelFieldName];
			}
		}
		return $this->table . ':' . $this->record['uid'];
	}

}
