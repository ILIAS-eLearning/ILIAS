<?php
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
 * Shibboleth role assignment rules
 *
 * @author  Stefan Meyer <meyer@leifos.com>
 * @author  Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @version $Id$
 *
 *
 * @ingroup AuthShibboleth
 */
class ilShibbolethRoleAssignmentRules
{
    protected static array $active_plugins = [];

    /**
     * @return array<int|string, \ilShibbolethRoleAssignmentRule>
     */
    public static function getAllRules(): array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $rules = array();
        /**
         * @var $ilDB ilDBInterface
         */
        $query = "SELECT rule_id FROM shib_role_assignment ORDER BY rule_id";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $rules[$row->rule_id] = new ilShibbolethRoleAssignmentRule($row->rule_id);
        }

        return $rules;
    }

    public static function getCountRules(): int
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $query = "SELECT COUNT(*) num FROM shib_role_assignment ";
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        return (int) ($row->num ?? 0);
    }

    public static function updateAssignments(int $a_usr_id, array $a_data): bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $rbacadmin = $DIC['rbacadmin'];
        $rbacreview = $DIC['rbacreview'];
        $ilLog = $DIC['ilLog'];
        $query = "SELECT rule_id,add_on_update,remove_on_update FROM shib_role_assignment " . "WHERE add_on_update = 1 OR remove_on_update = 1";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $rule = new ilShibbolethRoleAssignmentRule($row->rule_id);
            //			$matches = $rule->matches($a_data);
            if ($rule->doesMatch($a_data) && $row->add_on_update) {
                $ilLog->write(__METHOD__ . ': Assigned to role ' . ilObject::_lookupTitle($rule->getRoleId()));
                $rbacadmin->assignUser($rule->getRoleId(), $a_usr_id);
            }
            if (!$rule->doesMatch($a_data) && $row->remove_on_update) {
                $ilLog->write(__METHOD__ . ': Deassigned from role ' . ilObject::_lookupTitle($rule->getRoleId()));
                $rbacadmin->deassignUser($rule->getRoleId(), $a_usr_id);
            }
        }
        // check if is assigned to minimum one global role
        if (!array_intersect($rbacreview->assignedRoles($a_usr_id), $rbacreview->getGlobalRoles())) {
            $settings = new ilShibbolethSettings();
            $default_role = $settings->getDefaultRole();
            $ilLog->write(__METHOD__ . ': Assigned to default role ' . ilObject::_lookupTitle($default_role));
            $rbacadmin->assignUser($default_role, $a_usr_id);
        }

        return true;
    }

    public static function doAssignments(int $a_usr_id, array $a_data): bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $rbacadmin = $DIC['rbacadmin'];
        $ilLog = $DIC['ilLog'];
        $query = "SELECT rule_id,add_on_update FROM shib_role_assignment WHERE add_on_update = 1";
        $num_matches = 0;
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $rule = new ilShibbolethRoleAssignmentRule($row->rule_id);
            if ($rule->doesMatch($a_data)) {
                $num_matches++;
                $ilLog->write(__METHOD__ . ': Assigned to role ' . ilObject::_lookupTitle($rule->getRoleId()));
                $rbacadmin->assignUser($rule->getRoleId(), $a_usr_id);
            }
        }
        // Assign to default if no matching found
        if ($num_matches === 0) {
            $settings = new ilShibbolethSettings();
            $default_role = $settings->getDefaultRole();
            $ilLog->write(__METHOD__ . ': Assigned to default role ' . ilObject::_lookupTitle($default_role));
            $rbacadmin->assignUser($default_role, $a_usr_id);
        }

        return true;
    }

    public static function callPlugin(string $a_plugin_id, array $a_user_data): bool
    {
        global $DIC;
        foreach ($DIC['component.factory']->getActivePluginsInSlot('shibhk') as $plugin) {
            if ($plugin->checkRoleAssignment($a_plugin_id, $a_user_data)) {
                return true;
            }
        }
        return false;
    }
}
