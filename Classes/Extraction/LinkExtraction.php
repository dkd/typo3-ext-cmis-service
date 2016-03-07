<?php
namespace Dkd\CmisService\Extraction;

use Dkd\PhpCmis\Exception\CmisObjectNotFoundException;
use Dkd\PhpCmis\PropertyIds;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Extraction: Link field
 */
class LinkExtraction extends AbstractExtraction implements ExtractionInterface {

	/**
	 * Return the trimmed $content as a single value.
	 *
	 * @param mixed $content
	 * @return array
	 */
	public function extract($content) {
		return $this->getContentObjectRenderer()->typoLink_URL(array('param' => trim($content)));
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
		$targetPageUid = (integer) $content;
		if ($targetPageUid) {
			$configuration = $this->getObjectFactory()->getConfiguration()->getTableConfiguration()->getConfiguredExtractionSetup($table, $field);
			$cmisService = $this->getObjectFactory()->getCmisService();
			try {
				$targetPageUuid = $cmisService->getUuidForLocalRecord('pages', $targetPageUid);
				return array(
					array(
						PropertyIds::TARGET_ID => $targetPageUuid,
						PropertyIds::OBJECT_TYPE_ID => !empty($configuration['type']) ? $configuration['type'] : 'cmis:references'
					)
				);
			} catch (CmisObjectNotFoundException $error) {
				$this->getObjectFactory()->getLogger()->info(
					sprintf(
						'A link from %s:%s points to target page %d, but this target page has not yet been indexed.',
						$table,
						$field,
						$targetPageUid
					)
				);
			}
		}
		return array();
	}

	/**
	 * @return ContentObjectRenderer
	 */
	protected function getContentObjectRenderer() {
		return new ContentObjectRenderer();
	}

}
