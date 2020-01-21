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
   * Soap grp administration methods
   *
   * @author Stefan Meyer <meyer@leifos.com
   * @version $Id$
   *
   * @package ilias
   */
include_once './webservice/soap/classes/class.ilSoapAdministration.php';

class ilSoapGroupAdministration extends ilSoapAdministration
{
    const MEMBER = 1;
    const ADMIN = 2;
    const OWNER = 4;
    
    

    // Service methods
    public function addGroup($sid, $target_id, $grp_xml)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        if (!is_numeric($target_id)) {
            return $this->__raiseError(
                'No valid target id given. Please choose an existing reference id of an ILIAS category or group',
                'Client'
            );
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if (!$rbacsystem->checkAccess('create', $target_id, 'grp')) {
            return $this->__raiseError('Check access failed. No permission to create groups', 'Server');
        }

        if (ilObject::_isInTrash($target_id)) {
            return $this->__raiseError("Parent with ID $target_id has been deleted.", 'CLIENT_TARGET_DELETED');
        }


        $newObj = new ilObjGroup();
        $newObj->setTitle('dummy');
        $newObj->setDescription("");
        $newObj->create(true); // true for upload

        // Start import
        include_once("./Modules/Group/classes/class.ilObjGroup.php");
        include_once 'Modules/Group/classes/class.ilGroupXMLParser.php';
        $xml_parser = new ilGroupXMLParser($newObj, $grp_xml, $target_id);
        $new_ref_id = $xml_parser->startParsing();

        return $new_ref_id ? $new_ref_id : "0";
    }

    // Service methods
    public function updateGroup($sid, $ref_id, $grp_xml)
    {
        $this->initAuth($sid);
        $this->initIlias();


        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }



        if (!is_numeric($ref_id)) {
            return $this->__raiseError(
                'No valid target id given. Please choose an existing reference id of an ILIAS category or group',
                'Client'
            );
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if (!$rbacsystem->checkAccess('write', $ref_id, 'grp')) {
            return $this->__raiseError('Check access failed. No permission to edit groups', 'Server');
        }

        // Start import
        include_once("./Modules/Group/classes/class.ilObjGroup.php");

        if (!$grp = ilObjectFactory::getInstanceByRefId($ref_id, false)) {
            return $this->__raiseError('Cannot create group instance!', 'CLIENT_OBJECT_NOT_FOUND');
        }

        if (ilObject::_isInTrash($ref_id)) {
            return $this->__raiseError("Object with ID $ref_id has been deleted.", 'CLIENT_OBJECT_DELETED');
        }

        
        if (ilObjectFactory::getTypeByRefId($ref_id, false) !="grp") {
            return $this->__raiseError('Reference id does not point to a group!', 'CLIENT_WRONG_TYPE');
        }


        include_once 'Modules/Group/classes/class.ilGroupXMLParser.php';
        $xml_parser = new ilGroupXMLParser($grp, $grp_xml, -1);
        $xml_parser->setMode(ilGroupXMLParser::$UPDATE);
        $new_ref_id = $xml_parser->startParsing();

        return $new_ref_id ? $new_ref_id : "0";
    }


    public function groupExists($sid, $title)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        if (!$title) {
            return $this->__raiseError(
                'No title given. Please choose an title for the group in question.',
                'Client'
            );
        }

        return ilUtil::groupNameExists($title);
    }

    public function getGroup($sid, $ref_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        if (ilObject::_isInTrash($ref_id)) {
            return $this->__raiseError("Parent with ID $ref_id has been deleted.", 'CLIENT_OBJECT_DELETED');
        }


        if (!$grp_obj =&ilObjectFactory::getInstanceByRefId($ref_id, false)) {
            return $this->__raiseError(
                'No valid reference id given.',
                'Client'
            );
        }


        include_once 'Modules/Group/classes/class.ilGroupXMLWriter.php';

        $xml_writer = new ilGroupXMLWriter($grp_obj);
        $xml_writer->start();

        $xml = $xml_writer->getXML();

        return strlen($xml) ? $xml : '';
    }


    public function assignGroupMember($sid, $group_id, $user_id, $type)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        if (!is_numeric($group_id)) {
            return $this->__raiseError(
                'No valid group id given. Please choose an existing reference id of an ILIAS group',
                'Client'
            );
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if (($obj_type = ilObject::_lookupType(ilObject::_lookupObjId($group_id))) != 'grp') {
            $group_id = end($ref_ids = ilObject::_getAllReferences($group_id));
            if (ilObject::_lookupType(ilObject::_lookupObjId($group_id)) != 'grp') {
                return $this->__raiseError('Invalid group id. Object with id "' . $group_id . '" is not of type "group"', 'Client');
            }
        }

        if (!$rbacsystem->checkAccess('manage_members', $group_id)) {
            return $this->__raiseError('Check access failed. No permission to write to group', 'Server');
        }


        if (ilObject::_lookupType($user_id) != 'usr') {
            return $this->__raiseError('Invalid user id. User with id "' . $user_id . ' does not exist', 'Client');
        }
        if ($type != 'Admin' and
           $type != 'Member') {
            return $this->__raiseError('Invalid type ' . $type . ' given. Parameter "type" must be "Admin","Member"', 'Client');
        }

        if (!$tmp_group = ilObjectFactory::getInstanceByRefId($group_id, false)) {
            return $this->__raiseError('Cannot create group instance!', 'Server');
        }

        if (!$tmp_user = ilObjectFactory::getInstanceByObjId($user_id, false)) {
            return $this->__raiseError('Cannot create user instance!', 'Server');
        }


        include_once 'Modules/Group/classes/class.ilGroupParticipants.php';
        $group_members = ilGroupParticipants::_getInstanceByObjId($tmp_group->getId());

        switch ($type) {
            case 'Admin':
                $group_members->add($tmp_user->getId(), IL_GRP_ADMIN);
                break;

            case 'Member':
                $group_members->add($tmp_user->getId(), IL_GRP_MEMBER);
                break;
        }
        return true;
    }

    public function excludeGroupMember($sid, $group_id, $user_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }
        if (!is_numeric($group_id)) {
            return $this->__raiseError(
                'No valid group id given. Please choose an existing reference id of an ILIAS group',
                'Client'
            );
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if (($type = ilObject::_lookupType(ilObject::_lookupObjId($group_id))) != 'grp') {
            $group_id = end($ref_ids = ilObject::_getAllReferences($group_id));
            if (ilObject::_lookupType(ilObject::_lookupObjId($group_id)) != 'grp') {
                return $this->__raiseError('Invalid group id. Object with id "' . $group_id . '" is not of type "group"', 'Client');
            }
        }

        if (ilObject::_lookupType($user_id) != 'usr') {
            return $this->__raiseError('Invalid user id. User with id "' . $user_id . ' does not exist', 'Client');
        }

        if (!$tmp_group = ilObjectFactory::getInstanceByRefId($group_id, false)) {
            return $this->__raiseError('Cannot create group instance!', 'Server');
        }

        if (!$rbacsystem->checkAccess('manage_members', $group_id)) {
            return $this->__raiseError('Check access failed. No permission to write to group', 'Server');
        }

        $tmp_group->leave($user_id);
        return true;
    }


    public function isAssignedToGroup($sid, $group_id, $user_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }
        if (!is_numeric($group_id)) {
            return $this->__raiseError(
                'No valid group id given. Please choose an existing id of an ILIAS group',
                'Client'
            );
        }
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if (($type = ilObject::_lookupType(ilObject::_lookupObjId($group_id))) != 'grp') {
            $group_id = end($ref_ids = ilObject::_getAllReferences($group_id));
            if (ilObject::_lookupType(ilObject::_lookupObjId($group_id)) != 'grp') {
                return $this->__raiseError('Invalid group id. Object with id "' . $group_id . '" is not of type "group"', 'Client');
            }
        }

        if (ilObject::_lookupType($user_id) != 'usr') {
            return $this->__raiseError('Invalid user id. User with id "' . $user_id . ' does not exist', 'Client');
        }

        if (!$tmp_group = ilObjectFactory::getInstanceByRefId($group_id, false)) {
            return $this->__raiseError('Cannot create group instance!', 'Server');
        }

        if (!$rbacsystem->checkAccess('read', $group_id)) {
            return $this->__raiseError('Check access failed. No permission to read group data', 'Server');
        }

        include_once('./Modules/Group/classes/class.ilGroupParticipants.php');
        $participants = ilGroupParticipants::_getInstanceByObjId(ilObject::_lookupObjId($group_id));
        
        if ($participants->isAdmin($user_id)) {
            return 1;
        }
        if ($participants->isMember($user_id)) {
            return 2;
        }
        return 0;
    }

    // PRIVATE

    /**
         * get groups which belong to a specific user, fullilling the status
         *
         * @param string $sid
         * @param string $parameters following xmlresultset, columns (user_id, status with values  1 = "MEMBER", 2 = "TUTOR", 4 = "ADMIN", 8 = "OWNER" and any xor operation e.g.  1 + 4 = 5 = ADMIN and TUTOR, 7 = ADMIN and TUTOR and MEMBER)
         * @param string XMLResultSet, columns (ref_id, xml, parent_ref_id)
         */
    public function getGroupsForUser($sid, $parameters)
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
        if (ilSoapGroupAdministration::MEMBER == ($status & ilSoapGroupAdministration::MEMBER) ||
            ilSoapGroupAdministration::ADMIN == ($status & ilSoapGroupAdministration::ADMIN)) {
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
                        if (ilSoapGroupAdministration::MEMBER == ($status & ilSoapGroupAdministration::MEMBER) && strpos($role_title, "member") !== false) {
                            $ref_ids [] = $ref_id;
                        } elseif (ilSoapGroupAdministration::ADMIN  == ($status & ilSoapGroupAdministration::ADMIN) && strpos($role_title, "admin") !== false) {
                            $ref_ids [] = $ref_id;
                        }
                    }
                }
            }
        }
        
        if (($status & ilSoapGroupAdministration::OWNER) == ilSoapGroupAdministration::OWNER) {
            $owned_objects = ilObjectFactory::getObjectsForOwner("grp", $user_id);
            foreach ($owned_objects as $obj_id) {
                $allrefs = ilObject::_getAllReferences($obj_id);
                $refs = array();
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
        
        
        #print_r($ref_ids);
        include_once 'webservice/soap/classes/class.ilXMLResultSetWriter.php';
        include_once 'Modules/Group/classes/class.ilObjGroup.php';
        include_once 'Modules/Group/classes/class.ilGroupXMLWriter.php';

        $xmlResultSet = new ilXMLResultSet();
        $xmlResultSet->addColumn("ref_id");
        $xmlResultSet->addColumn("xml");
        $xmlResultSet->addColumn("parent_ref_id");
        
        foreach ($ref_ids as $group_id) {
            $group_obj = $this->checkObjectAccess($group_id, "grp", "write", true);
            if ($group_obj instanceof ilObjGroup) {
                $row = new ilXMLResultSetRow();
                $row->setValue("ref_id", $group_id);
                $xmlWriter = new ilGroupXMLWriter($group_obj);
                $xmlWriter->setAttachUsers(false);
                $xmlWriter->start();
                $row->setValue("xml", $xmlWriter->getXML());
                $row->setValue("parent_ref_id", $tree->getParentId($group_id));
                $xmlResultSet->addRow($row);
            }
        }
        $xmlResultSetWriter = new ilXMLResultSetWriter($xmlResultSet);
        $xmlResultSetWriter->start();
        return $xmlResultSetWriter->getXML();
    }
}
