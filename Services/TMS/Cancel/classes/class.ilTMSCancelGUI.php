<?php
/**
 * cat-tms-patch start
 */

use ILIAS\TMS\Booking;

require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/TMS/Cancel/classes/class.ilTMSCancelPlayerStateDB.php");

/**
 * Displays the TMS booking 
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilTMSCancelGUI  extends Booking\Player {
	use ILIAS\TMS\MyUsersHelper;

	/**
	 * @var ilTemplate
	 */
	protected $g_tpl;

	/**
	 * @var ilCtrl
	 */
	protected $g_ctrl;

	/**
	 * @var ilObjUser
	 */
	protected $g_user;

	/**
	 * @var	ilLanguage
	 */
	protected $g_lng;

	/**
	 * @var	mixed
	 */
	protected $parent_gui;

	/**
	 * @var string
	 */
	protected $parent_cmd;

	public function __construct($parent_gui, $parent_cmd, $execute_show = true) {
		global $DIC;

		$this->g_tpl = $DIC->ui()->mainTemplate();
		$this->g_ctrl = $DIC->ctrl();
		$this->g_user = $DIC->user();
		$this->g_lng = $DIC->language();

		$this->g_lng->loadLanguageModule('tms');

		$this->parent_gui = $parent_gui;
		$this->parent_cmd = $parent_cmd;

		/**
		 * ToDo: Remove this flag.
		 * It's realy ugly, but we need it. If we get here by a plugin parent
		 * the plugin executes show by him self. So we don't need it here
		 */
		$this->execute_show = $execute_show;
	}

	public function executeCommand() {
		// TODO: Check if current user may book course for other user here.
		// assert('$this->g_user->getId() === $_GET["usr_id"]');
		if(!$this->canCancelForUser($_GET["usr_id"])) {
			$this->redirectToPreviousLocation(array("nope"), false);
		}

		assert('is_numeric($_GET["crs_ref_id"])');
		assert('is_numeric($_GET["usr_id"])');

		$crs_ref_id = (int)$_GET["crs_ref_id"];
		$usr_id = (int)$_GET["usr_id"];
		global $DIC;
		$process_db = new ilTMSCancelPlayerStateDB();

		$this->init($DIC, $crs_ref_id, $usr_id, $process_db);

		$this->g_ctrl->setParameterByClass("ilTMSCancelGUI", "crs_ref_id", $crs_ref_id);
		$this->g_ctrl->setParameterByClass("ilTMSCancelGUI", "usr_id", $usr_id);

		$cmd = $this->g_ctrl->getCmd("start");
		$content = $this->process($cmd, $_POST);
		assert('is_string($content)');
		$this->g_tpl->setContent($content);
		if($this->execute_show) {
			$this->g_tpl->show();
		}
	}

	// STUFF FROM Booking\Player

	/**
	 * @inheritdocs
	 */
	protected function getForm() {
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->g_ctrl->getFormAction($this));
		$form->setShowTopButtons(true);
		return $form;
	}

	/**
	 * @inheritdocs
	 */
	protected function txt($id) {
		if ($id === "abort") {
			$id = "cancel";
		}
		else if ($id === "next") {
			$id = "btn_next";
		}
		else if ($id == "aborted") {
			$id = "booking_aborted";
		}
		else if ($id == "previous") {
			$id = "btn_previous";
		}
		return $this->g_lng->txt($id);
	}

	/**
	 * @inheritdocs
	 */
	protected function redirectToPreviousLocation($messages, $success) {
		$this->g_ctrl->setParameterByClass("ilTMSCancelGUI", "crs_ref_id", null);
		$this->g_ctrl->setParameterByClass("ilTMSCancelGUI", "usr_id", null);
		if (count($messages)) {
			$message = join("<br/>", $messages);
			if ($success) {
				ilUtil::sendSuccess($message, true);
			}
			else {
				ilUtil::sendInfo($message, true);
			}
		}
		$this->g_ctrl->redirect($this->parent_gui, $this->parent_cmd);
	}

	/**
	 * @inheritdocs
	 */
	protected function getPlayerTitle() {
		assert('is_numeric($_GET["usr_id"])');
		$usr_id = (int)$_GET["usr_id"];

		if($usr_id === (int)$this->g_user->getId()) {
			return $this->g_lng->txt("canceling");
		}

		require_once("Services/User/classes/class.ilObjUser.php");
		return sprintf($this->g_lng->txt("canceling_for"), ilObjUser::_lookupFullname($usr_id));
	}

	/**
	 * @inheritdocs
	 */
	protected function getOverViewDescription() {
		return $this->g_lng->txt("cancel_overview_description");
	}

	/**
	 * @inheritdocs
	 */
	protected function getConfirmButtonLabel() {
		return $this->g_lng->txt("cancel_confirm");
	}

	/**
	 * Is current user allowed to cancel for
	 * Checks the current user is sperior of
	 *
	 * @param int 	$usr_id
	 *
	 * @return bool
	 */
	protected function canCancelForUser($usr_id) {
		if($this->g_user->getId() == $usr_id) {
			return true;
		}

		$employees = $this->getUsersWhereCurrentCanViewBookings((int)$this->g_user->getId());
		return array_key_exists($usr_id, $employees);
	}

}

/**
 * cat-tms-patch end
 */
