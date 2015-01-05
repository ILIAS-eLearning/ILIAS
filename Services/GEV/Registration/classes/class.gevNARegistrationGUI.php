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
		if($cmd == "startRegistration") {
			$cmd = "startNARegistration";
		}
		
		switch ($cmd) {
			case "startNARegistration":
			case "checkAdviser":
			case "registerNA":
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

		if ($adviser_id === null) {
			$form->getItemByPostVar("adviser")->setAlert($this->lng->txt("gev_na_reg_no_adviser"));
			return $this->startNARegistration($form);
		}
		
		return $this->inputUserProfile(null, $form->getInput("email"), $adviser_id);
	}
	
	protected function inputUserProfile($a_form = null, $a_email = null, $a_adviser_id = null) {
		if ($a_form === null && ($a_email === null || $a_adviser_id === null)) {
			throw new Exception("gevNARegistrationGUI::inputUserProfile: either a_form or a_email and a_adviser_id need to be set.");
		} 
		
		if ($a_form !== null) {
			$form = $a_form;
			$form->setValuesByPost();
		}
		else {
			$form = $this->buildNAForm($a_email, $a_adviser_id);
		}
		
		require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
		$title = new catTitleGUI("gev_registration", null, "GEV_img/ico-head-registration.png");
		
		$tpl = new ilTemplate("tpl.gev_agent_profile.html", false, false, "Services/GEV/Registration");
		$tpl->setVariable("FORM", $form->getHTML());
		
		return   $title->render()
				.$tpl->get();
	}
	
	protected function registerNA() {
		$form = $this->buildNAForm();
		if (!$form->checkInput()) {
			return $this->inputUserProfile($form);
		}

		$user = new ilObjUser();
		$user->setLogin($form->getInput("username"));
		$user->setEmail($form->getInput("b_email"));
		$user->setPasswd($form->getInput("password"));
		$user->setLastname($form->getInput("lastname"));
		$user->setFirstname($form->getInput("firstname"));
		$user->setGender($form->getInput("gender"));
		$user->setUTitle($form->getInput("title"));
		$birthday = $form->getInput("birthday");
		$user->setBirthday($birthday["date"]);
		$user->setPhoneOffice($form->getInput("b_phone"));
		$user->setPhoneMobile($form->getInput("p_phone"));

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
		
		$user_utils->setPrivateEmail($form->getInput("p_email"));
		$user_utils->setPrivateStreet($form->getInput("p_street"));
		$user_utils->setPrivateCity($form->getInput("p_city"));
		$user_utils->setPrivateZipcode($form->getInput("p_zipcode"));
		
		
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
		
		$user_id = $user->getId();
		
		$user_utils->setADPNumberGEV($form->getInput("adp_gev"));
		//$user_utils->setADPNumberGEV($form->getInput("adp_vfs"));
		$user_utils->setJobNumber($form->getInput("position"));
		
		$role_utils = gevRoleUtils::getInstance();
		$role_utils->assignUserToGlobalRole($user_id, "NA");
		
		$user->update();
		
		$adviser_id = intval($form->getInput("adviser"));
		$this->sendConfirmationMail($user, $adviser_id);
		
		require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
		$title = new catTitleGUI("gev_agent_registration", null, "GEV_img/ico-head-evg_registration.png");
		
		ilUtil::sendSuccess($this->lng->txt("gev_na_registration_success"));
		$tpl = new ilTemplate("tpl.gev_na_successfull_registration.html", false, false, "Services/GEV/Registration");
		
		return	  $title->render()
				. $tpl->get();

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
		$adviser->setInfo($this->lng->txt("gev_na_adviser_input_info"));
		$adviser->setSize(40);
		$adviser->setRequired(true);
		$form->addItem($adviser);
		
		$chb1 = new ilCheckboxInputGUI("", "chb1");
		$chb1->setOptionTitle($this->lng->txt("evg_toc"));
		$chb1->setRequired(true);
		$form->addItem($chb1);
		
		return $form;
	}
	
	protected function buildNAForm($a_email = null, $a_adviser = null) {
		
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
		$form->addCommandButton("registerNA", $this->lng->txt("register"));
		$form->setFormAction($this->ctrl->getFormAction($this));
		
		$adviser = new ilHiddenInputGUI("adviser");
		if ($a_adviser !== null) {
			$adviser->setValue($a_adviser);
		}
		$form->addItem($adviser);
		
		$section1 = new ilFormSectionHeaderGUI();
		$section1->setTitle($this->lng->txt("gev_personal_data"));
		$form->addItem($section1);
		
		$username = new ilUserLoginInputGUI($this->lng->txt("gev_username_free"), "username");
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
		
		$title = new ilTextInputGUI($this->lng->txt("title"), "title");
		$form->addItem($title);
		
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
		
		$info = new ilNonEditableValueGUI("");
		$info->setValue($this->lng->txt("gev_private_contact_info"));
		$form->addItem($info);
		
		$p_email = new ilEMailInputGUI($this->lng->txt("email"), "p_email");
		if ($a_email !== null) {
			$p_email->setValue($a_email);
		}
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
		
		$section4 = new ilFormSectionHeaderGUI();
		$section4->setTitle($this->lng->txt("gev_further_information"));
		
		$adp_gev = new ilTextInputGUI($this->lng->txt("gev_adp_number"), "adp_gev");
		$adp_gev->setRequired(true);
		$form->addItem($adp_gev);
		
		/*$adp_vfs = new ilTextInputGUI($this->lng->txt("gev_adp_number_vfs"), "adp_vfs");
		$form->addItem($adp_vfs);*/
		
		$position = new ilTextInputGUI($this->lng->txt("gev_position"), "position");
		$form->addItem($position);
		
		return $form;
	}
	
	protected function sendConfirmationMail($a_user, $a_adviser_id) {
		require_once("Services/GEV/Utils/classes/class.gevNAUtils.php");
		require_once("Services/GEV/Mailing/classes/class.gevNARegistrationMails.php");
		require_once("Services/Utilities/classes/class.ilUtil.php");
		
		$na_utils = gevNAUtils::getInstance();
		
		$token = $na_utils->createConfirmationToken($a_user->getId(), $a_adviser_id);
		$link_base = ilUtil::_getHttpPath()."/na_confirmation.php?token=".$token;
		$link_confirm = $link_base."&action=confirm";
		$link_deny = $link_base."&action=deny";
		
		$na_mails = new gevNARegistrationMails( $a_user->getId()
											  , $link_confirm
											  , $link_deny
											  );
		$na_mails->send("na_confirmation", array($a_adviser_id));
	}
}

?>
