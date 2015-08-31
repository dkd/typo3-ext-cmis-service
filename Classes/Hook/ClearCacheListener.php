<?php
namespace Dkd\CmisService\Hook;

use Dkd\CmisService\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Clear Cache Listener
 *
 * Listens for commands to clear TYPO3 caches.
 */
class ClearCacheListener extends AbstractListener {

	/**
	 * @param string $command
	 * @return void
	 */
	public function clearCacheCommand($command) {
		$cachedConfigurationFile = GeneralUtility::getFileAbsFileName(ConfigurationManager::CACHE_RESOURCE);
		if (TRUE === file_exists($cachedConfigurationFile)) {
			unlink($cachedConfigurationFile);
		}
	}

}
