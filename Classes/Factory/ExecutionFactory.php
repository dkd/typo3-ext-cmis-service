<?php
namespace Dkd\CmisService\Factory;

use Dkd\CmisService\Execution\Cmis\EvictionExecution;
use Dkd\CmisService\Execution\Cmis\ImportExecution;
use Dkd\CmisService\Execution\Cmis\IndexExecution;
use Dkd\CmisService\Execution\Cmis\InitializationExecution;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class ExecutionFactory
 */
class ExecutionFactory {

	/**
	 * @return IndexExecution
	 */
	public function createIndexExecution() {
		return $this->getObjectManager()->get(IndexExecution::class);
	}
	
	public function createImportExecution() {
		return new ImportExecution();
	}

	/**
	 * @return EvictionExecution
	 */
	public function createEvictionExecution() {
		return new EvictionExecution();
	}

	/**
	 * @return InitializationExecution
	 */
	public function createInitializationExecution() {
		return new InitializationExecution();
	}

	/**
	 * @return ObjectManager
	 */
	protected function getObjectManager() {
		return GeneralUtility::makeInstance(ObjectManager::class);
	}

}
