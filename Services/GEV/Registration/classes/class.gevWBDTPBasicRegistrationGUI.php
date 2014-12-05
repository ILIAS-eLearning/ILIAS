<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* GUI for registering WBD-relevant information of a user.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

class gevWBDTPBasicRegistrationGUI {
	public function __construct() {
		global $lng, $ilCtrl, $ilLog, $ilUser;

		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->log = &$ilLog;
		$this->user = &$ilUser;
		$this->user_utils = gevUserUtils::getInstanceByObj($this->user);
	}

	public function executeCommand() {
		$this->checkWBDRelevantRole();
		$this->checkAlreadyRegistered();

		$cmd = $this->ctrl->getCmd();

		switch ($cmd) {
			case "startRegistration":
			case "setBWVId":
			case "noBWVId":
			case "createBWVId":
			case "registerTPBasis":
			//case "registerTPService":
				$ret = $this->$cmd();
				break;
			default:
				/*if ($this->user_utils->canBeRegisteredAsTPService()) {
					$ret = $this->createTPServiceBWVId();
				} else {
					$ret = $this->startRegistration();
				}
				*/
				$ret = $this->startRegistration();
		}

		require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
		$title = new catTitleGUI("gev_wbd_registration_basic"
								, "gev_wbd_registration_basic_header_note"
								, "GEV_img/ico-head-wbd_registration.png"
								);

		return    $title->render()
				. $ret
				;
	}

	protected function checkWBDRelevantRole() {
		if (!$this->user_utils->hasWBDRelevantRole()) {
			ilUtil::redirect("");
			exit();
		}
	}

	protected function checkAlreadyRegistered() {
		if ($this->user_utils->hasDoneWBDRegistration()) {
			ilUtil::redirect("");
			exit();
		}
	}

	protected function startRegistration() {
		$tpl = new ilTemplate("tpl.gev_wbd_start_registration.html", false, false, "Services/GEV/Registration");

		$tpl->setVariable("ACTION", $this->ctrl->getFormAction($this));
		$tpl->setVariable("QUESTION", $this->lng->txt("gev_wbd_registration_question_basis"));
		$tpl->setVariable("HAS_BWV_ID", $this->lng->txt("gev_wbd_registration_has_bwv_id"));
		$tpl->setVariable("HAS_BWV_ID_COMMAND", $this->lng->txt("gev_wbd_registration_has_bwv_id_cmd"));
		$tpl->setVariable("NO_BWV_ID", $this->lng->txt("gev_wbd_registration_no_bwv_id"));
		$tpl->setVariable("NO_BWV_ID_COMMAND", $this->lng->txt("gev_wbd_registration_no_bwv_id_cmd"));
		$tpl->setVariable("CREATE_BWV_ID", $this->lng->txt("gev_wbd_registration_create_bwv_id"));
		$tpl->setVariable("CREATE_BWV_ID_COMMAND", $this->lng->txt("gev_wbd_registration_create_bwv_id_cmd"));

		return $tpl->get();
	}

	protected function setBWVId() {
		if (!gevUserUtils::isValidBWVId($_POST["bwv_id"])) {
			/*
			if ($this->user_utils->canBeRegisteredAsTPService()) {
				return $this->createTPServiceBWVId();
			} else {
				return $this->startRegistration();
			}
			*/
			ilUtil::sendFailure($this->lng->txt("gev_bwv_id_input_not_valid"));
			
			return $this->startRegistration();
		}

		$this->user_utils->setWBDBWVId($_POST["bwv_id"]);
		$this->user_utils->setWBDTPType(gevUserUtils::WBD_EDU_PROVIDER);
		$this->user_utils->setWBDRegistrationDone();
		ilUtil::sendSuccess($this->lng->txt("gev_wbd_registration_finished_has_bwv_id"), true);
		ilUtil::redirect("");
	}

	protected function noBWVId() {
		$this->user_utils->setRawWBDOKZ(gevUserUtils::WBD_NO_OKZ);
		$this->user_utils->setWBDRegistrationDone();
		ilUtil::sendSuccess($this->lng->txt("gev_wbd_registration_finished_no_bwv_id"), true);
		ilUtil::redirect("");
	}

	protected function createBWVId() {
		/*if ($this->user_utils->canBeRegisteredAsTPService()) {
			return $this->createTPServiceBWVId();
		}
		else {
			return $this->createTPBasisBWVId();
		}
		*/
		return $this->createTPBasisBWVId();
	}

/*
	protected function createTPServiceBWVId($a_form = null) {
		$tpl = new ilTemplate("tpl.gev_wbd_tp_service_form.html", false, false, "Services/GEV/Registration");
		$form = $a_form===null ? $this->buildTPServiceForm() : $a_form;

		$tpl->setVariable("ACTION", $this->ctrl->getFormAction($this));
		$tpl->setVariable("FORM", $form->getHTML());
		$tpl->setVariable("QUESTION", $this->lng->txt("gev_wbd_registration_question_service"));
		$tpl->setVariable("HAS_BWV_ID", $this->lng->txt("gev_wbd_registration_has_bwv_id"));
		$tpl->setVariable("HAS_BWV_ID_COMMAND", $this->lng->txt("gev_wbd_registration_has_bwv_id_cmd"));
		$tpl->setVariable("NO_BWV_ID", $this->lng->txt("gev_wbd_registration_no_bwv_id"));
		$tpl->setVariable("NO_BWV_ID_COMMAND", $this->lng->txt("gev_wbd_registration_no_bwv_id_cmd"));

		return $tpl->get();
	}
*/	
/*
	protected function registerTPService() {
		$form = $this->buildTPServiceForm();

		$err = false;
		$form->setValuesByPost();
		if (!$form->checkInput()) {
			$err = true;
		}
		else {
			for ($i = 1; $i <= 4; ++$i) {
				$chb = $form->getItemByPostVar("chb".$i);
				if (!$chb->getChecked()) {
					$err = true;
					$chb->setAlert($this->lng->txt("gev_wbd_registration_cb_mandatory"));
				}
			}
		}

		if ($err) {
			return $this->createTPServiceBWVId($form);
		}

		$this->user_utils->setWBDTPType(gevUserUtils::WBD_TP_SERVICE);

		if ($form->getInput("notifications") == "diff") {
			$this->user_utils->setWBDCommunicationEmail($form->getInput("email"));
		}

		$this->user_utils->setWBDRegistrationDone();

		ilUtil::sendSuccess($this->lng->txt("gev_wbd_registration_finished_create_bwv_id"), true);
		ilUtil::redirect("ilias.php?baseClass=gevDesktopGUI&cmdClass=toMyCourses");
	}
*/
/*
	protected function buildTPServiceForm() {
		require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		require_once("Services/Form/classes/class.ilCheckboxInputGUI.php");
		require_once("Services/Form/classes/class.ilRadioGroupInputGUI.php");
		require_once("Services/Form/classes/class.ilEMailInputGUI.php");
		require_once("Services/Form/classes/class.ilRadioOption.php");

		$form = new ilPropertyFormGUI();
		//$form->addCommandButton("registerTPService", $this->lng->txt("register_tp_service"));
		$form->addCommandButton("startRegistration", $this->lng->txt("gev_wbd_registration_basic_back"));
		$form->setFormAction($this->ctrl->getFormAction($this));

		$wbd_link = "<a href='/Customizing/global/skin/genv/documents/02_AGB_WBD.pdf' target='_blank' style='color: #0000ff'>".$this->lng->txt("gev_agb_wbd")."</a>";
		$auftrag_link = $this->lng->txt("gev_mandate");
		$agb_link = "<a href='/Customizing/global/skin/genv/documents/01_AGB_TGIC.pdf' target='_blank' style='color: #0000ff'>".$this->lng->txt("gev_agb_tgic")."</a>";

		$chb1 = new ilCheckboxInputGUI("", "chb1");
		$chb1->setOptionTitle(sprintf($this->lng->txt("gev_give_mandate_tp_service"), $auftrag_link));
		$form->addItem($chb1);

		$chb2 = new ilCheckboxInputGUI("", "chb2");
		$chb2->setOptionTitle(sprintf($this->lng->txt("gev_confirm_wbd"), $wbd_link));
		$form->addItem($chb2);

		$chb3 = new ilCheckboxInputGUI("", "chb3");
		$chb3->setOptionTitle(sprintf($this->lng->txt("gev_confirm_agb"), $agb_link));
		$form->addItem($chb3);

		$chb4 = new ilCheckboxInputGUI("", "chb4");
		$chb4->setOptionTitle($this->lng->txt("gev_no_other_wbd_mandate"));
		$form->addItem($chb4);

		$opt1 = new ilRadioGroupInputGUI($this->lng->txt("gev_wbd_notifications"), "notifications");
		$opt1->addOption(new ilRadioOption($this->lng->txt("gev_wbd_notifications_to_auth"), "auth"));
		$extra = new ilRadioOption($this->lng->txt("gev_wbd_notifications_to_diff"), "diff");
		$email = new ilEMailInputGUI($this->lng->txt("gev_alternative_email"), "email");
		$email->setRequired(true);
		$extra->addSubItem($email);
		$opt1->addOption($extra);
		$opt1->setValue("auth");
		$form->addItem($opt1);


		return $form;
	}
*/
	protected function createTPBasisBWVId($a_form = null) {
		$tpl = new ilTemplate("tpl.gev_wbd_tp_basis_form.html", false, false, "Services/GEV/Registration");
		$form = $a_form===null ? $this->buildTPBasisForm() : $a_form;

		$tpl->setVariable("FORM", $form->getHTML());

		return $tpl->get();
	}

	protected function registerTPBasis() {
		$form = $this->buildTPBasisForm();

		$err = false;
		$form->setValuesByPost();
		if (!$form->checkInput()) {
			$err = true;
		}
		else {
			for ($i = 1; $i <= 5; ++$i) {
				$chb = $form->getItemByPostVar("chb".$i);
				if (!$chb->getChecked()) {
					$err = true;
					$chb->setAlert($this->lng->txt("gev_wbd_registration_cb_mandatory"));
				}
			}
		}

		if ($err) {
			return $this->createTPBasisBWVId($form);
		}

		$this->user_utils->setWBDTPType(gevUserUtils::WBD_TP_BASIS);

		if ($form->getInput("notifications") == "diff") {
			$this->user_utils->setWBDCommunicationEmail($form->getInput("email"));
		}

		$this->user_utils->setWBDRegistrationDone();

		/*$tpl = new ilTemplate("tpl.gev_wbd_registration_finished.html", false, false, "Services/GEV/Registration");
		return $tpl->get();*/
		ilUtil::sendSuccess($this->lng->txt("gev_wbd_registration_finished_create_bwv_id"), true);
		ilUtil::redirect("ilias.php?baseClass=gevDesktopGUI&cmdClass=toMyCourses");
	}

	protected function buildTPBasisForm() {
		require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		require_once("Services/Form/classes/class.ilCheckboxInputGUI.php");
		require_once("Services/Form/classes/class.ilRadioGroupInputGUI.php");
		require_once("Services/Form/classes/class.ilEMailInputGUI.php");
		require_once("Services/Form/classes/class.ilRadioOption.php");

		$form = new ilPropertyFormGUI();
		$form->addCommandButton("startRegistration", $this->lng->txt("gev_wbd_registration_basic_back"));
		$form->addCommandButton("registerTPBasis", $this->lng->txt("register_tp_basis"));
		$form->setFormAction($this->ctrl->getFormAction($this));

		$wbd_link = "<a href='/Customizing/global/skin/genv/documents/02_AGB_WBD.pdf' target='_blank' class='blue'>".$this->lng->txt("gev_agb_wbd")."</a>";
		$auftrag_link = "<a href='/Customizing/global/skin/genv/documents/GEV_TPBUVG_Finaler_Auftrag_TP_Basis_Makler.pdf' target='_blank' class='blue'>".$this->lng->txt("gev_mandate")."</a>";
		$agb_link = "<a href='/Customizing/global/skin/genv/documents/01_AGB_TGIC.pdf' target='_blank' class='blue'>".$this->lng->txt("gev_agb_tgic")."</a>";

		$chb1 = new ilCheckboxInputGUI("", "chb1");
		$chb1->setOptionTitle(sprintf($this->lng->txt("gev_give_mandate_tp_basis"), $auftrag_link));
		$form->addItem($chb1);

		$chb2 = new ilCheckboxInputGUI("", "chb2");
		$chb2->setOptionTitle(sprintf($this->lng->txt("gev_confirm_wbd"), $wbd_link));
		$form->addItem($chb2);

		$chb3 = new ilCheckboxInputGUI("", "chb3");
		$chb3->setOptionTitle(sprintf($this->lng->txt("gev_confirm_agb"), $agb_link));
		$form->addItem($chb3);

		$chb4 = new ilCheckboxInputGUI("", "chb4");
		$chb4->setOptionTitle($this->lng->txt("gev_confirm_qualification"));
		$form->addItem($chb4);

		$chb5 = new ilCheckboxInputGUI("", "chb5");
		$chb5->setOptionTitle($this->lng->txt("gev_no_other_wbd_mandate"));
		$form->addItem($chb5);

		$opt1 = new ilRadioGroupInputGUI($this->lng->txt("gev_wbd_notifications"), "notifications");
		$opt1->addOption(new ilRadioOption($this->lng->txt("gev_wbd_notifications_to_auth"), "auth"));
		$extra = new ilRadioOption($this->lng->txt("gev_wbd_notifications_to_diff"), "diff");
		$email = new ilEMailInputGUI($this->lng->txt("gev_alternative_email"), "email");
		$email->setRequired(true);
		$extra->addSubItem($email);
		$opt1->addOption($extra);
		$opt1->setValue("auth");
		$form->addItem($opt1);
		//$form->addItem($email);

		return $form;
	}
}

?>
