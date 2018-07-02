<?php
/**
 * cat-tms-patch start
 */

use ILIAS\TMS\Booking;
use ILIAS\TMS\Wizard;

require_once("Services/Form/classes/class.ilPropertyFormGUI.php");

/**
 * Displays the TMS booking
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
abstract class ilTMSCancelGUI  extends Wizard\Player {
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

	/**
	 * @var ilAppEventHandler
	 */
	protected $g_event_handler;

	public function __construct($parent_gui, $parent_cmd, $execute_show = true) {
		global $DIC;

		$this->g_tpl = $DIC->ui()->mainTemplate();
		$this->g_ctrl = $DIC->ctrl();
		$this->g_user = $DIC->user();
		$this->g_lng = $DIC->language();
		$this->g_event_handler = $DIC['ilAppEventHandler'];
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
		assert('is_numeric($_GET["usr_id"])');
		$usr_id = (int)$_GET["usr_id"];

		if(!$this->canCancelForUser($usr_id)) {
			$this->redirectToPreviousLocation(array("nope"), false);
		}

		assert('is_numeric($_GET["crs_ref_id"])');
		$crs_ref_id = (int)$_GET["crs_ref_id"];

		$ilias_bindings = new Booking\ILIASBindings
			( $this->g_lng
			, $this->g_ctrl
			, $this
			, $this->parent_gui
			, $this->parent_cmd
			, $this->getPlayerTitle()
			, $this->getConfirmButtonLabel()
			, $this->getOverViewDescription()
			);

		global $DIC;
		$state_db = new Wizard\SessionStateDB();
		$wizard = new Booking\Wizard
			( $DIC
			, $this->getComponentClass()
			, (int)$this->g_user->getId()
			, $crs_ref_id
			, $usr_id
			, $this->getOnFinishClosure()
			);
		$player = new Wizard\Player
			( $ilias_bindings
			, $wizard
			, $state_db
			);

		$this->setParameter($crs_ref_id, $usr_id);

		$cmd = $this->g_ctrl->getCmd("start");
		$content = $player->run($cmd, $_POST);
		assert('is_string($content)');
		$this->g_tpl->setContent($content);
		if($this->execute_show) {
			$this->g_tpl->show();
		}
	}

	/**
	 * Execute this when the player is finished.
	 *
	 * @param int 	$acting_usr_id
	 * @param int 	$target_usr_id
	 * @param int 	$crs_ref_id
	 * @return void
	 */
	abstract protected function callOnFinish($acting_usr_id, $target_usr_id, $crs_ref_id);

	/**
	 * Wrap callOnFinish to be called from the Wizard.
	 *
	 * @return callable
	 */
	protected function getOnFinishClosure() {
		return function($acting_usr_id, $target_usr_id, $crs_ref_id) {
			return $this->callOnFinish($acting_usr_id, $target_usr_id, $crs_ref_id);
		};
	}

	/**
	 * Lookup the course's obj_id.
	 * @param int 	$crs_ref_id
	 * @return int
	 */
	protected function lookupObjId($crs_ref_id) {
		assert('is_int($crs_ref_id)');
		$crs_obj_id = (int)\ilObject::_lookupObjId($crs_ref_id);
		return $crs_obj_id;
	}

	/**
	 * Get the title of the player.
	 *
	 * @return string
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
	 * Get a description for the overview step.
	 *
	 * @return string
	 */
	protected function getOverViewDescription() {
		return $this->g_lng->txt("cancel_overview_description");
	}

	/**
	 * Get the label for the confirm button.
	 *
	 * @return string
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

	/**
	 * Raises an event with course ids and user id as params.
	 * @param string 	$event
	 * @param int 	$usr_id
	 * @param int 	$crs_ref_id
	 * @return void
	 */
	protected function fireBookingEvent($event, $usr_id, $crs_ref_id) {
		assert('is_string($event)');
		assert('is_int($usr_id)');
		assert('is_int($crs_ref_id)');

		$crs_obj_id = $this->lookupObjId($crs_ref_id);
		$this->g_event_handler->raise(
			'Modules/Course',
			$event,
			array(
				 'crs_ref_id' => $crs_ref_id,
				 'obj_id' => $crs_obj_id,
				 'usr_id' => $usr_id
			 )
		 );
	}

}

/**
 * cat-tms-patch end
 */
