<?php
namespace Dkd\CmisService\Command;

use Dkd\CmisService\Analysis\TableConfigurationAnalyzer;
use Dkd\CmisService\Execution\Result;
use Dkd\CmisService\Service\InteractionService;
use Dkd\CmisService\Task\RecordImportTask;
use Dkd\PhpCmis\CmisObject\CmisObjectInterface;
use Dkd\PhpCmis\Data\DocumentInterface;
use Dkd\PhpCmis\Data\FolderInterface;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * CMIS Command Controller
 *
 * Main CLI interface for interacting with the
 * CMIS Service of this TYPO3 site.
 */
class CmisCommandController extends CommandController {

	const RESOURCE_CONFIGURATION = 'configuration';
	const RESOURCE_OBJECT = 'object';
	const RESOURCE_TREE = 'tree';
	const ACTION_DUMP = 'dump';
	const ACTION_DELETE = 'delete';
	const ACTION_DOWNLOAD = 'download';

	/**
	 * Contexts passed to Logger implementations when messages
	 * are dispatched from this class.
	 *
	 * @var array
	 */
	protected $logContexts = array('cmis_service', 'CLI');

	/**
	 * @var InteractionService
	 */
	protected $interactionService;

	/**
	 * @param InteractionService $interactionService
	 * @return void
	 */
	public function injectInteractionService(InteractionService $interactionService) {
		$this->interactionService = $interactionService;
	}

	/**
	 * Truncate Queue
	 *
	 * Used when the queue should be completely flushed
	 * of all pending Tasks, regardless of status.
	 *
	 * @return void
	 */
	public function truncateQueueCommand() {
		$result = $this->interactionService->truncateQueue();
		$this->echoResultToConsole($result);
	}

	/**
	 * Truncate Identity Storage
	 *
	 * Used when the local index associating records'
	 * table and UID to a CMIS UUID needs to be flushed,
	 * which it does when changing or recreating the
	 * CMIS storage. In other words this command should be
	 * executed whenever CMIS UUIDs have changed.
	 *
	 * Executes "TRUNCATE TABLE tx_cmisservice_identity".
	 *
	 * @return void
	 */
	public function truncateIdentityStorageCommand() {
		$result = $this->interactionService->truncateIdentities();
		$this->echoResultToConsole($result);
	}

	/**
	 * Generate Importing Tasks
	 *
	 * WARNING WARNING WARNING
	 *
	 * DO NOT RUN THIS BEFORE YOU HAVE RUN THE INDEXING TASKS!
	 * RUNNING THIS TASK ON AN EMPTY IDENTITY CACHE CAUSES
	 * DUPLICATES TO BE IMPORTED AND ASSOCIATED WITH THE CMIS
	 * OBJECT. MAKE ABSOLUTELY SURE YOU HAVE INDEXED EVERY
	 * MONITORED RECORD ON THE TYPO3 SITE BEFORE EXECUTING!
	 * DRY MODE IS ON BY DEFAULT TO PREVENT ERRORS
	 *
	 * Generates importing tasks by walking the remote
	 * CMIS server to detect new/un-indexed objects which
	 * can be imported to the TYPO3 site as records. Scans
	 * the CMIS site folder defined as site root, traverses
	 * all sub-folders and scans all objects therein.
	 *
	 * Uses the reverse of the indexing configuration to
	 * determine if an object type can be imported. If an
	 * object can be imported, an import task is added to
	 * the queue to be executed by the "picktask(s)" command.
	 *
	 * Importing updates the local UUID storage and attempts
	 * to fill the TYPO3-specific model properties back in
	 * the CMIS storage, turning the CMIS object into a fully
	 * associated and indexed object.
	 *
	 * Dry running (no actual tasks created) can be enabled
	 * but is disabled by default.
	 *
	 * @param string $table Table to import new CMIS objects into, or empty for all tables
	 * @param boolean $dry If TRUE tasks will not be added to the queue for execution
	 * @param boolean $verbose If TRUE outputs a list of all UUIDs detected for import
	 * @return void
	 */
	public function generateImportingTasksCommand($table = NULL, $dry = TRUE, $verbose = FALSE) {
		$this->response->setContent('Scanning CMIS site folder for new/un-indexed objects...' . PHP_EOL);
		if ($table === NULL) {
			$this->response->appendContent('Scanning for imports for all monitored tables' . PHP_EOL);
		} else {
			$this->response->appendContent(sprintf('Scanning for imports for table "%s"', $table) . PHP_EOL);
		}
		$this->response->send();
		// collect tasks for tables so we can report and/or queue them
		$tasks = $this->interactionService->createImportingTasks($table);
		$numberOfTasks = count($tasks);
		$numberOfTasksSuffix = $numberOfTasks != 1 ? 's' : '';
		$this->response->setContent(
			sprintf(
				'Detected %d new or un-indexed CMIS object%s',
				$numberOfTasks,
				$numberOfTasksSuffix
			) . PHP_EOL
		);
		if ($verbose) {
			foreach ($tasks as $task) {
				$this->response->appendContent(
					sprintf('* %s -> %s',
						$task->getParameter(RecordImportTask::OPTION_SOURCE),
						$task->getParameter(RecordImportTask::OPTION_TABLE)
					)
				);
			}
		}
		if (!$numberOfTasks) {
			$this->response->appendContent('No import tasks need to be created' . PHP_EOL);
			$this->response->send();
			return;
		}
		$this->response->send();
		if ($dry) {
			$this->response->appendContent(
				'WARNING! THIS ACTION CAUSES DUPLICATES TO BE IMPORTED AND FOREVER ASSOCIATED WITH THE CMIS ' . PHP_EOL .
				'OBJECT. DO NOT RUN THIS COMMAND UNLESS YOU ARE ABSOLUTELY, 100% SURE YOU HAVE INDEXED' . PHP_EOL .
				'EVERY SINGLE MONITORED RECORD ON THE TYPO3 SITE FIRST!' . PHP_EOL
			);
			$this->response->appendContent(
				sprintf(
					'Dry mode is ON - %d import task%s would have been created',
					$numberOfTasks,
					$numberOfTasksSuffix
				) . PHP_EOL
			);
		} else {
			$this->interactionService->addTasksToQueue($tasks);
			$this->response->setContent(
				sprintf(
					'Added import task%s for %d record%s to queue',
					$numberOfTasksSuffix,
					$numberOfTasks,
					$numberOfTasksSuffix
				) . PHP_EOL
			);
		}
	}

	/**
	 * Generate Indexing Tasks
	 *
	 * Generates indexing Tasks for all monitored content.
	 * Indexing tasks are then processed by pickTask() or
	 * pickTasks($num). No actual interaction with CMIS
	 * is done by this command - the execution of indexing
	 * Tasks performs this check and if no updates are
	 * required, skips further processing and marks the
	 * Task as successfully completed.
	 *
	 * @param string $table Table to index, or empty for all tables.
	 * @return void
	 */
	public function generateIndexingTasksCommand($table = NULL) {
		if (TRUE === empty($table)) {
			$tables = $this->interactionService->getMonitoredTableNames();
		} elseif (FALSE !== strpos($table, ',')) {
			$tables = explode(',', $table);
			$tables = array_map('trim', $tables);
		} else {
			$tables = array($table);
		}
		foreach ($tables as $tableName) {
			$result = $this->interactionService->createAndAddIndexingTasks($tableName);
			$this->echoResultToConsole($result);
		}
	}

	/**
	 * Initialize the CMIS repository
	 *
	 * It is safe to re-run this command multiple times!
	 *
	 * Analyse the CMIS repository's data storage to
	 * detect any TYPO3-specific data types that may be
	 * missing, then creates those data types. If a
	 * required object type already exists is is left
	 * untouched.
	 *
	 * Also takes into consideration any custom setup
	 * which adds types.
	 *
	 * This CLI command circumvents the Queue and directly
	 * executes the InitializationTask.
	 *
	 * @param boolean $verbose If TRUE (1) will output additional information about payloads
	 * @return void
	 */
	public function initializeCommand($verbose = FALSE) {
		$result = $this->interactionService->initializeRepository();
		$this->echoResultToConsole($result, $verbose);
	}

	/**
	 * Pick and execute one (1) Task
	 *
	 * Picks the next-in-line Task from the Queue and runs
	 * it, then exits.
	 *
	 * For multiple Tasks in one run, use pickTasks()
	 *
	 * @param boolean $verbose If TRUE (1) will output additional information about payloads
	 * @return void
	 */
	public function pickTaskCommand($verbose = FALSE) {
		$this->pickTasksCommand(1, $verbose);
	}

	/**
	 * Pick and execute one or more Tasks
	 *
	 * Pick the number of Tasks indicated in $tasks and run
	 * all of them in a single run.
	 *
	 * @param integer $tasks Number of tasks to pick and execute.
	 * @param boolean $verbose If TRUE (1) will output additional information about payloads
	 * @return void
	 */
	public function pickTasksCommand($tasks = 1, $verbose = FALSE) {
		$results = $this->interactionService->pickTasks($tasks);
		if ($verbose) {
			array_map(array($this, 'echoResultToConsole'), $results);
		} else {
			$this->response->setContent(sprintf('Executed %d tasks(s)', count($results)) . PHP_EOL);
			$this->response->send();
		}
	}

	/**
	 * Reads the current queue status
	 *
	 * @return void
	 */
	public function statusCommand() {
		$result = $this->interactionService->readQueueStatus();
		$this->echoResultToConsole($result);
	}

	/**
	 * Manipulate object
	 *
	 * Perform $action on object with $id in repository.
	 *
	 * Available commands are:
	 *
	 * - dump (forward to dumpCommand; dumps object properties)
	 * - delete (removes CMIS object by UUID)
	 * - download (content stream output to STDOUT)
	 *
	 * @param string $action
	 * @param string $id
	 * @return void
	 */
	public function objectCommand($action, $id) {
		$session = $this->getCmisObjectFactory()->getSession();
		if (self::ACTION_DUMP === $action) {
			$this->dumpCommand(self::RESOURCE_OBJECT, $id, FALSE);
		} elseif (self::ACTION_DELETE === $action) {
			$session->delete($session->createObjectId($id));
			$this->response->appendContent('Object "' . $id . '" has been deleted.' . PHP_EOL);
		} elseif (self::ACTION_DOWNLOAD === $action) {
			$this->response->setContent($session->getContentStream($session->createObjectId($id)));
		} else {
			$this->response->setContent('Unsupported command: ' . $action . PHP_EOL);
		}
		$this->response->send();
	}

	/**
	 * Dump resource data
	 *
	 * Dumps, as YAML, selected resource data. Supported
	 * resources are:
	 *
	 * - configuration
	 * - tree
	 * - object
	 *
	 * If no resource is specified, `configuration` is assumed.
	 * When dumping objects, the $id parameter is required
	 *
	 * @param string $resource
	 * @param string $id
	 * @param boolean $brief
	 * @return void
	 */
	public function dumpCommand($resource = self::RESOURCE_CONFIGURATION, $id = NULL, $brief = TRUE) {
		$data = array();
		$session = $this->getCmisObjectFactory()->getSession();
		if (self::RESOURCE_CONFIGURATION === $resource) {
			$data = $this->getObjectFactory()->getConfiguration()->getDefinitions();
		} elseif (self::RESOURCE_TREE === $resource) {
			$rootFolder = $session->getRootFolder();
			$data = $this->convertTreeBranchesToArrayValue($rootFolder->getChildren(), $brief);
		} elseif (self::RESOURCE_OBJECT === $resource) {
			$data = array();
			foreach ($session->getObject($session->createObjectId($id))->getProperties() as $propertyName => $property) {
				$data[$propertyName] = $property->getFirstValue();
			}
		}
		$yaml = Yaml::dump($data, 99);
		$this->response->setContent($yaml);
		$this->response->send();
		$this->getObjectFactory()->getLogger()->info(sprintf('Dump of %s (id %s) performed', $resource, $id), $this->logContexts);
	}

	/**
	 * Recursive method to make a succint representation
	 * of a single branch and any children of that branch.
	 *
	 * @param CmisObjectInterface[] $object
	 * @param boolean $brief
	 * @return array|string
	 */
	protected function convertTreeBranchesToArrayValue(array $objects, $brief = TRUE) {
		$values = array();
		foreach ($objects as $object) {
			/** @var DocumentInterface|FolderInterface $object */
			$value = NULL;
			$type = $object->getProperty('cmis:baseTypeId')->getFirstValue();
			$date = $object->getCreationDate()->format('Y-m-d');
			if (TRUE === $brief) {
				$name = $object->getName() . ' (' . $object->getId() . ')';
			} else {
				$name = $type . ',' . $date . ' ' . $object->getName();
			}
			if (TRUE === $object instanceof FolderInterface) {
				$value = $this->convertTreeBranchesToArrayValue($object->getChildren(), $brief);
				if (0 < count($value) && array_fill(0, count($value), NULL) == array_values($value)) {
					// every value is NULL; flip value array so it becomes a list of names
					$value = array_keys($value);
				}
			} elseif (TRUE === $object instanceof DocumentInterface) {
				if (FALSE === $brief) {
					$value = array(
						'id' => $object->getId(),
						'typo3uuid' => $object->getProperty('typo3uuid'),
						'created' => $date . ' by ' . $object->getCreatedBy(),
						'modified' => $object->getLastModificationDate()->format('Y-m-d') . ' by ' . $object->getLastModifiedBy(),
						'type' => $type,
					);
				}
			} else {
				$value = get_class($object);
			}
			$values[$name] = $value;
		}
		return $values;
	}

	/**
	 * @param Result $result
	 * @param boolean $verbose
	 * @return void
	 */
	protected function echoResultToConsole(Result $result, $verbose = FALSE) {
		$payload = $result->getPayload();
		$this->response->appendContent($result->getMessage() . PHP_EOL);
		if (0 < count($payload) && TRUE === $verbose) {
			$this->response->appendContent(var_export($payload, TRUE) . PHP_EOL);
		}
	}

	/**
	 * @return DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}
}
