<?php
namespace Dkd\CmisService\Extraction;

/**
 * Extraction: Boolean casting of value
 */
class BooleanExtraction implements ExtractionInterface {

	/**
	 * Extract any boolean-castable to boolean
	 *
	 * @param mixed $content
	 * @return string
	 */
	public function extract($content) {
		return (boolean) $content;
	}

}
