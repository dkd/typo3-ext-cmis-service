<?php
namespace Dkd\CmisService\Extraction;

/**
 * Extraction: Simple treatment of multi-values (CSV)
 */
class MultiValueExtraction extends AbstractExtraction {

	/**
	 * Return the exploded and trimmed array from CSV $content.
	 *
	 * @param mixed $content
	 * @return array
	 */
	public function extract($content) {
		$values = explode(',', (string) $content);
		$values = array_map('trim', $values);
		return $values;
	}

}
