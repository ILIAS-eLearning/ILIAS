<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Course seraching GUI for Generali
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

class gevUserProfileGUI {
	public function __construct() {
		global $lng, $ilCtrl, $tpl, $ilUser, $ilLog;

		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;
		$this->log = &$ilLog;
		$this->user = &$ilUser;
		$this->user_id = $this->user->getId();
		$this->user_utils = gevUserUtils::getInstanceByObj($this->user);

		$this->tpl->getStandardTemplate();
		$this->tpl->setTitle($this->lng->txt("gev_user_profile"));
	}

	public function executeCommand() {
		$form = $this->buildUserProfileForm();
		return $form->getHtml();
	}
	
	protected function buildUserProfileForm() {
		require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		require_once("Services/Form/classes/class.ilUserLoginInputGUI.php");
		require_once("Services/Form/classes/class.ilFormSectionHeaderGUI.php");
		require_once("Services/Form/classes/class.ilTextInputGUI.php");
		require_once("Services/Form/classes/class.ilDateTimeInputGUI.php");
		require_once("Services/Form/classes/class.ilRadioGroupInputGUI.php");
		require_once("Services/Form/classes/class.ilRadioOption.php");
		require_once("Services/Form/classes/class.ilEMailInputGUI.php");
		require_once("Services/Form/classes/class.ilNonEditableValueGUI.php");
		
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		
		$section1 = new ilFormSectionHeaderGUI();
		$section1->setTitle($this->lng->txt("gev_personal_data"));
		$form->addItem($section1);
		
		$username = new ilUserLoginInputGUI($this->lng->txt("username"), "username");
		$username->setRequired(true);
		$username->setValue($this->user->getLogin());
		$form->addItem($username);
		
		$lastname = new ilNonEditableValueGUI($this->lng->txt("lastname"));
		$lastname->setValue($this->user->getLastname());
		$lastname->setRequired(true);
		$form->addItem($lastname);
		
		$firstname = new ilNonEditableValueGUI($this->lng->txt("firstname"));
		$firstname->setValue($this->user->getFirstname());
		$firstname->setRequired(true);
		$form->addItem($firstname);
		
		$gender = new ilRadioGroupInputGUI($this->lng->txt("gender"), "gender");
		$gender->addOption(new ilRadioOption($this->lng->txt("gender_m"), "m"));
		$gender->addOption(new ilRadioOption($this->lng->txt("gender_f"), "f"));
		$gender->setValue($this->user->getGender());
		$gender->setRequired(true);
		$form->addItem($gender);
		
		$adp = new ilNonEditableValueGUI($this->lng->txt("gev_adp_number"));
		$adp->setValue($this->user_utils->getADPNumber());
		$form->addItem($adp);
		
		$position_key = new ilNonEditableValueGUI($this->lng->txt("gev_job_number"));
		$position_key->setValue($this->user_utils->getJobNumber());
		$form->addItem($position_key);
		
		$birthday = new ilBirthdayInputGUI($this->lng->txt("birthday"), "birthday");
		$birthday->setDate($this->user->getBirthday());
		$birthday->setRequired(true);
		$form->addItem($birthday);
		
		$birthplace = new ilTextInputGUI($this->lng->txt("gev_birthplace"), "birthplace");
		$birthplace->setValue($this->user_utils->getBirthplace());
		$birthplace->setRequired(true);
		$form->addItem($birthplace);
		
		$birthname = new ilTextInputGUI($this->lng->txt("gev_birthname"), "birthname");
		$birthname->setValue($this->user_utils->getBirthname());
		$birthname->setRequired(true);
		$form->addItem($birthname);
		
		$ihk = new ilTextInputGUI($this->lng->txt("gev_ihk_number"), "ihk_number");
		$ihk->setValue($this->user_utils->getIHKNumber());
		$form->addItem($ihk);
		
		$ad_title = new ilNonEditableValueGUI($this->lng->txt("gev_ad_title"));
		$ad_title->setValue($this->user_utils->getADTitle());
		$form->addItem($ad_title);
		
		$agent_key = new ilNonEditableValueGUI($this->lng->txt("gev_agent_key"));
		$agent_key->setValue($this->user_utils->getAgentKey());
		$form->addItem($agent_key);
		
		$company_title = new ilNonEditableValueGUI($this->lng->txt("gev_company_title"));
		$company_title->setValue($this->user_utils->getCompanyTitle());
		$form->addItem($company_title);
		
		$org_unit = new ilNonEditableValueGUI($this->lng->txt("gev_org_unit"));
		$org_unit->setValue($this->user_utils->getOrgUnitTitle());
		$form->addItem($org_unit);
		
		
		
		$section2 = new ilFormSectionHeaderGUI();
		$section2->setTitle($this->lng->txt("gev_business_contact"));
		$form->addItem($section2);
		
		$b_email = new ilEMailInputGUI($this->lng->txt("gev_email"), "b_email");
		$b_email->setValue($this->user->getEmail());
		$form->addItem($b_email);
		
		$b_street = new ilTextInputGUI($this->lng->txt("street"), "b_street");
		$b_street->setValue($this->user->getStreet());
		$form->addItem($b_street);
		
		$b_city = new ilTextInputGUI($this->lng->txt("city"), "b_city");
		$b_city->setValue($this->user->getCity());
		$form->addItem($b_city);
		
		$b_zipcode = new ilTextInputGUI($this->lng->txt("zipcode"), "b_zipcode");
		$b_zipcode->setValue($this->user->getZipcode());
		$form->addItem($b_zipcode);
		
		$b_country = new ilTextInputGUI($this->lng->txt("country"), "b_country");
		$b_country->setValue($this->user->getCountry());
		$form->addItem($b_country);
		
		$b_phone = new ilTextInputGUI($this->lng->txt("phone"), "b_phone");
		$b_phone->setValue($this->user->getPhoneOffice());
		$form->addItem($b_phone);
		
		$b_fax = new ilTextInputGUI($this->lng->txt("fax"), "b_fax");
		$b_fax->setValue($this->user->getFax());
		$form->addItem($b_fax);
		
		
		
		$section3 = new ilFormSectionHeaderGUI();
		$section3->setTitle($this->lng->txt("gev_private_contact"));
		$form->addItem($section3);
		
		$p_email = new ilEMailInputGUI($this->lng->txt("email"), "p_email");
		$p_email->setValue($this->user_utils->getPrivateEmail());
		$p_email->setRequired(true);
		$form->addItem($p_email);
		
		$p_street = new ilTextInputGUI($this->lng->txt("street"), "p_street");
		$p_street->setValue($this->user_utils->getPrivateStreet());
		$form->addItem($p_street);
		
		$p_city = new ilTextInputGUI($this->lng->txt("city"), "p_city");
		$p_city->setValue($this->user_utils->getPrivateCity());
		$form->addItem($p_city);
		
		$p_zipcode = new ilTextInputGUI($this->lng->txt("zipcode"), "p_zipcode");
		$p_zipcode->setValue($this->user_utils->getPrivateZipcode());
		$form->addItem($p_zipcode);
		
		$p_country = new ilTextInputGUI($this->lng->txt("country"), "p_country");
		$p_country->setValue($this->user_utils->getPrivateState());
		$form->addItem($p_country);
		
		$p_phone = new ilTextInputGUI($this->lng->txt("gev_mobile"), "p_phone");
		$p_phone->setValue($this->user_utils->getPrivatePhone());
		$p_phone->setRequired(true);
		$form->addItem($p_phone);
		
		$p_fax = new ilTextInputGUI($this->lng->txt("fax"), "p_fax");
		$p_fax->setValue($this->user->getFax());
		$form->addItem($p_fax);
		
		
		
		$section4 = new ilFormSectionHeaderGUI();
		$section4->setTitle($this->lng->txt("gev_activity"));
		$form->addItem($section4);
		
		$entry_date = new ilNonEditableValueGUI($this->lng->txt("gev_entry_date"));
		$_entry_date = $this->user_utils->getEntryDate();
		$entry_date->setValue($_entry_date?ilDatePresentation::formatDate($_entry_date):"");
		$form->addItem($entry_date);
		
		$exit_date = new ilNonEditableValueGUI($this->lng->txt("gev_entry_date"));
		$_exit_date = $this->user_utils->getEntryDate();
		$exit_date->setValue($_exit_date?ilDatePresentation::formatDate($_exit_date):"");
		$form->addItem($exit_date);
		
		$status = new ilNonEditableValueGUI($this->lng->txt("gev_status"));
		$status->setValue($this->user_utils->getStatus());
		$form->addItem($status);
		
		return $form;
	}
}

?>