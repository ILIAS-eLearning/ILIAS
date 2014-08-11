<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Course seraching GUI for Generali
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

class gevRegistrationGUI {
	public function __construct() {
		global $lng, $ilCtrl, $tpl, $ilLog;

		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;
		$this->log = &$ilLog;

		$this->tpl->getStandardTemplate();
	}

	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		
		switch ($cmd) {
			case "startEVGRegistration":
			case "finalizeEVGRegistration":
				$cont = $this->$cmd();
				break;
			default:
				ilUtil::redirect("login.php");
		}
		
		$this->tpl->setContent($cont);
		$this->tpl->show();
	}
	
	protected function startEVGRegistration($a_form = null) {
		require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
		
		$title = new catTitleGUI("gev_evg_registration", "gev_evg_registration_header_note", "GEV_img/ico-head-evg_registration.png");
		
		$tpl = new ilTemplate("tpl.gev_evg_start_registration.html", false, false, "Services/GEV/Registration");
		
		if ($a_form !== null) {
			$form = $a_form;
			$form->setValuesByPost();
		}
		else {
			$form = $this->buildEVGRegisterForm();
		}
		$tpl->setVariable("FORM", $form->getHTML());
		
		return  $title->render()
			  . $tpl->get();
	}
	
	protected function finalizeEVGRegistration() {	
		$form = $this->buildEVGRegisterForm();
		
		if (  !$form->checkInput()) {
			return $this->startEVGRegistration($form);
		}
		
		require_once("gev_utils.php");
		$import = get_gev_import();
		
		$error = $import->register( $form->getInput("position")
								  , $form->getInput("email"));
		if ($error) {
			ilUtil::sendFailure($this->lng->txt("gev_evg_registration_not_found"));
			return $this->startEVGRegistration($form);
		}
		
		require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");

		ilUtil::sendSuccess($this->lng->txt("gev_evg_registration_success"));
		$title = new catTitleGUI("gev_evg_registration", "gev_evg_registration_header_note", "GEV_img/ico-head-evg_registration.png");
		$tpl = new ilTemplate("tpl.gev_evg_successfull_registration.html", false, false, "Services/GEV/Registration");

		return	  $title->render()
				. $tpl->get();
	}

	protected function buildEVGRegisterForm() {
		require_once("Services/CaTUIComponents/classes/class.catPropertyFormGUI.php");
		require_once("Services/Form/classes/class.ilTextInputGUI.php");
		require_once("Services/Form/classes/class.ilEMailInputGUI.php");
		
		$form = new catPropertyFormGUI();
		$form->setTemplate("tpl.gev_evg_registration_form.html", "Services/GEV/Registration");
		$form->addCommandButton("finalizeEVGRegistration", $this->lng->txt("submit"));
		$this->ctrl->setTargetScript("gev_registration.php");
		$form->setFormAction($this->ctrl->getFormAction($this));
		
		$position = new ilTextInputGUI($this->lng->txt("gev_position"), "position");
		$position->setSize(40);
		$position->setRequired(true);
		$form->addItem($position);
		
		$email = new ilEMailInputGUI($this->lng->txt("email"), "email");
		$email->setSize(40);
		$email->setRequired(true);
		$form->addItem($email);
				
		return $form;
	}
}

?>