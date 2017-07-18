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
			//TODO: Permissions!
			case 'userInteraction':
			case 'getPopoverContent':
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

	protected function getPopoverContent() {
		global $DIC;

		/** @var ilBTPopOverGUI $gui */
		$gui = $DIC->backgroundTasks()->injector()->createInstance(ilBTPopOverGUI::class);

		$signalId = $_GET['replaceSignal'];
		$replaceSignal = new \ILIAS\UI\Implementation\Component\Popover\ReplaceContentSignal($signalId);
		$DIC->ctrl()->setParameterByClass(ilBTControllerGUI::class, 'replaceSignal', $signalId);
		$redirect_url = $_GET['from_url'];
		$replace_url = $DIC->ctrl()->getLinkTargetByClass([ilBTControllerGUI::class], "getPopoverContent", "", true);
		$button =$DIC->ui()->factory()->button()->standard('refresh', '#')
			->withOnClick(
				$replaceSignal->withAsyncRenderUrl($replace_url)
			);

		$components = [$button];
		$components = array_merge($components, $gui->getPopOverContent($DIC->user()->getId(), $redirect_url, $replace_url));

//		print_r($components);exit;
		$html = $DIC->ui()->renderer()->renderAsync(
			$gui->getPopOverContent($DIC->user()->getId(), $redirect_url, $replace_url));

		echo $html;
	}

	protected function url_origin( $s, $use_forwarded_host = false )
	{
		$ssl      = ( ! empty( $s['HTTPS'] ) && $s['HTTPS'] == 'on' );
		$sp       = strtolower( $s['SERVER_PROTOCOL'] );
		$protocol = substr( $sp, 0, strpos( $sp, '/' ) ) . ( ( $ssl ) ? 's' : '' );
		$port     = $s['SERVER_PORT'];
		$port     = ( ( ! $ssl && $port=='80' ) || ( $ssl && $port=='443' ) ) ? '' : ':'.$port;
		$host     = ( $use_forwarded_host && isset( $s['HTTP_X_FORWARDED_HOST'] ) ) ? $s['HTTP_X_FORWARDED_HOST'] : ( isset( $s['HTTP_HOST'] ) ? $s['HTTP_HOST'] : null );
		$host     = isset( $host ) ? $host : $s['SERVER_NAME'] . $port;
		return $protocol . '://' . $host;
	}

	public function full_url( $s, $use_forwarded_host = false )
	{
		return $this->url_origin( $s, $use_forwarded_host ) . $s['REQUEST_URI'];
	}
}