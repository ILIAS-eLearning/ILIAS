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
 * Soap rbac administration methods
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 *
 * @package ilias
 */
include_once './webservice/soap/classes/class.ilSoapAdministration.php';

class ilSoapRBACAdministration extends ilSoapAdministration
{
    public function deleteRole($sid, $role_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilAccess = $DIC['ilAccess'];

        if (!$tmp_role =&ilObjectFactory::getInstanceByObjId($role_id, false) or $tmp_role->getType() != 'role') {
            return $this->__raiseError(
                'No valid role id given. Please choose an existing id of an ILIAS role',
                'Client'
            );
        }


        $obj_ref = $rbacreview->getObjectReferenceOfRole($role_id);
        if (!$ilAccess->checkAccess('edit_permission', '', $obj_ref)) {
            return $this->__raiseError('Check access failed. No permission to delete role', 'Server');
        }

        // if it's last role of an user
        foreach ($assigned_users = $rbacreview->assignedUsers($role_id) as $user_id) {
            if (count($rbacreview->assignedRoles($user_id)) == 1) {
                return $this->__raiseError(
                    'Cannot deassign last role of users',
                    'Client'
                );
            }
        }

        // set parent id (role folder id) of role
        $rolf_id = end($rolf_ids = $rbacreview->getFoldersAssignedToRole($role_id, true));
        $tmp_role->setParent($rolf_id);
        $tmp_role->delete();

        return true;
    }

    public function addUserRoleEntry($sid, $user_id, $role_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        global $DIC;

        $rbacadmin = $DIC['rbacadmin'];
        $rbacreview = $DIC['rbacreview'];
        $ilAccess = $DIC['ilAccess'];

        if ($tmp_user =&ilObjectFactory::getInstanceByObjId($user_id) and $tmp_user->getType() != 'usr') {
            return $this->__raiseError(
                'No valid user id given. Please choose an existing id of an ILIAS user',
                'Client'
            );
        }
        if ($tmp_role =&ilObjectFactory::getInstanceByObjId($role_id) and $tmp_role->getType() != 'role') {
            return $this->__raiseError(
                'No valid role id given. Please choose an existing id of an ILIAS role',
                'Client'
            );
        }

        $obj_ref = $rbacreview->getObjectReferenceOfRole($role_id);
        if (!$ilAccess->checkAccess('edit_permission', '', $obj_ref)) {
            return $this->__raiseError('Check access failed. No permission to assign users', 'Server');
        }
        
        if (!$rbacadmin->assignUser($role_id, $user_id)) {
            return $this->__raiseError(
                'Error rbacadmin->assignUser()',
                'Server'
            );
        }
        return true;
    }
    public function deleteUserRoleEntry($sid, $user_id, $role_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        global $DIC;

        $rbacadmin = $DIC['rbacadmin'];
        $ilAccess = $DIC['ilAccess'];
        $rbacreview = $DIC['rbacreview'];

        if ($tmp_user =&ilObjectFactory::getInstanceByObjId($user_id, false) and $tmp_user->getType() != 'usr') {
            return $this->__raiseError(
                'No valid user id given. Please choose an existing id of an ILIAS user',
                'Client'
            );
        }
        if ($tmp_role =&ilObjectFactory::getInstanceByObjId($role_id, false) and $tmp_role->getType() != 'role') {
            return $this->__raiseError(
                'No valid role id given. Please choose an existing id of an ILIAS role',
                'Client'
            );
        }

        $obj_ref = $rbacreview->getObjectReferenceOfRole($role_id);
        if (!$ilAccess->checkAccess('edit_permission', '', $obj_ref)) {
            return $this->__raiseError('Check access failed. No permission to deassign users', 'Server');
        }

        if (!$rbacadmin->deassignUser($role_id, $user_id)) {
            return $this->__raiseError(
                'Error rbacadmin->deassignUser()',
                'Server'
            );
        }
        return true;
    }

    public function getOperations($sid)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        global $DIC;

        $rbacreview = $DIC['rbacreview'];

        if (is_array($ops = $rbacreview->getOperations())) {
            return $ops;
        } else {
            return $this->__raiseError('Unknown error', 'Server');
        }
    }

    public function revokePermissions($sid, $ref_id, $role_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        global $DIC;

        $rbacadmin = $DIC['rbacadmin'];
        $ilAccess = $DIC['ilAccess'];

        if (!$tmp_obj =&ilObjectFactory::getInstanceByRefId($ref_id, false)) {
            return $this->__raiseError(
                'No valid ref id given. Please choose an existing reference id of an ILIAS object',
                'Client'
            );
        }
        if ($tmp_role =&ilObjectFactory::getInstanceByObjId($role_id, false) and $tmp_role->getType() != 'role') {
            return $this->__raiseError(
                'No valid role id given. Please choose an existing id of an ILIAS role',
                'Client'
            );
        }
        if ($role_id == SYSTEM_ROLE_ID) {
            return $this->__raiseError(
                'Cannot revoke permissions of system role',
                'Client'
            );
        }

        if (!$ilAccess->checkAccess('edit_permission', '', $ref_id)) {
            return $this->__raiseError('Check access failed. No permission to revoke permissions', 'Server');
        }
        
        $rbacadmin->revokePermission($ref_id, $role_id);

        return true;
    }
    public function grantPermissions($sid, $ref_id, $role_id, $permissions)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        global $DIC;

        $rbacadmin = $DIC['rbacadmin'];
        $ilAccess = $DIC['ilAccess'];

        if (!$tmp_obj =&ilObjectFactory::getInstanceByRefId($ref_id, false)) {
            return $this->__raiseError(
                'No valid ref id given. Please choose an existing reference id of an ILIAS object',
                'Client'
            );
        }
        if ($tmp_role =&ilObjectFactory::getInstanceByObjId($role_id, false) and $tmp_role->getType() != 'role') {
            return $this->__raiseError(
                'No valid role id given. Please choose an existing id of an ILIAS role',
                'Client'
            );
        }
        
        if (!$ilAccess->checkAccess('edit_permission', '', $ref_id)) {
            return $this->__raiseError('Check access failed. No permission to grant permissions', 'Server');
        }
        
        
        // mjansen@databay.de: dirty fix
        if (isset($permissions['item'])) {
            $permissions = $permissions['item'];
        }

        if (!is_array($permissions)) {
            return $this->__raiseError(
                'No valid permissions given.' . print_r($permissions),
                'Client'
            );
        }

        $rbacadmin->revokePermission($ref_id, $role_id);
        $rbacadmin->grantPermission($role_id, $permissions, $ref_id);

        return true;
    }

    public function getLocalRoles($sid, $ref_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $ilAccess = $DIC['ilAccess'];

        if (!$tmp_obj =&ilObjectFactory::getInstanceByRefId($ref_id, false)) {
            return $this->__raiseError(
                'No valid ref id given. Please choose an existing reference id of an ILIAS object',
                'Client'
            );
        }

        if (!$ilAccess->checkAccess('edit_permission', '', $ref_id)) {
            return $this->__raiseError('Check access failed. No permission to access role information', 'Server');
        }


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

    public function getUserRoles($sid, $user_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        global $DIC;

        $rbacreview = $DIC['rbacreview'];

        if (!$tmp_user =&ilObjectFactory::getInstanceByObjId($user_id, false)) {
            return $this->__raiseError(
                'No valid user id given. Please choose an existing id of an ILIAS user',
                'Client'
            );
        }

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

    public function addRole($sid, $target_id, $role_xml)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $objDefinition = $DIC['objDefinition'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilAccess = $DIC['ilAccess'];

        if (!$tmp_obj =&ilObjectFactory::getInstanceByRefId($target_id, false)) {
            return $this->__raiseError(
                'No valid ref id given. Please choose an existing reference id of an ILIAS object',
                'Client'
            );
        }

        if (ilObject::_isInTrash($target_id)) {
            return $this->__raiseError("Parent with ID $target_id has been deleted.", 'CLIENT_TARGET_DELETED');
        }
        
        if (!$ilAccess->checkAccess('edit_permission', '', $target_id)) {
            return $this->__raiseError('Check access failed. No permission to create roles', 'Server');
        }
        
        include_once 'webservice/soap/classes/class.ilObjectXMLParser.php';

        $xml_parser = new ilObjectXMLParser($role_xml);
        $xml_parser->startParsing();

        foreach ($xml_parser->getObjectData() as $object_data) {

            // check if role title has il_ prefix
            if (substr($object_data['title'], 0, 3) == "il_") {
                return $this->__raiseError(
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

        return $new_roles ? $new_roles : array();
    }

    public function addRoleFromTemplate($sid, $target_id, $role_xml, $template_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $objDefinition = $DIC['objDefinition'];
        $rbacsystem = $DIC['rbacsystem'];
        $rbacadmin = $DIC['rbacadmin'];
        $ilAccess = $DIC['ilAccess'];

        if (!$tmp_obj =&ilObjectFactory::getInstanceByRefId($target_id, false)) {
            return $this->__raiseError(
                'No valid ref id given. Please choose an existing reference id of an ILIAS object',
                'Client'
            );
        }
        if (ilObject::_lookupType($template_id) != 'rolt') {
            return $this->__raiseError(
                'No valid template id given. Please choose an existing object id of an ILIAS role template',
                'Client'
            );
        }


        if (ilObject::_isInTrash($target_id)) {
            return $this->__raiseError("Parent with ID $target_id has been deleted.", 'CLIENT_TARGET_DELETED');
        }
        
        if (!$ilAccess->checkAccess('edit_permission', '', $target_id)) {
            return $this->__raiseError('Check access failed. No permission to create roles', 'Server');
        }
        

        include_once 'webservice/soap/classes/class.ilObjectXMLParser.php';

        $xml_parser = new ilObjectXMLParser($role_xml);
        $xml_parser->startParsing();

        foreach ($xml_parser->getObjectData() as $object_data) {

            // check if role title has il_ prefix
            if (substr($object_data['title'], 0, 3) == "il_") {
                return $this->__raiseError(
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


        // CREATE ADMIN ROLE





        return $new_roles ? $new_roles : array();
    }

    public function getObjectTreeOperations($sid, $ref_id, $user_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $rbacreview = $DIC['rbacreview'];
        $ilAccess = $DIC['ilAccess'];


        if (!$tmp_obj =&ilObjectFactory::getInstanceByRefId($ref_id, false)) {
            return $this->__raiseError(
                'No valid ref id given. Please choose an existing reference id of an ILIAS object',
                'Client'
            );
        }

        if (!$tmp_user =&ilObjectFactory::getInstanceByObjId($user_id, false)) {
            return $this->__raiseError(
                'No valid user id given.',
                'Client'
            );
        }

        if (ilObject::_isInTrash($ref_id)) {
            return $this->__raiseError("Parent with ID $target_id has been deleted.", 'CLIENT_TARGET_DELETED');
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

        foreach ($ops_data as $data) {
            $ret_data[] = $data;
        }
        return $ret_data ? $ret_data : array();
    }

    /**
     * get roles for a specific type and id
     *
     * @param String $sid    session id
     * @param String  $role_type can be empty which means "local & global", "local", "global", "user", "user_login" or "template"
     * @param Mixed $id can be -1 for system role folder, can be ref id in case for role type "local/global/template", can be user id with "user" or login in case for role type "user_login"
     * @return String according DTD role_3_7
     */
    public function getRoles($sid, $role_type, $id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $rbacreview = $DIC['rbacreview'];
        $ilUser = $DIC['ilUser'];
        $ilDB = $DIC['ilDB'];

        if (strcasecmp($role_type, "") != 0 &&
        strcasecmp($role_type, "local") != 0 &&
        strcasecmp($role_type, "global") != 0 &&
        strcasecmp($role_type, "user") != 0 &&
        strcasecmp($role_type, "user_login") != 0 &&
        strcasecmp($role_type, "template") != 0) {
            return $this->__raiseError('Called service with wrong role_type parameter \'' . $role_type . '\'', 'Client');
        }

        $roles = array();


        if (strcasecmp($role_type, "template") == 0) {
            // get templates
            $roles = $rbacreview->getRolesByFilter(6, $ilUser->getId());
        } elseif (strcasecmp($role_type, "user")==0 || strcasecmp($role_type, "user_login")==0) {
            // handle user roles
            $user_id = $this->parseUserID($id, $role_type);
            if ($user_id != $ilUser->getId()) {
                // check access for user folder
                $tmpUser = new ilObjUser($user_id);
                $timelimitOwner = $tmpUser->getTimeLimitOwner();
                if (!$rbacsystem->checkAccess('read', $timelimitOwner)) {
                    return $this->__raiseError('Check access for time limit owner failed.', 'Server');
                }
            }
            $role_type = ""; // local and global roles for user

            $query = sprintf(
                "SELECT object_data.title, rbac_fa.* FROM object_data, rbac_ua, rbac_fa WHERE rbac_ua.rol_id IN ('%s') AND rbac_ua.rol_id = rbac_fa.rol_id AND object_data.obj_id = rbac_fa.rol_id AND rbac_ua.usr_id=" . $user_id,
                join("','", $rbacreview->assignedRoles($user_id))
            );

            $rbacresult = $ilDB->query($query);
            while ($rbacrow = $rbacresult->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
                if ($rbacrow["assign"] != "y") {
                    continue;
                }

                $type = "";

                if ($rbacrow["parent"] == ROLE_FOLDER_ID) {
                    $type = "Global";
                } else {
                    $type = "Local";
                }
                if (strlen($type) && $tmp_obj = ilObjectFactory::getInstanceByObjId($rbacrow["rol_id"], false)) {
                    /* @var $tmp_obj IlObjRole */
                    $roles[] = array(
                            "obj_id" =>$rbacrow["rol_id"],
                            "title" => $tmp_obj->getTitle(),
                            "description" => $tmp_obj->getDescription(),
                            "role_type" => $type);
                }
            }
        } elseif ($id == "-1") {
            // get all roles of system role folder
            if (!$rbacsystem->checkAccess('read', ROLE_FOLDER_ID)) {
                return $this->__raiseError('Check access failed.', 'Server');
            }

            $roles = $rbacreview->getAssignableRoles(false, true);
        } else {
            // get local roles for a specific repository object
            // needs permission to read permissions of this object
            if (!$rbacsystem->checkAccess('edit_permission', $id)) {
                return $this->__raiseError('Check access for local roles failed.', 'Server');
            }

            if (!is_numeric($id)) {
                return $this->__raiseError('Id must be numeric to process roles of a repository object.', 'Client');
            }

            $role_type = "local";

            foreach ($rbacreview->getRolesOfRoleFolder($id, false) as $role_id) {
                if ($tmp_obj = ilObjectFactory::getInstanceByObjId($role_id, false)) {
                    $roles[] = array("obj_id" => $role_id, "title" => $tmp_obj->getTitle(), "description" => $tmp_obj->getDescription(), "role_type" => $role_type);
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
    }

    /**
     * search for roles.
     *
     * @param String $sid
     * @param String $searchterms comma separated search terms
     * @param String $operator must be or or and
     * @param String  $role_type can be empty which means "local & global", "local", "global", "user" = roles of user, "user_login" or "template"
     *
     */

    public function searchRoles($sid, $key, $combination, $role_type)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $rbacreview = $DIC['rbacreview'];
        $ilUser = $DIC['ilUser'];
        $ilDB = $DIC['ilDB'];


        if (strcasecmp($role_type, "") != 0 &&
        strcasecmp($role_type, "local") != 0 &&
        strcasecmp($role_type, "global") != 0 &&
        strcasecmp($role_type, "template") != 0) {
            return $this->__raiseError('Called service with wrong role_type parameter \'' . $role_type . '\'', 'Client');
        }

        if ($combination != 'and' and $combination != 'or') {
            return $this->__raiseError(
                'No valid combination given. Must be "and" or "or".',
                'Client'
            );
        }

        include_once './Services/Search/classes/class.ilQueryParser.php';

        $query_parser = new ilQueryParser($key);
        $query_parser->setMinWordLength(3);
        $query_parser->setCombination($combination == 'and' ? QP_COMBINATION_AND : QP_COMBINATION_OR);
        $query_parser->parse();
        if (!$query_parser->validate()) {
            return $this->__raiseError($query_parser->getMessage(), 'Client');
        }

        include_once './Services/Search/classes/class.ilObjectSearchFactory.php';

        $object_search = ilObjectSearchFactory::_getObjectSearchInstance($query_parser);
        $object_search->setFilter(array("role","rolt"));

        $res = $object_search->performSearch();
        $res->filter(ROOT_FOLDER_ID, $combination == 'and' ? true : false);

        $obj_ids = array();
        foreach ($res->getUniqueResults() as $entry) {
            $obj_ids [] = $entry['obj_id'];
        }

        $roles = array();
        if (count($obj_ids)> 0) {
            #print_r($obj_ids);
            $roles = $rbacreview->getRolesForIDs($obj_ids, $role_type == "template");
        }
        #print_r($roles);
        include_once './webservice/soap/classes/class.ilSoapRoleObjectXMLWriter.php';
        $xml_writer = new ilSoapRoleObjectXMLWriter();
        $xml_writer->setObjects($roles);
        $xml_writer->setType($role_type);
        if ($xml_writer->start()) {
            return $xml_writer->getXML();
        }
    }


    private function parseUserID($id, $role_type)
    {
        if (strcasecmp($role_type, "user")==0) {
            // get user roles for user id, which can be numeric or ilias id
            $user_id = !is_numeric($id) ? ilUtil::__extractId($id, IL_INST_ID) : $id;
            if (!is_numeric($user_id)) {
                return $this->__raiseError('ID must be either numeric or ILIAS conform id for type \'user\'', 'Client');
            }
        } elseif (strcasecmp($role_type, "user_login") == 0) {
            // check for login
            $user_id = ilObjUser::_lookupId($id);
            if (!$user_id) {
                // could not find a valid user
                return $this->__raiseError('User with login \'' . $id . '\' does not exist!', 'Client');
            }
        }
        return $user_id;
    }
}
