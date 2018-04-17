<?php
namespace Dkd\CmisService;

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Toolbar\ClearCacheActionsHookInterface;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConnectionManager implements SingletonInterface, ClearCacheActionsHookInterface {

    /**
     * Adds a menu entry to the clear cache menu to detect cmis connections.
     *
     * @param array $cacheActions Array of CacheMenuItems
     * @param array $optionValues Array of AccessConfigurations-identifiers (typically  used by userTS with options.clearCache.identifier)
     */
    public function manipulateCacheActions(&$cacheActions, &$optionValues)
    {
        if ($GLOBALS['BE_USER']->isAdmin()) {
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            $optionValues[] = 'clearCmisServiceConnectionCache';
            $iconFactory = GeneralUtility::makeInstance(IconFactory::class);

            $cacheActions[] = [
                'id' => 'clearCmisServiceConnectionCache',
                'title' => 'Initialize CMIS connections',
                'href' => $uriBuilder->buildUriFromRoute('ajax_cmis_updateConnections'),
                'icon' => $iconFactory->getIcon('actions-system-refresh', Icon::SIZE_SMALL)
            ];
        }
    }

    /**
     * Entrypoint for the ajax request
     */
    public function updateConnectionsInCacheMenu()
    {
        /** @var \Dkd\CmisService\Factory\ObjectFactory $objectFactory */
        $objectFactory = GeneralUtility::makeInstance('Dkd\\CmisService\\Factory\\ObjectFactory');
        $configurationManager = $objectFactory->getConfigurationManager();
        $configurationManager->expireCachedDefinition();
        $configurationManager->getMasterConfiguration();
    }
}
