<?php
namespace Dkd\CmisService\Analysis\Detection;
use Dkd\CmisService\Factory\ObjectFactory;

/**
 * Class RelationData
 */
class RelationData {

	/**
	 * @var string
	 */
	protected $sourceTable;

	/**
	 * @var string
	 */
	protected $sourceField;

	/**
	 * @var integer
	 */
	protected $sourceUid;

	/**
	 * @var string
	 */
	protected $targetTable;

	/**
	 * @var array
	 */
	protected $targetFields = array();

	/**
	 * @var array
	 */
	protected $targetUids = array();

	/**
	 * @return string
	 */
	public function getSourceTable() {
		return $this->sourceTable;
	}

	/**
	 * @param string $sourceTable
	 * @return void
	 */
	public function setSourceTable($sourceTable) {
		$this->sourceTable = $sourceTable;
	}

	/**
	 * @return string
	 */
	public function getSourceField() {
		return $this->sourceField;
	}

	/**
	 * @param string $sourceField
	 * @return void
	 */
	public function setSourceField($sourceField) {
		$this->sourceField = $sourceField;
	}

	/**
	 * @return integer
	 */
	public function getSourceUid() {
		return $this->sourceUid;
	}

	/**
	 * @param integer $sourceUid
	 * @return void
	 */
	public function setSourceUid($sourceUid) {
		$this->sourceUid = $sourceUid;
	}

	/**
	 * @return string
	 */
	public function getTargetTable() {
		return $this->targetTable;
	}

	/**
	 * @param string $targetTable
	 * @return void
	 */
	public function setTargetTable($targetTable) {
		$this->targetTable = $targetTable;
	}

	/**
	 * @return array
	 */
	public function getTargetFields() {
		return $this->targetFields;
	}

	/**
	 * @param array $targetField
	 * @return void
	 */
	public function setTargetFields(array $targetFields = NULL) {
		$this->targetFields = (array) $targetFields;
	}

	/**
	 * @return array
	 */
	public function getTargetUids() {
		return $this->targetUids;
	}

	/**
	 * @param array $targetUids
	 * @return void
	 */
	public function setTargetUids(array $targetUids) {
		$this->targetUids = $targetUids;
	}

	/**
	 * @param string $fieldName
	 */
	public function getRelationObjectType($fieldName) {
		$relation = $this->getObjectFactory()
			->getConfiguration()
			->getTableConfiguration()
			->getRelationType($this->sourceTable, $fieldName);
		return $relation ?: 'R:cm:references';
	}

	/**
	 * @return ObjectFactory
	 */
	protected function getObjectFactory() {
		return new ObjectFactory();
	}
}
