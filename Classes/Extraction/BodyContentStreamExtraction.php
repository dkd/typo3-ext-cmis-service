<?php
namespace Dkd\CmisService\Extraction;

use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Stream\StreamInterface;

/**
 * Extraction: ContentStream from input string
 */
class BodyContentStreamExtraction implements ExtractionInterface {

	/**
	 * Extract a string, creating a resource wrapper with
	 * the string loaded into memory and pointer placed
	 * at beginning of stream.
	 *
	 * Resulting stream suitable for property value of
	 * the "content" property of CMIS documents.
	 *
	 * @param mixed $content
	 * @return StreamInterface
	 */
	public function extract($content) {
		$pointer = fopen('php://temp', 'rw');
		fwrite($pointer, $content);
		rewind($pointer);
		return Stream::factory($pointer);
	}

}
