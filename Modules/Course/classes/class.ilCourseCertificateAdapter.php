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
// cat-tms-patch start
include_once "./Services/TMS/Certificate/classes/class.ilTMSCertificatePlaceholders.php";
// cat-tms-patch end

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

	// cat-tms-patch start
	/**
	 * @var \ilTMSCertificatePlaceholders
	 */
	protected $tms_adapter;
	// cat-tms-patch end

	/**
	* ilTestCertificateAdapter contructor
	*
	* @param object $object A reference to a test object
	*/
	function __construct($object)
	{
		$this->object = $object;
		parent::__construct();

		// cat-tms-patch start
		global $DIC;
		$this->g_lng = $DIC->language();
		$this->g_lng->loadLanguageModule("tms");
		$this->g_db = $DIC->database();
		$this->g_tree = $DIC->repositoryTree();
		$this->g_objDefinition = $DIC["objDefinition"];
		// cat-tms-patch end
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
		$vars = $this->getBaseVariablesForPreview(false);
		$vars["COURSE_TITLE"] = ilUtil::prepareFormOutput($this->object->getTitle());

		// cat-tms-patch start
		$vars = array_merge($vars, $this->getTMSVariablesForPreview());
		// cat-tms-patch end

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
		
		include_once './Services/User/classes/class.ilObjUser.php';
		$user_data = ilObjUser::_lookupFields($user_id);
		
		include_once './Modules/Course/classes/class.ilCourseParticipants.php';
		$completion_date = ilCourseParticipants::getDateTimeOfPassed($this->object->getId(), $user_id);		
		
		$vars = $this->getBaseVariablesForPresentation($user_data, null, $completion_date);		
		$vars["COURSE_TITLE"] = ilUtil::prepareFormOutput($this->object->getTitle());

		// cat-tms-patch start
		$vars = array_merge($vars, $this->getTMSVariablesForPresentation((int)$user_id));
		// cat-tms-patch end

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
		$vars["COURSE_TITLE"] = $this->lng->txt("crs_title");

		// cat-tms-patch start
		$vars = array_merge($vars, $this->getTMSVariablesDescription());
		// cat-tms-patch end

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
		self::_preloadListData($a_usr_id, $a_obj_id);

		if(isset(self::$has_certificate[$a_usr_id][$a_obj_id]))
		{
			return self::$has_certificate[$a_usr_id][$a_obj_id];
		}
		return false;
	}

	// cat-tms-patch start
	/**
	 * Get all tms placeholder for description
	 *
	 * @return string[]
	 */
	protected function getTMSVariablesDescription() {
		$ret = array();
		$ret["COURSE_TYPE"] = $this->g_lng->txt("pl_course_type");
		$ret["COURSE_STARTDATE"] = $this->g_lng->txt("pl_course_start_date");
		$ret["COURSE_ENDDATE"] = $this->g_lng->txt("pl_course_start_date");
		$ret["IDD_TIME"] = $this->g_lng->txt("pl_idd_learning_time");
		$ret["IDD_USER_TIME"] = $this->g_lng->txt("pl_idd_learning_time_user");
		return $ret;
	}

	/**
	 * Get preview values for tms placeholder
	 *
	 * @return string[]
	 */
	protected function getTMSVariablesForPreview() {
		$ret = array();
		$ret["COURSE_STARTDATE"] = ilDatePresentation::formatDate(new ilDate(time() - (24 * 60 * 60 * 10), IL_CAL_UNIX));
		$ret["COURSE_ENDDATE"] = ilDatePresentation::formatDate(new ilDate(time() - (24 * 60 * 60 * 5), IL_CAL_UNIX));
		$ret["COURSE_TYPE"] = $this->g_lng->txt("pl_course_type_preview");
		$ret["IDD_TIME"] = $this->g_lng->txt("pl_idd_learning_time_preview");
		$ret["IDD_USER_TIME"] = $this->g_lng->txt("pl_idd_learning_time_user_preview");
		return $ret;
	}

	/**
	 * Get real values for print
	 *
	 * @param int 	$user_id
	 *
	 * @return string[]
	 */
	protected function getTMSVariablesForPresentation($user_id) {
		assert('is_int($user_id)');
		$ret = array();
		$crs_ref_id = $this->object->getRefId();

		$crs_start = $this->object->getCourseStart();
		if($crs_start === null) {
			$ret["COURSE_STARTDATE"] = null;
			$ret["COURSE_ENDDATE"] = null;
		} else {
			$crs_end = $this->object->getCourseEnd();
			$ret["COURSE_STARTDATE"] = ilDatePresentation::formatDate($crs_start);
			$ret["COURSE_ENDDATE"] = ilDatePresentation::formatDate($crs_end);
		}

		$course_classification = $this->getFirstChildOfByType($crs_ref_id, "xccl");
		$cc_actions = $course_classification->getActions();
		$ret["COURSE_TYPE"] = array_shift($cc_actions->getTypeName($course_classification->getCourseClassification()->getType()));

		$edu_tracking = $this->getFirstChildOfByType($crs_ref_id, "xetr");
		$et_action = $edu_tracking->getActionsFor("IDD");
		$ret["IDD_TIME"] = $this->transformIDDLearningTimeToString($et_action->select()->getMinutes())." ".$this->g_lng->txt("form_hours");

		$course_member = $this->getFirstChildOfByType($crs_ref_id, "xcmb");
		$ret["IDD_USER_TIME"] = $this->transformIDDLearningTimeToString($course_member->getMinutesFor($user_id))." ".$this->g_lng->txt("form_hours");

		return $ret;
	}

	/**
	 * Transforms the idd minutes into printable string
	 *
	 * @param int 	$minutes
	 *
	 * @return string
	 */
	protected function transformIDDLearningTimeToString($minutes)
	{
		$hours = floor($minutes / 60);
		$minutes = $minutes - $hours * 60;
		return str_pad($hours, "2", "0", STR_PAD_LEFT).":".str_pad($minutes, "2", "0", STR_PAD_LEFT);
	}

	/**
	 * Get first child by type recursive
	 *
	 * @param int 	$ref_id
	 * @param string 	$search_type
	 *
	 * @return Object 	of search type
	 */
	protected function getFirstChildOfByType($ref_id, $search_type) {
		$childs = $this->g_tree->getChilds($ref_id);

		foreach ($childs as $child) {
			$type = $child["type"];
			if($type == $search_type) {
				return \ilObjectFactory::getInstanceByRefId($child["child"]);
			}

			if($this->g_objDefinition->isContainer($type)) {
				$ret = $this->getFirstChildOfByType($child["child"], $search_type);
				if(! is_null($ret)) {
					return $ret;
				}
			}
		}

		return null;
	}
	// cat-tms-patch end

}

?>
