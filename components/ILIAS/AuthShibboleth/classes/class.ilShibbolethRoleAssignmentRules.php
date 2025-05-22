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
    /**
     * @return array<int|string, ilShibbolethRoleAssignmentRule>
     */
    public static function getAllRules(): array
    {
        global $DIC;
        $db = $DIC->database();
        $rules = [];
        /**
         * @var $db ilDBInterface
         */
        $query = "SELECT rule_id FROM shib_role_assignment ORDER BY rule_id";
        $res = $db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $rules[$row->rule_id] = new ilShibbolethRoleAssignmentRule($row->rule_id);
        }

        return $rules;
    }

    public static function getCountRules(): int
    {
        global $DIC;
        $db = $DIC->database();
        $query = "SELECT COUNT(*) num FROM shib_role_assignment ";
        $res = $db->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        return (int) ($row->num ?? 0);
    }

    public static function updateAssignments(int $a_usr_id, array $a_data): bool
    {
        global $DIC;
        $db = $DIC->database();
        $rbac_admin = $DIC->rbac()->admin();
        $rbac_review = $DIC->rbac()->review();
        $logger = $DIC->logger()->root();
        $query = "SELECT rule_id,add_on_update,remove_on_update FROM shib_role_assignment " . "WHERE add_on_update = 1 OR remove_on_update = 1";
        $res = $db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $rule = new ilShibbolethRoleAssignmentRule($row->rule_id);
            //			$matches = $rule->matches($a_data);
            if ($row->add_on_update && $rule->doesMatch($a_data)) {
                $logger->write(__METHOD__ . ': Assigned to role ' . ilObject::_lookupTitle($rule->getRoleId()));
                $rbac_admin->assignUser($rule->getRoleId(), $a_usr_id);
            }
            if ($row->remove_on_update && !$rule->doesMatch($a_data)) {
                $logger->write(__METHOD__ . ': Deassigned from role ' . ilObject::_lookupTitle($rule->getRoleId()));
                $rbac_admin->deassignUser($rule->getRoleId(), $a_usr_id);
            }
        }
        // check if is assigned to minimum one global role
        if (!array_intersect($rbac_review->assignedRoles($a_usr_id), $rbac_review->getGlobalRoles())) {
            $settings = new ilShibbolethSettings();
            $default_role = $settings->getDefaultRole();
            $logger->write(__METHOD__ . ': Assigned to default role ' . ilObject::_lookupTitle($default_role));
            $rbac_admin->assignUser($default_role, $a_usr_id);
        }

        return true;
    }

    public static function doAssignments(int $a_usr_id, array $a_data): bool
    {
        global $DIC;
        $db = $DIC->database();
        $rbac_admin = $DIC->rbac()->admin();
        $logger = $DIC->logger()->root();
        $query = "SELECT rule_id,add_on_update FROM shib_role_assignment WHERE add_on_update = 1";
        $num_matches = 0;
        $res = $db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $rule = new ilShibbolethRoleAssignmentRule($row->rule_id);
            if ($rule->doesMatch($a_data)) {
                $num_matches++;
                $logger->write(__METHOD__ . ': Assigned to role ' . ilObject::_lookupTitle($rule->getRoleId()));
                $rbac_admin->assignUser($rule->getRoleId(), $a_usr_id);
            }
        }
        // Assign to default if no matching found
        if ($num_matches === 0) {
            $settings = new ilShibbolethSettings();
            $default_role = $settings->getDefaultRole();
            $logger->write(__METHOD__ . ': Assigned to default role ' . ilObject::_lookupTitle($default_role));
            $rbac_admin->assignUser($default_role, $a_usr_id);
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
