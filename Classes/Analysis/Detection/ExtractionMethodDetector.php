<?php
namespace Dkd\CmisService\Analysis\Detection;

use Dkd\CmisService\Extraction\ExtractionInterface;

/**
 * Class ExtractionMethodDetector
 */
class ExtractionMethodDetector extends AbstractDetector implements DetectorInterface {

	const METHOD_PASSTHROUGH = 'Passthrough';
	const METHOD_RICHTEXT = 'RichTextEditor';
	const METHOD_MULTIVALUE = 'MultiValue';
	const METHOD_SINGLEVALUE = 'SingleValue';
	const METHOD_SINGLERELATION = 'SingleRelationLabel';
	const METHOD_MULTIRELATION = 'MultipleRelationLabel';
	const METHOD_BOOLEAN = 'Boolean';
	const DEFAULT_METHOD = self::METHOD_PASSTHROUGH;
	const GROUPFIELDTYPE_FILE = 'files';
	const GROUPFIELDTYPE_DB = 'db';
	const FIELDTYPE_TEXT = 'text';
	const FIELDTYPE_CHECKBOX = 'checkbox';
	const FIELDTYPE_SELECT = 'select';
	const FIELDTYPE_GROUP = 'group';
	const FIELDTYPE_INLINE = 'inline';

	/**
	 * Returns an instance of an ExtractionInterface
	 * implementation which is suited for extracting
	 * the text-only representation of the value
	 * stored in records' column $field on $table.
	 *
	 * @param string $table
	 * @param string $field
	 * @return ExtractionInterface
	 */
	public function resolveExtractionForColumn($table, $field) {
		$configuration = $this->resolveTableConfigurationForField($table, $field);
		$type = $this->determineExtractionMethod($configuration);
		return $this->resolveExtractionByTypeNameOrClassName($type);
	}

	/**
	 * Returns an instance of an ExtractionInterface
	 * implementation based on native Extraction type
	 * or a third-party Extraction implementation.
	 *
	 * @param string $typeOrClass
	 * @return ExtractionInterface
	 */
	public function resolveExtractionByTypeNameOrClassName($typeOrClass) {
		$className = $nativeClassName = sprintf('Dkd\\CmisService\\Extraction\\%sExtraction', $typeOrClass);
		if (FALSE === class_exists($nativeClassName)) {
			$className = $typeOrClass;
		}
		if (FALSE === class_exists($className)) {
			throw new \RuntimeException('Invalid Extraction type: "' . $typeOrClass . '". Class not found.', 1413286569);
		}
		if (FALSE === is_a($className, 'Dkd\\CmisService\\Extraction\\ExtractionInterface', TRUE)) {
			throw new \RuntimeException('Invalid Extraction type: "' . $typeOrClass . '". Not an implementation of ' .
				'Dkd\\CmisService\\Extraction\\ExtractionInterface', 1410960261);
		}
		return new $className();
	}

	/**
	 * Takes a column configuration array and determines
	 * which extraction method should be used for values
	 * stored in that column.
	 *
	 * Returned method name is either an FQN of a third-
	 * party class implementing ExtractionInterface, or
	 * a short-name which is then turned into FQN using
	 * rule Dkd\CmisService\Extraction\{$type}Extraction.
	 *
	 * @param array $configurationArray
	 * @return string
	 */
	protected function determineExtractionMethod($configurationArray) {
		if (FALSE === isset($configurationArray['config'])) {
			return self::DEFAULT_METHOD;
		}
		if (TRUE === $this->isFieldCheckbox($configurationArray)) {
			// field is a checkbox and we explicitly extract the value
			// as boolean only.
			return self::METHOD_BOOLEAN;
		}
		if (TRUE === $this->isFieldRichTextEditor($configurationArray)) {
			// narrow match: field is text and explicitly defines RTE,
			// other text-type fields may require other methods.
			return self::METHOD_RICHTEXT;
		}
		if (TRUE === $this->isFieldSimpleSingleValued($configurationArray)) {
			// narrow match, group- and field type fields which do not
			// reference database tables, is not a file field and allows
			// only a single value.
			return self::METHOD_SINGLEVALUE;
		}
		if (TRUE === $this->isFieldSimpleMultiValued($configurationArray)) {
			// narrow match, group- and field type fields which do not
			// reference any database table and is not a file field
			return self::METHOD_MULTIVALUE;
		}
		if (TRUE === $this->isFieldSingleDatabaseRelation($configurationArray)) {
			// medium narrow match: field is a database field of type
			// select or group and points to a table, but allows only
			// a single value
			return self::METHOD_SINGLERELATION;
		}
		if (TRUE === $this->isFieldMultipleDatabaseRelation($configurationArray)) {
			// medium narrow match: field is a database field of type
			// select or group with internal type db and points to a
			// table but does not restrict the maximum number of items.
			return self::METHOD_MULTIRELATION;
		}

		return self::DEFAULT_METHOD;
	}

	/**
	 * @param array $configurationArray
	 * @return boolean
	 */
	protected function isFieldCheckbox(array $configurationArray) {
		return 'checkbox' === $this->getFieldType($configurationArray);
	}

	/**
	 * Returns TRUE if the field is a database relation
	 *
	 * @param array $configurationArray
	 * @return boolean
	 */
	protected function isFieldDatabaseRelation(array $configurationArray) {
		$fieldType = $this->getFieldType($configurationArray);
		$configuration = $configurationArray['config'];
		$isSelectOrGroup = in_array($fieldType, array(self::FIELDTYPE_SELECT, self::FIELDTYPE_GROUP, self::FIELDTYPE_INLINE));
		$hasTableOption = (TRUE === isset($configuration['table']) || TRUE === isset($configuration['foreign_table']));
		$internalType = (TRUE === isset($configuration['internal_type']) ? $configuration['internal_type'] : NULL);
		return ($isSelectOrGroup && $hasTableOption && (NULL === $internalType || self::GROUPFIELDTYPE_DB === $internalType));
	}

	/**
	 * Returns TRUE if the field is a relation to multiple
	 * records on this or another table.
	 *
	 * @param array $configurationArray
	 * @return boolean
	 */
	protected function isFieldMultipleDatabaseRelation(array $configurationArray) {
		$fieldType = $this->getFieldType($configurationArray);
		$configuration = $configurationArray['config'];
		$isDatabaseField = $this->isFieldDatabaseRelation($configurationArray);
		$hasManyToMany = (TRUE === isset($configuration['MM']));
		$hasSizeAboveOne = (TRUE === isset($configuration['size']) ? 1 < (integer) $configuration['size'] : TRUE);
		$hasMaxItemsAboveOne = (TRUE === isset($configuration['maxitems']) ? 1 < (integer) $configuration['maxitems'] : TRUE);
		return ($isDatabaseField && ($hasManyToMany || ($hasSizeAboveOne || $hasMaxItemsAboveOne)));
	}

	/**
	 * Returns TRUE if the field is a relation to a single
	 * record on this or another table.
	 *
	 * @param array $configurationArray
	 * @return boolean
	 */
	protected function isFieldSingleDatabaseRelation(array $configurationArray) {
		$fieldType = $this->getFieldType($configurationArray);
		$configuration = $configurationArray['config'];
		$isDatabaseField = $this->isFieldDatabaseRelation($configurationArray);
		$doesNotHaveManyToMany = (FALSE === isset($configuration['MM']));
		$hasSizeOne = (TRUE === isset($configuration['size']) ? 1 === (integer) $configuration['size'] : FALSE);
		$hasMaxItemsOne = (TRUE === isset($configuration['maxitems']) ? 1 === (integer) $configuration['maxitems'] : FALSE);
		return ($isDatabaseField && $doesNotHaveManyToMany && ($hasSizeOne || $hasMaxItemsOne));
	}

	/**
	 * Returns TRUE if the field contains simple values
	 * and allows only a single value in TCA, for example
	 * if field is a "select" or "group" without a table
	 * and without size or with size=1 or maxitems=1
	 *
	 * @param array $configurationArray
	 * @return boolean
	 */
	protected function isFieldSimpleSingleValued(array $configurationArray) {
		$isCorrectFieldType = $this->isFieldSimpleMultiValued($configurationArray);
		$configuration = $configurationArray['config'];
		$isSelectWithoutSize = 'select' === $this->getFieldType($configurationArray) && FALSE === isset($configuration['size']);
		$maxItems = TRUE === isset($configuration['maxitems']) ? (integer) $configuration['maxitems'] : 0;
		$size = TRUE === isset($configuration['size']) ? (integer) $configuration['size'] : 0;
		return ($isCorrectFieldType && (1 === $maxItems || 1 === $size || $isSelectWithoutSize));
	}

	/**
	 * Returns TRUE if the field contains simple values
	 * but allows multiple values in TCA, for example
	 * if field is a "select" or "group" without a table.
	 *
	 * @param array $configurationArray
	 * @return boolean
	 */
	protected function isFieldSimpleMultiValued(array $configurationArray) {
		$configuration = $configurationArray['config'];
		$fieldType = $this->getFieldType($configurationArray);
		$isSelectOrGroup = in_array($fieldType, array(self::FIELDTYPE_SELECT, self::FIELDTYPE_GROUP));
		$internalType = (TRUE === isset($configuration['internal_type']) ? $configuration['internal_type'] : NULL);
		$lacksTableOption = (FALSE === isset($configuration['table']));
		$isFileOrDbField = in_array($internalType, array(self::GROUPFIELDTYPE_DB, self::GROUPFIELDTYPE_FILE));
		return ($isSelectOrGroup && (FALSE === $isFileOrDbField && TRUE === $lacksTableOption));
	}

	/**
	 * Returns TRUE if the field is a "text" TCA type and
	 * the defaultExtras instruction contains any of the
	 * RTE-specific parameters TYPO3 supports.
	 *
	 * @param array $configurationArray
	 * @return boolean
	 */
	protected function isFieldRichTextEditor(array $configurationArray) {
		$fieldType = $this->getFieldType($configurationArray);
		$hasDefaultExtras = isset($configurationArray['defaultExtras']);
		$isRichTextOnly = ($hasDefaultExtras && FALSE !== strpos($configurationArray['defaultExtras'], 'rte_only'));
		$containsRichTextOptions = ($hasDefaultExtras && FALSE !== strpos($configurationArray['defaultExtras'], 'rte_transform'));
		if (self::FIELDTYPE_TEXT === $fieldType && ($isRichTextOnly || $containsRichTextOptions)) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Reads the type of the field defined in configuration.
	 *
	 * @param array $configurationArray
	 * @return string
	 */
	protected function getFieldType(array $configurationArray) {
		return $configurationArray['config']['type'];
	}

}
