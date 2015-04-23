<?php
namespace Dkd\CmisService\Extraction;

use Dkd\CmisService\Factory\ObjectFactory;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

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
	 * @return ContentObjectRenderer
	 */
	protected function getContentObjectRenderer() {
		return new ContentObjectRenderer();
	}

	/**
	 * @return ObjectFactory
	 */
	protected function getObjectFactory() {
		return new ObjectFactory();
	}

}
