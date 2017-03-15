<?php
namespace Dkd\CmisService\Execution\Cmis;

use Dkd\CmisService\Execution\AbstractExecution;
use Dkd\CmisService\Factory\CmisObjectFactory;
use Dkd\CmisService\Resolving\UUIDResolver;
use Dkd\CmisService\Service\CmisService;

/**
 * Class AbstractCmisExecution
 *
 * Base class with helper functions relevant for
 * executions using CMIS documents/service.
 */
abstract class AbstractCmisExecution extends AbstractExecution {

	/**
	 * Contexts passed to Logger implementations when messages
	 * are dispatched from this class.
	 *
	 * @var array
	 */
	protected $logContexts = array('cmis_service', 'execution', 'cmis');

	/**
	 * @return CmisService
	 */
	protected function getCmisService() {
		return new CmisService();
	}

	/**
	 * @return CmisObjectFactory
	 * @codeCoverageIgnore
	 */
	protected function getCmisObjectFactory() {
		return new CmisObjectFactory();
	}

}
