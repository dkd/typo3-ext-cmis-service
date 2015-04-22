<?php
// caches definitions:
// Queue - assigned to "system" group to avoid clearing cached states on anything but admin access.
$caches = &$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'];
if (!is_array($caches[\Dkd\CmisService\Factory\CacheFactory::CACHE_PREFIX . \Dkd\CmisService\Queue\SimpleQueue::CACHE_IDENTITY])) {
	$caches[\Dkd\CmisService\Factory\CacheFactory::CACHE_PREFIX . \Dkd\CmisService\Queue\SimpleQueue::CACHE_IDENTITY] = array(
		'groups' => array('system'),
		'frontend' => \Dkd\CmisService\Factory\CacheFactory::DEFAULT_FRONTEND,
		'backend' => \Dkd\CmisService\Factory\CacheFactory::DEFAULT_BACKEND
	);
}

// remove reference
unset($caches);

// add our CommandController which handles cronjobs
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][]
	= 'Dkd\\CmisService\\Command\\CmisCommandController';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][]
	= 'Dkd\\CmisService\\Hook\\DataHandlerListener';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][]
	= 'Dkd\\CmisService\\Hook\\DataHandlerListener';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['moveRecordClass'][]
	= 'Dkd\\CmisService\\Hook\\DataHandlerListener';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][]
	= 'Dkd\\CmisService\\Hook\\ClearCacheListener->clearCacheCommand';
