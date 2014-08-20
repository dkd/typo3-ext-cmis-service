<?php
namespace Dkd\CmisService;

/**
 * Initialize the CMIS TYPO3 CMS Service and its dependencies.
 *
 * Class with minimum dependencies to construct an initial
 * `cmis_service` environment.
 *
 * @package Dkd\CmisService
 */
class Initialization {

	/**
	 * @var boolean
	 */
	static $initialized = FALSE;

	/**
	 * Starts the CmisService initialization process.
	 *
	 * @return boolean
	 */
	public function start() {
		if (FALSE === $this->isInitialized()) {
			// forceful (re-)initialization of factories
			$this->initialize();
		}
		return $this->finish();
	}

	/**
	 * Internal method called only once on initialization.
	 *
	 * @return void
	 */
	protected function initialize() {
		return;
	}

	/**
	 * Internal method called only once on finish of initialization.
	 *
	 * @return boolean
	 */
	protected function finish() {
		return self::$initialized = TRUE;
	}

	/**
	 * Returns TRUE if initialization has already occurred.
	 *
	 * @return boolean
	 */
	protected function isInitialized() {
		return self::$initialized;
	}

	/**
	 * Resets the initialization indicator, forcing the class to
	 * completely re-initialize on next initialization call.
	 *
	 * @return boolean
	 */
	protected function reset() {
		return self::$initialized = FALSE;
	}

}
