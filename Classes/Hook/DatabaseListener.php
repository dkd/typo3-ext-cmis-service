<?php
namespace Dkd\CmisService\Hook;

use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Database\PostProcessQueryHookInterface;

/**
 * Database Listener
 *
 * Reacts to _every_ possible TYPO3 database API
 * call by hooking in to the DatabaseConnection's
 * methods to post-process all types of queries.
 *
 * The alternative to this Listener is the
 * DataHandler Listener which will only listen for
 * record changes, additions and deletions when
 * done through the TYPO3 backend.
 */
class DatabaseListener extends AbstractListener implements PostProcessQueryHookInterface {

	/**
	 * Post-processor for the SELECTquery method.
	 *
	 * @param string $select_fields Fields to be selected
	 * @param string $from_table Table to select data from
	 * @param string $where_clause Where clause
	 * @param string $groupBy Group by statement
	 * @param string $orderBy Order by statement
	 * @param integer $limit Database return limit
	 * @param DatabaseConnection $parentObject
	 * @return void
	 */
	public function exec_SELECTquery_postProcessAction(
		&$select_fields,
		&$from_table,
		&$where_clause,
		&$groupBy,
		&$orderBy,
		&$limit,
		DatabaseConnection $parentObject
	) {
		// no-operation case; silently pass every SELECT query.
	}

	/**
	 * Post-processor for the exec_INSERTquery method.
	 *
	 * @param string $table Database table name
	 * @param array $fieldsValues Field values as key => value pairs
	 * @param string /array $noQuoteFields List/array of keys NOT to quote
	 * @param DatabaseConnection $parentObject
	 * @return void
	 */
	public function exec_INSERTquery_postProcessAction(
		&$table,
		array &$fieldsValues,
		&$noQuoteFields,
		DatabaseConnection $parentObject
	) {
		$uid = $parentObject->sql_insert_id();
		$this->createAndQueueIndexingTask($table, $uid);
	}

	/**
	 * Post-processor for the exec_INSERTmultipleRows method.
	 *
	 * @param string $table Database table name
	 * @param array $fields Field names
	 * @param array $rows Table rows
	 * @param string /array $noQuoteFields List/array of keys NOT to quote
	 * @param DatabaseConnection $parentObject
	 * @return void
	 */
	public function exec_INSERTmultipleRows_postProcessAction(
		&$table,
		array &$fields,
		array &$rows,
		&$noQuoteFields,
		DatabaseConnection $parentObject
	) {
		// no-operation case; silently pass this particular type
		// of record insertion. Records added this way will be
		// indexed the next time the Queue is rebuilt.
	}

	/**
	 * Post-processor for the exec_UPDATEquery method.
	 *
	 * @param string $table Database table name
	 * @param string $where WHERE clause
	 * @param array $fieldsValues Field values as key => value pairs
	 * @param string /array $noQuoteFields List/array of keys NOT to quote
	 * @param DatabaseConnection $parentObject
	 * @return void
	 */
	public function exec_UPDATEquery_postProcessAction(
		&$table,
		&$where,
		array &$fieldsValues,
		&$noQuoteFields,
		DatabaseConnection $parentObject
	) {
		$uid = (TRUE === isset($fieldsValues['uid']) ? (integer) $fieldsValues['uid'] : NULL);
		if (NULL !== $uid) {
			$this->createAndQueueIndexingTask($table, $uid);
		}
	}

	/**
	 * Post-processor for the exec_DELETEquery method.
	 *
	 * @param string $table Database table name
	 * @param string $where WHERE clause
	 * @param DatabaseConnection $parentObject
	 * @return void
	 */
	public function exec_DELETEquery_postProcessAction(&$table, &$where, DatabaseConnection $parentObject) {
		// no-operation case; silently pass this deletion since any
		// number of rows can be deleted. Indexing happens on next
		// Queue rebuild.
	}

	/**
	 * Post-processor for the exec_TRUNCATEquery method.
	 *
	 * @param string $table Database table name
	 * @param DatabaseConnection $parentObject
	 * @return void
	 */
	public function exec_TRUNCATEquery_postProcessAction(&$table, DatabaseConnection $parentObject) {
		// dispatches a table-wide "index eviction" task which evicts
		// every indexed document associated with this table.
		$this->createAndQueueEvictionTask($table);
	}

}
