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
	 * @param string $record
	 */
	public function __construct($table, $record) {
		$this->table = $table;
		$this->record = $record;
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

}
