<?php
namespace Dkd\CmisService\Analysis;

use Dkd\CmisService\Analysis\Detection\IndexableColumnDetector;
use Dkd\CmisService\Analysis\Detection\RelationData;
use Dkd\CmisService\Factory\ObjectFactory;
use Dkd\PhpCmis\Exception\CmisRuntimeException;
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
	 * Returns the UID of the be_user record that owns
	 * this record - returns NULL if no user owns the
	 * record or the record cannot be owned by users.
	 *
	 * @return integer|NULL
	 */
	public function getAuthorUidFromRecord() {
		if (TRUE === isset($this->record['cruser_id']) && $this->record['cruser_id'] > 0) {
			return (integer) $this->record['cruser_id'];
		}
		return NULL;
	}

	/**
	 * @param string $fieldName
	 * @return RelationData
	 */
	public function getRelationDataForColumn($fieldName) {
		if (empty($fieldName)) {
			throw new CmisRuntimeException(
				sprintf(
					'Field name passed to RecordAnalyzer::getRelationDataForColumn was empty - your TCA may be invalid or ' .
					'incomplete, lacking a target field for a relation column. Table name: %s',
					$this->getTable()
				)
			);
		}
		$columnAnalyzer = $this->getColumnAnalyzer($fieldName);
		$configuration = $columnAnalyzer->getConfigurationArray();
		if ($columnAnalyzer->getFieldType() === ColumnAnalyzer::FIELDTYPE_GROUP) {
			$targetTable = $configuration['config']['allowed'];
		} else {
			$targetTable = $configuration['config']['foreign_table'];
		}
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
					'mm.uid_local = ' . $sourceUid . ' AND target.uid = mm.uid_foreign'
				);
			} elseif (FALSE === empty($this->record[$fieldName])) {
				if (FALSE === strpos($targetTable, ',')) {
					$relatedRecords = $this->getDatabaseConnection()->exec_SELECTgetRows(
						'*',
						$targetTable,
						'uid IN (' . $this->record[$fieldName] . ')'
					);
				} else {
					$relatedRecords = array();
					foreach (explode(',', $this->record[$fieldName]) as $identity) {
						$relatedRecords[] = array('uid' => $identity);
					}
				}
			} else {
				$relatedRecords = array();
			}
		} elseif ($columnAnalyzer->isFieldSimpleMultiValued()) {
			if (empty($targetTable)) {
				throw new CmisRuntimeException(
					sprintf(
						'Table field "%s:%s" is a relation, but the foreign_table parameter is empty',
						$targetTable,
						$fieldName
					)
				);
			}
			if (!empty($configuration['config']['foreign_table_field'])) {
				$targetField = (string) $configuration['config']['foreign_table_field'];
				$relatedRecords = $this->getDatabaseConnection()->exec_SELECTgetRows(
					'*',
					$targetTable,
					$targetField . ' = ' . $sourceUid
				);
				$targetFields = array($targetField => $sourceUid);
			} else {
				if (FALSE === strpos($targetTable, ',')) {
					$relatedRecords = $this->getDatabaseConnection()->exec_SELECTgetRows(
						'*',
						$targetTable,
						'uid IN (' . $this->record[$fieldName] . ')'
					);
				} else {
					$relatedRecords = array();
					foreach (explode(',', $this->record[$fieldName]) as $identity) {
						$relatedRecords[] = array('uid' => $identity);
					}
				}
			}
		} elseif ($columnAnalyzer->isFieldSingleDatabaseRelation()) {
			$relatedRecords = array(
				$this->loadRecordFromTable($targetTable, $this->record[$fieldName])
			);
		}

		foreach ($relatedRecords as $relatedRecord) {
			if (TRUE === empty($relatedRecord['uid'])) {
				continue;
			} elseif (is_numeric($relatedRecord['uid'])) {
				$relatedUid = (integer) $relatedRecord['uid'];
			} else {
				$relatedUid = $relatedRecord['uid'];
			}
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

		$relatedUids = array_diff($relatedUids, array(0));

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
	 * @return \DateTime
	 */
	public function getLastModifiedDateTime() {
		\DateTime::createFromFormat('U', $this->record['tstamp']);
	}

	/**
	 * @return \DateTime
	 */
	public function getCreationDateTime() {
		\DateTime::createFromFormat('U', $this->record['crtime']);
	}

	/**
	 * @param string $fieldName
	 * @return ColumnAnalyzer
	 */
	protected function getColumnAnalyzer($fieldName) {
		return new ColumnAnalyzer($GLOBALS['TCA'][$this->table]['columns'][$fieldName], $this->table, $fieldName);
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
