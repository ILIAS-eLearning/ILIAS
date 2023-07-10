<?php
/*
 +-----------------------------------------------------------------------------+
 | ILIAS open source                                                           |
 +-----------------------------------------------------------------------------+
 | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
 * Soap course administration methods
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * @package ilias
 */
include_once './webservice/soap/classes/class.ilSoapAdministration.php';

class ilSoapCourseAdministration extends ilSoapAdministration
{
    public const MEMBER = 1;
    public const TUTOR = 2;
    public const ADMIN = 4;
    public const OWNER = 8;

    /**
     * @return int|soap_fault|SoapFault|string|null
     */
    public function addCourse(string $sid, int $target_id, string $crs_xml)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if (!$target_obj = ilObjectFactory::getInstanceByRefId($target_id, false)) {
            return $this->raiseError('No valid target given.', 'Client');
        }

        if (ilObject::_isInTrash($target_id)) {
            return $this->raiseError("Parent with ID $target_id has been deleted.", 'CLIENT_OBJECT_DELETED');
        }

        if (!$rbacsystem->checkAccess('create', $target_id, 'crs')) {
            return $this->raiseError('Check access failed. No permission to create courses', 'Server');
        }

        include_once("Modules/Course/classes/class.ilObjCourse.php");

        $newObj = new ilObjCourse();
        $newObj->setType('crs');
        $newObj->setTitle('dummy');
        $newObj->setDescription("");
        $newObj->create(true); // true for upload
        $newObj->createReference();
        $newObj->putInTree($target_id);
        $newObj->setPermissions($target_id);

        include_once 'Modules/Course/classes/class.ilCourseXMLParser.php';

        $xml_parser = new ilCourseXMLParser($newObj);
        $xml_parser->setXMLContent($crs_xml);
        $xml_parser->startParsing();
        return $newObj->getRefId() ?: "0";
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public function deleteCourse(string $sid, int $course_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        include_once "./Services/Utilities/classes/class.ilUtil.php";
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if (ilObject::_lookupType(ilObject::_lookupObjId($course_id)) !== 'crs') {
            $ref_ids = ilObject::_getAllReferences($course_id);
            $course_id = end($ref_ids);
            if (ilObject::_lookupType(ilObject::_lookupObjId($course_id)) !== 'crs') {
                return $this->raiseError(
                    'Invalid course id. Object with id "' . $course_id . '" is not of type "course"',
                    'Client'
                );
            }
        }

        if (!$rbacsystem->checkAccess('delete', $course_id)) {
            return $this->raiseError('Check access failed. No permission to delete course', 'Server');
        }

        global $DIC;
        $tree = $DIC->repositoryTree();
        $user = $DIC->user();
        $rbacadmin = $DIC['rbacadmin'];
        $log = $DIC['log'];

        if ($tree->isDeleted($course_id)) {
            return $this->raiseError('Node already deleted', 'Server');
        }

        $subnodes = $tree->getSubTree($tree->getNodeData($course_id));
        foreach ($subnodes as $subnode) {
            $rbacadmin->revokePermission($subnode["child"]);
        }
        if (!$tree->moveToTrash($course_id, true, $user->getId())) {
            return $this->raiseError('Node already deleted', 'Client');
        }

        $log->write("SOAP ilObjectGUI::confirmedDeleteObject(), moved ref_id " . $course_id . " to trash");
        return true;
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public function assignCourseMember(string $sid, int $course_id, int $user_id, string $type)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if (ilObject::_lookupType(ilObject::_lookupObjId($course_id)) !== 'crs') {
            $ref_ids = ilObject::_getAllReferences($course_id);
            $course_id = end($ref_ids);
            if (ilObject::_lookupType(ilObject::_lookupObjId($course_id)) !== 'crs') {
                return $this->raiseError(
                    'Invalid course id. Object with id "' . $course_id . '" is not of type "course"',
                    'Client'
                );
            }
        }

        if (!$rbacsystem->checkAccess('manage_members', $course_id)) {
            return $this->raiseError('Check access failed. No permission to write to course', 'Server');
        }

        if (ilObject::_lookupType($user_id) !== 'usr') {
            return $this->raiseError('Invalid user id. User with id "' . $user_id . ' does not exist', 'Client');
        }
        if ($type !== 'Admin' &&
            $type !== 'Tutor' &&
            $type !== 'Member') {
            return $this->raiseError(
                'Invalid type given. Parameter "type" must be "Admin", "Tutor" or "Member"',
                'Client'
            );
        }

        if (!$tmp_course = ilObjectFactory::getInstanceByRefId($course_id, false)) {
            return $this->raiseError('Cannot create course instance!', 'Server');
        }

        if (!$tmp_user = ilObjectFactory::getInstanceByObjId($user_id, false)) {
            return $this->raiseError('Cannot create user instance!', 'Server');
        }

        include_once 'Modules/Course/classes/class.ilCourseParticipants.php';

        $course_members = ilCourseParticipants::_getInstanceByObjId($tmp_course->getId());

        switch ($type) {
            case 'Admin':
                require_once("Services/Administration/classes/class.ilSetting.php");
                $settings = new ilSetting();
                $course_members->add($tmp_user->getId(), ilParticipants::IL_CRS_ADMIN);
                $course_members->updateNotification(
                    $tmp_user->getId(),
                    (bool) $settings->get('mail_crs_admin_notification', "1")
                );
                break;

            case 'Tutor':
                $course_members->add($tmp_user->getId(), ilParticipants::IL_CRS_TUTOR);
                break;

            case 'Member':
                $course_members->add($tmp_user->getId(), ilParticipants::IL_CRS_MEMBER);
                break;
        }
        return true;
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public function excludeCourseMember(string $sid, int $course_id, int $user_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if (ilObject::_lookupType(ilObject::_lookupObjId($course_id)) !== 'crs') {
            $ref_ids = ilObject::_getAllReferences($course_id);
            $course_id = end($ref_ids);
            if (ilObject::_lookupType(ilObject::_lookupObjId($course_id)) !== 'crs') {
                return $this->raiseError(
                    'Invalid course id. Object with id "' . $course_id . '" is not of type "course"',
                    'Client'
                );
            }
        }

        if (ilObject::_lookupType($user_id) !== 'usr') {
            return $this->raiseError('Invalid user id. User with id "' . $user_id . ' does not exist', 'Client');
        }

        if (!$tmp_course = ilObjectFactory::getInstanceByRefId($course_id, false)) {
            return $this->raiseError('Cannot create course instance!', 'Server');
        }

        if (!$rbacsystem->checkAccess('manage_members', $course_id)) {
            return $this->raiseError('Check access failed. No permission to write to course', 'Server');
        }

        include_once 'Modules/Course/classes/class.ilCourseParticipants.php';

        $course_members = ilCourseParticipants::_getInstanceByObjId($tmp_course->getId());
        if (!$course_members->checkLastAdmin(array($user_id))) {
            return $this->raiseError('Cannot deassign last administrator from course', 'Server');
        }
        $course_members->delete($user_id);
        return true;
    }

    /**
     * @return int|soap_fault|SoapFault|string|null
     */
    public function isAssignedToCourse(string $sid, int $course_id, int $user_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if (ilObject::_lookupType(ilObject::_lookupObjId($course_id)) !== 'crs') {
            $ref_ids = ilObject::_getAllReferences($course_id);
            $course_id = end($ref_ids);
            if (ilObject::_lookupType(ilObject::_lookupObjId($course_id)) !== 'crs') {
                return $this->raiseError(
                    'Invalid course id. Object with id "' . $course_id . '" is not of type "course"',
                    'Client'
                );
            }
        }

        if (ilObject::_lookupType($user_id) !== 'usr') {
            return $this->raiseError('Invalid user id. User with id "' . $user_id . ' does not exist', 'Client');
        }

        /** @var ilObjCourse $tmp_course */
        if (!$tmp_course = ilObjectFactory::getInstanceByRefId($course_id, false)) {
            return $this->raiseError('Cannot create course instance!', 'Server');
        }

        if (!$rbacsystem->checkAccess('manage_members', $course_id)) {
            return $this->raiseError('Check access failed. No permission to write to course', 'Server');
        }

        include_once './Modules/Course/classes/class.ilCourseParticipants.php';
        $crs_members = ilCourseParticipants::_getInstanceByObjId($tmp_course->getId());

        if ($crs_members->isAdmin($user_id)) {
            return ilParticipants::IL_CRS_ADMIN;
        }
        if ($crs_members->isTutor($user_id)) {
            return ilParticipants::IL_CRS_TUTOR;
        }
        if ($crs_members->isMember($user_id)) {
            return ilParticipants::IL_CRS_MEMBER;
        }
        return "0";
    }

    /**
     * @return ilObjCourse|soap_fault|SoapFault|string|null
     */
    public function getCourseXML(string $sid, int $course_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        /** @var ilObjCourse $tmp_course */
        $tmp_course = $this->checkObjectAccess($course_id, ['crs'], "read", true);
        if ($this->isFault($tmp_course)) {
            return $tmp_course;
        }

        include_once 'Modules/Course/classes/class.ilCourseXMLWriter.php';
        $xml_writer = new ilCourseXMLWriter($tmp_course);
        $xml_writer->start();
        return $xml_writer->getXML();
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public function updateCourse(string $sid, int $course_id, string $xml)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if (ilObject::_lookupType(ilObject::_lookupObjId($course_id)) !== 'crs') {
            $ref_ids = ilObject::_getAllReferences($course_id);
            $course_id = end($ref_ids);
            if (ilObject::_lookupType(ilObject::_lookupObjId($course_id)) !== 'crs') {
                return $this->raiseError(
                    'Invalid course id. Object with id "' . $course_id . '" is not of type "course"',
                    'Client'
                );
            }
        }

        /** @var ilObjCourse $tmp_course */
        if (!$tmp_course = ilObjectFactory::getInstanceByRefId($course_id, false)) {
            return $this->raiseError('Cannot create course instance!', 'Server');
        }

        if (!$rbacsystem->checkAccess('write', $course_id)) {
            return $this->raiseError('Check access failed. No permission to write course', 'Server');
        }

        // First delete old meta data
        include_once 'Services/MetaData/classes/class.ilMD.php';

        $md = new ilMD($tmp_course->getId(), 0, 'crs');
        $md->deleteAll();

        include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
        ilCourseParticipants::_deleteAllEntries($tmp_course->getId());

        include_once 'Modules/Course/classes/class.ilCourseWaitingList.php';
        ilCourseWaitingList::_deleteAll($tmp_course->getId());

        include_once 'Modules/Course/classes/class.ilCourseXMLParser.php';

        $xml_parser = new ilCourseXMLParser($tmp_course);
        $xml_parser->setXMLContent($xml);
        $xml_parser->startParsing();
        $tmp_course->MDUpdateListener('General');

        return true;
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public function getCoursesForUser(string $sid, string $parameters)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $ilObjDataCache = $DIC['ilObjDataCache'];
        $tree = $DIC['tree'];

        include_once 'webservice/soap/classes/class.ilXMLResultSetParser.php';
        $parser = new ilXMLResultSetParser($parameters);
        try {
            $parser->startParsing();
        } catch (ilSaxParserException $exception) {
            return $this->raiseError($exception->getMessage(), "Client");
        }
        $xmlResultSet = $parser->getXMLResultSet();

        if (!$xmlResultSet->hasColumn("user_id")) {
            return $this->raiseError("parameter user_id is missing", "Client");
        }

        if (!$xmlResultSet->hasColumn("status")) {
            return $this->raiseError("parameter status is missing", "Client");
        }

        $user_id = (int) $xmlResultSet->getValue(0, "user_id");
        $status = (int) $xmlResultSet->getValue(0, "status");

        $ref_ids = array();

        if (self::MEMBER == ($status & self::MEMBER) ||
            self::TUTOR == ($status & self::TUTOR) ||
            self::ADMIN == ($status & self::ADMIN)) {
            foreach ($rbacreview->assignedRoles($user_id) as $role_id) {
                if ($role = ilObjectFactory::getInstanceByObjId($role_id, false)) {
                    #echo $role->getType();
                    if ($role->getType() !== "role") {
                        continue;
                    }
                    if ($role->getParent() == ROLE_FOLDER_ID) {
                        continue;
                    }
                    $role_title = $role->getTitle();

                    if ($ref_id = ilUtil::__extractRefId($role_title)) {
                        if (!ilObject::_exists($ref_id, true) || ilObject::_isInTrash($ref_id)) {
                            continue;
                        }

                        if (self::MEMBER == ($status & self::MEMBER) && strpos(
                            $role_title,
                            "member"
                        ) !== false) {
                            $ref_ids [] = $ref_id;
                        } elseif (self::TUTOR == ($status & self::TUTOR) && strpos(
                            $role_title,
                            "tutor"
                        ) !== false) {
                            $ref_ids [] = $ref_id;
                        } elseif (self::ADMIN == ($status & self::ADMIN) && strpos(
                            $role_title,
                            "admin"
                        ) !== false) {
                            $ref_ids [] = $ref_id;
                        } elseif (($status & self::OWNER) == self::OWNER && $ilObjDataCache->lookupOwner($ilObjDataCache->lookupObjId($ref_id)) == $user_id) {
                            $ref_ids [] = $ref_id;
                        }
                    }
                }
            }
        }
        if (($status & self::OWNER) == self::OWNER) {
            $owned_objects = ilObjectFactory::getObjectsForOwner("crs", $user_id);
            $refs = [];
            foreach ($owned_objects as $obj_id) {
                $allrefs = ilObject::_getAllReferences($obj_id);
                foreach ($allrefs as $r) {
                    if ($tree->isDeleted($r)) {
                        continue;
                    }
                    if ($tree->isInTree($r)) {
                        $refs[] = $r;
                    }
                }
                if (count($refs) > 0) {
                    $ref_ids[] = array_pop($refs);
                }
            }
        }
        $ref_ids = array_unique($ref_ids);

        $ref_ids = array_unique($ref_ids);

        include_once 'webservice/soap/classes/class.ilXMLResultSetWriter.php';
        include_once 'Modules/Course/classes/class.ilObjCourse.php';
        include_once 'Modules/Course/classes/class.ilCourseXMLWriter.php';

        $xmlResultSet = new ilXMLResultSet();
        $xmlResultSet->addColumn("ref_id");
        $xmlResultSet->addColumn("xml");
        $xmlResultSet->addColumn("parent_ref_id");

        global $DIC;

        $ilUser = $DIC['ilUser'];
        //#18004
        // Enable to see own participations by reducing the needed permissions
        $permission = $user_id === $ilUser->getId() ? 'read' : 'write';

        foreach ($ref_ids as $course_id) {
            $course_obj = $this->checkObjectAccess($course_id, ['crs'], $permission, true);
            if ($course_obj instanceof ilObjCourse) {
                $row = new ilXMLResultSetRow();
                $row->setValue("ref_id", $course_id);
                $xmlWriter = new ilCourseXMLWriter($course_obj);
                $xmlWriter->setAttachUsers(false);
                $xmlWriter->start();
                $row->setValue("xml", $xmlWriter->getXML());
                $row->setValue("parent_ref_id", $tree->getParentId($course_id));
                $xmlResultSet->addRow($row);
            }
        }
        $xmlResultSetWriter = new ilXMLResultSetWriter($xmlResultSet);
        $xmlResultSetWriter->start();
        return $xmlResultSetWriter->getXML();
    }
}
