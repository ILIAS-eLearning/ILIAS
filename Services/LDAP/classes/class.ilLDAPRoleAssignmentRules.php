<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Do role assignemnts
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilLDAPRoleAssignmentRules
{
    const ROLE_ACTION_ASSIGN = 'Assign';
    const ROLE_ACTION_DEASSIGN = 'Detach';
    
    protected static $default_role = null;

    public static function getDefaultRole(int $a_server_id) : int
    {
        return self::$default_role =
            ilLDAPAttributeMapping::_lookupGlobalRole($a_server_id);
    }
    
    /**
     * Get all assignable roles (used for import parser)
     * @return array roles
     */
    public static function getAllPossibleRoles(int $a_server_id) : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $roles = [];
        $query = "SELECT DISTINCT(role_id) FROM ldap_role_assignments " .
                'WHERE server_id = ' . $ilDB->quote($a_server_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $roles[$row->role_id] = $row->role_id;
        }
        $gr = self::getDefaultRole($a_server_id);
        $roles[$gr] = $gr;
        return $roles;
    }
    
    /**
     * get all possible attribute names
     * @return string[]
     */
    public static function getAttributeNames($a_server_id) : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT DISTINCT(att_name) " .
            "FROM ldap_role_assignments " .
            'WHERE server_id = ' . $ilDB->quote($a_server_id, 'integer');
        $res = $ilDB->query($query);
        $names = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $name = strtolower(trim($row->att_name ?? ''));
            if ($name) {
                $names[] = $name;
            }
        }
        
        $names = array_merge($names, self::getAdditionalPluginAttributes($a_server_id));
        return $names;
    }
    
    public static function getAssignmentsForUpdate(int $a_server_id, $a_usr_id, $a_usr_name, $a_usr_data) : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $rbacreview = $DIC['rbacreview'];
        $ilLog = $DIC['ilLog'];
        
        $query = "SELECT rule_id,add_on_update,remove_on_update FROM ldap_role_assignments " .
            "WHERE (add_on_update = 1 OR remove_on_update = 1) " .
                'AND server_id = ' . $ilDB->quote($a_server_id, 'integer');
        
        $res = $ilDB->query($query);
        $roles = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
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
        
        return $roles;
    }
    
    
    /**
     *
     * @return array role data
     * @param object $a_usr_id
     * @param object $a_usr_data
     */
    public static function getAssignmentsForCreation(int $a_server_id, $a_usr_name, $a_usr_data) : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilLog = $DIC['ilLog'];
        
        $query = "SELECT rule_id FROM ldap_role_assignments " .
                'WHERE server_id = ' . $ilDB->quote($a_server_id, 'integer');
        $res = $ilDB->query($query);
        
        $num_matches = 0;
        $roles = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
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
        
        return $roles;
    }
    
    /**
     * Call plugin check if the condition matches.
     */
    public static function callPlugin(int $a_plugin_id, array $a_user_data) : bool
    {
        global $DIC;

        $component_factory = $DIC["component.factory"];
        foreach ($component_factory->getActivePluginsInSlot('ldaphk') as $plugin) {
            if ($plugin->checkRoleAssignment($a_plugin_id, $a_user_data)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Fetch additional attributes from plugin
     * @return string[]
     */
    protected static function getAdditionalPluginAttributes(int $a_server_id) : array
    {
        global $DIC;

        $attributes = array();
        $component_factory = $DIC["component.factory"];
        foreach ($component_factory->getActivePluginsInSlot('ldaphk') as $plugin) {
            $attributes[] = $plugin->getAdditionalAttributeNames();
        }
        
        return array_merge(...$attributes);
    }

    
    /**
     * Parse role
     */
    protected static function parseRole(int $a_role_id, string $a_action) : array
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        
        return array(
            'id' => $a_role_id,
            'type' => $rbacreview->isGlobalRole($a_role_id) ? 'Global' : 'Local',
            'action' => $a_action
            );
    }
}
