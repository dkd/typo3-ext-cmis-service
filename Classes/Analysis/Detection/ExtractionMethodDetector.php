<?php
namespace Dkd\CmisService\Analysis\Detection;

use Dkd\CmisService\Analysis\ColumnAnalyzer;
use Dkd\CmisService\Analysis\TableConfigurationAnalyzer;
use Dkd\CmisService\Extraction\BodyContentStreamExtraction;
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
	const METHOD_LEGACYFILE = 'LegacyFileReference';
	const METHOD_BOOLEAN = 'Boolean';
	const DEFAULT_METHOD = self::METHOD_PASSTHROUGH;

	/**
	 * Returns a content stream extractor which makes
	 * stream wrappers from input strings.
	 *
	 * @return BodyContentStreamExtraction
	 */
	public function resolveBodyContentStreamExtractor() {
		return new BodyContentStreamExtraction();
	}

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
		$tableConfigurationAnalyzer = new TableConfigurationAnalyzer();
		$columnConfigurationAnalyzer = $tableConfigurationAnalyzer->getColumnAnalyzerForField($table, $field);
		$type = $this->determineExtractionMethod($columnConfigurationAnalyzer);
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
	 * Takes a column analyzer instance and determines
	 * which extraction method should be used for values
	 * stored in that column.
	 *
	 * Returned method name is either an FQN of a third-
	 * party class implementing ExtractionInterface, or
	 * a short-name which is then turned into FQN using
	 * rule Dkd\CmisService\Extraction\{$type}Extraction.
	 *
	 * @param ColumnAnalyzer $columnAnalyzer
	 * @return string
	 */
	protected function determineExtractionMethod(ColumnAnalyzer $columnAnalyzer) {
		if (NULL === $columnAnalyzer->getFieldType()) {
			return self::DEFAULT_METHOD;
		}
		if (TRUE === $columnAnalyzer->isFieldLegacyFileReference()) {
			// legacy file reference; we need an extractor that works
			// differently from the database relation extractor that
			// handles non-legacy FAL file references.
			return self::METHOD_LEGACYFILE;
		} elseif (TRUE === $columnAnalyzer->isFieldType(ColumnAnalyzer::FIELDTYPE_CHECKBOX)) {
			// field is a checkbox and we explicitly extract the value
			// as boolean only.
			return self::METHOD_BOOLEAN;
		}
		if (TRUE === $columnAnalyzer->isFieldRichTextEditor()) {
			// narrow match: field is text and explicitly defines RTE,
			// other text-type fields may require other methods.
			return self::METHOD_RICHTEXT;
		}
		if (TRUE === $columnAnalyzer->isFieldSimpleSingleValued()) {
			// narrow match, group- and field type fields which do not
			// reference database tables, is not a file field and allows
			// only a single value.
			return self::METHOD_SINGLEVALUE;
		}
		if (TRUE === $columnAnalyzer->isFieldSimpleMultiValued()) {
			// narrow match, group- and field type fields which do not
			// reference any database table and is not a file field
			return self::METHOD_MULTIVALUE;
		}
		if (TRUE === $columnAnalyzer->isFieldSingleDatabaseRelation()) {
			// medium narrow match: field is a database field of type
			// select or group and points to a table, but allows only
			// a single value
			return self::METHOD_SINGLERELATION;
		}
		if (TRUE === $columnAnalyzer->isFieldMultipleDatabaseRelation()) {
			// medium narrow match: field is a database field of type
			// select or group with internal type db and points to a
			// table but does not restrict the maximum number of items.
			return self::METHOD_MULTIRELATION;
		}

		return self::DEFAULT_METHOD;
	}

}
