<?php
namespace Dkd\CmisService\Execution\Cmis;

use Dkd\CmisService\Analysis\Detection\ExtractionMethodDetector;
use Dkd\CmisService\Analysis\RecordAnalyzer;
use Dkd\CmisService\Analysis\TableConfigurationAnalyzer;
use Dkd\CmisService\Constants;
use Dkd\CmisService\Execution\Exception;
use Dkd\CmisService\Execution\ExecutionInterface;
use Dkd\CmisService\Execution\Result;
use Dkd\CmisService\Factory\ObjectFactory;
use Dkd\CmisService\Service\RenderingService;
use Dkd\CmisService\Task\RecordIndexTask;
use Dkd\CmisService\Task\TaskInterface;
use Dkd\PhpCmis\CmisObject\CmisObjectInterface;
use Dkd\PhpCmis\Data\DocumentInterface;
use Dkd\PhpCmis\Data\FolderInterface;
use Dkd\PhpCmis\Data\RelationshipInterface;
use Dkd\PhpCmis\Exception\CmisContentAlreadyExistsException;
use Dkd\PhpCmis\Exception\CmisObjectNotFoundException;
use Dkd\PhpCmis\PropertyIds;

/**
 * Class IndexExecution
 */
class IndexExecution extends AbstractCmisExecution implements ExecutionInterface {

	const EVENT_MAP = 'map';
	const EVENT_MAPPED = 'mapped';
	const EVENT_SAVE = 'save';
	const EVENT_SAVED = 'saved';
	const EVENT_STREAM_SAVE = 'streamsave';
	const EVENT_STREAM_SAVED = 'streamsaved';

	/**
	 * @var RenderingService
	 */
	protected $renderingService;

	/**
	 * Contexts passed to Logger implementations when messages
	 * are dispatched from this class.
	 *
	 * @var array
	 */
	protected $logContexts = array('cmis_service', 'execution', 'cmis', 'indexing');

	/**
	 * @param RenderingService $renderingService
	 * @return void
	 */
	public function injectRenderingService(RenderingService $renderingService) {
		$this->renderingService = $renderingService;
	}

	/**
	 * @param TaskInterface $task
	 * @return boolean
	 */
	public function validate(TaskInterface $task) {
		if (FALSE === $task instanceof RecordIndexTask) {
			throw new \InvalidArgumentException(
				'Error in CMIS IndexExecution during Task validation. ' .
				'Task must be a Dkd\\CmisService\\Task\\RecordIndexTask or subclass; we received a ' . get_class($task));
		}
		return TRUE;
	}

	/**
	 * Index a record, creating a document in the index.
	 *
	 * @param RecordIndexTask $task
	 * @return Result
	 * @throws Exception
	 */
	public function execute(TaskInterface $task) {
		$objectFactory = new ObjectFactory();
		/** @var RecordIndexTask $task */
		$this->result = $this->createResultObject();
		$fields = (array) $task->getParameter(RecordIndexTask::OPTION_FIELDS);
		$table = $task->getParameter(RecordIndexTask::OPTION_TABLE);
		$uid = $task->getParameter(RecordIndexTask::OPTION_UID);
		$record = $this->loadRecordFromDatabase($table, $uid, $fields);
		if (NULL === $record) {
			throw new Exception(
				sprintf(
					'Record %d from table %s not loaded. Record removed or invalid fields requested? (fields requested: %s)',
					$uid,
					$table,
					implode(', ', $fields)
				)
			);
		}
		$data = array();
		$recordAnalyzer = new RecordAnalyzer($table, $record);
		$document = $this->getCmisService()->resolveObjectByTableAndUid($table, $uid);
		if (TRUE === (boolean) $task->getParameter(RecordIndexTask::OPTION_RELATIONS)) {
			// The Task was configured to also index the relations from
			// this document to other CMIS documents (which have already
			// been indexed by a previous Task). We therefore now turn
			// all TYPO3 relations into CMIS relationships in a sync-type
			// manner; both creating and removing relationships as needed.
			$this->synchronizeRelationships($document, $recordAnalyzer);
		} else {
			$this->event(self::EVENT_MAP, $task, array('object' => $document, 'source' => $fields));
			foreach ($fields as $fieldName) {
				$data[$fieldName] = $this->performTextExtraction($table, $fieldName, $record);
			}
			$cmisPropertyValues = $this->remapFieldsToDocumentProperties($data, $recordAnalyzer);
			$existingChild = $this->getCmisService()->resolveChildByName(
				$document->getParents()[0],
				$cmisPropertyValues[PropertyIds::NAME]
			);
			$this->event(self::EVENT_MAPPED, $task, array('object' => $document, 'source' => $fields, 'properties' => $data));
			$this->event(self::EVENT_SAVE, $task, array('object' => $document));
			try {
				$document->updateProperties($cmisPropertyValues);
			} catch (CmisContentAlreadyExistsException $error) {
				$cmisPropertyValues[PropertyIds::NAME] = $table . '-' . $uid;
				$document->updateProperties($cmisPropertyValues);
			}
			$this->affixAuthor($document, $recordAnalyzer);
			$this->affixContentStream($document, $recordAnalyzer, $task);
			$this->event(self::EVENT_SAVED, $task, array('object' => $document));
		}
		$this->result->setCode(Result::OK);
		$this->result->setMessage('Indexed record ' . $uid . ' from ' . $table);
		$this->result->setPayload($data);
		return $this->result;
	}

	/**
	 * @param CmisObjectInterface $document
	 * @param RecordAnalyzer $recordAnalyzer
	 * @param TaskInterface $task
	 * @return void
	 */
	protected function affixContentStream(CmisObjectInterface $document, RecordAnalyzer $recordAnalyzer, TaskInterface $task) {
		if ($document instanceof DocumentInterface) {
			$renderedBody = $this->renderingService->renderRecord(
				$recordAnalyzer->getTable(),
				$recordAnalyzer->getRecord()
			);
			$stream = $this->getExtractionMethodDetector()->resolveBodyContentStreamExtractor()->extract($renderedBody);
			$this->event(self::EVENT_STREAM_SAVE, $task, array('object' => $document, 'stream' => $stream));
			$document->setContentStream($stream, TRUE);
			$this->event(self::EVENT_STREAM_SAVED, $task, array('object' => $document, 'stream' => $stream));
		}
	}

	/**
	 * Affixes (sets if missing) the also indexed backend user record
	 * that owns this document. Does not allow changing the existing
	 * author (returns TRUE without action).
	 *
	 * @param CmisObjectInterface $document
	 * @param RecordAnalyzer $recordAnalyzer
	 * @return boolean TRUE if an author was affixed or already exists, FALSE otherwise
	 */
	protected function affixAuthor(CmisObjectInterface $document, RecordAnalyzer $recordAnalyzer) {
		$currentAuthor = $document->getProperty(PropertyIds::CREATED_BY);
		if (NULL !== $currentAuthor) {
			return TRUE;
		}
		$authorUserUid = $recordAnalyzer->getAuthorUidFromRecord();
		if (NULL !== $authorUserUid) {
			$authorUserObject = $this->getCmisService()->resolveObjectByTableAndUid('be_users', $authorUserUid);
			if (NULL !== $authorUserObject) {
				$document->updateProperties(array(
					PropertyIds::CREATED_BY => $authorUserObject->getName()
				));
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * Synchronizes the relationships between $document and other
	 * CMIS objects, as detected by the data that was extracted.
	 *
	 * @param CmisObjectInterface $document CMIS object to use in relationships
	 * @param RecordAnalyzer $recordAnalyzer An instance of RecordAnalyzer for record
	 * @return void
	 */
	protected function synchronizeRelationships(CmisObjectInterface $document, RecordAnalyzer $recordAnalyzer) {
		$tableConfigurationAnalyzer = new TableConfigurationAnalyzer();
		$objectFactory = $this->getObjectFactory();
		$table = $recordAnalyzer->getTable();
		$logger = $objectFactory->getLogger();
		$record = $recordAnalyzer->getRecord();
		foreach ($recordAnalyzer->getIndexableColumnNames() as $fieldName) {
			$columnAnalyzer = $tableConfigurationAnalyzer->getColumnAnalyzerForField($table, $fieldName);
			if ($columnAnalyzer->isFieldDatabaseRelation()) {
				$relationData = $recordAnalyzer->getRelationDataForColumn($fieldName);
				$targetUids = $relationData->getTargetUids();
				$configuredTargetTable = $relationData->getTargetTable();
				$session = $this->getCmisObjectFactory()->getSession();
				foreach ($targetUids as $targetUid) {
					try {
						if (FALSE !== strpos($targetUid, '_')) {
							$parts = explode('_', $targetUid);
							$targetUid = (integer) array_pop($parts);
							$targetTable = implode('_', $parts);
						} else {
							$targetTable = $configuredTargetTable;
						}
						if (FALSE === $objectFactory->getConfiguration()->getTableConfiguration()->isTableEnabled($targetTable)) {
							$logger->warning(
								sprintf(
									'Table %s is not configured for indexing; this relation cannot be indexed!',
									$targetTable
								)
							);
							continue;
						}
						$cmisObjectId = $objectFactory->getCmisService()->getUuidForLocalRecord($table, $targetUid);
						$foreignObject = $session->getObject($session->createObjectId($cmisObjectId));
						$relationType = $relationData->getRelationObjectType($fieldName);
						$session->createRelationship(array(
							PropertyIds::NAME => 'Relation',
							PropertyIds::SOURCE_ID => $document->getId(),
							PropertyIds::TARGET_ID => $foreignObject->getId(),
							PropertyIds::OBJECT_TYPE_ID => $relationType
						));
						$logger->info(
							sprintf(
								'Relationship (%s) created between %s and %s',
								$relationType,
								$document->getId(),
								$foreignObject->getId()
							)
						);
					} catch (CmisObjectNotFoundException $error) {
						$logger->info(
							sprintf(
								'Record %d from table %s is not yet indexed by CMIS. Original error message: %s',
								$targetUid,
								$targetTable,
								$error->getMessage()
							)
						);
					}
				}
			} elseif ($columnAnalyzer->isFieldLegacyFileReference()) {
				// @TODO: detect the already "imported" legacy file placed in CMIS
				// Extracted by LegacyFileReferenceExtractor which executed during the
				// first indexing step (without relations). We now need these as UUIDs
				// that can be used in relationships with the CMIS document.
			}

			// Relationships extracted by Extractors
			foreach ($this->performAssociationExtraction($table, $fieldName, $record) as $association) {
				$relationTitle = sprintf(
					'Relationship (%s) between %s and %s',
					$association[PropertyIds::OBJECT_TYPE_ID],
					$document->getId(),
					$association[PropertyIds::TARGET_ID]
				);
				if (!isset($association[PropertyIds::SOURCE_ID])) {
					$association[PropertyIds::SOURCE_ID] = $document->getId();
				}
				if (!isset($association[PropertyIds::NAME])) {
					$association[PropertyIds::NAME] = $relationTitle;
				}
				$session->createRelationship($association);
				$logger->info('Created ' . $relationTitle);
			}
		}
	}

	/**
	 * Maps properties which require mapping, translating
	 * the name from the TYPO3 column name into a CMIS
	 * Document property name.
	 *
	 * @param array $data
	 * @param RecordAnalyzer $recordAnalyzer
	 * @return array
	 */
	protected function remapFieldsToDocumentProperties(array $data, RecordAnalyzer $recordAnalyzer) {
		$record = $recordAnalyzer->getRecord();
		$table = $recordAnalyzer->getTable();
		$uid = $record['uid'];
		$values = array(
			PropertyIds::NAME => $recordAnalyzer->getTitleForRecord(),
			Constants::CMIS_PROPERTY_RAWDATA => serialize($data)
		);
		/*
		$parentUid = $record['pid'];
		if (0 < $parentUid) {
			$values[PropertyIds::PARENT_ID] = $this->getCmisService()
				->resolveObjectByTableAndUid('pages', $parentUid)->getId();
		}
		*/
		$propertyMap = $this->getObjectFactory()->getConfiguration()->getTableConfiguration()->getSingleTableMapping($table);
		foreach ($propertyMap as $recordProperty => $cmisPropertyId) {
			$values[$cmisPropertyId] = $this->performTextExtraction($table, $recordProperty, $record);
		}
		$values[Constants::CMIS_PROPERTY_FULLTITLE] = $values[PropertyIds::NAME];
		$values[PropertyIds::NAME] = $this->getCmisService()->sanitizeTitle($values[PropertyIds::NAME], $table . '-' . $uid);
		$values[PropertyIds::LAST_MODIFICATION_DATE] = $recordAnalyzer->getLastModifiedDateTime();
		$values[PropertyIds::CREATION_DATE] = $recordAnalyzer->getCreationDateTime();
		return $values;
	}

	/**
	 * @param string $table
	 * @param string $field
	 * @oaram array $record
	 * @return string
	 */
	protected function performTextExtraction($table, $field, $record) {
		return $this->getExtractionMethodDetector()->resolveExtractionForColumn($table, $field)->extract($record[$field]);
	}

	/**
	 * @param string $table
	 * @param string $field
	 * @oaram array $record
	 * @return array[]
	 */
	protected function performAssociationExtraction($table, $field, $record) {
		return $this->getExtractionMethodDetector()->resolveExtractionForColumn($table, $field)->extractAssociations($record[$field], $table, $field);
	}

	/**
	 * @return ExtractionMethodDetector
	 */
	protected function getExtractionMethodDetector() {
		return new ExtractionMethodDetector();
	}

	/**
	 * @return integer|NULL
	 */
	protected function getCurrentBackendUserUid() {
		if (TRUE === isset($GLOBALS['BE_USER']) && !empty($GLOBALS['BE_USER']->user['uid'])) {
			return $GLOBALS['BE_USER']->user['uid'];
		}
		return NULL;
	}

}
