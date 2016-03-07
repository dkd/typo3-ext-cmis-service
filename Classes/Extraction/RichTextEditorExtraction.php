<?php
namespace Dkd\CmisService\Extraction;

use Dkd\PhpCmis\Exception\CmisObjectNotFoundException;
use Dkd\PhpCmis\PropertyIds;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Extraction: TYPO3 Rich Text Editor formatting
 */
class RichTextEditorExtraction extends AbstractExtraction implements ExtractionInterface {

	/**
	 * Convert TYPO3 RTE content to HTML then extract plain text.
	 *
	 * @param mixed $content
	 * @return string
	 */
	public function extract($content) {
		return $this->getContentObjectRenderer()->HTMLparser_TSbridge(
			(string) $content,
			(array) $this->getObjectFactory()->getConfigurationManager()->getGlobalConfiguration(
				array(
					'lib.parseFunc_RTE',
					'lib.parseFunc_HTML',
					'lib.parseFunc'
				)
			)
		);
	}

	/**
	 * @param mixed $content
	 * @param string $table
	 * @param string $field
	 * @return array[]
	 */
	public function extractAssociations($content, $table, $field) {
		$transformed = $this->extract($content);
		$matches = array();
		$links = preg_match_all('/<link\\s([0-9]+)/i', $content, $matches);
		$configuration = $this->getObjectFactory()->getConfiguration()->getTableConfiguration()->getConfiguredExtractionSetup($table, $field);
		$cmisService = $this->getObjectFactory()->getCmisService();
		$session = $cmisService->getCmisSession();
		$relationType = !empty($configuration['type']) ? $configuration['type'] : 'cmis:references';
		if ($links) {
			$associations = array();
			foreach (array_map('intval', $matches[1]) as $pageUid) {
				try {
					$targetPageUuid = $cmisService->getUuidForLocalRecord('pages', $pageUid);
					$associations[] = array(
						PropertyIds::TARGET_ID => $targetPageUuid,
						PropertyIds::OBJECT_TYPE_ID => $relationType
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
			return $associations;
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
