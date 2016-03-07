<?php
namespace Dkd\CmisService\Extraction;

use Dkd\CmisService\Factory\ObjectFactory;

/**
 * Base class for extractions
 */
abstract class AbstractExtraction implements ExtractionInterface {

	/**
	 * Perform extraction, returning a simple string.
	 *
	 * @param mixed $content
	 * @return mixed
	 */
	public function extract($content) {
		return $content;
	}

	/**
	 * Extracts CMIS Relationships from value if value
	 * defines any associations. Returns empty array
	 * if no associations are detected or configured.
	 * Returns an array of arrays of properties for
	 * each required Relationship to be created.
	 *
	 * @param mixed $content
	 * @param string $table
	 * @param string $field
	 * @return array[]
	 */
	public function extractAssociations($content, $table, $field) {
		return array();
	}

	/**
	 * @return ObjectFactory
	 */
	protected function getObjectFactory() {
		return new ObjectFactory();
	}

}
