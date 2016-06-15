<?php
namespace Dkd\CmisService\Execution\Cmis;

use Dkd\CmisService\Analysis\TableConfigurationAnalyzer;
use Dkd\CmisService\Constants;
use Dkd\CmisService\Execution\ExecutionInterface;
use Dkd\CmisService\Execution\Result;
use Dkd\CmisService\Task\RecordImportTask;
use Dkd\CmisService\Task\TaskInterface;
use Dkd\PhpCmis\CmisObject\CmisObjectInterface;
use Dkd\PhpCmis\Data\FileableCmisObjectInterface;
use Dkd\PhpCmis\Data\ItemInterface;

/**
 * Class ImportExecution
 *
 * Performs import of one CMIS object to one
 * TYPO3 record, saves the UUID mapping when done.
 */
class ImportExecution extends AbstractCmisExecution implements ExecutionInterface {

    /**
     * Execute the import task
     *
     * @param TaskInterface $task
     * @return Result
     */
    public function execute(TaskInterface $task) {
        $table = $task->getParameter(RecordImportTask::OPTION_TABLE);
        $cmisObjectUuid = $task->getParameter(RecordImportTask::OPTION_SOURCE);
        $object = $this->getCmisService()->resolveObjectByUuid($cmisObjectUuid);
        $extractedPropertyValues = $this->extractObjectPropertyValuesBasedOnIndexingConfiguration($table, $object);

        if (!$object instanceof FileableCmisObjectInterface) {
            $storagePid = 0;
            // Note aboute this condition: CMIS "Items" are also fileable objects so this does cover all
            // conceivable supported records for TYPO3 (since they all have a PID), but on the off chance that
            // we are asked to import an object that is *not* fileable, we must check this and use 0 as default.
        } else if (count($object->getParents())) {
            // Object is file and has (at least one) parent, we can resolve the record, but must check if it
            // is or is not indexed.
            $indexedParentUuid = reset($object->getParents())->getId();
            $indexedParentRecord = $this->getCmisService()->getRecordForCmisUuid($indexedParentUuid);
            $storagePid = (integer) (isset($indexedParentRecord['uid']) ? $indexedParentRecord['uid'] : 0);
        } else {
            // The object is, for some reason, not yet filed under any parent that we can access.
            // Put it in root, even if only temporarily (see comments above).
            $storagePid = 0;
        }

        $alreadyMappedRecord = $this->getCmisService()->getRecordForCmisUuid($cmisObjectUuid);
        // Condition: if record not already mapped, create it. If mapped, replace current column values and save it.
        if (!$alreadyMappedRecord) {
            // The record is NOT yet mapped - but it might exist if we check for CMIS properties that
            // recorded the original UID of the record. If that record can be loaded we try to use that as base.
            $alreadyMappedRecord = $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
                '*',
                $table,
                sprintf(
                    'uid = %d',
                    $object->getPropertyValue(Constants::CMIS_PROPERTY_TYPO3UID)
                )
            );
        }

        $recordDataFromCmisObject = unserialize($object->getPropertyValue(Constants::CMIS_PROPERTY_RAWDATA));

        if ($alreadyMappedRecord) {
            $recordData = array_replace(
                (array) $recordDataFromCmisObject,
                (array) $alreadyMappedRecord,
                $extractedPropertyValues
            );
        } else {
            $recordData = array_replace(
                (array) $recordDataFromCmisObject,
                $extractedPropertyValues
            );
        }

        // Extract the title; look in the dedicated title column or fall back to CMIS name (which may
        // be a sanitised version of an original title - one we cannot detect or was left empty)
        $tableAnalyzer = new TableConfigurationAnalyzer();
        $labelColumnName = reset($tableAnalyzer->getLabelFieldListFromTable($table));
        $title = $object->getPropertyValue(Constants::CMIS_PROPERTY_FULLTITLE);
        if (!$title) {
            $title = $object->getName();
        }
        $recordData[$labelColumnName] = $title;

        $recordData = $this->filterRecordFieldsByDefinedTca($recordData, $table);

        $recordUid = $this->updateOrCreateRecordInStoragePageUid($recordData, $table, $storagePid);

        return new Result(
            sprintf(
                'Imported record %s:%d from CMIS object %s',
                $table,
                $recordUid,
                $cmisObjectUuid
            ),
            Result::OK
        );
    }

    /**
     * Extracts from CMIS object all properties which are defined
     * in indexing configuration for table. Returns only columns
     * that are configured.
     *
     * @param string $table
     * @param CmisObjectInterface $object
     * @return array
     */
    protected function extractObjectPropertyValuesBasedOnIndexingConfiguration($table, CmisObjectInterface $object) {
        $values = array();
        $configuration = $this->getObjectFactory()->getConfiguration()->getTableConfiguration()->getSingleTableConfiguration($table);
        if (isset($configuration['mapping'])) {
            foreach ((array) $configuration['mapping'] as $recordColumnName => $cmisPropertyId) {
                $values[$recordColumnName] = $object->getPropertyValue($cmisPropertyId);
            }
        }
        return $values;
    }

    /**
     * Creates or updates, as detected based on input record
     * containing an UID or not, record in table while setting
     * "pid" to $storagePageUid. Returns the record's UID.
     *
     * @param array $record
     * @param string $table
     * @param integer $storagePageUid
     * @return integer
     */
    protected function updateOrCreateRecordInStoragePageUid(array $record, $table, $storagePageUid) {
        $record['pid'] = $storagePageUid;
        $databaseConnection = $this->getDatabaseConnection();
        if (!isset($record['uid']) || $record['uid'] < 1) {
            $databaseConnection->exec_INSERTquery($table, $record);
            return $databaseConnection->sql_insert_id();
        }
        $databaseConnection->exec_UPDATEquery($table, sprintf('uid = %d', $record['uid']), $record);
        return (integer) $record['uid'];
    }

    /**
     * Filters the input array, removing any columns that are
     * not defined in the TCA of this site. Avoids sending those
     * fields to the SQL update - in case CMIS object contains
     * more properties than the current TYPO3 site supports, e.g.
     * properties from 3rd party extensions that are not installed.
     *
     * @param array $record
     * @param string $table
     * @return array
     */
    protected function filterRecordFieldsByDefinedTca(array $record, $table) {
        $filtered = array();
        foreach ($record as $key => $value) {
            if (isset($GLOBALS['TCA'][$table]['columns'][$key])) {
                $filtered[$key] = $value;
            }
        }
        return $filtered;
    }

}
