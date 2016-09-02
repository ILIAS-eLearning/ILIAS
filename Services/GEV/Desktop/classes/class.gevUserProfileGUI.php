<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Course seraching GUI for Generali
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/GEV/WBD/classes/class.gevWBD.php");

class gevUserProfileGUI {
	static $telno_regexp = "/^((00|[+])49((\s|[-\/])?)|0)1[5-7][0-9]([0-9]?)((\s|[-\/])?)([0-9 ]{7,12})$/";

	
	
	public function __construct() {
		global $lng, $ilCtrl, $tpl, $ilUser, $ilLog;

		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;
		$this->log = &$ilLog;
		$this->user = &$ilUser;
		$this->user_id = $this->user->getId();
		$this->user_utils = gevUserUtils::getInstance($this->user_id);
		$this->wbd = gevWBD::getInstance($this->user_id);

		$this->tpl->getStandardTemplate();
		$this->tpl->setTitle($this->lng->txt("gev_user_profile"));
	}

	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		
		if (!$cmd) {
			$cmd = "show";
		}
		
		switch ($cmd) {
			case "show":
			case "save":
				return $this->$cmd();
			default:
				return $this->show();
		}
	}
	
	protected function show() {
		$form = $this->buildUserProfileForm();
		return $form->getHtml();
	}
	
	protected function save() {
		$form = $this->buildUserProfileForm();
		$form->setValuesByPost();
		
		if ($form->checkInput()) {
			$err = false;
			$telno = $form->getInput("p_phone");
			$telno_field = $form->getItemByPostVar("p_phone");
			
			if(   $form->getInput("username") !== $this->user->getLogin()
			   && ilObjUser::_loginExists($form->getInput("username"))) {
				$username_field = $form->getItemByPostVar("username");
				$username_field->setAlert($this->lng->txt("login_invalid"));
				
				$err = true;
			}
			
			if ($this->wbd->hasWBDRelevantRole() 
				&& $this->wbd->hasDoneWBDRegistration()
				&& !gevWBD::isValidBWVId($form->getInput("bwv_id"))
				&& ! $form->getInput("bwv_id") == ''
				) {
				$form->getItemByPostVar("bwv_id")->setAlert("gev_bwv_id_invalid");
				$err = true;
			}
			if (!gevUserUtils::checkISODateStringIsValid($form->getInput('entry_date'))) {
				$form->getItemByPostVar("entry_date")->setAlert($this->lng->txt("gev_entry_date_invalid"));
				$err = true;
			}
			
			if (!$err) {
				$birthday = $form->getInput("birthday");
				$bday = new ilDateTime($birthday["date"], IL_CAL_DATE);
				$form->getItemByPostVar("birthday")->setDate($bday);
				
				$this->user->updateLogin($form->getInput("username"));
				$this->user->setGender($form->getInput("gender"));
				$this->user->setBirthday($birthday["date"]);
				$this->user->setEmail($form->getInput("b_email"));
				$this->user->setStreet($form->getInput("b_street"));
				$this->user->setCity($form->getInput("b_city"));
				$this->user->setZipcode($form->getInput("b_zipcode"));
				$this->user->setPhoneOffice($form->getInput("b_phone"));
				$this->user->setPhoneMobile($form->getInput("p_phone"));

				$this->user_utils->setBirthplace($form->getInput("birthplace"));
				$this->user_utils->setBirthname($form->getInput("birthname"));
				$this->user_utils->setIHKNumber($form->getInput("ihk_number"));
				$this->user_utils->setPrivateStreet($form->getInput("p_street"));
				$this->user_utils->setPrivateCity($form->getInput("p_city"));
				$this->user_utils->setPrivateZipcode($form->getInput("p_zipcode"));

				$this->user_utils->setEntryDate(new ilDate($form->getInput('entry_date'),IL_CAL_DATE));

				$this->user->readUserDefinedFields();
				$this->user->update();
				
				$bwv_id = $form->getInput("bwv_id");
				if ($bwv_id && !$bwv_id=='') {
					$this->wbd->setWBDBWVId($bwv_id);
					$this->wbd->setWBDTPType(gevWBD::WBD_EDU_PROVIDER);
					
					$inp = $form->getItemByPostVar("bwv_id");
					if ($inp) {
						$inp->setDisabled(true);
					}
					$cb = $form->getItemByPostVar("wbd_acceptance");
					if ($cb) {
						$cb->setDisabled(true);
					}
				}
			
				ilUtil::sendSuccess($this->lng->txt("gev_user_profile_saved"), true);
				
				if($this->wbd->hasWBDRelevantRole() && !$this->wbd->hasDoneWBDRegistration()) {
					ilUtil::redirect("ilias.php?baseClass=gevDesktopGUI&cmdClass=toMyCourses");
				}
			}
			else {
				ilUtil::sendFailure($this->lng->txt("form_input_not_valid"));
			}
		}
		
		return $form->getHtml();
	}
	
	protected function buildUserProfileForm() {
		require_once("Services/Calendar/classes/class.ilDate.php");
		require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		require_once("Services/Form/classes/class.ilFormSectionHeaderGUI.php");
		require_once("Services/Form/classes/class.ilTextInputGUI.php");
		require_once("Services/Form/classes/class.ilDateTimeInputGUI.php");
		require_once("Services/Form/classes/class.ilRadioGroupInputGUI.php");
		require_once("Services/Form/classes/class.ilRadioOption.php");
		require_once("Services/Form/classes/class.ilEMailInputGUI.php");
		require_once("Services/Form/classes/class.ilNonEditableValueGUI.php");
		
		$form = new ilPropertyFormGUI();
		$form->addCommandButton("save", $this->lng->txt("save"));
		$form->setFormAction($this->ctrl->getFormAction($this));
		
		$section1 = new ilFormSectionHeaderGUI();
		$section1->setTitle($this->lng->txt("gev_personal_data"));
		$form->addItem($section1);
		
		$username = new ilTextInputGUI($this->lng->txt("gev_username_free"), "username");
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
		
		$adp = new ilNonEditableValueGUI($this->lng->txt("gev_adp_number_gev"));
		$adp->setValue($this->user_utils->getADPNumberGEV());
		$form->addItem($adp);
		
		$adp2 = new ilNonEditableValueGUI($this->lng->txt("gev_adp_number_vfs"));
		$adp2->setValue($this->user_utils->getADPNumberVFS());
		$form->addItem($adp2);
		
		$position_key = new ilNonEditableValueGUI($this->lng->txt("gev_position"));
		$position_key->setValue($this->user_utils->getJobNumber());
		$form->addItem($position_key);
		
		$birthday = new ilBirthdayInputGUI($this->lng->txt("birthday"), "birthday");
		$date = new ilDateTime($this->user->getBirthday(), IL_CAL_DATE);
		$birthday->setDate($date);
		$birthday->setRequired($this->wbd->forceWBDUserProfileFields());
		$birthday->setStartYear(1900);
		$form->addItem($birthday);
		
		$birthplace = new ilTextInputGUI($this->lng->txt("gev_birthplace"), "birthplace");
		$birthplace->setValue($this->user_utils->getBirthplace());
		//$birthplace->setRequired(true);
		$form->addItem($birthplace);
		
		$birthname = new ilTextInputGUI($this->lng->txt("gev_birthname"), "birthname");
		$bn = $this->user_utils->getBirthname();
		$birthname->setValue($bn?$bn:$this->user->getLastname());
		//$birthname->setRequired(true);
		$form->addItem($birthname);
		
		$ihk = new ilTextInputGUI($this->lng->txt("gev_ihk_number"), "ihk_number");
		$ihk->setValue($this->user_utils->getIHKNumber());
		$form->addItem($ihk);
		
		if ($this->wbd->hasWBDRelevantRole()) {
			$_bwv_id = $this->wbd->getWBDBWVId();
			$next_wbd_action = $this->wbd->getNextWBDAction();
			$new_user_actions = array(gevWBD::USR_WBD_NEXT_ACTION_NEW_TP_SERVICE
										,gevWBD::USR_WBD_NEXT_ACTION_NEW_TP_BASIS);
			if (!$_bwv_id) {
				$bwv_id_value = $this->lng->txt("gev_bwv_id_info");

				if(in_array($next_wbd_action,$new_user_actions)){
					$bwv_id_value = $this->lng->txt("gev_bwv_id_info_get_new");
				}
			}
			else {
				$bwv_id_value = $_bwv_id;
			}
			$bwv_id = new ilNonEditableValueGUI($this->lng->txt("gev_bwv_id"));
			$bwv_id->setValue($bwv_id_value);
			$form->addItem($bwv_id);

			$tp_type = new ilNonEditableValueGUI($this->lng->txt("gev_wbd_type"));
			$tp_type->setValue($this->wbd->getWBDTPType());
			$form->addItem($tp_type);
		}
		
		$ad_title = new ilNonEditableValueGUI($this->lng->txt("gev_ad_title"));
		$ad_title->setValue($this->user_utils->getADTitle());
		$form->addItem($ad_title);
		
		$agent_key = new ilNonEditableValueGUI($this->lng->txt("gev_agent_key"));
		$agent_key->setValue($this->user_utils->getAgentKey());
		$form->addItem($agent_key);
		
		$org_unit = new ilNonEditableValueGUI($this->lng->txt("gev_org_unit"), "", true);
		$org_unit->setValue(implode("<br />", $this->user_utils->getAllOrgUnitTitlesUserIsMember()));
		$form->addItem($org_unit);
		
		$section2 = new ilFormSectionHeaderGUI();
		$section2->setTitle($this->lng->txt("gev_business_contact"));
		$form->addItem($section2);
		
		$b_email = new ilEMailInputGUI($this->lng->txt("gev_email"), "b_email");
		$_b_email = $this->user->getEmail();
		$b_email->setValue($_b_email);
		$b_email->setRequired($this->wbd->forceWBDUserProfileFields());
		$form->addItem($b_email);
		
		$b_phone = new ilTextInputGUI($this->lng->txt("gev_profile_phone"), "b_phone");
		$b_phone->setValue($this->user->getPhoneOffice());
		$form->addItem($b_phone);
		
		$b_street = new ilTextInputGUI($this->lng->txt("street"), "b_street");
		$b_street->setValue($this->user->getStreet());
		$b_street->setRequired($this->wbd->forceWBDUserProfileFields());
		$form->addItem($b_street);
		
		$b_city = new ilTextInputGUI($this->lng->txt("city"), "b_city");
		$b_city->setValue($this->user->getCity());
		$b_city->setRequired($this->wbd->forceWBDUserProfileFields());
		$form->addItem($b_city);
		
		$b_zipcode = new ilTextInputGUI($this->lng->txt("zipcode"), "b_zipcode");
		$b_zipcode->setValue($this->user->getZipcode());
		$b_zipcode->setRequired($this->wbd->forceWBDUserProfileFields());
		$form->addItem($b_zipcode);
		
		$info = new ilNonEditableValueGUI("");
		$info->setValue($this->lng->txt("gev_private_contact_info"));
		$form->addItem($info);
		
		$p_phone = new ilTextInputGUI($this->lng->txt("gev_mobile"), "p_phone");
		$telno = $this->user_utils->getMobilePhone();
		$p_phone->setValue($telno);
		if (!preg_match(self::$telno_regexp, $telno) && $this->wbd->forceWBDUserProfileFields()) {
				$p_phone->setAlert($this->lng->txt("gev_telno_wbd_alert"));
		}
		$p_phone->setRequired($this->wbd->forceWBDUserProfileFields());
		$form->addItem($p_phone);
		
		$section3 = new ilFormSectionHeaderGUI();
		$section3->setTitle($this->lng->txt("gev_private_contact"));
		$form->addItem($section3);
		
		$p_street = new ilTextInputGUI($this->lng->txt("street"), "p_street");
		$p_street->setValue($this->user_utils->getPrivateStreet());
		$form->addItem($p_street);
		
		$p_city = new ilTextInputGUI($this->lng->txt("city"), "p_city");
		$p_city->setValue($this->user_utils->getPrivateCity());
		$form->addItem($p_city);
		
		$p_zipcode = new ilTextInputGUI($this->lng->txt("zipcode"), "p_zipcode");
		$p_zipcode->setValue($this->user_utils->getPrivateZipcode());
		$form->addItem($p_zipcode);
		
		$section4 = new ilFormSectionHeaderGUI();
		$section4->setTitle($this->lng->txt("gev_activity"));
		$form->addItem($section4);
		
		$entry_date = new ilTextInputGUI($this->lng->txt("gev_entry_date"),'entry_date');
		$_entry_date = $this->user_utils->getEntryDate() ? $this->user_utils->getEntryDate()->get(IL_CAL_DATE) : "";
		$entry_date->setRequired($this->wbd->forceWBDUserProfileFields());
		$entry_date->setInfo($this->lng->txt('gev_entry_date_info'));
		$entry_date->setValue($_entry_date ? $_entry_date : "");
		$form->addItem($entry_date);
		
		$exit_date = new ilNonEditableValueGUI($this->lng->txt("gev_exit_date"));
		$_exit_date = $this->user_utils->getExitDate();
		$exit_date->setValue($_exit_date?ilDatePresentation::formatDate($_exit_date):"");
		$form->addItem($exit_date);
		
		return $form;
	}
}
