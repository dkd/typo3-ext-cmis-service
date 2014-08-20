<?php
namespace Dkd\CmisService\Configuration\Definitions;

/**
 * Implementation Configuration Definition
 *
 * @package Dkd\CmisService\Configuration\Definitions
 */
class ImplementationConfiguration extends AbstractConfigurationDefinition implements ConfigurationDefinitionInterface {

	const OBJECT_CLASS_QUEUE = 'objects.queue.className';
	const OBJECT_CLASS_WORKER = 'objects.worker.className';
	const OBJECT_CLASS_LOGGER = 'objects.logger.className';

	/**
	 * @var array
	 */
	protected $defaults = array(
		self::OBJECT_CLASS_QUEUE => 'Dkd\CmisService\Queue\SimpleQueue',
		self::OBJECT_CLASS_WORKER => 'Dkd\CmisService\Queue\SimpleWorker',
		self::OBJECT_CLASS_LOGGER => 'Dkd\CmisService\Logging\SimpleLogger'
	);

}
