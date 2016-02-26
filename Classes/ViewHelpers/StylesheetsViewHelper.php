<?php
namespace Dkd\CmisService\ViewHelpers;

/**
 * Class StylesheetsViewHelper
 */
class StylesheetsViewHelper extends AbstractResourcesViewHelper {

	/**
	 * Returns an array (for feeding into f:be.container) of
	 * stylesheet files that should be loaded. Will load an
	 * additional Bootstrap resource if TYPO3 version is
	 * below 7.0 which includes this resource natively.
	 *
	 * @return array
	 */
	public function render() {
		$path = $this->getPublicResourcePath();
		$stylesheets = array();
		if (TRUE === $this->isCoreVersionBelowSeven()) {
			$doc = $this->getDocInstance();
			$pageRenderer = $doc->getPageRenderer();
			$pageRenderer->addCssFile(
				$path . 'Themes/Bootstrap/css/bootstrap.min.css',
				'stylesheet',
				'all',
				'',
				TRUE,
				TRUE,
				'',
				TRUE
			);

			$stylesheets[] = $path . 'Vendor/Fonts/FontAwesome/css/font-awesome.min.css';
			$stylesheets[] = $path . 'Stylesheets/TYPO3-6-2-compatibility.css';
		}

		$stylesheets[] = $path . 'Stylesheets/LifeCycleGraph.css';
		$stylesheets[] = $path . 'Stylesheets/Default.css';

		return $stylesheets;
	}
}
