<?php
namespace Dkd\CmisService\Extraction;

/**
 * Extraction: Pass-through, no extraction.
 */
class PassthroughExtraction implements ExtractionInterface {

	/**
	 * Passes through $content, returning the exact same value.
	 *
	 * @param mixed $content
	 * @return string
	 */
	public function extract($content) {
		return $content;
	}

}
