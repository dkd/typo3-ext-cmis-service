<?php
namespace Dkd\CmisService\ViewHelpers;

class JavascriptViewHelper extends AbstractResourcesViewHelper {

    public function render() {
        $doc = $this->getDocInstance();
        $pageRenderer = $doc->getPageRenderer();
        $pageRenderer->loadJquery();
        $pageRenderer->loadRequireJsModule("TYPO3/CMS/Contentdashboard/Dashboard");
    }
}
