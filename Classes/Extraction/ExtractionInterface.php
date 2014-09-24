<?php
namespace Dkd\CmisService\Extraction;

/**
 * Extraction Interface
 *
 * Implemented by classes which are capable of extracting
 * plain text representations of richly formatted or
 * marked-up text content or proprietary file types.
 */
interface ExtractionInterface {

	/**
	 * Perform extraction, returning a simple string.
	 *
	 * @param mixed $content
	 * @return string
	 */
	public function extract($content);

}
