<?php
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
