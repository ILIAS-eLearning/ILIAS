<?php

use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionOption;
use ILIAS\Modules\OrgUnit\ARHelper\DIC;

/**
 * Class ilBTControllerGUI
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBTControllerGUI {

	use DIC;
	const FROM_URL = 'from_url';
	const OBSERVER_ID = 'observer_id';
	const SELECTED_OPTION = 'selected_option';
	const REPLACE_SIGNAL = 'replaceSignal';
	const CMD_QUIT = 'quitBucket';
	const CMD_GET_POPOVER_CONTENT = 'getPopoverContent';
	const CMD_USER_INTERACTION = 'userInteraction';


	public function executeCommand() {
		switch ($this->ctrl()->getCmdClass()) {
			default:
				$this->performCommand();
		}
	}


	protected function performCommand() {
		$cmd = $this->ctrl()->getCmd();
		switch ($cmd) {
			case self::CMD_USER_INTERACTION:
			case self::CMD_GET_POPOVER_CONTENT:
			case self::CMD_QUIT:
				$this->$cmd();
		}
	}


	protected function userInteraction() {
		$observer_id = (int)$this->http()->request()->getQueryParams()[self::OBSERVER_ID];
		$selected_option = $this->http()->request()->getQueryParams()[self::SELECTED_OPTION];
		$from_url = urldecode($this->http()->request()->getQueryParams()[self::FROM_URL]);

		$observer = $this->dic()->backgroundTasks()->persistence()->loadBucket($observer_id);
		$option = new UserInteractionOption("", $selected_option);
		$this->dic()->backgroundTasks()->taskManager()->continueTask($observer, $option);
		$this->ctrl()->redirectToURL($from_url);
	}


	protected function quitBucket() {
		$observer_id = (int)$this->http()->request()->getQueryParams()[self::OBSERVER_ID];
		$from_url = urldecode($this->http()->request()->getQueryParams()[self::FROM_URL]);

		$bucket = $this->dic()->backgroundTasks()->persistence()->loadBucket($observer_id);

		$this->dic()->backgroundTasks()->taskManager()->quitBucket($bucket);

		$this->ctrl()->redirectToURL($from_url);
	}


	protected function getPopoverContent() {
		/** @var ilBTPopOverGUI $gui */
		$gui = $this->dic()->backgroundTasks()->injector()->createInstance(ilBTPopOverGUI::class);
		$signal_id = $this->http()->request()->getQueryParams()[self::REPLACE_SIGNAL];

		$this->ctrl()
		     ->setParameterByClass(ilBTControllerGUI::class, self::REPLACE_SIGNAL, $signal_id);
		$redirect_url = $this->http()->request()->getQueryParams()[self::FROM_URL];
		$replace_url = $this->ctrl()
		                    ->getLinkTargetByClass([ ilBTControllerGUI::class ], self::CMD_GET_POPOVER_CONTENT, "", true);

		echo $this->ui()->renderer()->renderAsync($gui->getPopOverContent($this->user()
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