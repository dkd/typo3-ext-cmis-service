<?php
namespace Dkd\CmisService\Extraction;

/**
 * Extraction: Simple treatment of single values
 */
class SingleValueExtraction implements ExtractionInterface {

	/**
	 * Return the trimmed $content as a single value.
	 *
	 * @param mixed $content
	 * @return array
	 */
	public function extract($content) {
		return trim($content);
	}

}
