<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("./Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php");
require_once("./Modules/StudyProgramme/classes/helpers/class.ilAsyncOutputHandler.php");
require_once("./Modules/StudyProgramme/classes/helpers/class.ilAsyncPropertyFormGUI.php");
require_once("./Services/UIComponent/Button/classes/class.ilLinkButton.php");

/**
 * Class ilObjStudyProgrammeSettingsGUI
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 *
 */

class ilObjStudyProgrammeSettingsGUI {
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
	 * @var ilObjStudyProgramme
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

	/**
	 * @var ilObjStudyProgrammeGUI
	 */
	protected $parent_gui;

	/**
	 * @var string
	 */
	protected $tmp_heading;

	public function __construct($a_parent_gui, $a_ref_id) {
		global $tpl, $ilCtrl, $ilAccess, $ilToolbar, $ilLocator, $tree, $lng, $ilLog, $ilias;

		$this->parent_gui = $a_parent_gui;
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
			case "cancel":
				$content = $this->$cmd();
				break;
			default:
				throw new ilException("ilObjStudyProgrammeSettingsGUI: ".
									  "Command not supported: $cmd");
		}

		if(!$this->ctrl->isAsynch()) {
			$this->tpl->setContent($content);
		} else {
			$output_handler = new ilAsyncOutputHandler();
			$heading = $this->lng->txt("prg_async_".$this->ctrl->getCmd());
			if(isset($this->tmp_heading)) {
				$heading = $this->tmp_heading;
			}
			$output_handler->setHeading($heading);
			$output_handler->setContent($content);
			$output_handler->terminate();
		}
	}
	
	protected function view() {
		$this->buildModalHeading($this->lng->txt('prg_async_settings'),isset($_GET["currentNode"]));

		$form = $this->buildForm();
		$this->fillForm($form);
		return $form->getHTML();
	}
	
	/*protected function cancel() {
		$this->ctrl->redirect($this->parent_gui);
	}*/
	
	protected function update() {

		$form = $this->buildForm();
		$form->setValuesByPost();
		$update_possible = $this->checkForm($form);

		if ($update_possible) {
			$this->updateFromFrom($form);
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"),true);
			$response = ilAsyncOutputHandler::encodeAsyncResponse(array("success"=>true, "message"=>$this->lng->txt("msg_obj_modified")));
		} else {
			// TODO:
			ilUtil::sendFailure($this->lng->txt("msg_form_save_error"));
			$response = ilAsyncOutputHandler::encodeAsyncResponse(array("success"=>false, "errors"=>$form->getErrors()));
		}

		if($this->ctrl->isAsynch()) {
			return ilAsyncOutputHandler::handleAsyncOutput($form->getHTML(), $response, false);
		} else {
			if($update_possible) {
				$this->ctrl->redirect($this);
			} else {
				return $form->getHTML();
			}
		}
	}

	protected function cancel() {
		ilAsyncOutputHandler::handleAsyncOutput(ilAsyncOutputHandler::encodeAsyncResponse());

		$this->ctrl->redirect($this->parent_gui);
	}

	protected function buildModalHeading($label, $current_node) {
		if(!$current_node) {
			$this->ctrl->saveParameterByClass('ilobjstudyprogrammesettingsgui', 'ref_id');
			$heading_button = ilLinkButton::getInstance();
			$heading_button->setCaption('prg_open_node');
			$heading_button->setUrl($this->ctrl->getLinkTargetByClass('ilobjstudyprogrammetreegui', 'view'));

			$heading = "<div class=''>".$label."<div class='pull-right'>".$heading_button->render()."</div></div>";
			$this->tmp_heading = $heading;
		} else {
			$this->tmp_heading = "<div class=''>".$label."</div>";
		}
		
	}
	
	const PROP_TITLE = "title";
	const PROP_DESC = "desc";
	const PROP_TYPE = "type";
	const PROP_POINTS = "points";
	const PROP_STATUS = "status";

	protected function buildForm() {
		$form = new ilAsyncPropertyFormGUI();

		if(!$this->ctrl->isAsynch()) {
			$form->setAsync(false);
		}

		$form->setFormAction($this->ctrl->getFormAction($this));
		
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->lng->txt("prg_edit"));
		$form->addItem($header);
		
		$item = new ilTextInputGUI($this->lng->txt("title"), self::PROP_TITLE);
		$item->setRequired(true);
		$form->addItem($item);
		
		$item = new ilTextAreaInputGUI($this->lng->txt("description"), self::PROP_DESC);
		$form->addItem($item);
		
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->lng->txt("prg_type"));
		$form->addItem($header);
		
		$item = new ilSelectInputGUI($this->lng->txt("type"), self::PROP_TYPE);
		$item->setOptions(ilStudyProgrammeType::getAllTypesArray());
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
			$this->object = ilObjStudyProgramme::getInstanceByRefId($this->ref_id);
		}
		return $this->object;
	}
	
	protected function fillForm($a_form) {
		$obj = $this->getObject();
		
		$a_form->setValuesByArray(array
			( self::PROP_TITLE => $obj->getTitle()
			, self::PROP_DESC => $obj->getDescription()
			, self::PROP_TYPE => $obj->getSubtypeId()
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

		if($obj->getSubtypeId() != $a_form->getItemByPostVar(self::PROP_TYPE)->getValue()) {
			$obj->setSubtypeId($a_form->getItemByPostVar(self::PROP_TYPE)->getValue());
			$obj->updateCustomIcon();
			$this->parent_gui->setTitleAndDescription();
		}

		$obj->setPoints($a_form->getItemByPostVar(self::PROP_POINTS)->getValue());
		$obj->setStatus($a_form->getItemByPostVar(self::PROP_STATUS)->getValue());
	}
	
	static protected function getStatusOptions() {
		global $lng;
		
		return array( ilStudyProgramme::STATUS_DRAFT 
						=> $lng->txt("prg_status_draft")
					, ilStudyProgramme::STATUS_ACTIVE
						=> $lng->txt("prg_status_active")
					, ilStudyProgramme::STATUS_OUTDATED
						=> $lng->txt("prg_status_outdated")
					);
	}
}

?>