<?php
namespace Dkd\CmisService\Extraction;

/**
 * Extraction: Single (1:1) relation entity label extraction
 */
class SingleRelationLabelExtraction implements ExtractionInterface {

	/**
	 * Reads the value of the column identified as "label"
	 * in TCA, turning a relation $table:$uid into a string.
	 *
	 * @param mixed $content
	 * @return string
	 */
	public function extract($content) {
		// @TODO: implement
		return $content;
	}

}
