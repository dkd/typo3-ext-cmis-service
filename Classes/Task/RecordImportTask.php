<?php
namespace Dkd\CmisService\Task;

use Dkd\CmisService\Factory\ExecutionFactory;

/**
 * Class RecordImportTask
 *
 * Imports an object from CMIS to a record in
 * TYPO3. Uses reversed indexing mapping to detect
 * the target table based on the type of the source.
 *
 * Only handles the importing - import tasks can be
 * created from any external point via CmisService
 * and are then processed via the queue.
 */
class RecordImportTask extends AbstractTask {

    const OPTION_SOURCE = 'source';
    const OPTION_TABLE = 'table';

    /**
     * Returns an Execution object for indexing the
     * record as configured by Task's options.
     *
     * @return ExcecutionInterface
     */
    public function resolveExecutionObject() {
        $executionFactory = new ExecutionFactory();
        return $executionFactory->createImportExecution();
    }

    /**
     * Returns TRUE if this Task matches $task
     *
     * @param TaskInterface $task
     * @return boolean
     */
    public function matches(TaskInterface $task) {
        return ($task->getParameter(static::OPTION_SOURCE) === $this->getParameter(static::OPTION_SOURCE));
    }

    /**
     * Returns the `table:uid` format identifying the
     * record being indexed.
     *
     * @return string
     */
    public function getResourceId() {
        return $this->getParameter(self::OPTION_SOURCE);
    }

}
