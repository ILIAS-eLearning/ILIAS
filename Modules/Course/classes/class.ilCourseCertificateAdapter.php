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
* @version	$Id: class.ilTestCertificateAdapter.php 30898 2011-09-29 12:47:24Z jluetzen $
* @ingroup ModulesTest
*/
class ilCourseCertificateAdapter extends ilCertificateAdapter
{
	protected $object;
	protected static $has_certificate = array();
	
	/**
	* ilTestCertificateAdapter contructor
	*
	* @param object $object A reference to a test object
	*/
	function __construct(&$object)
	{
		global $lng;
		$this->object =& $object;
		$lng->loadLanguageModule('certificate');
	}

	/**
	* Returns the certificate path (with a trailing path separator)
	*
	* @return string The certificate path
	*/
	public function getCertificatePath()
	{
		return CLIENT_WEB_DIR . "/course/certificates/" . $this->object->getId() . "/";
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
		
		$vars = $this->getBaseVariablesForPreview(false);
		$vars["COURSE_TITLE"] = ilUtil::prepareFormOutput($this->object->getTitle());
		
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
	
		$user_id = $params["user_id"];
		
		include_once './Services/User/classes/class.ilObjUser.php';
		$user_data = ilObjUser::_lookupFields($user_id);
		
		include_once './Modules/Course/classes/class.ilCourseParticipants.php';
		$completion_date = ilCourseParticipants::getDateTimeOfPassed($this->object->getId(), $user_id);		
		
		$vars = $this->getBaseVariablesForPresentation($user_data, null, $completion_date);		
		$vars["COURSE_TITLE"] = ilUtil::prepareFormOutput($this->object->getTitle());
		
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
		global $lng;
		
		$vars = $this->getBaseVariablesDescription(false);
		$vars["COURSE_TITLE"] = $lng->txt("crs_title");
				
		$template = new ilTemplate("tpl.il_as_tst_certificate_edit.html", TRUE, TRUE, "Modules/Test");	
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
	* Returns the adapter type
	* This value will be used to generate file names for the certificates
	*
	* @return string A string value to represent the adapter type
	*/
	public function getAdapterType()
	{
		return "course";
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
		global $ilDB;
		
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
	
	/**
	 * Check if user has certificate for course
	 * 
	 * Used in ilObjCourseListGUI 
	 * 
	 * @param int $a_usr_id
	 * @param int $a_obj_id
	 * @return bool 
	 */
	static function _hasUserCertificate($a_usr_id, $a_obj_id)
	{
		if (isset(self::$has_certificate[$a_usr_id][$a_obj_id]))
		{
			return self::$has_certificate[$a_usr_id][$a_obj_id];
		}
		
		// obsolete?
		include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
		return ilCourseParticipants::getDateTimeOfPassed($a_obj_id, $a_usr_id);				
	}
}

?>