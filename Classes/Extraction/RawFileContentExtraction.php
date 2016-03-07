<?php
namespace Dkd\CmisService\Extraction;

/**
 * Extraction: Completely raw content of a file resource.
 */
class RawFileContentExtraction extends AbstractExtraction {

	/**
	 * Reads file from path in $content, returning the
	 * file's content as a string.
	 *
	 * @param mixed $content
	 * @return string
	 */
	public function extract($content) {
		// @TODO: implement
		return file_get_contents($content);
	}

}
