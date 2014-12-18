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
		die($cmd);
		switch ($cmd) {
			case "startNARegistration":
				$cont = $this->$cmd();
				break;
			default:
				ilUtil::redirect("login.php");
		}
		
		$this->tpl->setContent($cont);
		$this->tpl->show();
	}
	
	protected function startNARegistration($a_form = null) {
		die("gevNARegistrationGUI::startNARegistration");
		// get stellennummer and email
		require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
		
		$title = new catTitleGUI("gev_registration", null, "GEV_img/ico-head-registration.png");
		
		$tpl = new ilTemplate("tpl.gev_start_registration.html", false, false, "Services/GEV/Registration");
		
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
}

?>
