<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Certificate/classes/class.ilCertificateAdapter.php";

/**
 * Skill certificate adapter
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 * @ingroup ServicesSkill
 */
class ilSkillCertificateAdapter extends ilCertificateAdapter
{
	protected $object;
	private $skill;
	private $skill_level_id;
	
	/**
	 * Contructor
	 *
	 * @param object		skill object
	 * @param object		skill level id
	 */
	function __construct($a_skill, $a_skill_level_id)
	{
		$this->skill = $a_skill;
		$this->skill_level_id = $a_skill_level_id;
		parent::__construct();
		$this->lng->loadLanguageModule('skmg');
	}

	/**
	 * Returns the certificate path (with a trailing path separator)
	 *
	 * @return string The certificate path
	 */
	public function getCertificatePath()
	{
		return CLIENT_WEB_DIR."/certificates/skill/".$this->skill->getId().
			"/".$this->skill_level_id."/";
	}
	
	/**
	 * Returns an array containing all variables and values which can be exchanged in the certificate.
	 * The values will be taken for the certificate preview.
	 *
	 * @return array The certificate variables
 	 */
	public function getCertificateVariablesForPreview()
	{
		global $lng, $ilUser;
		
		$old = ilDatePresentation::useRelativeDates();
		ilDatePresentation::setUseRelativeDates(false);
		
		$vars = $this->getBaseVariablesForPreview();
		$vars["SKILL_TITLE"] = ilUtil::prepareFormOutput($this->skill->getTitleForCertificate());
		$vars["SKILL_LEVEL_TITLE"] = ilUtil::prepareFormOutput($this->skill->getLevelTitleForCertificate($this->skill_level_id));
		$vars["SKILL_TRIGGER_TITLE"] = ilUtil::prepareFormOutput($this->skill->getTriggerTitleForCertificate($this->skill_level_id));
		
		ilDatePresentation::setUseRelativeDates($old);
		
		$insert_tags = array();
		foreach($vars as $id => $caption)
		{
			$insert_tags["[".$id."]"] = $caption;
		}		
		return $insert_tags;
	}

	/**
	* Returns an array containing all variables and values which can be exchanged in the certificate
	* The values should be calculated from real data. The $params parameter array should contain all
	* necessary information to calculate the values.
	*
	* @param array $params An array of parameters to calculate the certificate parameter values
	* @return array The certificate variables
	*/
	public function getCertificateVariablesForPresentation($params = array())
	{
		global $lng;
		
		$lng->loadLanguageModule('certificate');
		
		$user_data = $params["user_data"];
		
		$vars = $this->getBaseVariablesForPresentation($user_data, $params["last_access"], null);				
		$vars["SKILL_TITLE"] = ilUtil::prepareFormOutput($this->skill->getTitleForCertificate());
		$vars["SKILL_LEVEL_TITLE"] = ilUtil::prepareFormOutput($this->skill->getLevelTitleForCertificate($this->skill_level_id));
		$vars["SKILL_TRIGGER_TITLE"] = ilUtil::prepareFormOutput($this->skill->getTriggerTitleForCertificate($this->skill_level_id));
	
		// custom completion date
		$achievement_date = ilBasicSkill::lookupLevelAchievementDate($user_data["usr_id"], $this->skill_level_id);
		if ($achievement_date !== false)
		{			
			$old = ilDatePresentation::useRelativeDates();
			ilDatePresentation::setUseRelativeDates(false);
		
			$vars["DATE_COMPLETED"] = ilDatePresentation::formatDate(new ilDate($achievement_date, IL_CAL_DATETIME));
			$vars["DATETIME_COMPLETED"] = ilDatePresentation::formatDate(new ilDateTime($achievement_date, IL_CAL_DATETIME));
			
			ilDatePresentation::setUseRelativeDates($old);
		}
		else
		{
			$vars["DATE_COMPLETED"] = "";
			$vars["DATETIME_COMPLETED"] = "";
		}
		
		foreach($vars as $id => $caption)
		{
			$insert_tags["[".$id."]"] = $caption;
		}		
		return $insert_tags;
	}
	
	/**
	* Returns a description of the available certificate parameters. The description will be shown at
	* the bottom of the certificate editor text area.
	*
	* @return string The certificate parameters description
	*/
	public function getCertificateVariablesDescription()
	{
		global $lng;

		$lng->loadLanguageModule("skmg");
		
		$vars = $this->getBaseVariablesDescription();
		$vars["SKILL_TITLE"] = $lng->txt("skmg_cert_skill_title");
		$vars["SKILL_LEVEL_TITLE"] = $lng->txt("skmg_cert_skill_level_title");
		$vars["SKILL_TRIGGER_TITLE"] = $lng->txt("skmg_cert_skill_trigger_title");
		
		$template = new ilTemplate("tpl.certificate_edit.html", TRUE, TRUE, "Services/Skill");
		$template->setCurrentBlock("items");
		foreach($vars as $id => $caption)
		{
			$template->setVariable("ID", $id);
			$template->setVariable("TXT", $caption);
			$template->parseCurrentBlock();
		}

		$template->setVariable("PH_INTRODUCTION", $lng->txt("certificate_ph_introduction"));

		return $template->get();
	}

	/**
	* Allows to add additional form fields to the certificate editor form
	* This method will be called when the certificate editor form will built
	* using the ilPropertyFormGUI class. Additional fields will be added at the
	* bottom of the form.
	*
	* @param object $form An ilPropertyFormGUI instance
	* @param array $form_fields An array containing the form values. The array keys are the names of the form fields
	*/
	public function addAdditionalFormElements(&$form, $form_fields)
	{
		global $lng;
		/*$short_name = new ilTextInputGUI($lng->txt("certificate_short_name"), "short_name");
		$short_name->setRequired(TRUE);
		require_once "./Services/Utilities/classes/class.ilStr.php";
		$short_name->setValue(strlen($form_fields["short_name"]) ? $form_fields["short_name"] : ilStr::subStr($this->object->getTitle(), 0, 30));
		$short_name->setSize(30);
		if (strlen($form_fields["short_name"])) {
			$short_name->setInfo(str_replace("[SHORT_TITLE]", $form_fields["short_name"], $lng->txt("certificate_short_name_description")));
		} else {
			$short_name->setInfo($lng->txt("certificate_short_name_description"));
		}
		if (count($_POST)) $short_name->checkInput();
		$form->addItem($short_name);

		$visibility = new ilCheckboxInputGUI($lng->txt("certificate_enabled_scorm"), "certificate_enabled_scorm");
		$visibility->setInfo($lng->txt("certificate_enabled_scorm_introduction"));
		$visibility->setValue(1);
		if ($form_fields["certificate_enabled_scorm"])
		{
			$visibility->setChecked(TRUE);
		}
		if (count($_POST)) $visibility->checkInput();
		$form->addItem($visibility);*/
	}
	
	/**
	* Allows to add additional form values to the array of form values evaluating a
	* HTTP POST action.
	* This method will be called when the certificate editor form will be saved using
	* the form save button.
	*
	* @param array $form_fields A reference to the array of form values
	*/
	public function addFormFieldsFromPOST(&$form_fields)
	{
		//$form_fields["certificate_enabled_scorm"] = $_POST["certificate_enabled_scorm"];
		//$form_fields["short_name"] = $_POST["short_name"];
	}

	/**
	* Allows to add additional form values to the array of form values evaluating the
	* associated adapter class if one exists 
	* This method will be called when the certificate editor form will be shown and the
	* content of the form has to be retrieved from wherever the form values are saved.
	*
	* @param array $form_fields A reference to the array of form values
	*/
	public function addFormFieldsFromObject(&$form_fields)
	{
		global $ilSetting;
		//$scormSetting = new ilSetting("scorm");
		//$form_fields["certificate_enabled_scorm"] = $scormSetting->get("certificate_" . $this->object->getId());
		//$form_fields["short_name"] = $scormSetting->get("certificate_short_name_" . $this->object->getId());
	}
	
	/**
	* Allows to save additional adapter form fields
	* This method will be called when the certificate editor form is complete and the
	* form values will be saved.
	*
	* @param array $form_fields A reference to the array of form values
	*/
	public function saveFormFields(&$form_fields)
	{
		global $ilSetting;
		//$scormSetting = new ilSetting("scorm");
		//$scormSetting->set("certificate_" . $this->object->getId(), $form_fields["certificate_enabled_scorm"]);
		//$scormSetting->set("certificate_short_name_" . $this->object->getId(), $form_fields["short_name"]);
	}

	/**
	* Returns the adapter type
	* This value will be used to generate file names for the certificates
	*
	* @return string A string value to represent the adapter type
	*/
	public function getAdapterType()
	{
		return "skill";
	}

	/**
	* Returns a certificate ID
	* This value will be used to generate unique file names for the certificates
	*
	* @return mixed A unique ID which represents a certificate
	*/
	public function getCertificateID()
	{
		return $this->skill_level_id;
	}

	/**
	* Set the name of the certificate file
	* This method will be called when the certificate will be generated
	*
	* @return string The certificate file name
	*/
	public function getCertificateFilename($params = array())
	{
		global $lng;
		
		$basename = parent::getCertificateFilename($params);
		
		$user_data = $params["user_data"];
		if (!is_array($user_data))
		{
			$short_title = $this->skill->getShortTitleForCertificate();
			return strftime("%y%m%d", time()) . "_" . $lng->txt("certificate_var_user_lastname") . "_" . $short_title . "_" . $basename;
		}
		else
		{
			return strftime("%y%m%d", time()) . "_" . $user_data["lastname"] . "_" . $params["short_title"] . "_" . $basename;
		}
	}

	/**
	* Is called when the certificate is deleted
	* Add some adapter specific code if more work has to be done when the
	* certificate file was deleted
	*/
	public function deleteCertificate()
	{
		global $ilSetting;
		//$scormSetting = new ilSetting("scorm");
		//$scormSetting->delete("certificate_" . $this->object->getId());
	}
}

?>
