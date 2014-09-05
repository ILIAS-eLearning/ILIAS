<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Course seraching GUI for Generali
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

class gevRegistrationGUI {
	public function __construct() {
		global $lng, $ilCtrl, $tpl, $ilLog;

		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;
		$this->log = &$ilLog;
		$this->import = null;
		$this->stellennummer_data = null;

		$this->tpl->getStandardTemplate();
	}

	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		
		switch ($cmd) {
			case "startRegistration":
			case "checkEVGOrAgent":
			case "registerAgent":
				$cont = $this->$cmd();
				break;
			default:
				ilUtil::redirect("login.php");
		}
		
		$this->tpl->setContent($cont);
		$this->tpl->show();
	}
	
	protected function getImport() {
		if ($this->import === null) {
			require_once("gev_utils.php");
			$this->import = get_gev_import();
		}
		
		return $this->import;
	}
	
	protected function loadStellennummerData($a_stellenummer) {
		if ($this->stellennummer_data === null) {
			$import = $this->getImport();
			$this->stellennummer_data = $import->get_stelle($a_stellennummer);
		}
	}
	
	protected function getStellennummerData($a_stellennummer) {
		$this->loadStellennummerData($a_stellennummer);

		if ($this->stellennummer_data["stellennummer"] != $a_stellennummer) {
			throw new Exception("gevRegistrationGUI::getStellennummerData: stellnummer does not match.");
		}
		
		return $this->stellennummer_data;
	}
	
	protected function isValidStellennummer($a_stellennummer) {
		$this->loadStellennummerData($a_stellennummer);
		return $this->stellennummer_data !== false && $this->stellennummer_data["stellennummer"] == $a_stellennummer;
	}
	
	protected function isAgent($a_stellennummer) {
		$data = $this->getStellennummerData($a_stellennummer);
		return $data["agent"] == 1;
	}

	protected function getVermittlerStatus($a_stellennummer) {
		$data = $this->getStellennummerData($a_stellennummer);
		return $data["vermittlerstatus"];
	}
	
	protected function getOrgUnitImportId($a_stellennummer) {
		$data = $this->getStellennummerData($a_stellennummer);
		return $data["org_unit"];
	}

	protected function startRegistration($a_form = null) {
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
		
		// result goes to checkEVGOrAgent
	}
	
	protected function checkEVGOrAgent() {
		$res = $this->checkRegistrationStartForm();
		
		if (!$res[1]) {
			return $this->startRegistration($res[0]);
		}
		
		$stellennummer = $res[0]->getInput("position");
		if($this->isValidStellennummer($stellennummer) && $this->isAgent($stellennummer)) {
			return $this->inputUserProfile(null, $res[0]);
		}
		else {
			return $this->checkEVGRegistration($res[0], $import);
		}
	}

	protected function inputUserProfile($a_form = null, $a_prev_form = null) {
		if ($a_form === null && $a_prev_form === null) {
			throw new Exception("gevRegistrationGUI::inputUserProfile: either a_form or a_prev_form need to be set.");
		} 
		
		if ($a_form !== null) {
			$form = $a_form;
			$form->setValuesByPost();
		}
		else {
			$form = $this->buildAgentForm($a_prev_form->getInput("email"), $a_prev_form->getInput("position"));
		}
		
		require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
		$title = new catTitleGUI("gev_agent_registration", "gev_agent_registration_note", "GEV_img/ico-head-agent_registration.png");
		
		$tpl = new ilTemplate("tpl.gev_agent_profile.html", false, false, "Services/GEV/Registration");
		$tpl->setVariable("FORM", $form->getHTML());
		
		return   $title->render()
				.$tpl->get();
	}
	
	protected function registerAgent() {
		$res = $this->checkAgentForm();
		
		if (!$res[1]) {
			return $this->inputUserProfile($res[0]);
		}
		$form = $res[0];
		
		$user = new ilObjUser();
		$user->setLogin($form->getInput("username"));
		$user->setEmail($form->getInput("b_email"));
		$user->setPasswd($form->getInput("password"));
		$user->setLastname($form->getInput("lastname"));
		$user->setFirstname($form->getInput("firstname"));
		$user->setGender($form->getInput("gender"));
		$birthday = $form->getInput("birthday");
		$user->setBirthday($birthday["date"]);
		$user->setStreet($form->getInput("b_street"));
		$user->setCity($form->getInput("b_city"));
		$user->setZipcode($form->getInput("b_zipcode"));
		$user->setCountry($form->getInput("b_country"));
		$user->setPhoneOffice($form->getInput("b_phone"));
		// is not active, owner is root
		$user->setActive(0, 6);
		$user->setTimeLimitUnlimited(true);
		// user already agreed at registration
		$now = new ilDateTime(time(),IL_CAL_UNIX);
		$user->setAgreeDate($now->get(IL_CAL_DATETIME));
		$user->setIsSelfRegistered(true);
		
		$user->create();
		$user->saveAsNew();
		
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$user_utils = gevUserUtils::getInstanceByObj($user);
		
		$user_utils->setBirthplace($form->getInput("birthplace"));
		$user_utils->setBirthname($form->getInput("birthname"));
		$user_utils->setIHKNumber($form->getInput("ihk_number"));
		$user_utils->setPrivateEmail($form->getInput("p_email"));
		$user_utils->setPrivateStreet($form->getInput("p_street"));
		$user_utils->setPrivateCity($form->getInput("p_city"));
		$user_utils->setPrivateZipcode($form->getInput("p_zipcode"));
		$user_utils->setPrivateState($form->getInput("p_country"));
		$user_utils->setPrivatePhone($form->getInput("p_phone"));
		
		
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
		
		$user_id = $user->getId();
		$stellennummer = $form->getInput("position");
		$vermittlerstatus = $this->getVermittlerStatus($stellennummer);
		
		$role_title = gevSettings::$VMS_ROLE_MAPPING[$vermittlerstatus][0];
		$role_utils = gevRoleUtils::getInstance();
		$role_utils->assignUserToGlobalRole($user_id, $role_title);
		
		$org_role_title = gevSettings::$VMS_ROLE_MAPPING[$vermittlerstatus][0];
		$org_unit_import_id = $this->getOrgUnitImportId($stellennummer);
		$org_unit_id = ilObjOrgUnit::_lookupObjIdByImportId($org_unit_import_id);
		if (!$org_unit_id) {
			throw new Exception("Could not determine obj_id for org unit with import id '".$org_unit_import_id."'");
		}
		$org_unit_utils = gevOrgUnitUtils::getInstance($org_unit_id);
		$org_unit_utils->getOrgUnitInstance();
		$org_unit_utils->assignUser($user_id, $org_role_title);
		
		require_once("Services/GEV/Mailing/classes/class.gevRegistrationMails.php");
		require_once("Services/Utilities/classes/class.ilUtil.php");
		$token = ilObjUser::_generateRegistrationHash($user->getId());
		$link = ILIAS_HTTP_PATH . '/confirmReg.php?client_id=' . CLIENT_ID . '&rh='.$token;
		$reg_mails = new gevRegistrationMails($link, $token);
		$reg_mails->getAutoMail("agent_activation")->send();
/*
		include_once './Services/Registration/classes/class.ilRegistrationMimeMailNotification.php';

		$mail = new ilRegistrationMimeMailNotification();
		$mail->setType(ilRegistrationMimeMailNotification::TYPE_NOTIFICATION_ACTIVATION);
		$mail->setRecipients(array($user));
		$mail->setAdditionalInformation(
			array( 'usr'           => $user
				 , 'hash_lifetime' => 5 * 60 * 60 // in seconds
				 )
			);
		$mail->send();
*/		
		require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
		$title = new catTitleGUI("gev_agent_registration", null, "GEV_img/ico-head-evg_registration.png");
		
		ilUtil::sendSuccess($this->lng->txt("gev_agent_registration_success"));
		$tpl = new ilTemplate("tpl.gev_agent_successfull_registration.html", false, false, "Services/GEV/Registration");
		
		return	  $title->render()
				. $tpl->get();
	}
	
	protected function checkEVGRegistration($a_form, $a_import) {
		require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
		$title = new catTitleGUI("gev_evg_registration", null, "GEV_img/ico-head-evg_registration.png");
	
		$import = $this->getImport();
		$error = $import->registerEVG( $a_form->getInput("position")
								  	 , $a_form->getInput("email")
								  	 );

		if ($error) {
			ilUtil::sendFailure($this->lng->txt("gev_evg_registration_not_found"));
			$tpl = new ilTemplate("tpl.gev_evg_registration_failed.html", false, false, "Services/GEV/Registration");
			$lnk = $this->ctrl->getLinkTarget($this, "startRegistration");
			$tpl->setVariable("BACKLINK", $lnk);
		}
		else {

			ilUtil::sendSuccess($this->lng->txt("gev_evg_registration_success"));
			$tpl = new ilTemplate("tpl.gev_evg_successfull_registration.html", false, false, "Services/GEV/Registration");
		}
	
		return	  $title->render()
				. $tpl->get();
	}

	protected function checkRegistrationStartForm() {
		$form = $this->buildRegistrationStartForm();
		$err = false;

		if (!$form->checkInput()) {
			$err = true;
		}

		for ($i = 1; $i <= 2; ++$i) {
			$id = "chb".$i;
			$chb = $form->getItemByPostVar($id);
			//if (!$chb->getChecked()) {  // TODO: this doesn't work, why?
			if ($_POST[$id] != 1) {
				$err = true;
				$chb->setAlert($this->lng->txt("evg_mandatory"));
			}
		}
		
		return array($form, !$err);
	}

	protected function buildRegistrationStartForm() {
		require_once("Services/CaTUIComponents/classes/class.catPropertyFormGUI.php");
		require_once("Services/Form/classes/class.ilTextInputGUI.php");
		require_once("Services/Form/classes/class.ilEMailInputGUI.php");
		
		$form = new catPropertyFormGUI();
		$form->setTemplate("tpl.gev_registration_form.html", "Services/GEV/Registration");
		$form->addCommandButton("checkEVGOrAgent", $this->lng->txt("register"));
		$this->ctrl->setTargetScript("gev_registration.php");
		$form->setFormAction($this->ctrl->getFormAction($this));
		
		$position = new ilTextInputGUI($this->lng->txt("gev_position"), "position");
		$position->setSize(40);
		$position->setRequired(true);
		$form->addItem($position);
		
		$email = new ilEMailInputGUI($this->lng->txt("evg_email"), "email");
		$email->setSize(40);
		$email->setRequired(true);
		$form->addItem($email);

		$chb1 = new ilCheckboxInputGUI("", "chb1");
		$chb1->setOptionTitle($this->lng->txt("evg_toc"));
		$chb1->setRequired(true);
		$form->addItem($chb1);

		$chb2 = new ilCheckboxInputGUI("", "chb2");
		$chb2->setOptionTitle($this->lng->txt("evg_wbd"));
		$chb2->setRequired(true);
		$form->addItem($chb2);

		return $form;
	}

	protected function checkAgentForm() {
		$form = $this->buildAgentForm();
		$err = false;
		if (!$form->checkInput()) {
			$err = true;
		}
		
		// validate phone number

		return array($form, !$err);
	}

	protected function buildAgentForm($a_email = null, $a_position = null) {
		
		require_once("Services/Calendar/classes/class.ilDate.php");
		require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		require_once("Services/Form/classes/class.ilFormSectionHeaderGUI.php");
		require_once("Services/Form/classes/class.ilTextInputGUI.php");
		require_once("Services/Form/classes/class.ilDateTimeInputGUI.php");
		require_once("Services/Form/classes/class.ilRadioGroupInputGUI.php");
		require_once("Services/Form/classes/class.ilRadioOption.php");
		require_once("Services/Form/classes/class.ilEMailInputGUI.php");
		require_once("Services/Form/classes/class.ilNonEditableValueGUI.php");
		require_once("Services/Form/classes/class.ilPasswordInputGUI.php");
		require_once("Services/Form/classes/class.ilUserLoginInputGUI.php");
		require_once("Services/Form/classes/class.ilHiddenInputGUI.php");
		
		$form = new ilPropertyFormGUI();
		$form->addCommandButton("registerAgent", $this->lng->txt("register"));
		$form->setFormAction($this->ctrl->getFormAction($this));
		
		$position = new ilHiddenInputGUI("position");
		if ($a_position !== null) {
			$position->setValue($a_position);
		}
		$form->addItem($position);
		
		$section1 = new ilFormSectionHeaderGUI();
		$section1->setTitle($this->lng->txt("gev_personal_data"));
		$form->addItem($section1);
		
		$username = new ilUserLoginInputGUI($this->lng->txt("username"), "username");
		$username->setRequired(true);
		$form->addItem($username);
		
		$password1 = new ilPasswordInputGUI($this->lng->txt("password"), "password");
		$password1->setRequired(true);
		$form->addItem($password1);
		
		$lastname = new ilTextInputGUI($this->lng->txt("lastname"), "lastname");
		$lastname->setRequired(true);
		$form->addItem($lastname);
		
		$firstname = new ilTextInputGUI($this->lng->txt("firstname"), "firstname");
		$firstname->setRequired(true);
		$form->addItem($firstname);
		
		$gender = new ilRadioGroupInputGUI($this->lng->txt("gender"), "gender");
		$gender->addOption(new ilRadioOption($this->lng->txt("gender_m"), "m"));
		$gender->addOption(new ilRadioOption($this->lng->txt("gender_f"), "f"));
		$gender->setRequired(true);
		$form->addItem($gender);

		$birthday = new ilBirthdayInputGUI($this->lng->txt("birthday"), "birthday");
		$birthday->setRequired(true);
		$birthday->setStartYear(1940);
		$form->addItem($birthday);
		
		$birthplace = new ilTextInputGUI($this->lng->txt("gev_birthplace"), "birthplace");
		$birthplace->setRequired(true);
		$form->addItem($birthplace);
		
		$birthname = new ilTextInputGUI($this->lng->txt("gev_birthname"), "birthname");
		$birthname->setRequired(true);
		$form->addItem($birthname);
		
		$ihk = new ilTextInputGUI($this->lng->txt("gev_ihk_number"), "ihk_number");
		$form->addItem($ihk);
		
		$section2 = new ilFormSectionHeaderGUI();
		$section2->setTitle($this->lng->txt("gev_business_contact"));
		$form->addItem($section2);
		
		$b_email = new ilTextInputGUI($this->lng->txt("gev_email"), "b_email");
		if ($a_email !== null) {
			$b_email->setValue($a_email);
		}
		$b_email->setRequired(true);
		$form->addItem($b_email);
		
		$b_phone = new ilTextInputGUI($this->lng->txt("gev_profile_phone"), "b_phone");
		$form->addItem($b_phone);
		
		$b_street = new ilTextInputGUI($this->lng->txt("street"), "b_street");
		$form->addItem($b_street);
		
		$b_city = new ilTextInputGUI($this->lng->txt("city"), "b_city");
		$form->addItem($b_city);
		
		$b_zipcode = new ilTextInputGUI($this->lng->txt("zipcode"), "b_zipcode");
		$form->addItem($b_zipcode);
		
		$b_country = new ilTextInputGUI($this->lng->txt("federal_state"), "b_country");
		$form->addItem($b_country);
		
		$info = new ilNonEditableValueGUI("");
		$info->setValue($this->lng->txt("gev_private_contact_info"));
		$form->addItem($info);
		
		$p_email = new ilEMailInputGUI($this->lng->txt("email"), "p_email");
		if ($a_email !== null) {
			$p_email->setValue($a_email);
		}
		$p_email->setRequired(true);
		$form->addItem($p_email);
		
		$p_phone = new ilTextInputGUI($this->lng->txt("gev_mobile"), "p_phone");
		$p_phone->setRequired(true);
		$form->addItem($p_phone);
		
		
		$section3 = new ilFormSectionHeaderGUI();
		$section3->setTitle($this->lng->txt("gev_private_contact"));
		$form->addItem($section3);
		
		$p_street = new ilTextInputGUI($this->lng->txt("street"), "p_street");
		$form->addItem($p_street);
		
		$p_city = new ilTextInputGUI($this->lng->txt("city"), "p_city");
		$form->addItem($p_city);
		
		$p_zipcode = new ilTextInputGUI($this->lng->txt("zipcode"), "p_zipcode");
		$form->addItem($p_zipcode);
		
		$p_country = new ilTextInputGUI($this->lng->txt("federal_state"), "p_country");
		$form->addItem($p_country);
		
		return $form;
	}
}

?>