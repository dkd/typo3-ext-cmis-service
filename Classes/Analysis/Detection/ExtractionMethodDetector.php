<?php
namespace Dkd\CmisService\Analysis\Detection;

use Dkd\CmisService\Extraction\ExtractionInterface;

/**
 * Class ExtractionMethodDetector
 */
class ExtractionMethodDetector extends AbstractDetector implements DetectorInterface {

	const DEFAULT_METHOD = 'Passthrough';

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
		$type = $this->determineExtractionMethod($configuration);
		$className = $nativeClassName = sprintf('Dkd\\CmisService\\Extraction\\%sExtraction', $type);
		if (FALSE === class_exists($nativeClassName)) {
			$className = $type;
		}
		$isValidImplementation = is_a($className, 'Dkd\\CmisService\\Extraction\\ExtractionInterface', TRUE);
		if (FALSE === class_exists($className) || FALSE === $isValidImplementation) {
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
		return self::DEFAULT_METHOD;
	}

}