<?php
namespace Dkd\CmisService\Extraction;

/**
 * Extraction: Legacy file reference; used for
 * files which are not identified by their FAL
 * identifier. Creates "file" CMIS objects as
 * needed or resolves existing files, then returns
 * the UUIDs of all related files in an array.
 */
class LegacyFileReferenceExtraction implements ExtractionInterface {

	/**
	 * Extracts CSV referneces to files, turning each
	 * reference into a UUID and returning an array
	 * of all UUIDs.
	 *
	 * @param mixed $content
	 * @return string
	 */
	public function extract($content) {
		// @TODO: Extract references, detect whether references are
		// in either identifier:uid format or raw file name.
		// Between those two formats, any legacy file reference field
		// value is supported.
		// @TODO: When detected format is identifier:uid, delegate to
		// FAL reference extraction class as array of identifiers.
		return array();
	}
}
