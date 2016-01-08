<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Base class for user registration.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*
* @ilCtrl_Calls gevLoginDataGUI: ilPasswordAssistanceGUI
*/

require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

class gevLoginDataGUI {
	public function __construct() {
		global $lng, $ilCtrl, $ilLog, $tpl;

		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->log = &$ilLog;
		$this->tpl = &$tpl;
		
		$this->tpl->getStandardTemplate();
	}

	public function executeCommand() {
		$next_class = $this->ctrl->getNextClass();
		$cmd = $this->ctrl->getCmd();

		switch ($next_class) {
			case "ilpasswordassistancegui":
				require_once("Services/Init/classes/class.ilPasswordAssistanceGUI.php");
				return $this->ctrl->forwardCommand(new ilPasswordAssistanceGUI());
			default:
				switch ($cmd) {
					case "showLoginHelper":
						$cont = $this->$cmd();
						break;
					default:
					ilUtil::redirect("login.php");
				}
		}
		
		$this->tpl->setContent($cont);
		$this->tpl->show();
	}

	protected function showLoginHelper() {
		require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
		
		$title = new catTitleGUI("gev_registration", null, "GEV_img/ico-head-registration.png");
		$title->setTitle("gev_forget_psswd_login");
		
		$tpl = new ilTemplate("tpl.gev_login_data.html", false, false, "Services/GEV/Registration");
		
		$target_script = $this->ctrl->getTargetScript();
		$this->ctrl->setTargetScript("ilias.php");
		
		$link = $this->ctrl->getLinkTargetByClass(array("ilStartUpGUI", "ilPasswordAssistanceGUI"), "showAssistanceForm");
		$link = preg_replace("/ilstartupgui/", "ilStartUpGUI", $link);
		$tpl->setVariable("CMD_FORGOT_PASSWORD_LINK", $link);
		
		$link = $this->ctrl->getLinkTargetByClass(array("ilStartUpGUI", "ilPasswordAssistanceGUI"), "showUsernameAssistanceForm");
		$link = preg_replace("/ilstartupgui/", "ilStartUpGUI", $link);
		$tpl->setVariable("CMD_FORGOT_USERNAME_LINK", $link);
		
		$this->ctrl->setTargetScript($target_script);

		$tpl->setVariable("CMD_FORGOT_PASSWORD",
				$this->lng->txt("gev_forget_passwort"));
		$tpl->setVariable("CMD_FORGOT_USERNAME",
				$this->lng->txt("gev_forget_username"));

		$tpl->setVariable("PRE_TEXT", $this->lng->txt("gev_forget_psswd_login_pretext"));
		
		return  $title->render()
			  . $tpl->get();
	}
}

?>
