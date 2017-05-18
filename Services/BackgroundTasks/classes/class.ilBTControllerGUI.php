<?php
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionOption;

/**
 * Class ilBTControllerGUI
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 *
 */
class ilBTControllerGUI {

	/** @var  ilCtrl */
	protected $ctrl;

	public function __construct() {
		global $ilCtrl;

		$this->ctrl = $ilCtrl;
	}


	public function executeCommand() {
		switch ($this->ctrl->getCmdClass()) {
			default:
				$this->performCommand();
		}
	}

	protected function performCommand() {
		$cmd = $this->ctrl->getCmd();
		switch ($cmd) {
			case 'userInteraction':
				$this->$cmd();
		}
	}

	protected function userInteraction() {
		global $DIC;

		$observer_id = (int) $_GET['observer_id'];
		$selected_option = $_GET['selected_option'];
		$from_url = urldecode($_GET['from_url']);

		$observer = $DIC->backgroundTasks()->persistence()->loadBucket($observer_id);
		$option = new UserInteractionOption("", $selected_option);
		$DIC->backgroundTasks()->taskManager()->continueTask($observer, $option);

		ilUtil::redirect($from_url);
	}
}