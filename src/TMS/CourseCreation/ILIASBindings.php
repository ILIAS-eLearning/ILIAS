<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\CourseCreation;

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
	 * @var string[] 
	 */
	protected $parent_guis;

	/**
	 * @var string
	 */
	protected $parent_cmd;

	/**
	 * @var int
	 */
	protected $parent_ref_id;

	final public function __construct(\ilLanguage $lng, \ilCtrl $ctrl, $gui, array $parent_guis, $parent_cmd, $parent_ref_id) {
		assert('is_object($gui)');
		assert('is_string($parent_cmd)');
		$this->lng = $lng;
		$this->ctrl = $ctrl;
		$this->lng->loadLanguageModule('tms');
		$this->gui = $gui;
		$this->parent_guis = $parent_guis;
		$this->parent_cmd = $parent_cmd;
		$this->parent_ref_id = $parent_ref_id;
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
			$id = "create_course_from_template";	
		}
		else if ($id == "overview_description") {
			$id = $summary;
		}
		else if ($id == "confirm") {
			$id = "create_course";
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
		$last_gui = $this->parent_guis[count($this->parent_guis)-1];
		$this->ctrl->setParameterByClass($last_gui, "ref_id", $this->parent_ref_id);
		$this->ctrl->redirectByClass($this->parent_guis, $this->parent_cmd);
	}
}
