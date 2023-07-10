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
 * Soap rbac administration methods
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilSoapRBACAdministration extends ilSoapAdministration
{
    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public function deleteRole(string $sid, int $role_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilAccess = $DIC['ilAccess'];

        if (!($tmp_role = ilObjectFactory::getInstanceByObjId($role_id, false)) || $tmp_role->getType() !== 'role') {
            return $this->raiseError(
                'No valid role id given. Please choose an existing id of an ILIAS role',
                'Client'
            );
        }

        $obj_ref = $rbacreview->getObjectReferenceOfRole($role_id);
        if (!$ilAccess->checkAccess('edit_permission', '', $obj_ref)) {
            return $this->raiseError('Check access failed. No permission to delete role', 'Server');
        }

        // if it's last role of an user
        foreach ($assigned_users = $rbacreview->assignedUsers($role_id) as $user_id) {
            if (count($rbacreview->assignedRoles($user_id)) === 1) {
                return $this->raiseError(
                    'Cannot deassign last role of users',
                    'Client'
                );
            }
        }

        // set parent id (role folder id) of role
        $rolf_ids = $rbacreview->getFoldersAssignedToRole($role_id, true);
        $rolf_id = end($rolf_ids);
        $tmp_role->setParent((int) $rolf_id);
        $tmp_role->delete();
        return true;
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public function addUserRoleEntry(string $sid, int $user_id, int $role_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        global $DIC;

        $rbacadmin = $DIC['rbacadmin'];
        $rbacreview = $DIC['rbacreview'];
        $ilAccess = $DIC['ilAccess'];

        $tmp_user = ilObjectFactory::getInstanceByObjId($user_id, false);
        if (!$tmp_user instanceof ilObjUser) {
            return $this->raiseError(
                'No valid user id given. Please choose an existing id of an ILIAS user',
                'Client'
            );
        }
        $tmp_role = ilObjectFactory::getInstanceByObjId($role_id, false);
        if (!$tmp_role instanceof ilObjRole) {
            return $this->raiseError(
                'No valid role id given. Please choose an existing id of an ILIAS role',
                'Client'
            );
        }

        $obj_ref = $rbacreview->getObjectReferenceOfRole($role_id);
        if (!$ilAccess->checkAccess('edit_permission', '', $obj_ref)) {
            return $this->raiseError('Check access failed. No permission to assign users', 'Server');
        }

        if (!$rbacadmin->assignUser($role_id, $user_id)) {
            return $this->raiseError(
                'Error rbacadmin->assignUser()',
                'Server'
            );
        }
        return true;
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public function deleteUserRoleEntry(string $sid, int $user_id, int $role_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        global $DIC;

        $rbacadmin = $DIC['rbacadmin'];
        $ilAccess = $DIC['ilAccess'];
        $rbacreview = $DIC['rbacreview'];

        if ($tmp_user = ilObjectFactory::getInstanceByObjId($user_id, false) and $tmp_user->getType() !== 'usr') {
            return $this->raiseError(
                'No valid user id given. Please choose an existing id of an ILIAS user',
                'Client'
            );
        }
        if ($tmp_role = ilObjectFactory::getInstanceByObjId($role_id, false) and $tmp_role->getType() !== 'role') {
            return $this->raiseError(
                'No valid role id given. Please choose an existing id of an ILIAS role',
                'Client'
            );
        }

        $obj_ref = $rbacreview->getObjectReferenceOfRole($role_id);
        if (!$ilAccess->checkAccess('edit_permission', '', $obj_ref)) {
            return $this->raiseError('Check access failed. No permission to deassign users', 'Server');
        }

        if (!$rbacadmin->deassignUser($role_id, $user_id)) {
            return $this->raiseError(
                'Error rbacadmin->deassignUser()',
                'Server'
            );
        }
        return true;
    }

    /**
     * @return soap_fault|SoapFault|null|array
     */
    public function getOperations(string $sid)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        global $DIC;

        $rbacreview = $DIC['rbacreview'];

        if (is_array($ops = $rbacreview->getOperations())) {
            return $ops;
        }

        return $this->raiseError('Unknown error', 'Server');
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public function revokePermissions(string $sid, int $ref_id, int $role_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        global $DIC;

        $rbacadmin = $DIC['rbacadmin'];
        $ilAccess = $DIC['ilAccess'];

        if (!$tmp_obj = ilObjectFactory::getInstanceByRefId($ref_id, false)) {
            return $this->raiseError(
                'No valid ref id given. Please choose an existing reference id of an ILIAS object',
                'Client'
            );
        }
        if (($tmp_role = ilObjectFactory::getInstanceByObjId($role_id, false)) && $tmp_role->getType() !== 'role') {
            return $this->raiseError(
                'No valid role id given. Please choose an existing id of an ILIAS role',
                'Client'
            );
        }
        if ($role_id === SYSTEM_ROLE_ID) {
            return $this->raiseError(
                'Cannot revoke permissions of system role',
                'Client'
            );
        }

        if (!$ilAccess->checkAccess('edit_permission', '', $ref_id)) {
            return $this->raiseError('Check access failed. No permission to revoke permissions', 'Server');
        }
        $rbacadmin->revokePermission($ref_id, $role_id);
        return true;
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public function grantPermissions(string $sid, int $ref_id, int $role_id, array $permissions)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        global $DIC;

        $rbacadmin = $DIC['rbacadmin'];
        $ilAccess = $DIC['ilAccess'];

        if (!$tmp_obj = ilObjectFactory::getInstanceByRefId($ref_id, false)) {
            return $this->raiseError(
                'No valid ref id given. Please choose an existing reference id of an ILIAS object',
                'Client'
            );
        }
        if (($tmp_role = ilObjectFactory::getInstanceByObjId($role_id, false)) && $tmp_role->getType() !== 'role') {
            return $this->raiseError(
                'No valid role id given. Please choose an existing id of an ILIAS role',
                'Client'
            );
        }

        if (!$ilAccess->checkAccess('edit_permission', '', $ref_id)) {
            return $this->raiseError('Check access failed. No permission to grant permissions', 'Server');
        }

        // mjansen@databay.de: dirty fix
        if (isset($permissions['item'])) {
            $permissions = $permissions['item'];
        }

        if (!is_array($permissions)) {
            return $this->raiseError(
                'No valid permissions given.' . print_r($permissions),
                'Client'
            );
        }

        $rbacadmin->revokePermission($ref_id, $role_id);
        $rbacadmin->grantPermission($role_id, $permissions, $ref_id);
        return true;
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public function getLocalRoles(string $sid, int $ref_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $ilAccess = $DIC['ilAccess'];

        if (!$tmp_obj = ilObjectFactory::getInstanceByRefId($ref_id, false)) {
            return $this->raiseError(
                'No valid ref id given. Please choose an existing reference id of an ILIAS object',
                'Client'
            );
        }

        if (!$ilAccess->checkAccess('edit_permission', '', $ref_id)) {
            return $this->raiseError('Check access failed. No permission to access role information', 'Server');
        }

        $objs = [];
        foreach ($rbacreview->getRolesOfRoleFolder($ref_id, false) as $role_id) {
            if ($tmp_obj = ilObjectFactory::getInstanceByObjId($role_id, false)) {
                $objs[] = $tmp_obj;
            }
        }
        if (count($objs)) {
            include_once './webservice/soap/classes/class.ilObjectXMLWriter.php';

            $xml_writer = new ilObjectXMLWriter();
            $xml_writer->setObjects($objs);
            if ($xml_writer->start()) {
                return $xml_writer->getXML();
            }
        }
        return '';
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public function getUserRoles(string $sid, int $user_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        global $DIC;

        $rbacreview = $DIC['rbacreview'];

        if (!$tmp_user = ilObjectFactory::getInstanceByObjId($user_id, false)) {
            return $this->raiseError(
                'No valid user id given. Please choose an existing id of an ILIAS user',
                'Client'
            );
        }

        $objs = [];
        foreach ($rbacreview->assignedRoles($user_id) as $role_id) {
            if ($tmp_obj = ilObjectFactory::getInstanceByObjId($role_id, false)) {
                $objs[] = $tmp_obj;
            }
        }
        if (count($objs)) {
            include_once './webservice/soap/classes/class.ilObjectXMLWriter.php';

            $xml_writer = new ilObjectXMLWriter();
            $xml_writer->setObjects($objs);
            if ($xml_writer->start()) {
                return $xml_writer->getXML();
            }
        }
        return '';
    }

    /**
     * @return array|soap_fault|SoapFault|null
     */
    public function addRole(string $sid, int $target_id, string $role_xml)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $objDefinition = $DIC['objDefinition'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilAccess = $DIC['ilAccess'];

        if (!$tmp_obj = ilObjectFactory::getInstanceByRefId($target_id, false)) {
            return $this->raiseError(
                'No valid ref id given. Please choose an existing reference id of an ILIAS object',
                'Client'
            );
        }

        if (ilObject::_isInTrash($target_id)) {
            return $this->raiseError("Parent with ID $target_id has been deleted.", 'CLIENT_TARGET_DELETED');
        }

        if (!$ilAccess->checkAccess('edit_permission', '', $target_id)) {
            return $this->raiseError('Check access failed. No permission to create roles', 'Server');
        }

        include_once 'webservice/soap/classes/class.ilObjectXMLParser.php';
        $xml_parser = new ilObjectXMLParser($role_xml);
        $xml_parser->startParsing();

        $new_roles = [];
        foreach ($xml_parser->getObjectData() as $object_data) {
            // check if role title has il_ prefix
            if (strpos($object_data['title'], "il_") === 0) {
                return $this->raiseError(
                    'Rolenames are not allowed to start with "il_" ',
                    'Client'
                );
            }

            include_once './Services/AccessControl/classes/class.ilObjRole.php';
            $role = new ilObjRole();
            $role->setTitle($object_data['title']);
            $role->setDescription($object_data['description']);
            $role->setImportId($object_data['import_id']);
            $role->create();

            $GLOBALS['DIC']['rbacadmin']->assignRoleToFolder($role->getId(), $target_id);
            $new_roles[] = $role->getId();
        }
        return $new_roles;
    }

    /**
     * @return array|soap_fault|SoapFault|null
     */
    public function addRoleFromTemplate(string $sid, int $target_id, string $role_xml, int $template_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $objDefinition = $DIC['objDefinition'];
        $rbacsystem = $DIC['rbacsystem'];
        $rbacadmin = $DIC['rbacadmin'];
        $ilAccess = $DIC['ilAccess'];

        if (!$tmp_obj = ilObjectFactory::getInstanceByRefId($target_id, false)) {
            return $this->raiseError(
                'No valid ref id given. Please choose an existing reference id of an ILIAS object',
                'Client'
            );
        }
        if (ilObject::_lookupType($template_id) !== 'rolt') {
            return $this->raiseError(
                'No valid template id given. Please choose an existing object id of an ILIAS role template',
                'Client'
            );
        }

        if (ilObject::_isInTrash($target_id)) {
            return $this->raiseError("Parent with ID $target_id has been deleted.", 'CLIENT_TARGET_DELETED');
        }

        if (!$ilAccess->checkAccess('edit_permission', '', $target_id)) {
            return $this->raiseError('Check access failed. No permission to create roles', 'Server');
        }

        include_once 'webservice/soap/classes/class.ilObjectXMLParser.php';
        $xml_parser = new ilObjectXMLParser($role_xml);
        $xml_parser->startParsing();

        $new_roles = [];
        foreach ($xml_parser->getObjectData() as $object_data) {
            // check if role title has il_ prefix
            if (strpos($object_data['title'], "il_") === 0) {
                return $this->raiseError(
                    'Rolenames are not allowed to start with "il_" ',
                    'Client'
                );
            }

            include_once './Services/AccessControl/classes/class.ilObjRole.php';
            $role = new ilObjRole();
            $role->setTitle($object_data['title']);
            $role->setDescription($object_data['description']);
            $role->setImportId($object_data['import_id']);
            $role->create();

            $GLOBALS['DIC']['rbacadmin']->assignRoleToFolder($role->getId(), $target_id);

            // Copy permssions
            $rbacadmin->copyRoleTemplatePermissions($template_id, ROLE_FOLDER_ID, $target_id, $role->getId());

            // Set object permissions according to role template
            $ops = $rbacreview->getOperationsOfRole($role->getId(), $tmp_obj->getType(), $target_id);
            $rbacadmin->grantPermission($role->getId(), $ops, $target_id);
            $new_roles[] = $role->getId();
        }
        return $new_roles;
    }

    /**
     * @return array|soap_fault|SoapFault|null
     */
    public function getObjectTreeOperations(string $sid, int $ref_id, int $user_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $rbacreview = $DIC['rbacreview'];
        $ilAccess = $DIC['ilAccess'];

        if (!$tmp_obj = ilObjectFactory::getInstanceByRefId($ref_id, false)) {
            return $this->raiseError(
                'No valid ref id given. Please choose an existing reference id of an ILIAS object',
                'Client'
            );
        }

        if (!$tmp_user = ilObjectFactory::getInstanceByObjId($user_id, false)) {
            return $this->raiseError(
                'No valid user id given.',
                'Client'
            );
        }

        if (ilObject::_isInTrash($ref_id)) {
            return $this->raiseError("Parent with ID " . $ref_id . "has been deleted.", 'CLIENT_TARGET_DELETED');
        }

        // check visible for all upper tree entries
        if (!$ilAccess->checkAccessOfUser($tmp_user->getId(), 'visible', '', $tmp_obj->getRefId())) {
            return array();
        }
        $op_data = $rbacreview->getOperation(2);
        $ops_data[] = $op_data;

        if (!$ilAccess->checkAccessOfUser($tmp_user->getId(), 'read', '', $tmp_obj->getRefId())) {
            return $ops_data;
        }

        $ops_data = array();
        $ops = $rbacreview->getOperationsOnTypeString($tmp_obj->getType());
        foreach ($ops as $ops_id) {
            $op_data = $rbacreview->getOperation($ops_id);

            if ($rbacsystem->checkAccessOfUser($user_id, $op_data['operation'], $tmp_obj->getRefId())) {
                $ops_data[$ops_id] = $op_data;
            }
        }

        $ret_data = [];
        foreach ($ops_data as $data) {
            $ret_data[] = $data;
        }
        return $ret_data;
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public function getRoles(string $sid, string $role_type, int $id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $rbacreview = $DIC['rbacreview'];
        $ilUser = $DIC['ilUser'];
        $ilDB = $DIC['ilDB'];

        if (strcasecmp($role_type, "") !== 0 &&
            strcasecmp($role_type, "local") !== 0 &&
            strcasecmp($role_type, "global") !== 0 &&
            strcasecmp($role_type, "user") !== 0 &&
            strcasecmp($role_type, "user_login") !== 0 &&
            strcasecmp($role_type, "template") !== 0) {
            return $this->raiseError(
                'Called service with wrong role_type parameter \'' . $role_type . '\'',
                'Client'
            );
        }

        $roles = array();

        if (strcasecmp($role_type, "template") === 0) {
            // get templates
            $roles = $rbacreview->getRolesByFilter(6, $ilUser->getId());
        } elseif (strcasecmp($role_type, "user") === 0 || strcasecmp($role_type, "user_login") === 0) {
            // handle user roles
            $user_id = $this->parseUserID($id, $role_type);
            if ((int) $user_id !== $ilUser->getId()) {
                // check access for user folder
                $tmpUser = new ilObjUser($user_id);
                $timelimitOwner = $tmpUser->getTimeLimitOwner();
                if (!$rbacsystem->checkAccess('read', $timelimitOwner)) {
                    return $this->raiseError('Check access for time limit owner failed.', 'Server');
                }
            }
            $role_type = ""; // local and global roles for user

            $query = sprintf(
                "SELECT object_data.title, rbac_fa.* FROM object_data, rbac_ua, rbac_fa WHERE rbac_ua.rol_id IN ('%s') AND rbac_ua.rol_id = rbac_fa.rol_id AND object_data.obj_id = rbac_fa.rol_id AND rbac_ua.usr_id=" . $user_id,
                implode("','", $rbacreview->assignedRoles($user_id))
            );

            $rbacresult = $ilDB->query($query);
            while ($rbacrow = $rbacresult->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
                if ($rbacrow["assign"] !== "y") {
                    continue;
                }

                $type = "";

                if ((int) $rbacrow["parent"] === ROLE_FOLDER_ID) {
                    $type = "Global";
                } else {
                    $type = "Local";
                }
                if (strlen($type) && $tmp_obj = ilObjectFactory::getInstanceByObjId($rbacrow["rol_id"], false)) {
                    /* @var $tmp_obj IlObjRole */
                    $roles[] = array(
                        "obj_id" => $rbacrow["rol_id"],
                        "title" => $tmp_obj->getTitle(),
                        "description" => $tmp_obj->getDescription(),
                        "role_type" => $type
                    );
                }
            }
        } elseif ($id === -1) {
            // get all roles of system role folder
            if (!$rbacsystem->checkAccess('read', ROLE_FOLDER_ID)) {
                return $this->raiseError('Check access failed.', 'Server');
            }

            $roles = $rbacreview->getAssignableRoles(false, true);
        } else {
            // get local roles for a specific repository object
            // needs permission to read permissions of this object
            if (!$rbacsystem->checkAccess('edit_permission', $id)) {
                return $this->raiseError('Check access for local roles failed.', 'Server');
            }

            $role_type = "local";

            foreach ($rbacreview->getRolesOfRoleFolder($id, false) as $role_id) {
                if ($tmp_obj = ilObjectFactory::getInstanceByObjId($role_id, false)) {
                    $roles[] = [
                        "obj_id" => $role_id,
                        "title" => $tmp_obj->getTitle(),
                        "description" => $tmp_obj->getDescription(),
                        "role_type" => $role_type
                    ];
                }
            }
        }

        include_once './webservice/soap/classes/class.ilSoapRoleObjectXMLWriter.php';

        $xml_writer = new ilSoapRoleObjectXMLWriter();
        $xml_writer->setObjects($roles);
        $xml_writer->setType($role_type);
        if ($xml_writer->start()) {
            return $xml_writer->getXML();
        }
        return '';
    }

    /**
     * search for roles.
     * @param string $role_type   can be empty which means "local & global", "local", "global", "user" = roles of user, "user_login" or "template"
     * @return soap_fault|SoapFault|null|string
     */
    public function searchRoles(string $sid, string $key, string $combination, string $role_type)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $rbacreview = $DIC['rbacreview'];
        $ilUser = $DIC['ilUser'];
        $ilDB = $DIC['ilDB'];

        if (strcasecmp($role_type, "") !== 0 &&
            strcasecmp($role_type, "local") !== 0 &&
            strcasecmp($role_type, "global") !== 0 &&
            strcasecmp($role_type, "template") !== 0) {
            return $this->raiseError(
                'Called service with wrong role_type parameter \'' . $role_type . '\'',
                'Client'
            );
        }

        if ($combination !== 'and' && $combination !== 'or') {
            return $this->raiseError(
                'No valid combination given. Must be "and" or "or".',
                'Client'
            );
        }

        include_once './Services/Search/classes/class.ilQueryParser.php';

        $query_parser = new ilQueryParser($key);
        $query_parser->setMinWordLength(3);
        $query_parser->setCombination($combination === 'and' ? ilQueryParser::QP_COMBINATION_AND : ilQueryParser::QP_COMBINATION_OR);
        $query_parser->parse();
        if (!$query_parser->validate()) {
            return $this->raiseError($query_parser->getMessage(), 'Client');
        }

        include_once './Services/Search/classes/class.ilObjectSearchFactory.php';

        $object_search = ilObjectSearchFactory::_getObjectSearchInstance($query_parser);
        $object_search->setFilter(array("role", "rolt"));

        $res = $object_search->performSearch();
        $res->filter(ROOT_FOLDER_ID, $combination === 'and');

        $obj_ids = array();
        foreach ($res->getUniqueResults() as $entry) {
            $obj_ids [] = $entry['obj_id'];
        }

        $roles = array();
        if (count($obj_ids) > 0) {
            $roles = $rbacreview->getRolesForIDs($obj_ids, $role_type === "template");
        }

        include_once './webservice/soap/classes/class.ilSoapRoleObjectXMLWriter.php';
        $xml_writer = new ilSoapRoleObjectXMLWriter();
        $xml_writer->setObjects($roles);
        $xml_writer->setType($role_type);
        if ($xml_writer->start()) {
            return $xml_writer->getXML();
        }
        return '';
    }

    private function parseUserID(int $id, string $role_type)
    {
        $user_id = 0;
        if (strcasecmp($role_type, "user") === 0) {
            // get user roles for user id, which can be numeric or ilias id
            $user_id = !is_numeric($id) ? ilUtil::__extractId($id, IL_INST_ID) : $id;
            if (!is_numeric($user_id)) {
                return $this->raiseError('ID must be either numeric or ILIAS conform id for type \'user\'', 'Client');
            }
        } elseif (strcasecmp($role_type, "user_login") === 0) {
            // check for login
            $user_id = ilObjUser::_lookupId($id);
            if (!$user_id) {
                // could not find a valid user
                return $this->raiseError('User with login \'' . $id . '\' does not exist!', 'Client');
            }
        }
        return $user_id;
    }
}
