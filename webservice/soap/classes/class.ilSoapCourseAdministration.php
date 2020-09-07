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
   *
   * @author Stefan Meyer <smeyer.ilias@gmx.de>
   * @version $Id$
   *
   * @package ilias
   */
include_once './webservice/soap/classes/class.ilSoapAdministration.php';

class ilSoapCourseAdministration extends ilSoapAdministration
{
    const MEMBER = 1;
    const TUTOR = 2;
    const ADMIN = 4;
    const OWNER = 8;


    // Service methods
    public function addCourse($sid, $target_id, $crs_xml)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        if (!is_numeric($target_id)) {
            return $this->__raiseError(
                'No valid target id given. Please choose an existing reference id of an ILIAS category',
                'Client'
            );
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if (!$target_obj =&ilObjectFactory::getInstanceByRefId($target_id, false)) {
            return $this->__raiseError('No valid target given.', 'Client');
        }

        if (ilObject::_isInTrash($target_id)) {
            return $this->__raiseError("Parent with ID $target_id has been deleted.", 'CLIENT_OBJECT_DELETED');
        }

        if (!$rbacsystem->checkAccess('create', $target_id, 'crs')) {
            return $this->__raiseError('Check access failed. No permission to create courses', 'Server');
        }


        // Start import
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
        return $newObj->getRefId() ? $newObj->getRefId() : "0";
    }

    public function deleteCourse($sid, $course_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        if (!is_numeric($course_id)) {
            return $this->__raiseError(
                'No valid course id given. Please choose an existing reference id of an ILIAS course',
                'Client'
            );
        }

        include_once "./Services/Utilities/classes/class.ilUtil.php";
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if (($obj_type = ilObject::_lookupType(ilObject::_lookupObjId($course_id))) != 'crs') {
            $course_id = end($ref_ids = ilObject::_getAllReferences($course_id));
            if (ilObject::_lookupType(ilObject::_lookupObjId($course_id)) != 'crs') {
                return $this->__raiseError('Invalid course id. Object with id "' . $course_id . '" is not of type "course"', 'Client');
            }
        }

        if (!$rbacsystem->checkAccess('delete', $course_id)) {
            return $this->__raiseError('Check access failed. No permission to delete course', 'Server');
        }


        global $DIC;

        $tree = $DIC['tree'];
        $rbacadmin = $DIC['rbacadmin'];
        $log = $DIC['log'];

        if ($tree->isDeleted($course_id)) {
            return $this->__raiseError('Node already deleted', 'Server');
        }

        $subnodes = $tree->getSubtree($tree->getNodeData($course_id));
        foreach ($subnodes as $subnode) {
            $rbacadmin->revokePermission($subnode["child"]);

            // remove item from all user desktops
            $affected_users = ilUtil::removeItemFromDesktops($subnode["child"]);
        }
        if (!$tree->saveSubTree($course_id, true)) {
            return $this->__raiseError('Node already deleted', 'Client');
        }
        
        // write log entry
        $log->write("SOAP ilObjectGUI::confirmedDeleteObject(), moved ref_id " . $course_id . " to trash");
        
        // remove item from all user desktops
        $affected_users = ilUtil::removeItemFromDesktops($course_id);
        
        return true;
    }

    public function assignCourseMember($sid, $course_id, $user_id, $type)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        if (!is_numeric($course_id)) {
            return $this->__raiseError(
                'No valid course id given. Please choose an existing reference id of an ILIAS course',
                'Client'
            );
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if (($obj_type = ilObject::_lookupType(ilObject::_lookupObjId($course_id))) != 'crs') {
            $course_id = end($ref_ids = ilObject::_getAllReferences($course_id));
            if (ilObject::_lookupType(ilObject::_lookupObjId($course_id)) != 'crs') {
                return $this->__raiseError('Invalid course id. Object with id "' . $course_id . '" is not of type "course"', 'Client');
            }
        }

        if (!$rbacsystem->checkAccess('manage_members', $course_id)) {
            return $this->__raiseError('Check access failed. No permission to write to course', 'Server');
        }

        
        if (ilObject::_lookupType($user_id) != 'usr') {
            return $this->__raiseError('Invalid user id. User with id "' . $user_id . ' does not exist', 'Client');
        }
        if ($type != 'Admin' and
           $type != 'Tutor' and
           $type != 'Member') {
            return $this->__raiseError('Invalid type given. Parameter "type" must be "Admin", "Tutor" or "Member"', 'Client');
        }

        if (!$tmp_course = ilObjectFactory::getInstanceByRefId($course_id, false)) {
            return $this->__raiseError('Cannot create course instance!', 'Server');
        }

        if (!$tmp_user = ilObjectFactory::getInstanceByObjId($user_id, false)) {
            return $this->__raiseError('Cannot create user instance!', 'Server');
        }

        include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
        
        $course_members = ilCourseParticipants::_getInstanceByObjId($tmp_course->getId());

        switch ($type) {
            case 'Admin':
                require_once("Services/Administration/classes/class.ilSetting.php");
                $settings = new ilSetting();
                $course_members->add($tmp_user->getId(), IL_CRS_ADMIN);
                $course_members->updateNotification($tmp_user->getId(), $settings->get('mail_crs_admin_notification', true));
                break;

            case 'Tutor':
                $course_members->add($tmp_user->getId(), IL_CRS_TUTOR);
                break;

            case 'Member':
                $course_members->add($tmp_user->getId(), IL_CRS_MEMBER);
                break;
        }

        return true;
    }

    public function excludeCourseMember($sid, $course_id, $user_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }
        if (!is_numeric($course_id)) {
            return $this->__raiseError(
                'No valid course id given. Please choose an existing reference id of an ILIAS course',
                'Client'
            );
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if (($obj_type = ilObject::_lookupType(ilObject::_lookupObjId($course_id))) != 'crs') {
            $course_id = end($ref_ids = ilObject::_getAllReferences($course_id));
            if (ilObject::_lookupType(ilObject::_lookupObjId($course_id)) != 'crs') {
                return $this->__raiseError('Invalid course id. Object with id "' . $course_id . '" is not of type "course"', 'Client');
            }
        }

        if (ilObject::_lookupType($user_id) != 'usr') {
            return $this->__raiseError('Invalid user id. User with id "' . $user_id . ' does not exist', 'Client');
        }

        if (!$tmp_course = ilObjectFactory::getInstanceByRefId($course_id, false)) {
            return $this->__raiseError('Cannot create course instance!', 'Server');
        }

        if (!$rbacsystem->checkAccess('manage_members', $course_id)) {
            return $this->__raiseError('Check access failed. No permission to write to course', 'Server');
        }

        include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
        
        $course_members = ilCourseParticipants::_getInstanceByObjId($tmp_course->getId());
        if (!$course_members->checkLastAdmin(array($user_id))) {
            return $this->__raiseError('Cannot deassign last administrator from course', 'Server');
        }

        $course_members->delete($user_id);

        return true;
    }

    
    public function isAssignedToCourse($sid, $course_id, $user_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }
        if (!is_numeric($course_id)) {
            return $this->__raiseError(
                'No valid course id given. Please choose an existing reference id of an ILIAS course',
                'Client'
            );
        }
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if (($obj_type = ilObject::_lookupType(ilObject::_lookupObjId($course_id))) != 'crs') {
            $course_id = end($ref_ids = ilObject::_getAllReferences($course_id));
            if (ilObject::_lookupType(ilObject::_lookupObjId($course_id)) != 'crs') {
                return $this->__raiseError('Invalid course id. Object with id "' . $course_id . '" is not of type "course"', 'Client');
            }
        }

        if (ilObject::_lookupType($user_id) != 'usr') {
            return $this->__raiseError('Invalid user id. User with id "' . $user_id . ' does not exist', 'Client');
        }

        if (!$tmp_course = ilObjectFactory::getInstanceByRefId($course_id, false)) {
            return $this->__raiseError('Cannot create course instance!', 'Server');
        }

        if (!$rbacsystem->checkAccess('manage_members', $course_id)) {
            return $this->__raiseError('Check access failed. No permission to write to course', 'Server');
        }

        include_once './Modules/Course/classes/class.ilCourseParticipants.php';
        $crs_members = ilCourseParticipants::_getInstanceByObjId($tmp_course->getId());
        
        if ($crs_members->isAdmin($user_id)) {
            return IL_CRS_ADMIN;
        }
        if ($crs_members->isTutor($user_id)) {
            return IL_CRS_TUTOR;
        }
        if ($crs_members->isMember($user_id)) {
            return IL_CRS_MEMBER;
        }

        return "0";
    }


    public function getCourseXML($sid, $course_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }
        if (!is_numeric($course_id)) {
            return $this->__raiseError(
                'No valid course id given. Please choose an existing reference id of an ILIAS course',
                'Client'
            );
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        $tmp_course = $this->checkObjectAccess($course_id, "crs", "read", true);
        if ($this->isFault($tmp_course)) {
            return $tmp_course;
        }
        
        /*if(($obj_type = ilObject::_lookupType(ilObject::_lookupObjId($course_id))) != 'crs')
        {
            $course_id = end($ref_ids = ilObject::_getAllReferences($course_id));
            if(ilObject::_lookupType(ilObject::_lookupObjId($course_id)) != 'crs')
            {
                return $this->__raiseError('Invalid course id. Object with id "'. $course_id.'" is not of type "course"','Client');
            }
        }

        if(!$tmp_course = ilObjectFactory::getInstanceByRefId($course_id,false))
        {
            return $this->__raiseError('Cannot create course instance!','Server');
        }

        if(!$rbacsystem->checkAccess('read',$course_id))
        {
            return $this->__raiseError('Check access failed. No permission to read course','Server');
        }*/

        include_once 'Modules/Course/classes/class.ilCourseXMLWriter.php';

        $xml_writer = new ilCourseXMLWriter($tmp_course);
        $xml_writer->start();
        
        return $xml_writer->getXML();
    }

    public function updateCourse($sid, $course_id, $xml)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }
        
        if (!is_numeric($course_id)) {
            return $this->__raiseError(
                'No valid course id given. Please choose an existing reference id of an ILIAS course',
                'Client'
            );
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if (($obj_type = ilObject::_lookupType(ilObject::_lookupObjId($course_id))) != 'crs') {
            $course_id = end($ref_ids = ilObject::_getAllReferences($course_id));
            if (ilObject::_lookupType(ilObject::_lookupObjId($course_id)) != 'crs') {
                return $this->__raiseError('Invalid course id. Object with id "' . $course_id . '" is not of type "course"', 'Client');
            }
        }

        if (!$tmp_course = ilObjectFactory::getInstanceByRefId($course_id, false)) {
            return $this->__raiseError('Cannot create course instance!', 'Server');
        }

        if (!$rbacsystem->checkAccess('write', $course_id)) {
            return $this->__raiseError('Check access failed. No permission to write course', 'Server');
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

    // PRIVATE

    /**
     * get courses which belong to a specific user, fullilling the status
     *
     * @param string $sid
     * @param string $parameters following xmlresultset, columns (user_id, status with values  1 = "MEMBER", 2 = "TUTOR", 4 = "ADMIN", 8 = "OWNER" and any xor operation e.g.  1 + 4 = 5 = ADMIN and TUTOR, 7 = ADMIN and TUTOR and MEMBER)
     * @param string XMLResultSet, columns (ref_id, xml, parent_ref_id)
     */
    public function getCoursesForUser($sid, $parameters)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
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
            return $this->__raiseError($exception->getMessage(), "Client");
        }
        $xmlResultSet = $parser->getXMLResultSet();

        if (!$xmlResultSet->hasColumn("user_id")) {
            return $this->__raiseError("parameter user_id is missing", "Client");
        }
            
        if (!$xmlResultSet->hasColumn("status")) {
            return $this->__raiseError("parameter status is missing", "Client");
        }
        
        $user_id = (int) $xmlResultSet->getValue(0, "user_id");
        $status = (int) $xmlResultSet->getValue(0, "status");
        
        $ref_ids = array();

        // get roles
        #var_dump($xmlResultSet);
        #echo "uid:".$user_id;
        #echo "status:".$status;
        if (ilSoapCourseAdministration::MEMBER == ($status & ilSoapCourseAdministration::MEMBER) ||
            ilSoapCourseAdministration::TUTOR == ($status & ilSoapCourseAdministration::TUTOR) ||
            ilSoapCourseAdministration::ADMIN == ($status & ilSoapCourseAdministration::ADMIN)) {
            foreach ($rbacreview->assignedRoles($user_id) as $role_id) {
                if ($role = ilObjectFactory::getInstanceByObjId($role_id, false)) {
                    #echo $role->getType();
                    if ($role->getType() != "role") {
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
                        
                        #echo $role_title;
                        if (ilSoapCourseAdministration::MEMBER == ($status & ilSoapCourseAdministration::MEMBER) && strpos($role_title, "member") !== false) {
                            $ref_ids [] = $ref_id;
                        } elseif (ilSoapCourseAdministration::TUTOR  == ($status & ilSoapCourseAdministration::TUTOR) && strpos($role_title, "tutor") !== false) {
                            $ref_ids [] = $ref_id;
                        } elseif (ilSoapCourseAdministration::ADMIN  == ($status & ilSoapCourseAdministration::ADMIN) && strpos($role_title, "admin") !== false) {
                            $ref_ids [] = $ref_id;
                        } elseif (($status & ilSoapCourseAdministration::OWNER) == ilSoapCourseAdministration::OWNER && $ilObjDataCache->lookupOwner($ilObjDataCache->lookupObjId($ref_id)) == $user_id) {
                            $ref_ids [] = $ref_id;
                        }
                    }
                }
            }
        }
        if (($status & ilSoapCourseAdministration::OWNER) == ilSoapCourseAdministration::OWNER) {
            $owned_objects = ilObjectFactory::getObjectsForOwner("crs", $user_id);
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
        #print_r($ref_ids);
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
        $permission = $user_id == $ilUser->getId() ? 'read' : 'write';
        
        foreach ($ref_ids as $course_id) {
            $course_obj = $this->checkObjectAccess($course_id, "crs", $permission, true);
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
