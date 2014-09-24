<?php
namespace Dkd\CmisService\Extraction;

/**
 * Extraction: TYPO3 Rich Text Editor formatting
 */
class RichTextEditorExtraction implements ExtractionInterface {

	/**
	 * Convert TYPO3 RTE content to HTML then extract plain text.
	 *
	 * @param mixed $content
	 * @return string
	 */
	public function extract($content) {
		// @TODO: implement
		return $content;
	}

}
