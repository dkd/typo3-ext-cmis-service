<?php
namespace Dkd\CmisService\Service;

use Dkd\CmisService\Constants;
use Dkd\CmisService\Factory\ObjectFactory;
use Dkd\CmisService\SingletonInterface;

/**
 * Class RenderingService
 */
class RenderingService implements SingletonInterface {

	/**
	 * Renders a record using a nested frontend request
	 * to a custom PAGE type in TYPO3. This is the only
	 * perfectly sure way to retrieve a proper HTML
	 * representation of the record (whichever type it
	 * may be).
	 *
	 * The table name gets passed in the request and
	 * allows the rendering to be adjusted based on the
	 * origin table. This method also singles out any
	 * requests to render the "pages" table and performs
	 * a special requests which contains only the page UID.
	 *
	 * @param string $table
	 * @param array $record
	 * @return string
	 */
	public function renderRecord($table, array $record) {
		if ($table === 'pages') {
			$content = $this->renderPageBody($record['uid']);
		} elseif ($table === 'tt_content') {
			$content = $this->renderContentElementOnPage($record['uid'], $record['pid']);
		} else {
			$content = $this->renderRecordWithFunction($table, $record);
		}
		return $content;
	}

	/**
	 * @param integer $pageUid
	 * @return string
	 */
	protected function renderPageBody($pageUid) {
		return $this->fetchBody($pageUid);
	}

	/**
	 * @param integer $contentElementUid
	 * @param integer $pageUid
	 * @return string
	 */
	protected function renderContentElementOnPage($contentElementUid, $pageUid) {
		return $this->fetchBody($pageUid, $contentElementUid);
	}

	/**
	 * Renders an arbitrary record by table name.
	 *
	 * Detects the first input- or text-type field that
	 * exists in the record and uses the unmodified text
	 * value from that field.
	 *
	 * @param string $table
	 * @param array $record
	 * @return string
	 */
	protected function renderRecordWithFunction($table, array $record) {
		return 'Rendered record ' . $record['uid'] . ' from table ' . $table;
	}

	/**
	 * @param integer $pageUid
	 * @param integer|NULL $contentUid
	 * @return string
	 */
	protected function fetchBody($pageUid, $contentUid = NULL) {
		$uri = sprintf('?type=41683&id=%d', $pageUid);
		if ($contentUid) {
			$uri .= sprintf('&tx_cmisservice_renderer[contentUid]=%d', $contentUid);
		}
		$parentPageUid = $pageUid;
		while ($domain === NULL && $parentPageUid > 0) {
			$domain = $this->getObjectFactory()->getCmisService()->resolvePrimaryDomainRecordForPageUid($parentPageUid);
			$parentPageUid = reset($GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('pid', 'pages', "uid = '" . $parentPageUid . "'"));
		}
		$content = file_get_contents('http://' . $domain['domainName'] . '/' . $uri);
		if (empty($content)) {
			// retry on localhost
			$content = file_get_contents('http://localhost/' . $uri);
		}
		return $content;
	}

	/**
	 * @return ObjectFactory
	 */
	protected function getObjectFactory() {
		return new ObjectFactory();
	}

}
