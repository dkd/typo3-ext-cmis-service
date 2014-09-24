<?php
namespace Dkd\CmisService\Extraction;

/**
 * Extraction: Command-line execution to extract text content
 */
class CommandBasedExtraction implements ExtractionInterface {

	/**
	 * Attempts to execute the command specified in $content.
	 * If the command requires input, it must be specified
	 * as part of the command.
	 *
	 * Ideal for executing third-party extractions of file
	 * based content like "pdf2txt".
	 *
	 * @param mixed $content
	 * @return string
	 */
	public function extract($content) {
		// @TODO: implement
		return NULL;
	}

}
