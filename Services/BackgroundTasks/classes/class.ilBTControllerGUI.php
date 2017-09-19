<?php

use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionOption;

require_once("./Services/BackgroundTasks/classes/class.ilBTPopOverGUI.php");

/**
 * Class ilBTControllerGUI
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 *
 */
class ilBTControllerGUI {

	const CMD_QUIT = 'quitBucket';
	const CMD_GET_POPOVER_CONTENT = 'getPopoverContent';
	const CMD_USER_INTERACTION = 'userInteraction';
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
			case self::CMD_USER_INTERACTION:
			case self::CMD_GET_POPOVER_CONTENT:
			case self::CMD_QUIT:
				$this->$cmd();
		}
	}


	protected function userInteraction() {
		global $DIC;

		$observer_id = (int)$_GET['observer_id'];
		$selected_option = $_GET['selected_option'];
		$from_url = urldecode($_GET['from_url']);

		$observer = $DIC->backgroundTasks()->persistence()->loadBucket($observer_id);
		$option = new UserInteractionOption("", $selected_option);
		$DIC->backgroundTasks()->taskManager()->continueTask($observer, $option);

		ilUtil::redirect($from_url);
	}


	protected function quitBucket() {
		global $DIC;

		$observer_id = (int)$_GET['observer_id'];
		$from_url = urldecode($_GET['from_url']);

		$bucket = $DIC->backgroundTasks()->persistence()->loadBucket($observer_id);

		$DIC->backgroundTasks()->taskManager()->quitBucket($bucket);

		ilUtil::redirect($from_url);
	}


	protected function getPopoverContent() {
		global $DIC;

		/** @var ilBTPopOverGUI $gui */
		$gui = $DIC->backgroundTasks()->injector()->createInstance(ilBTPopOverGUI::class);
		$signalId = $_GET['replaceSignal'];
		//		$replaceSignal = new \ILIAS\UI\Implementation\Component\Popover\ReplaceContentSignal($signalId);
		$DIC->ctrl()->setParameterByClass(ilBTControllerGUI::class, 'replaceSignal', $signalId);
		$redirect_url = $_GET['from_url'];
		$replace_url = $DIC->ctrl()
		                   ->getLinkTargetByClass([ ilBTControllerGUI::class ], self::CMD_GET_POPOVER_CONTENT, "", true);

		echo $DIC->ui()->renderer()->renderAsync($gui->getPopOverContent($DIC->user()
		                                                                     ->getId(), $redirect_url, $replace_url));
	}


	/**
	 * @param      $s
	 * @param bool $use_forwarded_host
	 *
	 * @return string
	 */
	private function url_origin($s, $use_forwarded_host = false) {
		$ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on');
		$sp = strtolower($s['SERVER_PROTOCOL']);
		$protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
		$port = $s['SERVER_PORT'];
		$port = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
		$host = ($use_forwarded_host
		         && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
		$host = isset($host) ? $host : $s['SERVER_NAME'] . $port;

		return $protocol . '://' . $host;
	}


	/**
	 * @param      $s
	 * @param bool $use_forwarded_host
	 *
	 * @return string
	 */
	public function full_url($s, $use_forwarded_host = false) {
		return $this->url_origin($s, $use_forwarded_host) . $s['REQUEST_URI'];
	}
}