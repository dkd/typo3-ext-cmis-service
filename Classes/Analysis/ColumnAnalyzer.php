<?php
namespace Dkd\CmisService\Analysis;

/**
 * Class ColumnAnalyzer
 *
 * Analyzes TCA columns, detecting various aspects about
 * columns like the type of field, whether a field contains
 * a relation and such.
 */
class ColumnAnalyzer {

	const GROUPFIELDTYPE_FILE = 'files';
	const GROUPFIELDTYPE_DB = 'db';
	const FIELDTYPE_TEXT = 'text';
	const FIELDTYPE_CHECKBOX = 'checkbox';
	const FIELDTYPE_SELECT = 'select';
	const FIELDTYPE_GROUP = 'group';
	const FIELDTYPE_INLINE = 'inline';

	/**
	 * @var array
	 */
	protected $configurationArray;

	/**
	 * @param array $configurationArray
	 */
	public function __construct(array $configurationArray) {
		$this->setConfigurationArray($configurationArray);
	}

	/**
	 * @param array $configurationArray
	 * @return void
	 */
	public function setConfigurationArray(array $configurationArray) {
		// @TODO: validate required configuration
		$this->configurationArray = $configurationArray;
	}

	/**
	 * @return array
	 */
	public function getConfigurationArray() {
		return $this->configurationArray;
	}

	/**
	 * Returns TRUE if the field is a database relation
	 *
	 * @return boolean
	 */
	public function isFieldDatabaseRelation() {
		$configuration = $this->configurationArray['config'];
		$isSelectOrGroup = $this->isFieldMultiValue($configuration);
		if ($this->getFieldType() === self::FIELDTYPE_GROUP) {
			$internalType = $this->getInternalType($configuration);
			$hasTableOption = (self::GROUPFIELDTYPE_DB === $internalType);
		} else {
			$hasTableOption = $this->hasTableOption($configuration);
		}
		return ($isSelectOrGroup && $hasTableOption);
	}

	/**
	 * Returns TRUE if the field is a relation to multiple
	 * records on this or another table.
	 *
	 * @return boolean
	 */
	public function isFieldMultipleDatabaseRelation() {
		$configuration = $this->configurationArray['config'];
		$isDatabaseRelationField = $this->isFieldDatabaseRelation();
		$hasManyToMany = $this->isFieldManyToManyRelation($configuration);
		$allowsSingleValue = $this->isFieldLimitedToSingleValue($configuration);
		return ($isDatabaseRelationField && ($hasManyToMany || FALSE === $allowsSingleValue));
	}

	/**
	 * Returns TRUE if the field is a relation to a single
	 * record on this or another table.
	 *
	 * @return boolean
	 */
	public function isFieldSingleDatabaseRelation() {
		$configuration = $this->configurationArray['config'];
		$isDatabaseRelationField = $this->isFieldDatabaseRelation();
		$doesNotHaveManyToMany = FALSE === $this->isFieldManyToManyRelation($configuration);
		$allowsSingleValue = $this->isFieldLimitedToSingleValue($configuration);
		return ($isDatabaseRelationField && $doesNotHaveManyToMany && $allowsSingleValue);
	}

	/**
	 * Returns TRUE if the field contains simple values
	 * and allows only a single value in TCA, for example
	 * if field is a "select" or "group" without a table
	 * and without size or with size=1 or maxitems=1
	 *
	 * @return boolean
	 */
	public function isFieldSimpleSingleValued() {
		$configuration = $this->configurationArray['config'];
		$isCorrectFieldType = $this->isFieldSimpleMultiValued();
		$isSelectWithoutSize = $this->isFieldType(self::FIELDTYPE_SELECT) && FALSE === isset($configuration['size']);
		$allowsSingleValue = $this->isFieldLimitedToSingleValue($configuration);
		return ($isCorrectFieldType && ($allowsSingleValue || $isSelectWithoutSize));
	}

	/**
	 * Returns TRUE if the field contains simple values
	 * but allows multiple values in TCA, for example
	 * if field is a "select" or "group" without a table.
	 *
	 * @return boolean
	 */
	public function isFieldSimpleMultiValued() {
		$configuration = $this->configurationArray['config'];
		$isSelectOrGroup = $this->isFieldMultiValue($configuration);
		$internalType = $this->getInternalType($configuration);
		$lacksTableOption = FALSE === $this->hasTableOption($configuration);
		$isFileOrDbField = in_array($internalType, array(self::GROUPFIELDTYPE_DB, self::GROUPFIELDTYPE_FILE));
		return (($isSelectOrGroup && $lacksTableOption) || TRUE === $isFileOrDbField);
	}

	/**
	 * Returns TRUE if the field is a "text" TCA type and
	 * the defaultExtras instruction contains any of the
	 * RTE-specific parameters TYPO3 supports.
	 *
	 * @return boolean
	 */
	public function isFieldRichTextEditor() {
		$configurationArray = $this->configurationArray;
		$hasDefaultExtras = isset($configurationArray['defaultExtras']);
		$isRichTextOnly = ($hasDefaultExtras && FALSE !== strpos($configurationArray['defaultExtras'], 'rte_only'));
		$containsRichTextOptions = ($hasDefaultExtras && FALSE !== strpos($configurationArray['defaultExtras'], 'rte_transform'));
		if ($this->isFieldType(self::FIELDTYPE_TEXT) && ($isRichTextOnly || $containsRichTextOptions)) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Returns TRUE if the field is configured to contain one
	 * or more file references as a CSV value. Will silently
	 * ignore misconfigured/mismatched fields, e.g. will return
	 * FALSE for anything other than a legacy file field.
	 *
	 * @return boolean
	 */
	public function isFieldLegacyFileReference() {
		$configurationArray = $this->configurationArray;
		$fieldConfiguration = $configurationArray['config'];
		$fieldType = $this->getFieldType();
		$internalType = !empty($fieldConfiguration['internal_type']) ? $fieldConfiguration['internal_type'] : NULL;
		return (self::FIELDTYPE_GROUP === $fieldType && self::GROUPFIELDTYPE_FILE === $internalType);
	}

	/**
	 * Reads the type of the field defined in configuration.
	 *
	 * @return string
	 */
	public function getFieldType() {
		return $this->configurationArray['config']['type'];
	}

	/**
	 * @param string $type
	 * @return boolean
	 */
	public function isFieldType($type) {
		return $type === $this->getFieldType();
	}

	/**
	 * @param array $configuration
	 * @return string|NULL
	 */
	protected function getInternalType(array $configuration) {
		return (TRUE === isset($configuration['internal_type']) ? $configuration['internal_type'] : NULL);
	}

	/**
	 * @param array $configuration
	 * @return boolean
	 */
	protected function hasTableOption(array $configuration) {
		return (
			TRUE === isset($configuration['table'])
			|| TRUE === isset($configuration['foreign_table'])
			|| TRUE === isset($configuration['MM'])
		);
	}

	/**
	 * @param array $configuration
	 * @return boolean
	 */
	protected function isFieldLimitedToSingleValue(array $configuration) {
		$maxItems = TRUE === isset($configuration['maxitems']) ? (integer) $configuration['maxitems'] : 0;
		$size = TRUE === isset($configuration['size']) ? (integer) $configuration['size'] : 1;
		return (1 === $maxItems || 1 === $size);
	}

	/**
	 * @param array $configuration
	 * @return boolean
	 */
	protected function isFieldMultiValue(array $configuration) {
		$fieldType = $this->getFieldType();
		return in_array($fieldType, array(self::FIELDTYPE_SELECT, self::FIELDTYPE_GROUP, self::FIELDTYPE_INLINE));
	}

	/**
	 * @param array $configuration
	 * @return boolean
	 */
	protected function isFieldManyToManyRelation(array $configuration) {
		return isset($configuration['MM']) && FALSE === empty($configuration['MM']);
	}

}
