<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\OrgUnit\Webservices\SOAP\AddUserIdToPositionInOrgUnit;
use ILIAS\OrgUnit\Webservices\SOAP\EmployeePositionId;
use ILIAS\OrgUnit\Webservices\SOAP\ImportOrgUnitTree;
use ILIAS\OrgUnit\Webservices\SOAP\OrgUnitTree;
use ILIAS\OrgUnit\Webservices\SOAP\PositionIds;
use ILIAS\OrgUnit\Webservices\SOAP\PositionTitle;
use ILIAS\OrgUnit\Webservices\SOAP\RemoveUserIdFromPositionInOrgUnit;
use ILIAS\OrgUnit\Webservices\SOAP\SuperiorPositionId;
use ILIAS\OrgUnit\Webservices\SOAP\UserIdsOfPosition;
use ILIAS\OrgUnit\Webservices\SOAP\UserIdsOfPositionAndOrgUnit;

/**
 * soap server
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 *
 * @package ilias
 */

class ilSoapFunctions
{
    // These functions are wrappers for soap, since it cannot register methods inside classes

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function login(string $client, string $username, string $password)
    {
        include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';
        $sua = new ilSoapUserAdministration();
        return $sua->login($client, $username, $password);
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public static function logout(string $sid)
    {
        include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';
        $sua = new ilSoapUserAdministration();
        return $sua->logout($sid);
    }

    /**
     * @return int|soap_fault|SoapFault|null
     */
    public static function lookupUser(string $sid, string $user_name)
    {
        include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';
        $sua = new ilSoapUserAdministration();
        return $sua->lookupUser($sid, $user_name);
    }

    /**
     * @return int|soap_fault|SoapFault|string|null
     */
    public static function addCourse(string $sid, int $target_id, string $crs_xml)
    {
        include_once './webservice/soap/classes/class.ilSoapCourseAdministration.php';
        $sca = new ilSoapCourseAdministration();
        return $sca->addCourse($sid, $target_id, $crs_xml);
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public static function deleteCourse(string $sid, int $course_id)
    {
        include_once './webservice/soap/classes/class.ilSoapCourseAdministration.php';
        $sca = new ilSoapCourseAdministration();
        return $sca->deleteCourse($sid, $course_id);
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public static function assignCourseMember(string $sid, int $course_id, int $user_id, string $type)
    {
        include_once './webservice/soap/classes/class.ilSoapCourseAdministration.php';
        $sca = new ilSoapCourseAdministration();
        return $sca->assignCourseMember($sid, $course_id, $user_id, $type);
    }

    /**
     * @return int|soap_fault|SoapFault|string|null
     */
    public static function isAssignedToCourse(string $sid, int $course_id, int $user_id)
    {
        include_once './webservice/soap/classes/class.ilSoapCourseAdministration.php';
        $sca = new ilSoapCourseAdministration();
        return $sca->isAssignedToCourse($sid, $course_id, $user_id);
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public static function excludeCourseMember(string $sid, int $course_id, int $user_id)
    {
        include_once './webservice/soap/classes/class.ilSoapCourseAdministration.php';
        $sca = new ilSoapCourseAdministration();
        return $sca->excludeCourseMember($sid, $course_id, $user_id);
    }

    /**
     * @return ilObjCourse|soap_fault|SoapFault|string|null
     */
    public static function getCourseXML(string $sid, int $course_id)
    {
        include_once './webservice/soap/classes/class.ilSoapCourseAdministration.php';
        $sca = new ilSoapCourseAdministration();
        return $sca->getCourseXML($sid, $course_id);
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public static function updateCourse(string $sid, int $course_id, string $xml)
    {
        include_once './webservice/soap/classes/class.ilSoapCourseAdministration.php';
        $sca = new ilSoapCourseAdministration();
        return $sca->updateCourse($sid, $course_id, $xml);
    }

    /**
     * @return int|soap_fault|SoapFault|string|null
     */
    public static function getObjIdByImportId(string $sid, string $import_id)
    {
        include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';
        $soa = new ilSoapObjectAdministration();
        return $soa->getObjIdByImportId($sid, $import_id);
    }

    /**
     * @return array|soap_fault|SoapFault|null
     */
    public static function getRefIdsByImportId(string $sid, string $import_id)
    {
        include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';
        $soa = new ilSoapObjectAdministration();
        return $soa->getRefIdsByImportId($sid, $import_id);
    }

    /**
     * @return array|soap_fault|SoapFault|null
     */
    public static function getRefIdsByObjId(string $sid, int $object_id)
    {
        include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';
        $soa = new ilSoapObjectAdministration();
        return $soa->getRefIdsByObjId($sid, $object_id);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function getObjectByReference(string $sid, int $a_ref_id, int $user_id)
    {
        include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';
        $soa = new ilSoapObjectAdministration();
        return $soa->getObjectByReference($sid, $a_ref_id, $user_id);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function getObjectsByTitle(string $sid, string $a_title, int $user_id)
    {
        include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';
        $soa = new ilSoapObjectAdministration();
        return $soa->getObjectsByTitle($sid, $a_title, $user_id);
    }

    /**
     * @return bool|int|soap_fault|SoapFault|string|null
     */
    public static function addObject(string $sid, int $a_target_id, string $a_xml)
    {
        include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';
        $soa = new ilSoapObjectAdministration();
        return $soa->addObject($sid, $a_target_id, $a_xml);
    }

    /**
     * @return int|soap_fault|SoapFault|string|null
     */
    public static function addReference(string $sid, int $a_source_id, int $a_target_id)
    {
        include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';
        $soa = new ilSoapObjectAdministration();
        return $soa->addReference($sid, $a_source_id, $a_target_id);
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public static function deleteObject(string $sid, int $reference_id)
    {
        include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';
        $soa = new ilSoapObjectAdministration();
        return $soa->deleteObject($sid, $reference_id);
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public static function removeFromSystemByImportId(string $sid, string $import_id)
    {
        include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';
        $soa = new ilSoapObjectAdministration();
        return $soa->removeFromSystemByImportId($sid, $import_id);
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public static function updateObjects(string $sid, string $obj_xml)
    {
        include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';
        $soa = new ilSoapObjectAdministration();
        return $soa->updateObjects($sid, $obj_xml);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function searchObjects(string $sid, array $types, string $key, string $combination, int $user_id)
    {
        include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';
        $soa = new ilSoapObjectAdministration();
        return $soa->searchObjects($sid, $types, $key, $combination, $user_id);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function getTreeChilds(string $sid, int $ref_id, array $types, int $user_id)
    {
        include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';
        $soa = new ilSoapObjectAdministration();
        return $soa->getTreeChilds($sid, $ref_id, $types, $user_id);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function getXMLTree(string $sid, int $ref_id, array $types, int $user_id)
    {
        include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';
        $soa = new ilSoapObjectAdministration();
        return $soa->getXMLTree($sid, $ref_id, $types, $user_id);
    }

    /**
     * @return soap_fault|SoapFault|null|array
     */
    public static function getOperations(string $sid)
    {
        include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';
        $soa = new ilSoapRBACAdministration();
        return $soa->getOperations($sid);
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public static function addUserRoleEntry(string $sid, int $user_id, int $role_id)
    {
        include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';
        $soa = new ilSoapRBACAdministration();
        return $soa->addUserRoleEntry($sid, $user_id, $role_id);
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public static function deleteUserRoleEntry(string $sid, int $user_id, int $role_id)
    {
        include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';
        $soa = new ilSoapRBACAdministration();
        return $soa->deleteUserRoleEntry($sid, $user_id, $role_id);
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public static function revokePermissions(string $sid, int $ref_id, int $role_id)
    {
        include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';
        $soa = new ilSoapRBACAdministration();
        return $soa->revokePermissions($sid, $ref_id, $role_id);
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public static function grantPermissions(string $sid, int $ref_id, int $role_id, array $permissions)
    {
        include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';
        $soa = new ilSoapRBACAdministration();
        return $soa->grantPermissions($sid, $ref_id, $role_id, $permissions);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function getLocalRoles(string $sid, int $ref_id)
    {
        include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';
        $soa = new ilSoapRBACAdministration();
        return $soa->getLocalRoles($sid, $ref_id);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function getUserRoles(string $sid, int $user_id)
    {
        include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';
        $soa = new ilSoapRBACAdministration();
        return $soa->getUserRoles($sid, $user_id);
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public static function deleteRole(string $sid, int $role_id)
    {
        include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';
        $soa = new ilSoapRBACAdministration();
        return $soa->deleteRole($sid, $role_id);
    }

    /**
     * @return array|soap_fault|SoapFault|null
     */
    public static function addRole(string $sid, int $target_id, string $obj_xml)
    {
        include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';
        $soa = new ilSoapRBACAdministration();
        return $soa->addRole($sid, $target_id, $obj_xml);
    }

    /**
     * @return array|soap_fault|SoapFault|null
     */
    public static function addRoleFromTemplate(string $sid, int $target_id, string $obj_xml, int $template_id)
    {
        include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';
        $soa = new ilSoapRBACAdministration();
        return $soa->addRoleFromTemplate($sid, $target_id, $obj_xml, $template_id);
    }

    /**
     * @return array|soap_fault|SoapFault|null
     */
    public static function getObjectTreeOperations(string $sid, int $ref_id, int $user_id)
    {
        include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';
        $soa = new ilSoapRBACAdministration();
        return $soa->getObjectTreeOperations($sid, $ref_id, $user_id);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function addGroup(string $sid, int $target_id, int $group_xml)
    {
        include_once './webservice/soap/classes/class.ilSoapGroupAdministration.php';
        $soa = new ilSoapGroupAdministration();
        return $soa->addGroup($sid, $target_id, $group_xml);
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public static function groupExists(string $sid, string $title)
    {
        include_once './webservice/soap/classes/class.ilSoapGroupAdministration.php';
        $soa = new ilSoapGroupAdministration();
        return $soa->groupExists($sid, $title);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function getGroup(string $sid, int $ref_id)
    {
        include_once './webservice/soap/classes/class.ilSoapGroupAdministration.php';
        $soa = new ilSoapGroupAdministration();
        return $soa->getGroup($sid, $ref_id);
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public static function assignGroupMember(string $sid, int $group_id, int $user_id, string $type)
    {
        include_once './webservice/soap/classes/class.ilSoapGroupAdministration.php';
        $sca = new ilSoapGroupAdministration();
        return $sca->assignGroupMember($sid, $group_id, $user_id, $type);
    }

    /**
     * @return int|soap_fault|SoapFault|null
     */
    public static function isAssignedToGroup(string $sid, int $group_id, int $user_id)
    {
        include_once './webservice/soap/classes/class.ilSoapGroupAdministration.php';
        $sca = new ilSoapGroupAdministration();
        return $sca->isAssignedToGroup($sid, $group_id, $user_id);
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public static function excludeGroupMember(string $sid, int $group_id, int $user_id)
    {
        include_once './webservice/soap/classes/class.ilSoapGroupAdministration.php';
        $sca = new ilSoapGroupAdministration();
        return $sca->excludeGroupMember($sid, $group_id, $user_id);
    }

    /**
     * @return bool|int|soap_fault|SoapFault|null
     */
    public static function ilClone(string $sid, int $copy_identifier)
    {
        include_once './webservice/soap/classes/class.ilSoapUtils.php';

        $sou = new ilSoapUtils();
        $sou->disableSOAPCheck();
        $sou->ignoreUserAbort();
        return $sou->ilClone($sid, $copy_identifier);
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public static function ilCloneDependencies(string $sid, int $copy_identifier)
    {
        include_once './webservice/soap/classes/class.ilSoapUtils.php';

        $sou = new ilSoapUtils();
        $sou->disableSOAPCheck();
        $sou->ignoreUserAbort();
        return $sou->ilCloneDependencies($sid, $copy_identifier);
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public static function saveQuestion(string $sid, int $active_id, int $question_id, int $pass, array $solution)
    {
        include_once './webservice/soap/classes/class.ilSoapTestAdministration.php';
        $sass = new ilSoapTestAdministration();
        return $sass->saveQuestion($sid, $active_id, $question_id, $pass, $solution);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function saveQuestionSolution(string $sid, int $active_id, int $question_id, int $pass, int $solution)
    {
        include_once './webservice/soap/classes/class.ilSoapTestAdministration.php';
        $sass = new ilSoapTestAdministration();
        return $sass->saveQuestionSolution($sid, $active_id, $question_id, $pass, $solution);
    }

    /**
     * @return array|soap_fault|SoapFault|null
     */
    public static function getQuestionSolution(string $sid, int $active_id, int $question_id, int $pass)
    {
        include_once './webservice/soap/classes/class.ilSoapTestAdministration.php';
        $sass = new ilSoapTestAdministration();
        return $sass->getQuestionSolution($sid, $active_id, $question_id, $pass);
    }

    /**
     * @return array|soap_fault|SoapFault|null
     */
    public static function getTestUserData(string $sid, int $active_id)
    {
        include_once './webservice/soap/classes/class.ilSoapTestAdministration.php';
        $sass = new ilSoapTestAdministration();
        return $sass->getTestUserData($sid, $active_id);
    }

    /**
     * @return int|soap_fault|SoapFault|null
     */
    public static function getNrOfQuestionsInPass(string $sid, int $active_id, int $pass)
    {
        include_once './webservice/soap/classes/class.ilSoapTestAdministration.php';
        $sass = new ilSoapTestAdministration();
        return $sass->getNrOfQuestionsInPass($sid, $active_id, $pass);
    }

    /**
     * @return false|int|soap_fault|SoapFault|string|null
     */
    public static function getPositionOfQuestion(string $sid, int $active_id, int $question_id, int $pass)
    {
        include_once './webservice/soap/classes/class.ilSoapTestAdministration.php';
        $sass = new ilSoapTestAdministration();
        return $sass->getPositionOfQuestion($sid, $active_id, $question_id, $pass);
    }

    /**
     * @return array|int|soap_fault|SoapFault|null
     */
    public static function getPreviousReachedPoints(string $sid, int $active_id, int $question_id, int $pass)
    {
        include_once './webservice/soap/classes/class.ilSoapTestAdministration.php';
        $sass = new ilSoapTestAdministration();
        return $sass->getPreviousReachedPoints($sid, $active_id, $question_id, $pass);
    }

    /**
     * @return ilObjMediaObject|soap_fault|SoapFault|null
     */
    public static function saveTempFileAsMediaObject(string $sid, string $name, string $tmp_name)
    {
        include_once './webservice/soap/classes/class.ilSoapUtils.php';

        $sou = new ilSoapUtils();
        $sou->disableSOAPCheck();
        return $sou->saveTempFileAsMediaObject($sid, $name, $tmp_name);
    }

    /**
     * @return int[]|soap_fault|SoapFault|null
     */
    public static function getMobsOfObject(string $sid, string $a_type, int $a_id)
    {
        include_once './webservice/soap/classes/class.ilSoapUtils.php';
        $sou = new ilSoapUtils();
        $sou->disableSOAPCheck();
        return $sou->getMobsOfObject($sid, $a_type, $a_id);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function getStructureObjects(string $sid, int $ref_id)
    {
        include_once './webservice/soap/classes/class.ilSoapStructureObjectAdministration.php';
        $sca = new ilSOAPStructureObjectAdministration();
        return $sca->getStructureObjects($sid, $ref_id);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function getRoles(string $sid, string $role_type, int $id)
    {
        include_once './webservice/soap/classes/class.ilSoapRBACAdministration.php';
        $soa = new ilSoapRBACAdministration();
        return $soa->getRoles($sid, $role_type, $id);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function importUsers(string $sid, int $folder_id, string $usr_xml, int $conflict_rule, bool $send_account_mail)
    {
        include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';
        $sua = new ilSoapUserAdministration();
        return $sua->importUsers($sid, $folder_id, $usr_xml, $conflict_rule, $send_account_mail);
    }

    /**
     * @return ilObject|mixed|soap_fault|SoapFault|string|null
     */
    public static function getUsersForContainer(string $sid, int $ref_id, bool $attach_roles, int $active)
    {
        include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';
        $sua = new ilSoapUserAdministration();
        return $sua->getUsersForContainer($sid, $ref_id, $attach_roles, $active);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function getUsersForRole(string $sid, int $role_id, bool $attach_roles, int $active)
    {
        include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';
        $sua = new ilSoapUserAdministration();
        return $sua->getUserForRole($sid, $role_id, $attach_roles, $active);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function searchUser(string $sid, array $a_keyfields, string $query_operator, array $a_keyvalues, bool $attach_roles, int $active)
    {
        include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';
        $sua = new ilSoapUserAdministration();
        return $sua->searchUser($sid, $a_keyfields, $query_operator, $a_keyvalues, $attach_roles, $active);
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public static function hasNewMail(string $sid)
    {
        include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';
        $sua = new ilSoapUserAdministration();
        return $sua->hasNewMail($sid);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function getExerciseXML(string $sid, int $ref_id, int $attachFileContentsMode)
    {
        include_once './webservice/soap/classes/class.ilSoapExerciseAdministration.php';
        $sta = new ilSoapExerciseAdministration();
        return $sta->getExerciseXML($sid, $ref_id, $attachFileContentsMode);
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public static function updateExercise(string $sid, int $ref_id, string $xml)
    {
        include_once './webservice/soap/classes/class.ilSoapExerciseAdministration.php';
        $sta = new ilSoapExerciseAdministration();
        return $sta->updateExercise($sid, $ref_id, $xml);
    }

    /**
     * @return int|soap_fault|SoapFault|null
     */
    public static function addExercise(string $sid, int $ref_id, string $xml)
    {
        include_once './webservice/soap/classes/class.ilSoapExerciseAdministration.php';
        $sta = new ilSoapExerciseAdministration();
        return $sta->addExercise($sid, $ref_id, $xml);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function getFileXML(string $sid, int $ref_id, int $attachFileContentsMode)
    {
        include_once './webservice/soap/classes/class.ilSoapFileAdministration.php';
        $sta = new ilSoapFileAdministration();
        return $sta->getFileXML($sid, $ref_id, $attachFileContentsMode);
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public static function updateFile(string $sid, int $ref_id, string $xml)
    {
        include_once './webservice/soap/classes/class.ilSoapFileAdministration.php';
        $sta = new ilSoapFileAdministration();
        return $sta->updateFile($sid, $ref_id, $xml);
    }

    /**
     * @return int|soap_fault|SoapFault|null
     */
    public static function addFile(string $sid, int $ref_id, string $xml)
    {
        include_once './webservice/soap/classes/class.ilSoapFileAdministration.php';
        $sta = new ilSoapFileAdministration();
        return $sta->addFile($sid, $ref_id, $xml);
    }

    /**
     * @return array|soap_fault|SoapFault|null
     */
    public static function getObjIdsByRefIds(string $sid, array $ref_ids)
    {
        include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';
        $soa = new ilSoapObjectAdministration();
        return $soa->getObjIdsByRefIds($sid, $ref_ids);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function getUserXML(string $sid, array $user_ids, bool $attach_roles)
    {
        include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';
        $sua = new ilSoapUserAdministration();
        return $sua->getUserXML($sid, $user_ids, $attach_roles);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function updateGroup(string $sid, int $ref_id, string $grp_xml)
    {
        include_once './webservice/soap/classes/class.ilSoapGroupAdministration.php';
        $sua = new ilSoapGroupAdministration();
        return $sua->updateGroup($sid, $ref_id, $grp_xml);
    }

    /**
     * @return false|soap_fault|SoapFault|string|null
     */
    public static function getIMSManifestXML(string $sid, int $ref_id)
    {
        include_once './webservice/soap/classes/class.ilSoapSCORMAdministration.php';
        $sua = new ilSoapSCORMAdministration();
        return $sua->getIMSManifestXML($sid, $ref_id);
    }

    public static function hasSCORMCertificate(string $sid, int $ref_id, int $usr_id)
    {
        include_once './webservice/soap/classes/class.ilSoapSCORMAdministration.php';
        $sua = new ilSoapSCORMAdministration();
        return $sua->hasSCORMCertificate($sid, $ref_id, $usr_id);
    }

    /**
     * @return bool|int|mixed|soap_fault|SoapFault|null
     */
    public static function copyObject(string $sid, string $copy_settings_xml)
    {
        include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';
        $soa = new ilSoapObjectAdministration();
        return $soa->copyObject($sid, $copy_settings_xml);
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public static function startBackgroundTaskWorker(string $sid)
    {
        require_once("./Services/BackgroundTasks/classes/class.ilSoapBackgroundTasksAdministration.php");
        $soa = new ilSoapBackgroundTasksAdministration();
        return $soa->runAsync($sid);
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public static function moveObject(string $sid, int $ref_id, int $target_id)
    {
        include_once './webservice/soap/classes/class.ilSoapObjectAdministration.php';
        $soa = new ilSoapObjectAdministration();
        return $soa->moveObject($sid, $ref_id, $target_id);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function getTestResults(string $sid, int $ref_id, bool $sum_only)
    {
        include_once './webservice/soap/classes/class.ilSoapTestAdministration.php';
        $soa = new ilSoapTestAdministration();
        return $soa->getTestResults($sid, $ref_id, $sum_only);
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public static function removeTestResults(string $sid, int $ref_id, array $a_user_ids)
    {
        include_once './webservice/soap/classes/class.ilSoapTestAdministration.php';
        $soa = new ilSoapTestAdministration();
        return $soa->removeTestResults($sid, $ref_id, $a_user_ids);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function getCoursesForUser(string $sid, string $parameters)
    {
        include_once 'webservice/soap/classes/class.ilSoapCourseAdministration.php';
        $soc = new ilSoapCourseAdministration();
        return $soc->getCoursesForUser($sid, $parameters);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function getGroupsForUser(string $sid, string $parameters)
    {
        include_once 'webservice/soap/classes/class.ilSoapGroupAdministration.php';
        $soc = new ilSoapGroupAdministration();
        return $soc->getGroupsForUser($sid, $parameters);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function getPathForRefId(string $sid, int $ref_id)
    {
        include_once 'webservice/soap/classes/class.ilSoapObjectAdministration.php';
        $soa = new ilSoapObjectAdministration();
        return $soa->getPathForRefId($sid, $ref_id);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function searchRoles(string $sid, string $key, string $combination, string $role_type)
    {
        include_once 'webservice/soap/classes/class.ilSoapRBACAdministration.php';
        $roa = new ilSoapRBACAdministration();
        return $roa->searchRoles($sid, $key, $combination, $role_type);
    }

    public static function getInstallationInfoXML(): string
    {
        include_once 'webservice/soap/classes/class.ilSoapAdministration.php';
        $roa = new ilSoapAdministration();
        return $roa->getInstallationInfoXML();
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function getClientInfoXML(string $clientid)
    {
        include_once 'webservice/soap/classes/class.ilSoapAdministration.php';
        $roa = new ilSoapAdministration();
        return $roa->getClientInfoXML($clientid);
    }

    /**
     * @return string
     */
    public static function buildHTTPPath(): string
    {
        if (($_SERVER["HTTPS"] ?? '') === "on") {
            $protocol = 'https://';
        } else {
            $protocol = 'http://';
        }
        $host = $_SERVER['HTTP_HOST'] ?? '';

        $path = dirname($_SERVER['REQUEST_URI'] ?? '');

        //dirname cuts the last directory from a directory path e.g content/classes return content
        include_once 'Services/FileServices/classes/class.ilFileUtils.php';
        $module = ilFileUtils::removeTrailingPathSeparators(ILIAS_MODULE);

        $dirs = explode('/', $module);
        $uri = $path;
        foreach ($dirs as $dir) {
            $uri = dirname($uri);
        }
        return ilFileUtils::removeTrailingPathSeparators($protocol . $host . $uri);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function getSCORMCompletionStatus(string $sid, int $usr_id, int $a_ref_id)
    {
        include_once './webservice/soap/classes/class.ilSoapSCORMAdministration.php';
        $sua = new ilSoapSCORMAdministration();
        return $sua->getSCORMCompletionStatus($sid, $usr_id, $a_ref_id);
    }

    /**
     * @return int|soap_fault|SoapFault|null
     */
    public static function getUserIdBySid(string $sid)
    {
        include_once './webservice/soap/classes/class.ilSoapUserAdministration.php';
        $sua = new ilSoapUserAdministration();
        return $sua->getUserIdBySid($sid);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function readWebLink(string $sid, int $ref_id)
    {
        include_once './webservice/soap/classes/class.ilSoapWebLinkAdministration.php';
        $swa = new ilSoapWebLinkAdministration();
        return $swa->readWebLink($sid, $ref_id);
    }

    /**
     * @return int|soap_fault|SoapFault|null
     */
    public static function createWebLink(string $sid, int $ref_id, string $xml)
    {
        include_once './webservice/soap/classes/class.ilSoapWebLinkAdministration.php';

        $swa = new ilSoapWebLinkAdministration();
        return $swa->createWebLink($sid, $ref_id, $xml);
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public static function updateWebLink(string $sid, int $ref_id, string $xml)
    {
        include_once './webservice/soap/classes/class.ilSoapWebLinkAdministration.php';
        $swa = new ilSoapWebLinkAdministration();
        return $swa->updateWebLink($sid, $ref_id, $xml);
    }

    public static function deleteExpiredDualOptInUserObjects(string $sid, int $usr_id): bool
    {
        include_once './webservice/soap/classes/class.ilSoapUtils.php';

        $sou = new ilSoapUtils();
        $sou->disableSOAPCheck();
        $sou->ignoreUserAbort();
        return $sou->deleteExpiredDualOptInUserObjects($sid, $usr_id);
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public static function deleteProgress(string $sid, array $ref_ids, array $usr_ids, array $type_filter, array $progress_filter)
    {
        include_once './webservice/soap/classes/class.ilSoapLearningProgressAdministration.php';
        $sla = new ilSoapLearningProgressAdministration();
        return $sla->deleteProgress($sid, $ref_ids, $usr_ids, $type_filter, $progress_filter);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public static function getLearningProgressChanges(string $sid, string $timestamp, bool $include_ref_ids, array $type_filter)
    {
        include_once './webservice/soap/classes/class.ilSoapLearningProgressAdministration.php';
        $s = new ilSoapLearningProgressAdministration();
        return $s->getLearningProgressChanges($sid, $timestamp, $include_ref_ids, $type_filter);
    }

    /**
     * @return soap_fault|SoapFault|string
     */
    public static function getProgressInfo(string $sid, int $ref_id, array $progress_filter)
    {
        include_once './webservice/soap/classes/class.ilSoapLearningProgressAdministration.php';
        $sla = new ilSoapLearningProgressAdministration();
        return $sla->getProgressInfo($sid, $ref_id, $progress_filter);
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public static function exportDataCollectionContent(string $sid, int $data_collection_id, ?int $table_id = null, string $format = "xls", ?string $filepath = null)
    {
        include_once './webservice/soap/classes/class.ilSoapDataCollectionAdministration.php';
        $dcl = new ilSoapDataCollectionAdministration();
        return $dcl->exportDataCollectionContent($sid, $data_collection_id, $table_id, $format, $filepath);
    }

    /**
     * @param ...$params
     * @return mixed
     */
    public static function addUserToPositionInOrgUnit(...$params)
    {
        $h = new AddUserIdToPositionInOrgUnit();
        return $h->execute($params);
    }

    /**
     * @param ...$params
     * @return mixed
     */
    public static function getEmployeePositionId(...$params)
    {
        $h = new EmployeePositionId();
        return $h->execute($params);
    }

    /**
     * @param ...$params
     * @return mixed
     */
    public static function importOrgUnitsSimpleXML(...$params)
    {
        $h = new ImportOrgUnitTree();
        return $h->execute($params);
    }

    /**
     * @param ...$params
     * @return mixed
     */
    public static function getOrgUnitsSimpleXML(...$params)
    {
        $h = new OrgUnitTree();
        return $h->execute($params);
    }

    /**
     * @param ...$params
     * @return mixed
     */
    public static function getPositionIds(...$params)
    {
        $h = new PositionIds();
        return $h->execute($params);
    }

    /**
     * @param ...$params
     * @return mixed
     */
    public static function getPositionTitle(...$params)
    {
        $h = new PositionTitle();
        return $h->execute($params);
    }

    /**
     * @param ...$params
     * @return mixed
     */
    public static function removeUserFromPositionInOrgUnit(...$params)
    {
        $h = new RemoveUserIdFromPositionInOrgUnit();
        return $h->execute($params);
    }

    /**
     * @param ...$params
     * @return mixed
     */
    public static function getSuperiorPositionId(...$params)
    {
        $h = new SuperiorPositionId();
        return $h->execute($params);
    }

    /**
     * @param ...$params
     * @return mixed
     */
    public static function getUserIdsOfPosition(...$params)
    {
        $h = new UserIdsOfPosition();
        return $h->execute($params);
    }

    /**
     * @param ...$params
     * @return mixed
     */
    public function getUserIdsOfPositionAndOrgUnit(...$params)
    {
        $h = new UserIdsOfPositionAndOrgUnit();
        return $h->execute($params);
    }

    /**
     * @param mixed $name
     * @param mixed $arguments
     * @return mixed
     * @throws SoapFault
     */
    public function __call($name, $arguments)
    {
        // SoapHookPlugins need the client-ID submitted
        // no initialized ILIAS => no request wrapper available.
        if (!isset($_GET['client_id'])) {
            throw new SoapFault('SOAP-ENV:Server', "Function '$name' does not exist");
        }
        // Note: We need to bootstrap ILIAS in order to get $ilPluginAdmin and load the soap plugins.
        // We MUST use a context that does not handle authentication at this point (session is checked by SOAP).
        ilContext::init(ilContext::CONTEXT_SOAP_NO_AUTH);
        ilInitialisation::initILIAS();
        ilContext::init(ilContext::CONTEXT_SOAP);
        global $DIC;
        $soapHook = new ilSoapHook($DIC['component.factory']);
        // Method name may be invoked with namespace e.g. 'myMethod' vs 'ns:myMethod'
        if (strpos($name, ':') !== false) {
            [$_, $name] = explode(':', $name);
        }
        $method = $soapHook->getMethodByName($name);
        if ($method) {
            try {
                return $method->execute($arguments);
            } catch (ilSoapPluginException $e) {
                throw new SoapFault('SOAP-ENV:Server', $e->getMessage());
            }
        }
        throw new SoapFault('SOAP-ENV:Server', "Function '$name' does not exist");
    }
}
