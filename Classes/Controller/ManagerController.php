<?php
namespace Dkd\CmisService\Controller;

use Dkd\CmisService\Execution\Result;
use Dkd\CmisService\Factory\ObjectFactory;
use Dkd\CmisService\Service\InteractionService;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class ManagerController
 */
class ManagerController extends ActionController {

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
	 * @return void
	 */
	public function indexAction() {
		$monitoredTables = $this->interactionService->getMonitoredTableNames();
		$targetedRecords = array_map(array($this->interactionService, 'countAllEnabledRecordsFromTable'), $monitoredTables);
		$this->view->assign('tables', array_combine(
			$monitoredTables,
			$targetedRecords
		));
		$numberOfIdentities = $this->interactionService->countIdentities();
		$numberOfQueuedTasks = $this->interactionService->countQueue();
		$totalTargetedRecords = array_sum($targetedRecords);
		$percentIndexed = $totalTargetedRecords == 0 ? 0 : round(($numberOfIdentities / $totalTargetedRecords) * 100);
		$percentQueued = $totalTargetedRecords == 0 ? 0 : min(round(($numberOfQueuedTasks / $totalTargetedRecords) * 100), 100 - $percentIndexed);
		$this->view->assign('status', array(
			'queued' => $numberOfQueuedTasks,
			'identities' => $numberOfIdentities,
			'remaining' => ($totalTargetedRecords - $numberOfIdentities),
			'total' => $totalTargetedRecords,
			'percent' => array(
				'indexed' => $percentIndexed,
				'queued' => $percentQueued,
				'remaining' => 100 - $percentQueued - $percentIndexed
			)
		));
	}

	/**
	 * @return void
	 */
	public function repositoriesAction() {
		$cmisServerNames = $this->interactionService->getConfiguredServerNames();
		$this->view->assign('servers', array(
			'configurations' => array_combine(
				$cmisServerNames,
				array_map(array($this->interactionService, 'getServerConfigurationByServerName'), $cmisServerNames)
			),
			'active' => $this->interactionService->getActiveServerName(),
			'status' => array_combine(
				$cmisServerNames,
				array_map(array($this->interactionService, 'checkServerConnection'), $cmisServerNames)
			)
		));
	}

	/**
	 * @return void
	 */
	public function tablesAction() {
		$monitoredTables = $this->interactionService->getMonitoredTableNames();
		$this->view->assign('monitored', array_combine(
			$monitoredTables,
			array_map(array($this->interactionService, 'getTableConfigurationByTableName'), $monitoredTables)
		));
	}

	/**
	 * @return void
	 */
	public function logAction() {

	}

	/**
	 * @return void
	 */
	public function refreshStatusAction() {
		$result = $this->interactionService->readQueueStatus();
		$this->addFlashMessageWithResult($result);
		$this->redirect('index');
	}

	/**
	 * @return void
	 */
	public function truncateQueueAction() {
		$result = $this->interactionService->truncateQueue();
		$this->addFlashMessageWithResult($result);
		$this->redirect('index');
	}

	/**
	 * @return void
	 */
	public function truncateIdentitiesAction() {
		$result = $this->interactionService->truncateIdentities();
		$this->addFlashMessageWithResult($result);
		$this->redirect('index');
	}

	/**
	 * @param string $table
	 * @return void
	 */
	public function generateIndexingTasksAction($table) {
		$result = $this->interactionService->createAndAddIndexingTasks($table);
		$this->addFlashMessageWithResult($result);
		$this->redirect('index');
	}

	/**
	 * @return void
	 */
	public function pickTaskAction() {
		$result = $this->interactionService->pickTask();
		$this->addFlashMessageWithResult($result);
		$this->redirect('index');
	}

	/**
	 * @param integer $tasks
	 * @return void
	 */
	public function pickTasksAction($tasks) {
		$results = $this->interactionService->pickTasks($tasks);
		$this->addFlashMessageWithResults($results);
		$this->redirect('index');
	}

	/**
	 * @param Result $result
	 * @return void
	 */
	protected function addFlashMessageWithResult(Result $result) {
		$this->addFlashMessage($result->getMessage(), 'Action performed', $result->getCode());
	}

	/**
	 * @param Result[] $results
	 * @return void
	 */
	protected function addFlashMessageWithResults(array $results) {
		$code = FlashMessage::OK;
		$message = '<ul>';
		foreach ($results as $result) {
			$code = max($code, $result->getCode());
			$message .= '<li>' . $result->getMessage() . '</li>';
		}
		$message .= '</ul>';
		$this->addFlashMessage($message, sprintf('Actions performed (%d)', count($results)), $code);
	}

}
