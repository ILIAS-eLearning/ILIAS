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
include_once './webservice/soap/lib/nusoap.php';

// These functions are wrappers for nusoap, since it cannot register methods inside classes

// USER ADMINISTRATION
function login($client,$username,$password)
{
	include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

	$sua =& new ilSoapUserAdministration();

	return $sua->login($client,$username,$password);
}

function loginCAS($client, $PT, $user)
{
	include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

	$sua =& new ilSoapUserAdministration();

	return $sua->loginCAS($client, $PT, $user);
}

function logout($sid)
{
	include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

	$sua =& new ilSoapUserAdministration();

	return $sua->logout($sid);
}
function lookupUser($sid,$user_name)
{
	include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

	$sua =& new ilSoapUserAdministration();

	return $sua->lookupUser($sid,$user_name);
}

function getUser($sid,$user_id)
{
	include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

	$sua =& new ilSoapUserAdministration();

	return $sua->getUser($sid,$user_id);
}

function updateUser($sid,$user_data)
{
	include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

	$sua =& new ilSoapUserAdministration();

	return $sua->updateUser($sid,$user_data);
}

function updatePassword($sid,$user_id,$new_password)
{
	include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

	$sua =& new ilSoapUserAdministration();

	return $sua->updatePassword($sid,$user_id,$new_password);
}

function addUser($sid,$user_data,$global_role_id)
{
	include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

	$sua =& new ilSoapUserAdministration();

	return $sua->addUser($sid,$user_data,$global_role_id);
}
function deleteUser($sid,$user_id)
{
	include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

	$sua =& new ilSoapUserAdministration();

	return $sua->deleteUser($sid,$user_id);
}


// COURSE ADMINSTRATION
function addCourse($sid,$target_id,$crs_xml)
{
	include_once './webservice/soap/classes/class.ilSoapCourseAdministration.php';

	$sca =& new ilSoapCourseAdministration();

	return $sca->addCourse($sid,$target_id,$crs_xml);
}
function deleteCourse($sid,$course_id)
{
	include_once './webservice/soap/classes/class.ilSoapCourseAdministration.php';

	$sca =& new ilSoapCourseAdministration();

	return $sca->deleteCourse($sid,$course_id);
}
function assignCourseMember($sid,$course_id,$user_id,$type)
{
	include_once './webservice/soap/classes/class.ilSoapCourseAdministration.php';

	$sca =& new ilSoapCourseAdministration();

	return $sca->assignCourseMember($sid,$course_id,$user_id,$type);
}
function isAssignedToCourse($sid,$course_id,$user_id)
{
	include_once './webservice/soap/classes/class.ilSoapCourseAdministration.php';

	$sca =& new ilSoapCourseAdministration();

	return $sca->isAssignedToCourse($sid,$course_id,$user_id);
}

function excludeCourseMember($sid,$course_id,$user_id)
{
	include_once './webservice/soap/classes/class.ilSoapCourseAdministration.php';

	$sca =& new ilSoapCourseAdministration();

	return $sca->excludeCourseMember($sid,$course_id,$user_id);
}
function getCourseXML($sid,$course_id)
{
	include_once './webservice/soap/classes/class.ilSoapCourseAdministration.php';

	$sca =& new ilSoapCourseAdministration();

	return $sca->getCourseXML($sid,$course_id);
}
function updateCourse($sid,$course_id,$xml)
{
	include_once './webservice/soap/classes/class.ilSoapCourseAdministration.php';

	$sca =& new ilSoapCourseAdministration();

	return $sca->updateCourse($sid,$course_id,$xml);
}
// Object admninistration
function getObjIdByImportId($sid,$import_id)
{
	include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

	$soa =& new ilSoapObjectAdministration();

	return $soa->getObjIdByImportId($sid,$import_id);
}

function getRefIdsByImportId($sid,$import_id)
{
	include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

	$soa =& new ilSoapObjectAdministration();

	return $soa->getRefIdsByImportId($sid,$import_id);
}
function getRefIdsByObjId($sid,$object_id)
{
	include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

	$soa =& new ilSoapObjectAdministration();

	return $soa->getRefIdsByObjId($sid,$object_id);
}


function getObjectByReference($sid,$a_ref_id,$user_id)
{
	include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

	$soa =& new ilSoapObjectAdministration();

	return $soa->getObjectByReference($sid,$a_ref_id,$user_id);
}

function getObjectsByTitle($sid,$a_title,$user_id)
{
	include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

	$soa =& new ilSoapObjectAdministration();

	return $soa->getObjectsByTitle($sid,$a_title,$user_id);
}

function addObject($sid,$a_target_id,$a_xml)
{
	include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

	$soa =& new ilSoapObjectAdministration();

	return $soa->addObject($sid,$a_target_id,$a_xml);
}

function addReference($sid,$a_source_id,$a_target_id)
{
	include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

	$soa =& new ilSoapObjectAdministration();

	return $soa->addReference($sid,$a_source_id,$a_target_id);
}

function deleteObject($sid,$reference_id)
{
	include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

	$soa =& new ilSoapObjectAdministration();

	return $soa->deleteObject($sid,$reference_id);
}

function removeFromSystemByImportId($sid,$import_id)
{
	include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

	$soa =& new ilSoapObjectAdministration();

	return $soa->removeFromSystemByImportId($sid,$import_id);
}

function updateObjects($sid,$obj_xml)
{
	include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

	$soa =& new ilSoapObjectAdministration();

	return $soa->updateObjects($sid,$obj_xml);
}
function searchObjects($sid,$types,$key,$combination,$user_id)
{
	include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

	$soa =& new ilSoapObjectAdministration();

	return $soa->searchObjects($sid,$types,$key,$combination,$user_id);
}

function getTreeChilds($sid,$ref_id,$types,$user_id)
{
	include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

	$soa =& new ilSoapObjectAdministration();

	return $soa->getTreeChilds($sid,$ref_id,$types,$user_id);
}

function getXMLTree($sid,$ref_id,$types,$user_id)
{
	include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

	$soa =& new ilSoapObjectAdministration();

	return $soa->getXMLTree($sid,$ref_id,$types,$user_id);
}



// Rbac Tree functions
function getOperations($sid)
{
	include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';

	$soa =& new ilSoapRBACAdministration();

	return $soa->getOperations($sid);
}


function addUserRoleEntry($sid,$user_id,$role_id)
{
	include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';

	$soa =& new ilSoapRBACAdministration();

	return $soa->addUserRoleEntry($sid,$user_id,$role_id);
}

function deleteUserRoleEntry($sid,$user_id,$role_id)
{
	include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';

	$soa =& new ilSoapRBACAdministration();

	return $soa->deleteUserRoleEntry($sid,$user_id,$role_id);
}

function revokePermissions($sid,$ref_id,$role_id)
{
	include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';

	$soa =& new ilSoapRBACAdministration();

	return $soa->revokePermissions($sid,$ref_id,$role_id);
}

function grantPermissions($sid,$ref_id,$role_id,$permissions)
{
	include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';

	$soa =& new ilSoapRBACAdministration();

	return $soa->grantPermissions($sid,$ref_id,$role_id,$permissions);
}

function getLocalRoles($sid,$ref_id)
{
	include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';

	$soa =& new ilSoapRBACAdministration();

	return $soa->getLocalRoles($sid,$ref_id);
}

function getUserRoles($sid,$user_id)
{
	include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';

	$soa =& new ilSoapRBACAdministration();

	return $soa->getUserRoles($sid,$user_id);
}

function deleteRole($sid,$role_id)
{
	include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';

	$soa =& new ilSoapRBACAdministration();

	return $soa->deleteRole($sid,$role_id);
}

function addRole($sid,$target_id,$obj_xml)
{
	include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';

	$soa =& new ilSoapRBACAdministration();

	return $soa->addRole($sid,$target_id,$obj_xml);
}
function addRoleFromTemplate($sid,$target_id,$obj_xml,$template_id)
{
	include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';

	$soa =& new ilSoapRBACAdministration();

	return $soa->addRoleFromTemplate($sid,$target_id,$obj_xml,$template_id);
}

function getObjectTreeOperations($sid,$ref_id,$user_id)
{
	include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';

	$soa =& new ilSoapRBACAdministration();

	return $soa->getObjectTreeOperations($sid,$ref_id,$user_id);
}

function addGroup($sid,$target_id,$group_xml)
{
	include_once './webservice/soap/classes/class.ilSoapGroupAdministration.php';

	$soa =& new ilSoapGroupAdministration();

	return $soa->addGroup($sid,$target_id,$group_xml);
}

function groupExists($sid,$title)
{
	include_once './webservice/soap/classes/class.ilSoapGroupAdministration.php';

	$soa =& new ilSoapGroupAdministration();

	return $soa->addGroup($sid,$title);
}
function getGroup($sid,$ref_id)
{
	include_once './webservice/soap/classes/class.ilSoapGroupAdministration.php';

	$soa =& new ilSoapGroupAdministration();

	return $soa->getGroup($sid,$ref_id);
}

function assignGroupMember($sid,$group_id,$user_id,$type)
{
	include_once './webservice/soap/classes/class.ilSoapGroupAdministration.php';

	$sca =& new ilSoapGroupAdministration();

	return $sca->assignGroupMember($sid,$group_id,$user_id,$type);
}
function isAssignedToGroup($sid,$group_id,$user_id)
{
	include_once './webservice/soap/classes/class.ilSoapGroupAdministration.php';

	$sca =& new ilSoapGroupAdministration();

	return $sca->isAssignedToGroup($sid,$group_id,$user_id);
}

function excludeGroupMember($sid,$group_id,$user_id)
{
	include_once './webservice/soap/classes/class.ilSoapGroupAdministration.php';

	$sca =& new ilSoapGroupAdministration();

	return $sca->excludeGroupMember($sid,$group_id,$user_id,$type);
}


function sendMail($sid,$to,$cc,$bcc,$sender,$subject,$message,$attach)
{
	include_once './webservice/soap/classes/class.ilSoapUtils.php';

	$sou =& new ilSoapUtils();
	$sou->disableSOAPCheck();
	$sou->ignoreUserAbort();

	return $sou->sendMail($sid,$to,$cc,$bcc,$sender,$subject,$message,$attach);
}

function ilClone($sid,$copy_identifier)
{
	include_once './webservice/soap/classes/class.ilSoapUtils.php';

	$sou = new ilSoapUtils();
	$sou->disableSOAPCheck();
	$sou->ignoreUserAbort();

	return $sou->ilClone($sid,$copy_identifier);
}
function ilCloneDependencies($sid,$copy_identifier)
{
	include_once './webservice/soap/classes/class.ilSoapUtils.php';

	$sou = new ilSoapUtils();
	$sou->disableSOAPCheck();
	$sou->ignoreUserAbort();

	return $sou->ilCloneDependencies($sid,$copy_identifier);
}

function saveQuestionResult($sid,$user_id,$test_id,$question_id,$pass,$solution)
{
	include_once './webservice/soap/classes/class.ilSoapUtils.php';

	$sou =& new ilSoapUtils();
	$sou->disableSOAPCheck();

	return $sou->saveQuestionResult($sid,$user_id,$test_id,$question_id,$pass,$solution);
}

function saveTempFileAsMediaObject($sid,$name,$tmp_name)
{
	include_once './webservice/soap/classes/class.ilSoapUtils.php';

	$sou =& new ilSoapUtils();
	$sou->disableSOAPCheck();

	return $sou->saveTempFileAsMediaObject($sid, $name, $tmp_name);
}

function getMobsOfObject($sid, $a_type, $a_id)
{
	include_once './webservice/soap/classes/class.ilSoapUtils.php';

	$sou =& new ilSoapUtils();
	$sou->disableSOAPCheck();

	return $sou->getMobsOfObject($sid, $a_type, $a_id);
}

function getStructureObjects ($sid, $ref_id) {
	include_once './webservice/soap/classes/class.ilSoapStructureObjectAdministration.php';

	$sca = & new ilSOAPStructureObjectAdministration();

	return $sca->getStructureObjects ($sid, $ref_id);
}

function getRoles($sid, $role_type, $id)
{
	include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';

	$soa =& new ilSoapRBACAdministration();

	return $soa->getRoles($sid, $role_type, $id);
}

function importUsers ($sid, $folder_id, $usr_xml, $conflict_rule, $send_account_mail)
{
	include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

	$sua =& new ilSoapUserAdministration();

	return $sua->importUsers($sid, $folder_id, $usr_xml, $conflict_rule, $send_account_mail);
}

function getUsersForContainer ($sid, $ref_id, $attach_roles, $active)
{
	include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

	$sua =& new ilSoapUserAdministration();

	return $sua->getUsersForContainer($sid, $ref_id, $attach_roles, $active);
}

function getUsersForRole ($sid, $role_id, $attach_roles, $active)
{
	include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

	$sua =& new ilSoapUserAdministration();

	return $sua->getUserForRole($sid, $role_id, $attach_roles, $active);
}


function searchUser ($sid, $a_keyfields, $query_operator, $a_keyvalues, $attach_roles, $active) {
	include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

	$sua =& new ilSoapUserAdministration();

	return $sua->searchUser ($sid, $a_keyfields, $query_operator, $a_keyvalues, $attach_roles, $active);

}

function hasNewMail($sid)
{
	include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

	$sua =& new ilSoapUserAdministration();

	return $sua->hasNewMail($sid);
}

function getNIC($sid) {
	include_once './webservice/soap/classes/class.ilSoapAdministration.php';
	$soa = & new ilSoapAdministration();
	return $soa->getNIC($sid);
}

function getExerciseXML ($sid, $ref_id, $attachFileContentsMode) {
	include_once './webservice/soap/classes/class.ilSoapExerciseAdministration.php';
	$sta = & new ilSoapExerciseAdministration();
	return $sta->getExerciseXML($sid, $ref_id, $attachFileContentsMode);

}


function updateExercise ($sid, $ref_id, $xml) {
	include_once './webservice/soap/classes/class.ilSoapExerciseAdministration.php';
	$sta = & new ilSoapExerciseAdministration();
	return $sta->updateExercise($sid, $ref_id, $xml);

}

function addExercise ($sid, $ref_id, $xml) {
	include_once './webservice/soap/classes/class.ilSoapExerciseAdministration.php';
	$sta = & new ilSoapExerciseAdministration();
	return $sta->addExercise($sid, $ref_id, $xml);

}

function getFileXML ($sid, $ref_id, $attachFileContentsMode)
{
	include_once './webservice/soap/classes/class.ilSoapFileAdministration.php';
	$sta = & new ilSoapFileAdministration();
	return $sta->getFileXML($sid, $ref_id, $attachFileContentsMode);

}


function updateFile ($sid, $ref_id, $xml)
{
	include_once './webservice/soap/classes/class.ilSoapFileAdministration.php';
	$sta = & new ilSoapFileAdministration();
	return $sta->updateFile($sid, $ref_id, $xml);

}

function addFile ($sid, $ref_id, $xml)
{
	include_once './webservice/soap/classes/class.ilSoapFileAdministration.php';
	$sta = & new ilSoapFileAdministration();
	return $sta->addFile($sid, $ref_id, $xml);

}

function getObjIdsByRefIds($sid, $ref_ids)
{
	include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';

	$soa =& new ilSoapObjectAdministration();

	return $soa->getObjIdsByRefIds($sid, $ref_ids);
}

function getUserXML($sid,$user_ids, $attach_roles)
{
	include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';

	$sua =& new ilSoapUserAdministration();

	return $sua->getUserXML($sid, $user_ids, $attach_roles);
}

function updateGroup($sid, $ref_id, $grp_xml)
{
	include_once './webservice/soap/classes/class.ilSoapGroupAdministration.php';

	$sua =& new ilSoapGroupAdministration();

	return $sua->updateGroup($sid,$ref_id, $grp_xml);
}


?>
