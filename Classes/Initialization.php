<?php
namespace Dkd\CmisService;

/**
 * Initialize the CMIS TYPO3 CMS Service and its dependencies.
 *
 * Class with minimum dependencies to construct an initial
 * `cmis_service` environment.
 */

class Initialization {

	/**
	 * @var boolean
	 */
	static $initialized = FALSE;

	/**
	 * @return boolean
	 */
	public function start() {
		$isInitialized = $this->isInitialized();
		if (FALSE === $isInitialized) {
			// forceful (re-)initialization of factories
			$this->initialize();
		}
		return $this->finish();
	}

	/**
	 * @return void
	 */
	protected function initialize() {

	}

	/**
	 * @return boolean
	 */
	protected function finish() {
		return self::$initialized = TRUE;
	}

	/**
	 * @return boolean
	 */
	protected function isInitialized() {
		return self::$initialized;
	}

	/**
	 * @return boolean
	 */
	protected function reset() {
		return self::$initialized = FALSE;
	}

}
