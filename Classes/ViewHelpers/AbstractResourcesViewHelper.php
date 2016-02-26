<?php
namespace Dkd\CmisService\ViewHelpers;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper;

/**
 * Class StylesheetsViewHelper
 */
abstract class AbstractResourcesViewHelper extends AbstractBackendViewHelper {

	/**
	 * Returns an array (for feeding into f:be.container) of files that should be loaded.
	 *
	 * @return array
	 */
	abstract public function render();

	/**
	 * @codeCoverageIgnore
	 * @return boolean
	 */
	protected function isCoreVersionBelowSeven() {
		return version_compare(VersionNumberUtility::getNumericTypo3Version(), '7.1.0', '<');
	}

	/**
	 * @codeCoverageIgnore
	 * @return string
	 */
	protected function getPublicResourcePath() {
		return '../' . ExtensionManagementUtility::siteRelPath('cmis_service') . 'Resources/Public/';
	}
}
