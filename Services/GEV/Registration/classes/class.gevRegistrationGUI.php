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
		
		require_once("gev_utils.php");
		$import = get_gev_import();
		
		if($import->isAgent($form->getInput("position"))) {
			return $this->inputUserProfile($res[0]);
		}
		else {
			return $this->checkEVGRegistration($res[0], $import);
		}
	}

	protected function inputUserProfile($a_form = null) {
		if ($a_form !== null) {
			$form = $a_form;
			$form->setValuesByPost();
		}
		else {
			$form = $this->buildAgentForm();
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
		
		$user = new ilUserObj();
		$user->setLogin($form->getInput("username"));
		$user->setEmail($form->getInput("b_email"));
		$user->setPassword($form->getInput("password1"));
		$user->setLastname($form->getInput("lastname"));
		$user->setFirstname($form->getInput("firstname"));
		$user->setGender($form->getInput("gender"));
		$birthday = $form->getInput("birthday");
		$user->setBirthday($birthday["date"]);
		$this->user->setStreet($form->getInput("b_street"));
		$this->user->setCity($form->getInput("b_city"));
		$this->user->setZipcode($form->getInput("b_zipcode"));
		$this->user->setCountry($form->getInput("b_country"));
		$this->user->setPhoneOffice($form->getInput("b_phone"));
		// is not active, owner is root
		$user->setActive(0, 6);
		$user->setTimeLimitUnlimited(true);
		// user already agreed at registration
		$now = new ilDateTime(time(),IL_CAL_UNIX);
		$ilias_user->setAgreeDate($now->get(IL_CAL_DATETIME));
		
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
		
		include_once './Services/Registration/classes/class.ilRegistrationMimeMailNotification.php';

		$mail = new ilRegistrationMimeMailNotification();
		$mail->setType(ilRegistrationMimeMailNotification::TYPE_NOTIFICATION_ACTIVATION);
		$mail->setRecipients(array($user));
		$mail->setAdditionalInformation(
			array( 'usr'           => $this
				 , 'hash_lifetime' => 5 * 60 // in minutes
				 )
			);
		$mail->send();
		
		require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
		$title = new catTitleGUI("gev_agent_registration", null, "GEV_img/ico-head-evg_registration.png");
		
		ilUtil::sendSuccess($this->lng->txt("gev_evg_registration_success"));
		$tpl = new ilTemplate("tpl.gev_agent_successfull_registration.html", false, false, "Services/GEV/Registration");
		
		return	  $title->render()
				. $tpl->get();
	}
	
	protected function checkEVGRegistration($a_form, $a_import) {
		require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
		$title = new catTitleGUI("gev_evg_registration", null, "GEV_img/ico-head-evg_registration.png");
	
		$error = $import->registerEVG( $form->getInput("position")
								  , $form->getInput("email"));

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
		
		return array($form, $err);
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
		
		// check for exisiting username?
		// check password

		return array($form, $err);
	}

	protected function buildAgentForm() {
		
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
		
		$form = new ilPropertyFormGUI();
		$form->addCommandButton("registerAgent", $this->lng->txt("register"));
		$form->setFormAction($this->ctrl->getFormAction($this));
		
		$section1 = new ilFormSectionHeaderGUI();
		$section1->setTitle($this->lng->txt("gev_personal_data"));
		$form->addItem($section1);
		
		$username = new ilUserLoginInputGUI($this->lng->txt("username"), "username");
		$username->setRequired(true);
		$form->addItem($username);
		
		$password1 = new ilPasswordInputGUI($this->lng->txt("password"), "password1");
		$password1->setRequired(true);
		$form->addItem($password1);
		
		$password2 = new ilPasswordInputGUI($this->lng->txt("password"), "password2");
		$password2->setRequired(true);
		$form->addItem($password2);
		
		$lastname = new ilTextInputGUI($this->lng->txt("lastname"));
		$lastname->setRequired(true);
		$form->addItem($lastname);
		
		$firstname = new ilTextInputGUI($this->lng->txt("firstname"));
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