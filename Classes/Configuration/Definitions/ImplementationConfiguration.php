<?php
namespace Dkd\CmisService\Configuration\Definitions;

class ImplementationConfiguration implements ConfigurationDefinitionInterface {

	const DEFAULT_CLASS_LOGGER = 'Dkd\CmisService\Logging\SimpleLogger';
	const DEFAULT_CLASS_QUEUE = 'Dkd\CmisService\Queue\SimpleQueue';
	const DEFAULT_CLASS_WORKER = 'Dkd\CmisService\Queue\SimpleWorker';
	const DEFAULT_CLASS_CONFIGURATION_READER = 'Dkd\CmisService\Configuration\Reader\YamlConfigurationReader';
	const DEFAULT_CLASS_CONFIGURATION_WRITER = 'Dkd\CmisService\Configuration\Writer\YamlConfigurationWriter';

}
