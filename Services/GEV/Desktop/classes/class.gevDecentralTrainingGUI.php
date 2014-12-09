<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Forms for decentral trainings.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/Utils/classes/class.gevDecentralTrainingUtils.php");
require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
require_once("Services/CaTUIComponents/classes/class.catPropertyFormGUI.php");

class gevDecentralTrainingGUI {
	public function __construct() {
		global $lng, $ilCtrl, $tpl, $ilUser, $ilLog;

		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;
		$this->log = &$ilLog;
		$this->current_user = &$ilUser;
		$this->user_id = null;
		$this->date = null;
/*		$this->user_utils = null;
		$this->crs_id = null;
		$this->crs_utils = null;
		$this->is_self_learning = null;
		$this->is_webinar = null;*/

		$this->tpl->getStandardTemplate();
	}

	public function executeCommand() {
		$this->loadUserId();
		$this->loadDate();
		
		//$this->checkCanCreateDecentralTraining();
		
		$cmd = $this->ctrl->getCmd();
		
		switch($cmd) {
			case "chooseTemplate":
			case "createTraining":
			case "cancel":
				$cont = $this->$cmd();
			default:
				$this->log->write("gevDecentralTrainingGUI: Unknown command '".$this->cmd."'");
		}
		
		
		if ($cont) {
			$this->tpl->setContent($cont);
		}
	}
	
	protected function loadUserId() {
		$this->user_id = intval($_GET["user_id"]);
	}
	
	protected function loadDate() {
		$this->date = $_GET["date"];
	}
	
	protected function cancel() {
		$this->ctrl->redirectByClass("ilTEPGUI");
	}
	
	protected function chooseTemplate() {
		$title = new catTitleGUI("gev_dec_training_creation", "gev_dec_training_creation_header_note", "GEV_img/ico-head-create-decentral-training.png");
		
		$form = $this->buildSelectTemplateForm();
		
		return   $title->render()
				.$form->getHTML();
	}
	
	protected function createTraining($a_form = null) {
		print_r($_POST);
		die();
	}
	
	protected function buildSelectTemplateForm($a_user_id = null, $a_date = null) {
		require_once("Services/Form/classes/class.ilRadioGroupInputGUI.php");
		require_once("Services/Form/classes/class.ilRadioOption.php");
		require_once("Services/Form/classes/class.ilSelectInputGUI.php");
		require_once("Services/Form/classes/class.ilHiddenInputGUI.php");
		
		$dec_utils = gevDecentralTrainingUtils::getInstance();

		$form = new catPropertyFormGUI();
		$form->setTemplate("tpl.gev_dec_training_choose_template_form.html", "Services/GEV/Desktop");
		$form->setTitle($this->lng->txt("gev_dec_training_choose_template"));
		$form->addCommandButton("createTraining", $this->lng->txt("continue"));
		$form->addCommandButton("cancel", $this->lng->txt("cancel"));
		$this->ctrl->setParameter($this, "user_id", $this->user_id);
		$this->ctrl->setParameter($this, "date", $this->date);
		$form->setFormAction($this->ctrl->getFormAction($this));
		
		$templates = array();
		$key = null;
		
		foreach ($dec_utils->getAvailableTemplatesFor($this->current_user->getId()) as $obj_id => $info) {
			if (!$info["ltype"]) {
				// Only use templates with a learning type
				continue;
			}
			if (!array_key_exists($info["ltype"], $templates)) {
				$templates[$info["ltype"]] = array();
			}
			if ($key === null) {
				$key = strtolower(str_replace(" ", "_", $info["ltype"]));
			}
			$templates[$info["ltype"]][$info["obj_id"]] = $info["title"];
		}
		
		$ltype_choice = new ilRadioGroupInputGUI($this->lng->txt("gev_course_type"), "ltype");
		$form->addItem($ltype_choice);
		foreach ($templates as $ltype => $tmplts) {
			$key = strtolower(str_replace(" ", "_", $ltype));
			$ltype_opt = new ilRadioOption($ltype, $key);
			$ltype_choice->addOption($ltype_opt);
			
			$training_select = new ilSelectInputGUI($this->lng->txt("gev_dec_training_template"), $key."_template");
			$training_select->setOptions($tmplts);

			$ltype_opt->addSubItem($training_select);
		}
		$ltype_choice->setValue($key);

		return $form;
	}
	
	
}

?>