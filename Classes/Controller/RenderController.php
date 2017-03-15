<?php
namespace Dkd\CmisService\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

/**
 * Class RenderController
 */
class RenderController extends ActionController {

	/**
	 * @param integer $contentUid
	 * @return string
	 */
	public function renderAction($contentUid = NULL) {
		if ($contentUid) {
			$contentRecord = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', 'tt_content', "uid = '" . $contentUid . "'");
			$content = $this->renderRecord($contentRecord);
		} else {
			$content = file_get_contents(
				$_SERVER['REQUEST_SCHEME'] .
				'://' .
				$_SERVER['SERVER_ADDR'] .
				str_replace('type=41683&', '', $_SERVER['REQUEST_URI'])
			);
		}
		return $content;
	}

	/**
	 * @return ViewInterface
	 */
	protected function resolveView() {
		$viewObjectName = $this->resolveViewObjectName();
		$view = $this->objectManager->get($this->defaultViewObjectName);
		$this->setViewConfiguration($view);
		$view->initializeView();
		$view->assign('settings', $this->settings);
		return $view;
	}

	/**
	 * @param \TYPO3\CMS\Extbase\Mvc\RequestInterface $request
	 * @return boolean
	 */
	public function canProcessRequest(\TYPO3\CMS\Extbase\Mvc\RequestInterface $request) {
		return TRUE;
	}

	/**
	 * This function renders a raw tt_content record into the corresponding
	 * element by typoscript RENDER function. We keep track of already
	 * rendered records to avoid rendering the same record twice inside the
	 * same nested stack of content elements.
	 *
	 * @param array $row
	 * @return string|NULL
	 */
	protected function renderRecord(array $row) {
		if (0 < $GLOBALS['TSFE']->recordRegister['tt_content:' . $row['uid']]) {
			return NULL;
		}
		$conf = array(
			'tables' => 'tt_content',
			'source' => $row['uid'],
			'dontCheckPid' => 1
		);
		$parent = $GLOBALS['TSFE']->currentRecord;
		// If the currentRecord is set, we register, that this record has invoked this function.
		// It's should not be allowed to do this again then!!
		if (FALSE === empty($parent)) {
			$GLOBALS['TSFE']->recordRegister[$parent]++;
		}
		$html = $GLOBALS['TSFE']->cObj->cObjGetSingle('RECORDS', $conf);
		$GLOBALS['TSFE']->currentRecord = $parent;
		if (FALSE === empty($parent)) {
			$GLOBALS['TSFE']->recordRegister[$parent]--;
		}
		return $html;
	}
}
