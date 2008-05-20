<?php
/*
 +-----------------------------------------------------------------------------+
 | ILIAS open source                                                           |
 +-----------------------------------------------------------------------------+
 | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
 |                                                                             |
 | This program is free software; you can redistribute it and/or               |
 | modify it under the terms of the GNU General Public License                 |
 | as published by the Free Software Foundation; either version 2              |
 | of the License, or (at your option) any later version.                      |
 |                                                                             |
 | This program is distributed in the hope that it will be useful,             |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
 | GNU General Public License for more details.                                |
 |                                                                             |
 | You should have received a copy of the GNU General Public License           |
 | along with this program; if not, write to the Free Software                 |
 | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
 +-----------------------------------------------------------------------------+
 */


/**
 * soap server
 *
 * @author Stefan Meyer <smeyer@databay.de>
 * @version $Id$
 *
 * @package ilias
 */

class ilSoapFunctions {

	// These functions are wrappers for soap, since it cannot register methods inside classes

	// USER ADMINISTRATION
	public static function  login($client,$username,$password)
	{
#echo "Hallo";
		include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

		$sua =& new ilSoapUserAdministration();

		return $sua->login($client,$username,$password);
	}

	public static function  loginCAS($client, $PT, $user)
	{
		include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

		$sua =& new ilSoapUserAdministration();

		return $sua->loginCAS($client, $PT, $user);
	}

	public static function  loginLDAP($client, $username, $password)
	{
		include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

		$sua =& new ilSoapUserAdministration();

		return $sua->loginLDAP($client, $username, $password);
	}


	public static function  logout($sid)
	{
		include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

		$sua =& new ilSoapUserAdministration();

		return $sua->logout($sid);
	}
	public static function  lookupUser($sid,$user_name)
	{
		include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

		$sua =& new ilSoapUserAdministration();

		return $sua->lookupUser($sid,$user_name);
	}

	public static function  getUser($sid,$user_id)
	{
		include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

		$sua =& new ilSoapUserAdministration();

		return $sua->getUser($sid,$user_id);
	}

	public static function  updateUser($sid,$user_data)
	{
		include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

		$sua =& new ilSoapUserAdministration();

		return $sua->updateUser($sid,$user_data);
	}

	public static function  updatePassword($sid,$user_id,$new_password)
	{
		include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

		$sua =& new ilSoapUserAdministration();

		return $sua->updatePassword($sid,$user_id,$new_password);
	}

	public static function  addUser($sid,$user_data,$global_role_id)
	{
		include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

		$sua =& new ilSoapUserAdministration();

		return $sua->addUser($sid,$user_data,$global_role_id);
	}
	public static function  deleteUser($sid,$user_id)
	{
		include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

		$sua =& new ilSoapUserAdministration();

		return $sua->deleteUser($sid,$user_id);
	}


	// COURSE ADMINSTRATION
	public static function  addCourse($sid,$target_id,$crs_xml)
	{
		include_once './webservice/soap/classes/class.ilSoapCourseAdministration.php';

		$sca =& new ilSoapCourseAdministration();

		return $sca->addCourse($sid,$target_id,$crs_xml);
	}
	public static function  deleteCourse($sid,$course_id)
	{
		include_once './webservice/soap/classes/class.ilSoapCourseAdministration.php';

		$sca =& new ilSoapCourseAdministration();

		return $sca->deleteCourse($sid,$course_id);
	}
	public static function  assignCourseMember($sid,$course_id,$user_id,$type)
	{
		include_once './webservice/soap/classes/class.ilSoapCourseAdministration.php';

		$sca =& new ilSoapCourseAdministration();

		return $sca->assignCourseMember($sid,$course_id,$user_id,$type);
	}
	public static function  isAssignedToCourse($sid,$course_id,$user_id)
	{
		include_once './webservice/soap/classes/class.ilSoapCourseAdministration.php';

		$sca =& new ilSoapCourseAdministration();

		return $sca->isAssignedToCourse($sid,$course_id,$user_id);
	}

	public static function  excludeCourseMember($sid,$course_id,$user_id)
	{
		include_once './webservice/soap/classes/class.ilSoapCourseAdministration.php';

		$sca =& new ilSoapCourseAdministration();

		return $sca->excludeCourseMember($sid,$course_id,$user_id);
	}
	public static function  getCourseXML($sid,$course_id)
	{
		include_once './webservice/soap/classes/class.ilSoapCourseAdministration.php';

		$sca =& new ilSoapCourseAdministration();

		return $sca->getCourseXML($sid,$course_id);
	}
	public static function  updateCourse($sid,$course_id,$xml)
	{
		include_once './webservice/soap/classes/class.ilSoapCourseAdministration.php';

		$sca =& new ilSoapCourseAdministration();

		return $sca->updateCourse($sid,$course_id,$xml);
	}
	// Object admninistration
	public static function  getObjIdByImportId($sid,$import_id)
	{
		include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

		$soa =& new ilSoapObjectAdministration();

		return $soa->getObjIdByImportId($sid,$import_id);
	}

	public static function  getRefIdsByImportId($sid,$import_id)
	{
		include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

		$soa =& new ilSoapObjectAdministration();

		return $soa->getRefIdsByImportId($sid,$import_id);
	}
	public static function  getRefIdsByObjId($sid,$object_id)
	{
		include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

		$soa =& new ilSoapObjectAdministration();

		return $soa->getRefIdsByObjId($sid,$object_id);
	}


	public static function  getObjectByReference($sid,$a_ref_id,$user_id)
	{
		include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

		$soa =& new ilSoapObjectAdministration();

		return $soa->getObjectByReference($sid,$a_ref_id,$user_id);
	}

	public static function  getObjectsByTitle($sid,$a_title,$user_id)
	{
		include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

		$soa =& new ilSoapObjectAdministration();

		return $soa->getObjectsByTitle($sid,$a_title,$user_id);
	}

	public static function  addObject($sid,$a_target_id,$a_xml)
	{
		include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

		$soa =& new ilSoapObjectAdministration();

		return $soa->addObject($sid,$a_target_id,$a_xml);
	}

	public static function  addReference($sid,$a_source_id,$a_target_id)
	{
		include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

		$soa =& new ilSoapObjectAdministration();

		return $soa->addReference($sid,$a_source_id,$a_target_id);
	}

	public static function  deleteObject($sid,$reference_id)
	{
		include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

		$soa =& new ilSoapObjectAdministration();

		return $soa->deleteObject($sid,$reference_id);
	}

	public static function  removeFromSystemByImportId($sid,$import_id)
	{
		include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

		$soa =& new ilSoapObjectAdministration();

		return $soa->removeFromSystemByImportId($sid,$import_id);
	}

	public static function  updateObjects($sid,$obj_xml)
	{
		include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

		$soa =& new ilSoapObjectAdministration();

		return $soa->updateObjects($sid,$obj_xml);
	}
	public static function  searchObjects($sid,$types,$key,$combination,$user_id)
	{
		include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

		$soa =& new ilSoapObjectAdministration();

		return $soa->searchObjects($sid,$types,$key,$combination,$user_id);
	}

	public static function  getTreeChilds($sid,$ref_id,$types,$user_id)
	{
		include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

		$soa =& new ilSoapObjectAdministration();

		return $soa->getTreeChilds($sid,$ref_id,$types,$user_id);
	}

	public static function  getXMLTree($sid,$ref_id,$types,$user_id)
	{
		include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

		$soa =& new ilSoapObjectAdministration();

		return $soa->getXMLTree($sid,$ref_id,$types,$user_id);
	}



	// Rbac Tree public static function s
	public static function  getOperations($sid)
	{
		include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';

		$soa =& new ilSoapRBACAdministration();

		return $soa->getOperations($sid);
	}


	public static function  addUserRoleEntry($sid,$user_id,$role_id)
	{
		include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';

		$soa =& new ilSoapRBACAdministration();

		return $soa->addUserRoleEntry($sid,$user_id,$role_id);
	}

	public static function  deleteUserRoleEntry($sid,$user_id,$role_id)
	{
		include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';

		$soa =& new ilSoapRBACAdministration();

		return $soa->deleteUserRoleEntry($sid,$user_id,$role_id);
	}

	public static function  revokePermissions($sid,$ref_id,$role_id)
	{
		include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';

		$soa =& new ilSoapRBACAdministration();

		return $soa->revokePermissions($sid,$ref_id,$role_id);
	}

	public static function  grantPermissions($sid,$ref_id,$role_id,$permissions)
	{
		include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';

		$soa =& new ilSoapRBACAdministration();

		return $soa->grantPermissions($sid,$ref_id,$role_id,$permissions);
	}

	public static function  getLocalRoles($sid,$ref_id)
	{
		include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';

		$soa =& new ilSoapRBACAdministration();

		return $soa->getLocalRoles($sid,$ref_id);
	}

	public static function  getUserRoles($sid,$user_id)
	{
		include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';

		$soa =& new ilSoapRBACAdministration();

		return $soa->getUserRoles($sid,$user_id);
	}

	public static function  deleteRole($sid,$role_id)
	{
		include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';

		$soa =& new ilSoapRBACAdministration();

		return $soa->deleteRole($sid,$role_id);
	}

	public static function  addRole($sid,$target_id,$obj_xml)
	{
		include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';

		$soa =& new ilSoapRBACAdministration();

		return $soa->addRole($sid,$target_id,$obj_xml);
	}
	public static function  addRoleFromTemplate($sid,$target_id,$obj_xml,$template_id)
	{
		include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';

		$soa =& new ilSoapRBACAdministration();

		return $soa->addRoleFromTemplate($sid,$target_id,$obj_xml,$template_id);
	}

	public static function  getObjectTreeOperations($sid,$ref_id,$user_id)
	{
		include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';

		$soa =& new ilSoapRBACAdministration();

		return $soa->getObjectTreeOperations($sid,$ref_id,$user_id);
	}

	public static function  addGroup($sid,$target_id,$group_xml)
	{
		include_once './webservice/soap/classes/class.ilSoapGroupAdministration.php';

		$soa =& new ilSoapGroupAdministration();

		return $soa->addGroup($sid,$target_id,$group_xml);
	}

	public static function  groupExists($sid,$title)
	{
		include_once './webservice/soap/classes/class.ilSoapGroupAdministration.php';

		$soa =& new ilSoapGroupAdministration();

		return $soa->addGroup($sid,$title);
	}
	public static function  getGroup($sid,$ref_id)
	{
		include_once './webservice/soap/classes/class.ilSoapGroupAdministration.php';

		$soa =& new ilSoapGroupAdministration();

		return $soa->getGroup($sid,$ref_id);
	}

	public static function  assignGroupMember($sid,$group_id,$user_id,$type)
	{
		include_once './webservice/soap/classes/class.ilSoapGroupAdministration.php';

		$sca =& new ilSoapGroupAdministration();

		return $sca->assignGroupMember($sid,$group_id,$user_id,$type);
	}
	public static function  isAssignedToGroup($sid,$group_id,$user_id)
	{
		include_once './webservice/soap/classes/class.ilSoapGroupAdministration.php';

		$sca =& new ilSoapGroupAdministration();

		return $sca->isAssignedToGroup($sid,$group_id,$user_id);
	}

	public static function  excludeGroupMember($sid,$group_id,$user_id)
	{
		include_once './webservice/soap/classes/class.ilSoapGroupAdministration.php';

		$sca =& new ilSoapGroupAdministration();

		return $sca->excludeGroupMember($sid,$group_id,$user_id,$type);
	}


	public static function  sendMail($sid,$to,$cc,$bcc,$sender,$subject,$message,$attach)
	{
		include_once './webservice/soap/classes/class.ilSoapUtils.php';

		$sou =& new ilSoapUtils();
		$sou->disableSOAPCheck();
		$sou->ignoreUserAbort();

		return $sou->sendMail($sid,$to,$cc,$bcc,$sender,$subject,$message,$attach);
	}

	public static function  ilClone($sid,$copy_identifier)
	{
		include_once './webservice/soap/classes/class.ilSoapUtils.php';

		$sou = new ilSoapUtils();
		$sou->disableSOAPCheck();
		$sou->ignoreUserAbort();

		return $sou->ilClone($sid,$copy_identifier);
	}
	public static function  ilCloneDependencies($sid,$copy_identifier)
	{
		include_once './webservice/soap/classes/class.ilSoapUtils.php';

		$sou = new ilSoapUtils();
		$sou->disableSOAPCheck();
		$sou->ignoreUserAbort();

		return $sou->ilCloneDependencies($sid,$copy_identifier);
	}

	public static function  saveQuestionResult($sid,$user_id,$test_id,$question_id,$pass,$solution)
	{
		include_once './webservice/soap/classes/class.ilSoapTestAdministration.php';

		$sass =& new ilSoapTestAdministration();

		return $sass->saveQuestionResult($sid,$user_id,$test_id,$question_id,$pass,$solution);
	}

	public static function  saveQuestion($sid,$active_id,$question_id,$pass,$solution)
	{
		include_once './webservice/soap/classes/class.ilSoapTestAdministration.php';

		$sass =& new ilSoapTestAdministration();

		return $sass->saveQuestion($sid,$active_id,$question_id,$pass,$solution);
	}

	public static function  getQuestionSolution($sid,$active_id,$question_id,$pass)
	{
		include_once './webservice/soap/classes/class.ilSoapTestAdministration.php';

		$sass =& new ilSoapTestAdministration();

		return $sass->getQuestionSolution($sid,$active_id,$question_id,$pass);
	}

	public static function  saveTempFileAsMediaObject($sid,$name,$tmp_name)
	{
		include_once './webservice/soap/classes/class.ilSoapUtils.php';

		$sou =& new ilSoapUtils();
		$sou->disableSOAPCheck();

		return $sou->saveTempFileAsMediaObject($sid, $name, $tmp_name);
	}

	public static function  getMobsOfObject($sid, $a_type, $a_id)
	{
		include_once './webservice/soap/classes/class.ilSoapUtils.php';

		$sou =& new ilSoapUtils();
		$sou->disableSOAPCheck();

		return $sou->getMobsOfObject($sid, $a_type, $a_id);
	}

	public static function  getStructureObjects ($sid, $ref_id) {
		include_once './webservice/soap/classes/class.ilSoapStructureObjectAdministration.php';

		$sca = & new ilSOAPStructureObjectAdministration();

		return $sca->getStructureObjects ($sid, $ref_id);
	}

	public static function  getRoles($sid, $role_type, $id)
	{
		include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';

		$soa =& new ilSoapRBACAdministration();

		return $soa->getRoles($sid, $role_type, $id);
	}

	public static function  importUsers ($sid, $folder_id, $usr_xml, $conflict_rule, $send_account_mail)
	{
		include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

		$sua =& new ilSoapUserAdministration();

		return $sua->importUsers($sid, $folder_id, $usr_xml, $conflict_rule, $send_account_mail);
	}

	public static function  getUsersForContainer ($sid, $ref_id, $attach_roles, $active)
	{
		include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

		$sua =& new ilSoapUserAdministration();

		return $sua->getUsersForContainer($sid, $ref_id, $attach_roles, $active);
	}

	public static function  getUsersForRole ($sid, $role_id, $attach_roles, $active)
	{
		include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

		$sua =& new ilSoapUserAdministration();

		return $sua->getUserForRole($sid, $role_id, $attach_roles, $active);
	}


	public static function  searchUser ($sid, $a_keyfields, $query_operator, $a_keyvalues, $attach_roles, $active) {
		include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

		$sua =& new ilSoapUserAdministration();

		return $sua->searchUser ($sid, $a_keyfields, $query_operator, $a_keyvalues, $attach_roles, $active);

	}

	public static function  hasNewMail($sid)
	{
		include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

		$sua =& new ilSoapUserAdministration();

		return $sua->hasNewMail($sid);
	}

	public static function  getNIC($sid) {
		include_once './webservice/soap/classes/class.ilSoapAdministration.php';
		$soa = & new ilSoapAdministration();
		return $soa->getNIC($sid);
	}

	public static function  getExerciseXML ($sid, $ref_id, $attachFileContentsMode) {
		include_once './webservice/soap/classes/class.ilSoapExerciseAdministration.php';
		$sta = & new ilSoapExerciseAdministration();
		return $sta->getExerciseXML($sid, $ref_id, $attachFileContentsMode);

	}


	public static function  updateExercise ($sid, $ref_id, $xml) {
		include_once './webservice/soap/classes/class.ilSoapExerciseAdministration.php';
		$sta = & new ilSoapExerciseAdministration();
		return $sta->updateExercise($sid, $ref_id, $xml);

	}

	public static function  addExercise ($sid, $ref_id, $xml) {
		include_once './webservice/soap/classes/class.ilSoapExerciseAdministration.php';
		$sta = & new ilSoapExerciseAdministration();
		return $sta->addExercise($sid, $ref_id, $xml);

	}

	public static function  getFileXML ($sid, $ref_id, $attachFileContentsMode)
	{
		include_once './webservice/soap/classes/class.ilSoapFileAdministration.php';
		$sta = & new ilSoapFileAdministration();
		return $sta->getFileXML($sid, $ref_id, $attachFileContentsMode);

	}


	public static function  updateFile ($sid, $ref_id, $xml)
	{
		include_once './webservice/soap/classes/class.ilSoapFileAdministration.php';
		$sta = & new ilSoapFileAdministration();
		return $sta->updateFile($sid, $ref_id, $xml);

	}

	public static function  addFile ($sid, $ref_id, $xml)
	{
		include_once './webservice/soap/classes/class.ilSoapFileAdministration.php';
		$sta = & new ilSoapFileAdministration();
		return $sta->addFile($sid, $ref_id, $xml);

	}

	public static function  getObjIdsByRefIds($sid, $ref_ids)
	{
		include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

		$soa =& new ilSoapObjectAdministration();

		return $soa->getObjIdsByRefIds($sid, $ref_ids);
	}

	public static function  getUserXML($sid,$user_ids, $attach_roles)
	{
		include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

		$sua =& new ilSoapUserAdministration();

		return $sua->getUserXML($sid, $user_ids, $attach_roles);
	}

	public static function  updateGroup($sid, $ref_id, $grp_xml)
	{
		include_once './webservice/soap/classes/class.ilSoapGroupAdministration.php';

		$sua =& new ilSoapGroupAdministration();

		return $sua->updateGroup($sid,$ref_id, $grp_xml);
	}

	public static function  getIMSManifestXML($sid, $ref_id) {
		include_once './webservice/soap/classes/class.ilSoapSCORMAdministration.php';

		$sua =& new ilSoapSCORMAdministration();

		return $sua->getIMSManifestXML($sid,$ref_id);
	}

	/**
	 * copy object in repository
	 * $sid	session id
	 * $settings_xml contains copy wizard settings following ilias_copy_wizard_settings.dtd
	 */
	public static function  copyObject($sid, $copy_settings_xml) {
		include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

		$soa =& new ilSoapObjectAdministration();

		return $soa->copyObject($sid, $copy_settings_xml);

	}
	
 	/** move object in repository
	 * @param $sid	session id
	 * @param $refid  source iod
	 * @param $target target ref id
	 * @return int refid of new location, -1 if not successful
	 */
	public static function  moveObject($sid, $ref_id, $target_id) 
	{
		include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

		$soa = new ilSoapObjectAdministration();

		return $soa->moveObject($sid, $ref_id, $target_id);
	}

		
	/**
	 * get results of test
	 *
	 * @param string $sid
	 * @param int $ref_id
	 * @param boolean $sum_only
	 *
	 * @return XMLResultSet with columns firstname, lastname, matriculation, maximum points, received points
	 */

	public static function  getTestResults ($sid, $ref_id,$sum_only) {
		include_once './webservice/soap/classes/class.ilSoapTestAdministration.php';

		$soa = new ilSoapTestAdministration();

		return $soa->getTestResults($sid, $ref_id,$sum_only);
		
	}
	
	/**
	 * return courses for users depending on the status
	 *
	 * @param string $sid
	 * @param string $parameters xmlString following xmlResultSet
	 * @return string xmlResultSet
	 */
	public static function  getCoursesForUser($sid, $parameters) {
		include_once 'webservice/soap/classes/class.ilSoapCourseAdministration.php';
		$soc = new ilSoapCourseAdministration();
		return $soc->getCoursesForUser($sid, $parameters);
	}
	
/**
	 * return courses for users depending on the status
	 *
	 * @param string $sid
	 * @param string $parameters xmlString following xmlResultSet
	 * @return string xmlResultSet
	 */
	public static function  getGroupsForUser($sid, $parameters) {
		include_once 'webservice/soap/classes/class.ilSoapGroupAdministration.php';
		$soc = new ilSoapGroupAdministration();
		return $soc->getGroupsForUser($sid, $parameters);
	}
	
	public static function  getPathForRefId($sid, $ref_id) {
		include_once 'webservice/soap/classes/class.ilSoapObjectAdministration.php';
		$soa = new ilSoapObjectAdministration();
		return $soa->getPathForRefId($sid, $ref_id);
	}
	
	public static function  searchRoles ($sid, $key, $combination, $role_type)
	{
		include_once 'webservice/soap/classes/class.ilSoapRBACAdministration.php';		
		$roa = new ilSoapRBACAdministration();
		return $roa->searchRoles($sid, $key, $combination, $role_type);
	}

	
	public static function  getInstallationInfoXML() {
		include_once 'webservice/soap/classes/class.ilSoapAdministration.php';		
		$roa = new ilSoapAdministration();
		return $roa->getInstallationInfoXML();
	}
	
	public static function  getClientInfoXML($clientid) {
		include_once 'webservice/soap/classes/class.ilSoapAdministration.php';		
		$roa = new ilSoapAdministration();
		return $roa->getClientInfoXML($clientid);
	}
	
	/**
	 * builds http path if no client is available
	 *
	 * @return string
	 */
	public static function  buildHTTPPath() {
	    if($_SERVER["HTTPS"] == "on")
		{
			$protocol = 'https://';
		}
		else
		{
			$protocol = 'http://';
		}
		$host = $_SERVER['HTTP_HOST'];

		$path = dirname($_SERVER['REQUEST_URI']);

		//dirname cuts the last directory from a directory path e.g content/classes return content
		include_once 'Services/Utilities/classes/class.ilUtil.php';
		$module = ilUtil::removeTrailingPathSeparators(ILIAS_MODULE);

		$dirs = explode('/',$module);
		$uri = $path;
		foreach($dirs as $dir)
		{
			$uri = dirname($uri);
		}
		return ilUtil::removeTrailingPathSeparators($protocol.$host.$uri);	
	}
}

/*	function  ilClone($sid,$copy_identifier)
	{
		return ilSoapFunctions::ilClone($sid,$copy_identifier);
	}
	
	function  ilCloneDependencies($sid,$copy_identifier)
	{
		return ilSoapFunctions::ilCloneDependencies($sid,$copy_identifier);
	}*/

	?>