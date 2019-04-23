<?php

class ilStudyProgrammeCertificateAdapter
extends ilCertificateAdapter
{
	function __construct($object)
	{
		$this->object = $object;
		parent::__construct();
	}

	/**
	 * Returns the certificate path (with a trailing path separator)
	 *
	 * @return string The certificate path
	 */
	public function getCertificatePath()
	{
		return CLIENT_WEB_DIR . "/studyProgramme/certificates/" . $this->object->getId() . "/";
	}

	/**
	 * Returns an array containing all variables and values which can be exchanged in the certificate.
	 * The values will be taken for the certificate preview.
	 *
	 * @return array The certificate variables
	 */
	public function getCertificateVariablesForPreview()
	{
		$vars = $this->getBaseVariablesForPreview(false);
		$vars['SP_TITLE'] = ilUtil::prepareFormOutput($this->object->getTitle());
		$vars['SP_DESCRIPTION'] = ilUtil::prepareFormOutput($this->object->getDescription());
		$vars['SP_TYPE'] = ilUtil::prepareFormOutput($this->object->getSubType());
		$vars['POINTS'] = ilUtil::prepareFormOutput($this->object->getPoints());
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
		$user_id = $params["user_id"];
		
		$user_data = ilObjUser::_lookupFields($user_id);
		
		$vars = $this->getBaseVariablesForPresentation($user_data, null, $completion_date);		
		$vars['SP_TITLE'] = ilUtil::prepareFormOutput($this->object->getTitle());
		$vars['SP_DESCRIPTION'] = ilUtil::prepareFormOutput($this->object->getDescription());
		$vars['SP_TYPE'] = ilUtil::prepareFormOutput($this->object->getSubType());
		$vars['POINTS'] = ilUtil::prepareFormOutput($this->object->getPoints());
		$insert_tags = array();
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
		$vars = $this->getBaseVariablesDescription(false);
		$vars["SP_TITLE"] = $this->lng->txt("crs_title");
				
		$template = new ilTemplate("tpl.il_as_tst_certificate_edit.html", TRUE, TRUE, "Modules/Test");	
		$template->setCurrentBlock("items");
		foreach($vars as $id => $caption)
		{
			$template->setVariable("ID", $id);
			$template->setVariable("TXT", $caption);
			$template->parseCurrentBlock();
		}

		$template->setVariable("PH_INTRODUCTION", $this->lng->txt("certificate_ph_introduction"));

		return $template->get();
	}

	/**
	* Returns the adapter type
	* This value will be used to generate file names for the certificates
	*
	* @return string A string value to represent the adapter type
	*/
	public function getAdapterType()
	{
		return "studyProgramme";
	}

	/**
	* Returns a certificate ID
	* This value will be used to generate unique file names for the certificates
	*
	* @return mixed A unique ID which represents a certificate
	*/
	public function getCertificateID()
	{
		return $this->object->getId();
	}

	/**
	 * Get certificate/passed status for all given objects and users
	 * 
	 * Used in ilObjCourseAccess for ilObjCourseListGUI 
	 * 
	 * @param array $a_usr_ids
	 * @param array $a_obj_ids 
	 */
	static function _preloadListData($a_usr_ids, $a_obj_ids)
	{
		if (!is_array($a_usr_ids))
		{
			$a_usr_ids = array($a_usr_ids);
		}
		if (!is_array($a_obj_ids))
		{
			$a_obj_ids = array($a_obj_ids);
		}
		foreach ($a_usr_ids as $usr_id)
		{
			foreach ($a_obj_ids as $obj_id)
			{
				self::$has_certificate[$usr_id][$obj_id] = false;
			}
		}
		
		include_once "Services/Certificate/classes/class.ilCertificate.php";
		if (ilCertificate::isActive())
		{
			$obj_active = ilCertificate::areObjectsActive($a_obj_ids);
		
			include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
			$data = ilCourseParticipants::getPassedUsersForObjects($a_obj_ids, $a_usr_ids);			
			foreach($data as $rec)
			{					
				if($obj_active[$rec["obj_id"]])
				{
					self::$has_certificate[$rec["usr_id"]][$rec["obj_id"]] = true;
				}
			}
		}
	}
}