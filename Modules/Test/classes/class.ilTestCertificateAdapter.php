<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/

include_once "./Services/Certificate/classes/class.ilCertificateAdapter.php";

/**
* Test certificate adapter
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTest
*/
class ilTestCertificateAdapter extends ilCertificateAdapter
{
	private $object;
	
	/**
	* ilTestCertificateAdapter contructor
	*
	* @param object $object A reference to a test object
	*/
	function __construct(&$object)
	{
		$this->object =& $object;
	}

	/**
	* Returns the certificate path (with a trailing path separator)
	*
	* @return string The certificate path
	*/
	public function getCertificatePath()
	{
		return CLIENT_WEB_DIR . "/assessment/certificates/" . $this->object->getId() . "/";
	}
	
	/**
	* Returns an array containing all variables and values which can be exchanged in the certificate.
	* The values will be taken for the certificate preview.
	*
	* @return array The certificate variables
	*/
	public function getCertificateVariablesForPreview()
	{
		global $lng;
		include_once "./classes/class.ilFormat.php";
		$insert_tags = array(
			"[USER_FULLNAME]" => ilUtil::prepareFormOutput($lng->txt("certificate_var_user_fullname")),
			"[USER_FIRSTNAME]" => ilUtil::prepareFormOutput($lng->txt("certificate_var_user_firstname")),
			"[USER_LASTNAME]" => ilUtil::prepareFormOutput($lng->txt("certificate_var_user_lastname")),
			"[USER_TITLE]" => ilUtil::prepareFormOutput($lng->txt("certificate_var_user_title")),
			"[USER_INSTITUTION]" => ilUtil::prepareFormOutput($lng->txt("certificate_var_user_institution")),
			"[USER_DEPARTMENT]" => ilUtil::prepareFormOutput($lng->txt("certificate_var_user_department")),
			"[USER_STREET]" => ilUtil::prepareFormOutput($lng->txt("certificate_var_user_street")),
			"[USER_CITY]" => ilUtil::prepareFormOutput($lng->txt("certificate_var_user_city")),
			"[USER_ZIPCODE]" => ilUtil::prepareFormOutput($lng->txt("certificate_var_user_zipcode")),
			"[USER_COUNTRY]" => ilUtil::prepareFormOutput($lng->txt("certificate_var_user_country")),
			"[RESULT_PASSED]" => ilUtil::prepareFormOutput($lng->txt("certificate_var_result_passed")),
			"[RESULT_POINTS]" => ilUtil::prepareFormOutput($lng->txt("certificate_var_result_points")),
			"[RESULT_PERCENT]" => ilUtil::prepareFormOutput($lng->txt("certificate_var_result_percent")),
			"[MAX_POINTS]" => ilUtil::prepareFormOutput($lng->txt("certificate_var_max_points")),
			"[RESULT_MARK_SHORT]" => ilUtil::prepareFormOutput($lng->txt("certificate_var_result_mark_short")),
			"[RESULT_MARK_LONG]" => ilUtil::prepareFormOutput($lng->txt("certificate_var_result_mark_long")),
			"[TEST_TITLE]" => ilUtil::prepareFormOutput($this->object->getTitle()),
			"[DATE]" => ilFormat::formatDate(ilFormat::unixtimestamp2datetime(time()), "date", FALSE, FALSE),
			"[DATETIME]" => ilFormat::formatDate(ilFormat::unixtimestamp2datetime(time()), "datetime", TRUE, FALSE)
		);
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
		
		$active_id = $params["active_id"];
		$pass = $params["pass"];
		$userfilter = array_key_exists("userfilter", $params) ? $params["userfilter"] : "";
		$passedonly = array_key_exists("passedonly", $params) ? $params["passedonly"] : FALSE;
		if (strlen($pass))
		{
			$result_array =& $this->object->getTestResult($active_id, $pass);
		}
		else
		{
			$result_array =& $this->object->getTestResult($active_id);
		}
		if (($passedonly) && ($result_array["test"]["passed"] == FALSE)) return "";
		$passed = $result_array["test"]["passed"] ? $lng->txt("certificate_passed") : $lng->txt("certificate_failed");
		if (!$result_array["test"]["total_max_points"])
		{
			$percentage = 0;
		}
		else
		{
			$percentage = ($result_array["test"]["total_reached_points"]/$result_array["test"]["total_max_points"])*100;
		}
		$mark_obj = $this->object->mark_schema->getMatchingMark($percentage);
		$user_id = $this->object->_getUserIdFromActiveId($active_id);
		include_once './Services/User/classes/class.ilObjUser.php';
		$user_data = ilObjUser::_lookupFields($user_id);
		if (strlen($userfilter))
		{
			if (!@preg_match("/$userfilter/i", $user_data["lastname"] . ", " . $user_data["firstname"] . " " . $user_data["title"]))
			{
				return "";
			}
		}
		include_once "./classes/class.ilFormat.php";
		$insert_tags = array(
			"[USER_FULLNAME]" => ilUtil::prepareFormOutput(trim($user_data["title"] . " " . $user_data["firstname"] . " " . $user_data["lastname"])),
			"[USER_FIRSTNAME]" => ilUtil::prepareFormOutput($user_data["firstname"]),
			"[USER_LASTNAME]" => ilUtil::prepareFormOutput($user_data["lastname"]),
			"[USER_TITLE]" => ilUtil::prepareFormOutput($user_data["title"]),
			"[USER_INSTITUTION]" => ilUtil::prepareFormOutput($user_data["institution"]),
			"[USER_DEPARTMENT]" => ilUtil::prepareFormOutput($user_data["department"]),
			"[USER_STREET]" => ilUtil::prepareFormOutput($user_data["street"]),
			"[USER_CITY]" => ilUtil::prepareFormOutput($user_data["city"]),
			"[USER_ZIPCODE]" => ilUtil::prepareFormOutput($user_data["zipcode"]),
			"[USER_COUNTRY]" => ilUtil::prepareFormOutput($user_data["country"]),
			"[RESULT_PASSED]" => ilUtil::prepareFormOutput($passed),
			"[RESULT_POINTS]" => ilUtil::prepareFormOutput($result_array["test"]["total_reached_points"]),
			"[RESULT_PERCENT]" => sprintf("%2.2f", $percentage) . "%",
			"[MAX_POINTS]" => ilUtil::prepareFormOutput($result_array["test"]["total_max_points"]),
			"[RESULT_MARK_SHORT]" => ilUtil::prepareFormOutput($mark_obj->getShortName()),
			"[RESULT_MARK_LONG]" => ilUtil::prepareFormOutput($mark_obj->getOfficialName()),
			"[TEST_TITLE]" => ilUtil::prepareFormOutput($this->object->getTitle()),
			"[DATE]" => ilFormat::formatDate(ilFormat::unixtimestamp2datetime(time()), "date"),
			"[DATETIME]" => ilFormat::formatDate(ilFormat::unixtimestamp2datetime(time()), "datetime", TRUE)
		);
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
		
		$template = new ilTemplate("tpl.il_as_tst_certificate_edit.html", TRUE, TRUE, "Modules/Test");
		$template->setVariable("PH_INTRODUCTION", $lng->txt("certificate_ph_introduction"));
		$template->setVariable("PH_USER_FULLNAME", $lng->txt("certificate_ph_fullname"));
		$template->setVariable("PH_USER_FIRSTNAME", $lng->txt("certificate_ph_firstname"));
		$template->setVariable("PH_USER_LASTNAME", $lng->txt("certificate_ph_lastname"));
		$template->setVariable("PH_RESULT_PASSED", $lng->txt("certificate_ph_passed"));
		$template->setVariable("PH_RESULT_POINTS", $lng->txt("certificate_ph_resultpoints"));
		$template->setVariable("PH_RESULT_PERCENT", $lng->txt("certificate_ph_resultpercent"));
		$template->setVariable("PH_USER_TITLE", $lng->txt("certificate_ph_title"));
		$template->setVariable("PH_USER_STREET", $lng->txt("certificate_ph_street"));
		$template->setVariable("PH_USER_INSTITUTION", $lng->txt("certificate_ph_institution"));
		$template->setVariable("PH_USER_DEPARTMENT", $lng->txt("certificate_ph_department"));
		$template->setVariable("PH_USER_CITY", $lng->txt("certificate_ph_city"));
		$template->setVariable("PH_USER_ZIPCODE", $lng->txt("certificate_ph_zipcode"));
		$template->setVariable("PH_USER_COUNTRY", $lng->txt("certificate_ph_country"));
		$template->setVariable("PH_MAX_POINTS", $lng->txt("certificate_ph_maxpoints"));
		$template->setVariable("PH_RESULT_MARK_SHORT", $lng->txt("certificate_ph_markshort"));
		$template->setVariable("PH_RESULT_MARK_LONG", $lng->txt("certificate_ph_marklong"));
		$template->setVariable("PH_TEST_TITLE", $lng->txt("certificate_ph_testtitle"));
		$template->setVariable("PH_DATE", $lng->txt("certificate_ph_date"));
		$template->setVariable("PH_DATETIME", $lng->txt("certificate_ph_datetime"));
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
		$visibility = new ilRadioMatrixInputGUI($lng->txt("certificate_visibility"), "certificate_visibility");
		$options = array(
			0 => $lng->txt("certificate_visibility_always"),
			1 => $lng->txt("certificate_visibility_passed"),
			2 => $lng->txt("certificate_visibility_never")
		);
		$visibility->setOptions($options);
		$visibility->setInfo($lng->txt("certificate_visibility_introduction"));
		$visibility->setValue($form_fields["certificate_visibility"]);
		if (count($_POST)) $visibility->checkInput();
		$form->addItem($visibility);
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
		$form_fields["certificate_visibility"] = $_POST["certificate_visibility"];
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
		$form_fields["certificate_visibility"] = $this->object->getCertificateVisibility();
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
		$this->object->saveCertificateVisibility($form_fields["certificate_visibility"]);
	}

	/**
	* Returns the adapter type
	* This value will be used to generate file names for the certificates
	*
	* @return string A string value to represent the adapter type
	*/
	public function getAdapterType()
	{
		return "test";
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
}

?>
