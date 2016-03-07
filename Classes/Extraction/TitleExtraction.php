<?php
namespace Dkd\CmisService\Extraction;

/**
 * Extraction: Title field defined in TCA
 */
class TitleExtraction extends AbstractExtraction {

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
