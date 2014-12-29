<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Base class for user registration.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*
* @ilCtrl_Calls gevRegistrationGUI: gevAgentRegistrationGUI
* @ilCtrl_Calls gevRegistrationGUI: gevNARegistrationGUI
*/

require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

class gevRegistrationGUI {
	public function __construct() {
		global $lng, $ilCtrl, $ilLog, $tpl;

		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->log = &$ilLog;
		$this->tpl = &$tpl;
		
		$this->tpl->getStandardTemplate();
	}

	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass();
		switch ($next_class) {
			case "gevagentregistrationgui":
				require_once("Services/GEV/Registration/classes/class.gevAgentRegistrationGUI.php");
				$gui = new gevAgentRegistrationGUI();
				$this->ctrl->forwardCommand($gui);
				return;
			case "gevnaregistrationgui":
				require_once("Services/GEV/Registration/classes/class.gevNARegistrationGUI.php");
				$gui = new gevNARegistrationGUI();
				$this->ctrl->forwardCommand($gui);
				return;
			default:
				switch ($cmd) {
					case "startRegistration":
						$cont = $this->$cmd();
						break;
					default:
						ilUtil::redirect("login.php");
				}
		}
		
		$this->tpl->setContent($cont);
		$this->tpl->show();
	}

	protected function startRegistration() {
		require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
		
		$title = new catTitleGUI("gev_registration", null, "GEV_img/ico-head-registration.png");
		
		$tpl = new ilTemplate("tpl.gev_start_registration.html", false, false, "Services/GEV/Registration");
		
		$tpl->setVariable("PRE_TEXT", $this->lng->txt("gev_registration_pretext"));
		$tpl->setVariable("POST_TEXT", $this->lng->txt("gev_registration_posttext"));
		$tpl->setVariable("ACTION_AGENT_REGISTRATION", $this->ctrl->getFormActionByClass("gevAgentRegistrationGUI"));
		$tpl->setVariable("AGENT_REGISTRATION_LABEL", $this->lng->txt("gev_agent_registration_label"));
		$tpl->setVariable("AGENT_REGISTRATION_BUTTON", $this->lng->txt("gev_agent_registration_button"));
		$tpl->setVariable("ACTION_NA_REGISTRATION", $this->ctrl->getFormActionByClass("gevNARegistrationGUI"));
		$tpl->setVariable("NA_REGISTRATION_LABEL", $this->lng->txt("gev_na_registration_label"));
		$tpl->setVariable("NA_REGISTRATION_BUTTON", $this->lng->txt("gev_na_registration_button"));
		
		return  $title->render()
			  . $tpl->get();
	}
}

?>
