<?php
namespace Dkd\CmisService\Execution\Cmis;

use Dkd\CmisService\CmisApi;
use Dkd\CmisService\Constants;
use Dkd\CmisService\Execution\Cmis\AbstractCmisExecution;
use Dkd\CmisService\Execution\ExecutionInterface;
use Dkd\CmisService\Execution\Result;
use Dkd\CmisService\Factory\ObjectFactory;
use Dkd\CmisService\Task\InitializationTask;
use Dkd\CmisService\Task\TaskInterface;
use Dkd\PhpCmis\Exception\CmisContentAlreadyExistsException;
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
		Constants::CMIS_PROPERTY_TYPO3UUID,
		Constants::CMIS_PROPERTY_TYPO3TABLE,
		Constants::CMIS_PROPERTY_TYPO3UID,
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
	 * Evict a document from the index.
	 *
	 * @param InitializationTask $task
	 * @return Result
	 */
	public function execute(TaskInterface $task) {
		/** @var EvictionTask $task */
		$this->result = $this->createResultObject();
		try {
			$this->validatePresenceOfCustomCmisTypes($this->requiredCustomTypes);
			$this->result->setMessage('CMIS Repository initialized!');
		} catch (\InvalidArgumentException $error) {
			$this->result->setCode(Result::ERR);
			$this->result->setError($error);
			$this->result->setMessage($error->getMessage());
		}
		return $this->result;
	}

	/**
	 * @param array $typeIds
	 * @throws CmisObjectNotFoundException
	 */
	protected function validatePresenceOfCustomCmisTypes(array $typeIds) {
		$session = $this->getCmisObjectFactory()->getSession();
		foreach ($typeIds as $typeId) {
			$session->getTypeDefinition($typeId);
		}
	}

	/**
	 * @return ObjectFactory
	 */
	protected function getObjectFactory() {
		return new ObjectFactory();
	}

}
