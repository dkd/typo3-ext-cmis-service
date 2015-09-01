<?php
namespace Dkd\CmisService;

/**
 * Constants used by EXT:cmis_service
 */
abstract class Constants {

	const CMIS_DOCUMENT_TYPE_SITES = 'F:st:sites';
	const CMIS_DOCUMENT_TYPE_SITE = 'F:dkd:typo3:sys_domain';
	const CMIS_DOCUMENT_TYPE_MAIN_ASPECT = 'P:dkd:typo3:aspect:general';
	const CMIS_DOCUMENT_TYPE_ARBITRARY = 'D:dkd:typo3:arbitrary';
	const CMIS_DOCUMENT_TYPE_PAGES = 'F:dkd:typo3:pages';
	const CMIS_DOCUMENT_TYPE_CONTENT = 'D:dkd:typo3:tt_content';
	const CMIS_PROPERTY_TYPO3TABLE = 'dkd:typo3:general:record_table';
	const CMIS_PROPERTY_TYPO3UID = 'dkd:typo3:general:record_id';
	const CMIS_PROPERTY_RAWDATA = 'dkd:typo3:general:record_data';

}
