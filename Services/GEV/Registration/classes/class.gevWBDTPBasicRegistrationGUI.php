<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* GUI for registering WBD-relevant information of a user.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/GEV/WBD/classes/class.gevWBD.php");

class gevWBDTPBasicRegistrationGUI {
	public function __construct() {
		global $lng, $ilCtrl, $ilLog, $ilUser;

		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->log = &$ilLog;
		$this->user = &$ilUser;
		$this->user_utils = gevUserUtils::getInstanceByObj($this->user);
		$this->wbd = gevWBD::getInstanceByObj($this->user);
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
			case "registerTPBasisProfile":
			case "registerTPBasis":
				$ret = $this->$cmd();
				break;
			default:
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
		if (!$this->wbd->hasWBDRelevantRole()) {
			$this->redirectToBookingOr("");
			exit();
		}
	}

	protected function checkAlreadyRegistered() {
		if ($this->wbd->hasDoneWBDRegistration()) {
			$this->redirectToBookingOr("");
			exit();
		}
	}
	
	protected function redirectToBookingOr($a_target) {
		require_once("Services/Authentication/classes/class.ilSession.php");
		$after_registration = ilSession::get("gev_after_registration");
		if ($after_registration) {
			ilUtil::redirect($after_registration);
		}
		else {
			ilUtil::redirect($a_target);
		}
	}

	protected function startRegistration($bwv_id = null) {
		require_once("Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php");
		require_once("Services/Form/classes/class.ilCheckboxInputGUI.php");
		
		$tpl = new ilTemplate("tpl.gev_wbd_start_registration.html", false, false, "Services/GEV/Registration");

		$tpl->setVariable("ACTION", $this->ctrl->getFormAction($this));
		$tpl->setVariable("QUESTION", $this->lng->txt("gev_wbd_registration_question_basis"));
		$chb = new ilCheckboxInputGUI("", "wbd_acceptance");
		$chb->setOptionTitle($this->lng->txt("evg_wbd"));
		$chb->setRequired(true);
		if ($bwv_id) {
			$tpl->setVariable("BWV_ID", $bwv_id);
		}
		$tpl->setVariable("WBD_ACCEPTANCE_CHECKBOX", $chb->render());
		$tpl->setVariable("HAS_BWV_ID", $this->lng->txt("gev_wbd_registration_has_bwv_id"));
		$tpl->setVariable("HAS_BWV_ID_COMMAND", $this->lng->txt("gev_wbd_registration_has_bwv_id_cmd"));
		$tpl->setVariable("NO_BWV_ID", $this->lng->txt("gev_wbd_registration_no_bwv_id"));
		$tpl->setVariable("NO_BWV_ID_COMMAND", $this->lng->txt("gev_wbd_registration_no_bwv_id_cmd"));
		$tpl->setVariable("CREATE_BWV_ID", $this->lng->txt("gev_wbd_registration_create_bwv_id"));
		$tpl->setVariable("CREATE_BWV_ID_COMMAND", $this->lng->txt("gev_wbd_registration_create_bwv_id_cmd"));

		return $tpl->get();
	}

	protected function setBWVId() {
		if (!gevWBD::isValidBWVId($_POST["bwv_id"])) {
			ilUtil::sendFailure($this->lng->txt("gev_bwv_id_input_not_valid"));
			return $this->startRegistration($_POST["bwv_id"]);
		}
		
		if ($_POST["wbd_acceptance"] != 1) {
			ilUtil::sendFailure($this->lng->txt("gev_needs_wbd_acceptance"));
			return $this->startRegistration($_POST["bwv_id"]);
		}

		$this->wbd->setWBDBWVId($_POST["bwv_id"]);
		$this->wbd->setWBDTPType(gevWBD::WBD_EDU_PROVIDER);
		$this->wbd->setWBDRegistrationDone();
		
		$usr = new ilObjUser($this->user_utils->getUser()->getId());
		$usr->update();
		
		ilUtil::sendSuccess($this->lng->txt("gev_wbd_registration_finished_has_bwv_id"), true);
		$this->redirectToBookingOr("");
	}

	protected function noBWVId() {
		$this->wbd->setRawWBDOKZ(gevWBD::WBD_NO_OKZ);
		$this->wbd->setWBDRegistrationDone();
		ilUtil::sendSuccess($this->lng->txt("gev_wbd_registration_finished_no_bwv_id"), true);
		$this->redirectToBookingOr("");
	}

	protected function createBWVId() {
		return $this->buildTPBasisProfileForm()->getHTML();
	}

	protected function createTPBasisBWVId($a_form = null) {
		$tpl = new ilTemplate("tpl.gev_wbd_tp_basis_form.html", false, false, "Services/GEV/Registration");
		$form = $a_form===null ? $this->buildTPBasisForm() : $a_form;

		$tpl->setVariable("FORM", $form->getHTML());

		return $tpl->get();
	}

	protected function registerTPBasisProfile() {
		$form = $this->buildTPBasisProfileForm();
		
		$err = false;
		
		$form->setValuesByPost();
		if (!$form->checkInput()) {
			$err = true;
		}
		
		if ($_POST["wbd_acceptance"] != 1) {
			$err = true;
			$form->getItemByPostVar("wbd_acceptance")
				->setAlert($this->lng->txt("gev_wbd_registration_cb_mandatory"));
		}
		
		$telno_inp = $form->getItemByPostVar("phone");
		require_once("./Services/GEV/Desktop/classes/class.gevUserProfileGUI.php");
		if (!preg_match(gevUserProfileGUI::$telno_regexp, $telno_inp->getValue())) {
				$telno_inp->setAlert($this->lng->txt("gev_telno_wbd_alert"));
				$err = true;
		}
		
		if ($err) {
			return $form->getHTML();
		}

		$birthday = $form->getInput("birthday");
		$bday = new ilDateTime($birthday["date"], IL_CAL_DATE);
		$form->getItemByPostVar("birthday")->setDate($bday);
		$this->user->setBirthday($birthday["date"]);
		$this->user->setStreet($form->getInput("street"));
		$this->user->setCity($form->getInput("city"));
		$this->user->setZipcode($form->getInput("zipcode"));
		$this->user->setPhoneMobile($form->getInput("phone"));
		$this->user->update();

		return $this->createTPBasisBWVId();
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

		$this->wbd->setWBDTPType(gevWBD::WBD_NO_SERVICE);
		$this->wbd->setNextWBDAction(gevWBD::USR_WBD_NEXT_ACTION_NEW_TP_BASIS);

		if ($form->getInput("notifications") == "diff") {
			$this->wbd->setWBDCommunicationEmail($form->getInput("email"));
		}
		else {
			$this->wbd->setWBDCommunicationEmail($this->user_utils->getUser()->getEmail());
		}

		$this->wbd->setWBDRegistrationDone();

		$usr = new ilObjUser($this->user_utils->getUser()->getId());
		$usr->update();

		/*$tpl = new ilTemplate("tpl.gev_wbd_registration_finished.html", false, false, "Services/GEV/Registration");
		return $tpl->get();*/
		ilUtil::sendSuccess($this->lng->txt("gev_wbd_registration_finished_create_bwv_id"), true);
		$this->redirectToBookingOr("ilias.php?baseClass=gevDesktopGUI&cmdClass=toMyCourses");
	}
	
	protected function buildTPBasisProfileForm() {
		require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		require_once("Services/Form/classes/class.ilCheckboxInputGUI.php");
		require_once("Services/Form/classes/class.ilRadioGroupInputGUI.php");
		require_once("Services/Form/classes/class.ilEMailInputGUI.php");
		require_once("Services/Form/classes/class.ilRadioOption.php");

		$form = new ilPropertyFormGUI();
		$form->addCommandButton("registerTPBasisProfile", $this->lng->txt("btn_next"));
		$form->addCommandButton("startRegistration", $this->lng->txt("gev_wbd_registration_basic_back"));
		$form->setFormAction($this->ctrl->getFormAction($this));
		
		$street = new ilTextInputGUI($this->lng->txt("street"), "street");
		$street->setRequired(true);
		$form->addItem($street);
		
		$zipcode = new ilTextInputGUI($this->lng->txt("zipcode"), "zipcode");
		$zipcode->setRequired(true);
		$form->addItem($zipcode);
		
		$city = new ilTextInputGUI($this->lng->txt("city"), "city");
		$city->setRequired(true);
		$form->addItem($city);
		
		$birthday = new ilBirthdayInputGUI($this->lng->txt("birthday"), "birthday");
		$birthday->setRequired(true);
		$birthday->setStartYear(1900);
		$form->addItem($birthday);

		$chb = new ilCheckboxInputGUI("", "wbd_acceptance");
		$chb->setOptionTitle($this->lng->txt("evg_wbd"));
		$form->addItem($chb);
		
		$phone = new ilTextInputGUI($this->lng->txt("gev_mobile"), "phone");
		$phone->setRequired(true);
		$form->addItem($phone);

		return $form;
	}

	protected function buildTPBasisForm() {
		require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		require_once("Services/Form/classes/class.ilCheckboxInputGUI.php");
		require_once("Services/Form/classes/class.ilRadioGroupInputGUI.php");
		require_once("Services/Form/classes/class.ilEMailInputGUI.php");
		require_once("Services/Form/classes/class.ilRadioOption.php");

		$form = new ilPropertyFormGUI();
		$form->addCommandButton("registerTPBasis", $this->lng->txt("register_tp_basis"));
		$form->addCommandButton("startRegistration", $this->lng->txt("gev_wbd_registration_basic_back"));
		$form->setFormAction($this->ctrl->getFormAction($this));

		$wbd_link = "<a href='/Customizing/global/skin/genv/static/documents/02_AGB_WBD.pdf' target='_blank' class='blue'>".$this->lng->txt("gev_agb_wbd")."</a>";
		$auftrag_link = "<a href='/Customizing/global/skin/genv/static/documents/GEV_TPBUVG_Finaler_Auftrag_TP_Basis_Makler.pdf' target='_blank' class='blue'>".$this->lng->txt("gev_mandate")."</a>";
		$agb_link = "<a href='/Customizing/global/skin/genv/static/documents/01_AGB_TGIC.pdf' target='_blank' class='blue'>".$this->lng->txt("gev_agb_tgic")."</a>";

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

		return $form;
	}
}

?>
