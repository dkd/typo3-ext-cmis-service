<?php
namespace Dkd\CmisService\Extraction;

/**
 * Extraction: Multiple (1:n, m:n) relation entity label extraction
 */
class MultipleRelationLabelExtraction extends AbstractExtraction {

	/**
	 * Reads the value of the column identified as "label"
	 * in TCA, turning a set of relations formatted as
	 * $table:$uid,$table:$uid2,$table:$uid3 into a string
	 * value "Name1, Name2, Name3".
	 *
	 * This particular format is chosen in order for legacy
	 * support of TYPO3 CMS "group" type fields to work
	 * transparently with many-table relations as well.
	 *
	 * @param mixed $content
	 * @return string
	 */
	public function extract($content) {
		// @TODO: implement
		return $content;
	}

}
