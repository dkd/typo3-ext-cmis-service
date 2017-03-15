<?php
namespace Dkd\CmisService\Hook;

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
		$cachedConfigurationFile = GeneralUtility::getFileAbsFileName(
		    $this->getObjectFactory()->getConfigurationManager()->getCachedResourceIdentifier()
        );
		if (TRUE === file_exists($cachedConfigurationFile)) {
			unlink($cachedConfigurationFile);
		}
	}

}
