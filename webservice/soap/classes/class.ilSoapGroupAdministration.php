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

include_once './webservice/soap/classes/class.ilSoapAdministration.php';

/**
 * Soap grp administration methods
 * @author  Stefan Meyer <meyer@leifos.com
 * @version $Id$
 * @package ilias
 */
class ilSoapGroupAdministration extends ilSoapAdministration
{
    public const MEMBER = 1;
    public const ADMIN = 2;
    public const OWNER = 4;

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public function addGroup(string $sid, int $target_id, string $grp_xml)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if (!$rbacsystem->checkAccess('create', $target_id, 'grp')) {
            return $this->raiseError('Check access failed. No permission to create groups', 'Server');
        }

        if (ilObject::_isInTrash($target_id)) {
            return $this->raiseError("Parent with ID $target_id has been deleted.", 'CLIENT_TARGET_DELETED');
        }

        $newObj = new ilObjGroup();
        $newObj->setTitle('dummy');
        $newObj->setDescription("");
        $newObj->create();

        include_once("./Modules/Group/classes/class.ilObjGroup.php");
        include_once 'Modules/Group/classes/class.ilGroupXMLParser.php';
        $xml_parser = new ilGroupXMLParser($newObj, $grp_xml, $target_id);
        $xml_parser->startParsing();
        $new_ref_id = $xml_parser->getObjectRefId();

        return $new_ref_id ?: "0";
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public function updateGroup(string $sid, int $ref_id, string $grp_xml)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if (!$rbacsystem->checkAccess('write', $ref_id, 'grp')) {
            return $this->raiseError('Check access failed. No permission to edit groups', 'Server');
        }

        include_once("./Modules/Group/classes/class.ilObjGroup.php");

        /** @var ilObjGroup $grp */
        if (!$grp = ilObjectFactory::getInstanceByRefId($ref_id, false)) {
            return $this->raiseError('Cannot create group instance!', 'CLIENT_OBJECT_NOT_FOUND');
        }

        if (ilObject::_isInTrash($ref_id)) {
            return $this->raiseError("Object with ID $ref_id has been deleted.", 'CLIENT_OBJECT_DELETED');
        }

        if (ilObjectFactory::getTypeByRefId($ref_id, false) !== "grp") {
            return $this->raiseError('Reference id does not point to a group!', 'CLIENT_WRONG_TYPE');
        }

        include_once 'Modules/Group/classes/class.ilGroupXMLParser.php';
        $xml_parser = new ilGroupXMLParser($grp, $grp_xml, -1);
        $xml_parser->setMode(ilGroupXMLParser::$UPDATE);
        $xml_parser->startParsing();
        $new_ref_id = $xml_parser->getObjectRefId();

        return $new_ref_id ?: "0";
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public function groupExists(string $sid, string $title)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        if (!$title) {
            return $this->raiseError(
                'No title given. Please choose an title for the group in question.',
                'Client'
            );
        }

        return ilUtil::groupNameExists($title);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public function getGroup(string $sid, int $ref_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        if (ilObject::_isInTrash($ref_id)) {
            return $this->raiseError("Parent with ID $ref_id has been deleted.", 'CLIENT_OBJECT_DELETED');
        }

        /** @var ilObjGroup $grp_obj */
        if (!$grp_obj = ilObjectFactory::getInstanceByRefId($ref_id, false)) {
            return $this->raiseError(
                'No valid reference id given.',
                'Client'
            );
        }

        include_once 'Modules/Group/classes/class.ilGroupXMLWriter.php';
        $xml_writer = new ilGroupXMLWriter($grp_obj);
        $xml_writer->start();

        return $xml_writer->getXML();
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public function assignGroupMember(string $sid, int $group_id, int $user_id, string $type)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if (ilObject::_lookupType(ilObject::_lookupObjId($group_id)) !== 'grp') {
            $ref_ids = ilObject::_getAllReferences($group_id);
            $group_id = end($ref_ids);
            if (ilObject::_lookupType(ilObject::_lookupObjId($group_id)) !== 'grp') {
                return $this->raiseError(
                    'Invalid group id. Object with id "' . $group_id . '" is not of type "group"',
                    'Client'
                );
            }
        }

        if (!$rbacsystem->checkAccess('manage_members', $group_id)) {
            return $this->raiseError('Check access failed. No permission to write to group', 'Server');
        }

        if (ilObject::_lookupType($user_id) !== 'usr') {
            return $this->raiseError('Invalid user id. User with id "' . $user_id . ' does not exist', 'Client');
        }
        if ($type !== 'Admin' &&
            $type !== 'Member') {
            return $this->raiseError(
                'Invalid type ' . $type . ' given. Parameter "type" must be "Admin","Member"',
                'Client'
            );
        }

        /** @var ilObjGroup $tmp_group */
        if (!$tmp_group = ilObjectFactory::getInstanceByRefId($group_id, false)) {
            return $this->raiseError('Cannot create group instance!', 'Server');
        }

        /** @var ilObjUser $tmp_user */
        if (!$tmp_user = ilObjectFactory::getInstanceByObjId($user_id, false)) {
            return $this->raiseError('Cannot create user instance!', 'Server');
        }

        include_once 'Modules/Group/classes/class.ilGroupParticipants.php';
        $group_members = ilGroupParticipants::_getInstanceByObjId($tmp_group->getId());

        switch ($type) {
            case 'Admin':
                $group_members->add($tmp_user->getId(), ilParticipants::IL_GRP_ADMIN);
                break;

            case 'Member':
                $group_members->add($tmp_user->getId(), ilParticipants::IL_GRP_MEMBER);
                break;
        }
        return true;
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public function excludeGroupMember(string $sid, int $group_id, int $user_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if (ilObject::_lookupType(ilObject::_lookupObjId($group_id)) !== 'grp') {
            $ref_ids = ilObject::_getAllReferences($group_id);
            $group_id = end($ref_ids);
            if (ilObject::_lookupType(ilObject::_lookupObjId($group_id)) !== 'grp') {
                return $this->raiseError(
                    'Invalid group id. Object with id "' . $group_id . '" is not of type "group"',
                    'Client'
                );
            }
        }

        if (ilObject::_lookupType($user_id) !== 'usr') {
            return $this->raiseError('Invalid user id. User with id "' . $user_id . ' does not exist', 'Client');
        }

        /** @var ilObjGroup $tmp_group */
        if (!$tmp_group = ilObjectFactory::getInstanceByRefId($group_id, false)) {
            return $this->raiseError('Cannot create group instance!', 'Server');
        }

        if (!$rbacsystem->checkAccess('manage_members', $group_id)) {
            return $this->raiseError('Check access failed. No permission to write to group', 'Server');
        }

        $tmp_group->leave($user_id);
        return true;
    }

    /**
     * @return int|soap_fault|SoapFault|null
     */
    public function isAssignedToGroup(string $sid, int $group_id, int $user_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if (ilObject::_lookupType(ilObject::_lookupObjId($group_id)) !== 'grp') {
            $ref_ids = ilObject::_getAllReferences($group_id);
            $group_id = end($ref_ids);
            if (ilObject::_lookupType(ilObject::_lookupObjId($group_id)) !== 'grp') {
                return $this->raiseError(
                    'Invalid group id. Object with id "' . $group_id . '" is not of type "group"',
                    'Client'
                );
            }
        }

        if (ilObject::_lookupType($user_id) !== 'usr') {
            return $this->raiseError('Invalid user id. User with id "' . $user_id . ' does not exist', 'Client');
        }

        /** @var ilObjGroup $tmp_group */
        if (!$tmp_group = ilObjectFactory::getInstanceByRefId($group_id, false)) {
            return $this->raiseError('Cannot create group instance!', 'Server');
        }

        if (!$rbacsystem->checkAccess('read', $group_id)) {
            return $this->raiseError('Check access failed. No permission to read group data', 'Server');
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

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public function getGroupsForUser(string $sid, string $parameters)
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

        if (ilSoapGroupAdministration::MEMBER == ($status & ilSoapGroupAdministration::MEMBER) ||
            ilSoapGroupAdministration::ADMIN == ($status & ilSoapGroupAdministration::ADMIN)) {
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

                        #echo $role_title;
                        if (ilSoapGroupAdministration::MEMBER == ($status & ilSoapGroupAdministration::MEMBER) && strpos(
                            $role_title,
                            "member"
                        ) !== false) {
                            $ref_ids [] = $ref_id;
                        } elseif (ilSoapGroupAdministration::ADMIN == ($status & ilSoapGroupAdministration::ADMIN) && strpos(
                            $role_title,
                            "admin"
                        ) !== false) {
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

        include_once 'webservice/soap/classes/class.ilXMLResultSetWriter.php';
        include_once 'Modules/Group/classes/class.ilObjGroup.php';
        include_once 'Modules/Group/classes/class.ilGroupXMLWriter.php';

        $xmlResultSet = new ilXMLResultSet();
        $xmlResultSet->addColumn("ref_id");
        $xmlResultSet->addColumn("xml");
        $xmlResultSet->addColumn("parent_ref_id");

        foreach ($ref_ids as $group_id) {
            $group_obj = $this->checkObjectAccess($group_id, ['grp'], "write", true);
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
