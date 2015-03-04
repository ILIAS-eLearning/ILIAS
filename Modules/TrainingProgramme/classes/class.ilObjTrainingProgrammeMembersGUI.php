<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Modules/TrainingProgramme/classes/class.ilObjTrainingProgramme.php");

/**
 * Class ilObjTrainingProgrammeMembersGUI
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 *
 */

class ilObjTrainingProgrammeMembersGUI {
	/**
	 * @var ilCtrl
	 */
	public $ctrl;
	
	/**
	 * @var ilTemplate
	 */
	public $tpl;
	
	/**
	 * @var ilAccessHandler
	 */
	protected $ilAccess;
	
	/**
	 * @var ilObjTrainingProgramme
	 */
	public $object;
	
	/**
	 * @var ilLog
	 */
	protected $ilLog;
	
	/**
	 * @var Ilias
	 */
	public $ilias;

	/**
	 * @var ilLng
	 */
	public $lng;

	public function __construct($a_parent_gui, $a_ref_id) {
		global $tpl, $ilCtrl, $ilAccess, $ilToolbar, $ilLocator, $tree, $lng, $ilLog, $ilias;

		$this->ref_id = $a_ref_id;
		$this->parent_gui = $a_parent_gui;
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->ilAccess = $ilAccess;
		$this->ilLocator = $ilLocator;
		$this->tree = $tree;
		$this->toolbar = $ilToolbar;
		$this->ilLog = $ilLog;
		$this->ilias = $ilias;
		$this->lng = $lng;
		
		$this->object = null;

		$lng->loadLanguageModule("prg");
	}
	
	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		
		if ($cmd == "") {
			$cmd = "view";
		}
		
		switch ($cmd) {
			case "view":
			case "update":
				$cont = $this->$cmd();
				break;
			default:
				throw new ilException("ilObjTrainingProgrammeSettingsGUI: ".
									  "Command not supported: $cmd");
		}
		
		$this->tpl->setContent($cont);
	}
	
	protected function view() {
		$form = $this->buildForm();
		$this->fillForm($form);
		return $form->getHTML();
	}
	
	protected function update() {
		$form = $this->buildForm();
		$form->setValuesByPost();
		if ($this->checkForm($form)) {
			$this->updateFromFrom($form);
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"));
		}
		else {
			// TODO:
			ilUtil::sendFailure($this->lng->txt("TODO"));
		}
		return $form->getHTML();
	}
	
	const PROP_TITLE = "title";
	const PROP_DESC = "desc";
	const PROP_TYPE = "type";
	const PROP_POINTS = "points";
	const PROP_STATUS = "status";

	protected function buildForm() {
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->lng->txt("prg_edit"));
		$form->addItem($header);
		
		$item = new ilTextInputGUI($this->lng->txt("title"), self::PROP_TITLE);
		$form->addItem($item);
		
		$item = new ilTextAreaInputGUI($this->lng->txt("description"), self::PROP_DESC);
		$form->addItem($item);
		
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->lng->txt("prg_type"));
		$form->addItem($header);
		
		$item = new ilSelectInputGUI($this->lng->txt("type"), self::PROP_TYPE);
		$form->addItem($item);
		
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->lng->txt("prg_assessment"));
		$form->addItem($header);
		
		$item = new ilNumberInputGUI($this->lng->txt("prg_points"), self::PROP_POINTS);
		$item->setMinValue(0);
		$form->addItem($item);
		
		$item = new ilSelectInputGUI($this->lng->txt("prg_status"), self::PROP_STATUS);
		$item->setOptions(self::getStatusOptions());
		$form->addItem($item);
		
		$form->addCommandButton("update", $this->lng->txt("save"));
		$form->addCommandButton("cancel", $this->lng->txt("cancel"));
		
		return $form;
	}
	
	protected function getObject() {
		if ($this->object === null) {
			$this->object = ilObjTrainingProgramme::getInstanceByRefId($this->ref_id);
		}
		return $this->object;
	}
	
	protected function fillForm($a_form) {
		$obj = $this->getObject();
		
		$a_form->setValuesByArray(array
			( self::PROP_TITLE => $obj->getTitle()
			, self::PROP_DESC => $obj->getDescription()
			// TODO: , self::PROP_TYPE
			, self::PROP_POINTS => $obj->getPoints()
			, self::PROP_STATUS => $obj->getStatus()
			));
	}
	
	protected function checkForm($a_form) {
		if (!$a_form->checkInput()) {
			return false;
		}
		return true;
	}
	
	protected function updateFromFrom($a_form) {
		$obj = $this->getObject();
		
		$obj->setTitle($a_form->getItemByPostVar(self::PROP_TITLE)->getValue());
		$obj->setDescription($a_form->getItemByPostVar(self::PROP_DESC)->getValue());
		//TODO: $obj->setType($a_form->getItemByPostVar(self::PROP_TYPE)->getValue());
		$obj->setPoints($a_form->getItemByPostVar(self::PROP_POINTS)->getValue());
		$obj->setStatus($a_form->getItemByPostVar(self::PROP_STATUS)->getValue());
	}
	
	static protected function getStatusOptions() {
		global $lng;
		
		return array( ilTrainingProgramme::STATUS_DRAFT 
						=> $lng->txt("prg_status_draft")
					, ilTrainingProgramme::STATUS_ACTIVE
						=> $lng->txt("prg_status_active")
					, ilTrainingProgramme::STATUS_OUTDATED
						=> $lng->txt("prg_status_outdated")
					);
	}
}

?>