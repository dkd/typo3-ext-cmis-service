<?php

// Configuration registration - static TypoScript for this extension
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'CMIS base settings');

// Configuration registration - static TypoScript for this extension; news setup
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/News', 'CMIS news settings');

// Register/configure the CMIS indexing interaction module
if (TYPO3_MODE === 'BE') {
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		'Dkd.CmisService',
		'tools',
		'tx_cmisservice_manager',
		'after:lang',
		array(
			'Manager' => 'index,tables,repositories,refreshStatus,truncateQueue,truncateIdentities,generateIndexingTasks,pickTask,pickTasks',
		),
		array(
			'access' => 'admin',
			'icon' => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/ManagerModuleIcon.svg',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xlf',
			'inheritNavigationComponentFromMainModule' => FALSE
		)
	);
}
