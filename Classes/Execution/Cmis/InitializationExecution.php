<?php
namespace Dkd\CmisService\Execution\Cmis;

use Dkd\CmisService\CmisApi;
use Dkd\CmisService\Constants;
use Dkd\CmisService\Execution\ExecutionInterface;
use Dkd\CmisService\Execution\Result;
use Dkd\CmisService\Task\InitializationTask;
use Dkd\CmisService\Task\TaskInterface;
use Dkd\PhpCmis\Exception\CmisObjectNotFoundException;

/**
 * Class InitializationExecution
 */
class InitializationExecution extends AbstractCmisExecution implements ExecutionInterface {

	/**
	 * Contexts passed to Logger implementations when messages
	 * are dispatched from this class.
	 *
	 * @var array
	 */
	protected $logContexts = array('cmis_service', 'execution', 'cmis', 'initialization');

	/**
	 * @var array
	 */
	protected $requiredCustomTypes = array(
		Constants::CMIS_DOCUMENT_TYPE_ARBITRARY,
		Constants::CMIS_DOCUMENT_TYPE_PAGES,
		Constants::CMIS_DOCUMENT_TYPE_CONTENT
	);

	/**
	 * Validates that this Task is an instance of
	 * the expected and supported class.
	 *
	 * @param TaskInterface $task
	 * @return boolean
	 * @throws \InvalidArgumentException
	 */
	public function validate(TaskInterface $task) {
		if (FALSE === $task instanceof InitializationTask) {
			throw new \InvalidArgumentException(
				'Error in CMIS IndexExecution during Task validation. ' .
				'Task must be a Dkd\\CmisService\\Task\\InitializationTask or subclass; we received a ' . get_class($task));
		}
		return TRUE;
	}

	/**
	 * Initialize the CMIS integrations:
	 *
	 * - Verify that the current CMIS server can
	 *   operate our special TYPO3 objects.
	 * - Initialize the CMIS storage by creating
	 *   a special "Site" folder to use as root.
	 *
	 * @param InitializationTask $task
	 * @return Result
	 */
	public function execute(TaskInterface $task) {
		/** @var EvictionTask $task */
		$this->result = $this->createResultObject();
		try {
			$this->validatePresenceOfCustomCmisTypes($this->requiredCustomTypes);
			$this->createCmisSitesForFirstDomainOfAllRootPages();
			$this->result->setMessage('CMIS Repository initialized!');
		} catch (\InvalidArgumentException $error) {
			$this->result->setCode(Result::ERR);
			$this->result->setError($error);
			$this->result->setMessage($error->getMessage());
		}
		return $this->result;
	}

	/**
	 * Uses the shared execution logic to ensure
	 * that every recorded domain has a Site folder
	 * in CMIS - by simply attempting to resolve
	 * each one. The ad-hoc folder creation logic
	 * then takes care of the rest.
	 *
	 * @return void
	 */
	protected function createCmisSitesForFirstDomainOfAllRootPages() {
		$pagesWithDomainRecords = $this->getDatabaseConnection()->exec_SELECTgetRows(
			'pid', 'sys_domain', 'hidden = 0', 'pid', 'sorting ASC'
		);
		$pageUids = array_map('reset', $pagesWithDomainRecords);
		foreach ($pageUids as $pageUid) {
			$this->getCmisService()->resolveCmisSiteFolderByPageUid($pageUid);
		}
	}

	/**
	 * Verifies that all required types exist in the
	 * CMIS server, including those types added via
	 * the custom TYPO3 CMIS model.
	 *
	 * @param array $typeIds
	 * @throws CmisObjectNotFoundException
	 */
	protected function validatePresenceOfCustomCmisTypes(array $typeIds) {
		$session = $this->getCmisObjectFactory()->getSession();
		foreach ($typeIds as $typeId) {
			$session->getTypeDefinition($typeId);
		}
	}

}
