<?php
namespace Dkd\CmisService\Extraction;

/**
 * Extraction: HTML to plain text
 */
class HtmlExtraction extends AbstractExtraction {

	/**
	 * Extract HTML to plain text
	 *
	 * @param mixed $content
	 * @return string
	 */
	public function extract($content) {
		return strip_tags($content);
	}

}
