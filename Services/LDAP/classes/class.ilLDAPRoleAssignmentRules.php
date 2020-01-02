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
 * @classDescription Do role assignemnts
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesLDAP
 */
class ilLDAPRoleAssignmentRules
{
    const ROLE_ACTION_ASSIGN	= 'Assign';
    const ROLE_ACTION_DEASSIGN	= 'Detach';
    
    protected static $active_plugins = null;
    protected static $default_role = null;
    
    
    /**
     * Get default global role
     * @param int $a_server_id
     * @return
     */
    public static function getDefaultRole($a_server_id)
    {
        include_once './Services/LDAP/classes/class.ilLDAPAttributeMapping.php';
        include_once './Services/LDAP/classes/class.ilLDAPServer.php';
            
        return self::$default_role =
            ilLDAPAttributeMapping::_lookupGlobalRole($a_server_id);
    }
    
    /**
     * Get all assignable roles (used for import parser)
     * @param int $a_server_id
     * @return array roles
     */
    public static function getAllPossibleRoles($a_server_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT DISTINCT(role_id) FROM ldap_role_assignments " .
                'WHERE server_id = ' . $ilDB->quote($a_server_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $roles[$row->role_id] = $row->role_id;
        }
        $gr = self::getDefaultRole($a_server_id);
        $roles[$gr] = $gr;
        return $roles ? $roles : array();
    }
    
    // begin-patch ldap_multiple
    /**
     * get all possible attribute names
     * @param int $a_server_id
     * @return
     */
    public static function getAttributeNames($a_server_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT DISTINCT(att_name) " .
            "FROM ldap_role_assignments " .
            'WHERE server_id = ' . $ilDB->quote($a_server_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $name = strtolower(trim($row->att_name));
            if ($name) {
                $names[] = $name;
            }
        }
        
        $names = array_merge((array) $names, self::getAdditionalPluginAttributes($a_server_id));
        return $names ? $names : array();
    }
    
    // begin-patch ldap_multiple
    /**
     *
     * @global type $ilDB
     * @global type $rbacadmin
     * @global type $rbacreview
     * @global type $ilSetting
     * @global type $ilLog
     * @param int $a_server_id
     * @param type $a_usr_id
     * @param type $a_usr_name
     * @param type $a_usr_data
     * @return array
     */
    public static function getAssignmentsForUpdate($a_server_id, $a_usr_id, $a_usr_name, $a_usr_data)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $rbacadmin = $DIC['rbacadmin'];
        $rbacreview = $DIC['rbacreview'];
        $ilSetting = $DIC['ilSetting'];
        $ilLog = $DIC['ilLog'];
        
        $query = "SELECT rule_id,add_on_update,remove_on_update FROM ldap_role_assignments " .
            "WHERE (add_on_update = 1 OR remove_on_update = 1) " .
                'AND server_id = ' . $ilDB->quote($a_server_id, 'integer');
        
        $res = $ilDB->query($query);
        $roles = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            include_once './Services/LDAP/classes/class.ilLDAPRoleAssignmentRule.php';
            $rule = ilLDAPRoleAssignmentRule::_getInstanceByRuleId($row->rule_id);
            
            $matches = $rule->matches($a_usr_data);
            if ($matches and $row->add_on_update) {
                $ilLog->info(': Assigned to role: ' . $a_usr_name . ' => ' . ilObject::_lookupTitle($rule->getRoleId()));
                $roles[] = self::parseRole($rule->getRoleId(), self::ROLE_ACTION_ASSIGN);
            }
            if (!$matches and $row->remove_on_update) {
                $ilLog->info(': Deassigned from role: ' . $a_usr_name . ' => ' . ilObject::_lookupTitle($rule->getRoleId()));
                $roles[] = self::parseRole($rule->getRoleId(), self::ROLE_ACTION_DEASSIGN);
            }
        }
        
        // Check if there is minimum on global role
        $deassigned_global = 0;
        foreach ($roles as $role_data) {
            if ($role_data['type'] == 'Global' and
                $role_data['action'] == self::ROLE_ACTION_DEASSIGN) {
                $deassigned_global++;
            }
        }
        if (count($rbacreview->assignedGlobalRoles($a_usr_id)) == $deassigned_global) {
            $ilLog->info(': No global role left. Assigning to default role.');
            $roles[] = self::parseRole(
                self::getDefaultRole($a_server_id),
                self::ROLE_ACTION_ASSIGN
            );
        }
        
        return $roles ? $roles : array();
    }
    
    
    /**
     *
     * @return array role data
     * @param int $a_server_id
     * @param object $a_usr_id
     * @param object $a_usr_data
     *
     * @access public
     * @static
     */
    public static function getAssignmentsForCreation($a_server_id, $a_usr_name, $a_usr_data)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilLog = $DIC['ilLog'];
        
        $query = "SELECT rule_id FROM ldap_role_assignments " .
                'WHERE server_id = ' . $ilDB->quote($a_server_id, 'integer');
        $res = $ilDB->query($query);
        
        $num_matches = 0;
        $roles = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            include_once './Services/LDAP/classes/class.ilLDAPRoleAssignmentRule.php';
            $rule = ilLDAPRoleAssignmentRule::_getInstanceByRuleId($row->rule_id);
            
            if ($rule->matches($a_usr_data)) {
                $num_matches++;
                $ilLog->info(': Assigned to role: ' . $a_usr_name . ' => ' . ilObject::_lookupTitle($rule->getRoleId()));
                $roles[] = self::parseRole($rule->getRoleId(), self::ROLE_ACTION_ASSIGN);
            }
        }
        
        // DONE: check for global role
        $found_global = false;
        foreach ($roles as $role_data) {
            if ($role_data['type'] == 'Global') {
                $found_global = true;
                break;
            }
        }
        if (!$found_global) {
            $ilLog->info(': No matching rule found. Assigning to default role.');
            $roles[] = self::parseRole(
                self::getDefaultRole($a_server_id),
                self::ROLE_ACTION_ASSIGN
            );
        }
        
        return $roles ? $roles : array();
    }
    
    /**
     * Call plugin check if the condition matches.
     *
     * @return bool
     * @param object $a_plugin_id
     * @param object $a_user_data
     */
    public static function callPlugin($a_plugin_id, $a_user_data)
    {
        global $DIC;

        $ilPluginAdmin = $DIC['ilPluginAdmin'];
        
        if (self::$active_plugins == null) {
            self::$active_plugins = $ilPluginAdmin->getActivePluginsForSlot(
                IL_COMP_SERVICE,
                'LDAP',
                'ldaphk'
            );
        }
        
        $assigned = false;
        foreach (self::$active_plugins as $plugin_name) {
            $ok = false;
            $plugin_obj = $ilPluginAdmin->getPluginObject(
                IL_COMP_SERVICE,
                'LDAP',
                'ldaphk',
                $plugin_name
            );
            
            if ($plugin_obj instanceof ilLDAPRoleAssignmentPlugin) {
                $ok = $plugin_obj->checkRoleAssignment($a_plugin_id, $a_user_data);
            }
            
            if ($ok) {
                $assigned = true;
            }
        }
        return $assigned;
    }

    // begin-patch ldap_multiple
    
    /**
     * Fetch additional attributes from plugin
     * @param int $a_server_id
     * @return
     */
    protected static function getAdditionalPluginAttributes($a_server_id)
    {
        global $DIC;

        $ilPluginAdmin = $DIC['ilPluginAdmin'];
        
        if (self::$active_plugins == null) {
            self::$active_plugins = $ilPluginAdmin->getActivePluginsForSlot(
                IL_COMP_SERVICE,
                'LDAP',
                'ldaphk'
            );
        }

        $attributes = array();
        foreach (self::$active_plugins as $plugin_name) {
            $ok = false;
            $plugin_obj = $ilPluginAdmin->getPluginObject(
                IL_COMP_SERVICE,
                'LDAP',
                'ldaphk',
                $plugin_name
            );
            
            if ($plugin_obj instanceof ilLDAPRoleAssignmentPlugin) {
                $attributes = array_merge($attributes, $plugin_obj->getAdditionalAttributeNames());
            }
        }
        return $attributes ? $attributes : array();
    }

    
    /**
     * Parse role
     * @return
     * @param int $a_role_id
     * @param string $a_action
     */
    protected static function parseRole($a_role_id, $a_action)
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        
        return array(
            'id'		=> $a_role_id,
            'type'		=> $rbacreview->isGlobalRole($a_role_id) ? 'Global' : 'Local',
            'action'	=> $a_action
            );
    }
}
