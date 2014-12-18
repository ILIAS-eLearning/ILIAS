<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Command class for registration of an NA (Nebenagent).
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

class gevNARegistrationGUI {
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
			case "startNARegistration":
			case "checkAdviser":
				$cont = $this->$cmd();
				break;
			default:
				ilUtil::redirect("login.php");
		}
		
		$this->tpl->setContent($cont);
		$this->tpl->show();
	}
	
	protected function startNARegistration($a_form = null) {
		// get stellennummer and email
		require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
		
		$title = new catTitleGUI("gev_registration", null, "GEV_img/ico-head-registration.png");
		
		$tpl = new ilTemplate("tpl.gev_start_na_registration.html", false, false, "Services/GEV/Registration");
		
		if ($a_form !== null) {
			$form = $a_form;
			$form->setValuesByPost();
		}
		else {
			$form = $this->buildRegistrationStartForm();
		}
		$tpl->setVariable("FORM", $form->getHTML());
		
		return  $title->render()
			  . $tpl->get();
	}
	
	protected function checkAdviser() {
		$form = $this->buildRegistrationStartForm();
		
		if (!$form->checkInput()) {
			return $this->startNARegistration($form);
		}
		
		if ($_POST["chb1"] != 1) {
			$form->getItemByPostVar("chb1")->setAlert($this->lng->txt("evg_mandatory"));
			return $this->startNARegistration($form);
		}
		
		require_once("Services/GEV/Utils/classes/class.gevNAUtils.php");
		$adviser_id = gevNAUtils::getInstance()->searchAdviser($form->getInput("adviser"));
		echo $adviser_id?$adviser_id:"not found";
		die();
	}
	
	protected function buildRegistrationStartForm() {
		require_once("Services/CaTUIComponents/classes/class.catPropertyFormGUI.php");
		require_once("Services/Form/classes/class.ilTextInputGUI.php");
		require_once("Services/Form/classes/class.ilEMailInputGUI.php");
		
		$form = new catPropertyFormGUI();
		$form->setTemplate("tpl.gev_registration_form.html", "Services/GEV/Registration");
		$form->addCommandButton("checkAdviser", $this->lng->txt("continue"));
		$form->setFormAction($this->ctrl->getFormAction($this));
		
		$email = new ilEMailInputGUI($this->lng->txt("evg_email"), "email");
		$email->setSize(40);
		$email->setRequired(true);
		$form->addItem($email);
		
		$adviser = new ilTextInputGUI($this->lng->txt("gev_na_adviser"), "adviser");
		$adviser->setSize(40);
		$adviser->setRequired(true);
		$form->addItem($adviser);
		
		$chb1 = new ilCheckboxInputGUI("", "chb1");
		$chb1->setOptionTitle($this->lng->txt("evg_toc"));
		$chb1->setRequired(true);
		$form->addItem($chb1);
		
		return $form;
	}
}

?>
