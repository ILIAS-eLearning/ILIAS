<?php
/**
 * cat-tms-patch start
 */

namespace ILIAS\TMS\Booking;

use ILIAS\TMS\Wizard;

require_once("Services/Form/classes/class.ilPropertyFormGUI.php");

/**
 * ILIAS Bindings for TMS-Booking process.
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ILIASBindings implements Wizard\ILIASBindings {
	/**
	 * @var	ilLanguage
	 */
	protected $lng;

	/**
	 * @var	ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var object
	 */
	protected $gui;

	/**
	 * @var object
	 */
	protected $parent_gui;

	/**
	 * @var string
	 */
	protected $parent_cmd;

	/**
	 * @var string
	 */
	protected $player_title;

	/**
	 * @var string
	 */
	protected $confirm_button_label;

	/**
	 * @var string
	 */
	protected $overview_description;

	final public function __construct(\ilLanguage $lng, \ilCtrl $ctrl, $gui, $parent_gui, $parent_cmd, $player_title, $confirm_button_label, $overview_description) {
		assert('is_object($gui)');
		assert('is_object($parent_gui)');
		assert('is_string($parent_cmd)');
		assert('is_string($confirm_button_label)');
		assert('is_string($player_title)');
		assert('is_string($overview_description)');
		$this->lng = $lng;
		$this->ctrl = $ctrl;
		$this->lng->loadLanguageModule('tms');
		$this->gui = $gui;
		$this->parent_gui = $parent_gui;
		$this->parent_cmd = $parent_cmd;
		$this->player_title = $player_title;
		$this->confirm_button_label = $confirm_button_label;
		$this->overview_description = $overview_description;
	}

	/**
	 * @inheritdocs
	 */
	public function getForm() {
		$form = new \ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this->gui));
		$form->setShowTopButtons(true);
		return $form;
	}

	/**
	 * @inheritdocs
	 */
	public function txt($id) {
		if ($id === "abort") {
			$id = "cancel";
		}
		else if ($id === "next") {
			$id = "btn_next";
		}
		else if ($id == "aborted") {
			$id = "process_aborted";
		}
		else if ($id == "previous") {
			$id = "btn_previous";
		}
		else if ($id == "title") {
			return $this->player_title;
		}
		else if ($id == "confirm") {
			return $this->confirm_button_label;
		}
		else if ($id == "overview_description") {
			return $this->overview_description;
		}
		else if ($id == "no_steps_available") {
			$id = "booking_not_available";
		}

		return $this->lng->txt($id);
	}

	/**
	 * @inheritdocs
	 */
	public function redirectToPreviousLocation($messages, $success) {
		if (count($messages)) {
			$message = join("<br/>", $messages);
			if ($success) {
				\ilUtil::sendSuccess($message, true);
			}
			else {
				\ilUtil::sendInfo($message, true);
			}
		}
		$this->ctrl->redirect($this->parent_gui, $this->parent_cmd);
	}
}

/**
 * cat-tms-patch end
 */
