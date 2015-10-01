<?php
namespace Dkd\CmisService\Analysis;

use Dkd\CmisService\Analysis\Detection\IndexableColumnDetector;
use Dkd\CmisService\Analysis\Detection\RelationData;
use Dkd\CmisService\Factory\ObjectFactory;
use TYPO3\CMS\Core\Database\DatabaseConnection;

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
	 * @param string $fieldName
	 * @return RelationData
	 */
	public function getRelationDataForColumn($fieldName) {
		$columnAnalyzer = $this->getColumnAnalyzer($fieldName);
		$configuration = $columnAnalyzer->getConfigurationArray();
		$targetTable = $configuration['config']['foreign_table'];
		$sourceUid = (integer) $this->record['uid'];
		$relatedUids = array();
		$targetFields = array();

		if ($columnAnalyzer->isFieldMultipleDatabaseRelation()) {
			$bindingTable = $configuration['config']['MM'];
			if (FALSE === empty($bindingTable)) {
				$targetFields = $configuration['config']['MM_match_fields'];
				$relatedRecords = $this->getDatabaseConnection()->exec_SELECTgetRows(
					'target.*',
					$bindingTable . ' mm, ' . $targetTable . ' target',
					'mm.uid_foreign = ' . $sourceUid . ' AND target.uid = mm.uid_local'
				);
			} elseif (FALSE === empty($this->record[$fieldName])) {
				$relatedRecords = $this->getDatabaseConnection()->exec_SELECTgetRows(
					'*',
					$targetTable,
					'uid IN (' . $this->record[$fieldName] . ')'
				);
			} else {
				$relatedRecords = array();
			}
		} elseif ($columnAnalyzer->isFieldSimpleMultiValued()) {
			$relatedRecords = $this->getDatabaseConnection()->exec_SELECTgetRows(
				'*',
				$configuration['config']['foreign_table'],
				$configuration['config']['foreign_table_field'] . ' = ' . (string) $sourceUid
			);
			$targetFields = array($configuration['config']['foreign_table_field'] => $sourceUid);
		} elseif ($columnAnalyzer->isFieldSingleDatabaseRelation()) {
			$relatedRecords = array(
				$this->loadRecordFromTable($targetTable, $this->record[$fieldName])
			);
		}

		foreach ($relatedRecords as $relatedRecord) {
			$relatedUid = (integer) $relatedRecord['uid'];
			if (NULL === $relatedRecord) {
				$this->getObjectFactory()->getLogger()->info(
					sprintf(
						'Detected dead reference from %s%d to %s:%d; skipped',
						$this->table,
						$sourceUid,
						$targetTable,
						$relatedUid
					)
				);
				continue;
			}
			$relatedUids[] = $relatedUid;
		}

		$relation = new RelationData();
		$relation->setTargetTable($targetTable);
		$relation->setTargetFields($targetFields);
		$relation->setTargetUids($relatedUids);
		$relation->setSourceField($fieldName);
		$relation->setSourceTable($this->table);
		$relation->setSourceUid($sourceUid);

		return $relation;
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

	/**
	 * @param string $fieldName
	 * @return ColumnAnalyzer
	 */
	protected function getColumnAnalyzer($fieldName) {
		return new ColumnAnalyzer($GLOBALS['TCA'][$this->table]['columns'][$fieldName]);
	}

	/**
	 * @param string $table
	 * @param integer $uid
	 * @return array|NULL
	 */
	protected function loadRecordFromTable($table, $uid) {
		return $this->getDatabaseConnection()->exec_SELECTgetSingleRow('*', $table, "uid = '" . $uid . "'") OR NULL;
	}

	/**
	 * @return DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * @return ObjectFactory
	 */
	protected function getObjectFactory() {
		return new ObjectFactory();
	}

}
