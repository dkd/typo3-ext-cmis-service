<?php
namespace Dkd\CmisService\Analysis\Detection;

/**
 * Class AbstractDetector
 */
abstract class AbstractDetector {

	/**
	 * Returns the configuration array which describes
	 * how a specific field is supposed to be edited. We
	 * need the location above the "config" array rather
	 * than the "config" array itself - since the RTE
	 * is enabled by a flag outside this array.
	 *
	 * @param string $table
	 * @param string $field
	 * @return array
	 */
	protected function resolveTableConfigurationForField($table, $field) {
		return $GLOBALS['TCA'][$table]['columns'][$field];
	}

}
