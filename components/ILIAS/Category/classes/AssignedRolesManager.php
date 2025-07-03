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

declare(strict_types=1);

namespace ILIAS\Catgory;

use ILIAS\Category\InternalDomainService;

class AssignedRolesManager
{
    public function __construct(
        protected InternalDomainService $domain,
        protected int $ref_id,
        protected int $managed_user_id,
        protected int $managing_user_id,
    ) {
    }

    public function getAssignableRoles(): array
    {
        $rbacreview = $this->domain->rbac()->review();
        $tmp_obj = \ilObjectFactory::getInstanceByObjId($this->managed_user_id);
        // Admin => all roles
        if (in_array(SYSTEM_ROLE_ID, $rbacreview->assignedRoles($this->managing_user_id), true)) {
            $global_roles = $rbacreview->getGlobalRolesArray();
        } elseif ($tmp_obj?->getTimeLimitOwner() === $this->ref_id) {
            $global_roles = $rbacreview->getGlobalAssignableRoles();
        } else {
            $global_roles = [];
        }
        return array_merge(
            $global_roles,
            $rbacreview->getAssignableChildRoles($this->ref_id)
        );
    }

    public function switchAssignment(array $ids): void
    {
        $roles = $this->getAssignableRoles();
        $lng = $this->domain->lng();
        $rbacreview = $this->domain->rbac()->review();
        $rbacadmin = $this->domain->rbac()->admin();

        // check minimum one global role
        if (!$this->checkGlobalRoles($ids)) {
            throw new \Exception($lng->txt('no_global_role_left'));
        }

        $assigned_roles = $rbacreview->assignedRoles($this->managed_user_id);
        foreach ($roles as $role) {
            if (in_array((int) $role['obj_id'], $ids, true) && !in_array((int) $role['obj_id'], $assigned_roles, true)) {
                $rbacadmin->assignUser((int) $role['obj_id'], $this->managed_user_id);
            }
            if (in_array((int) $role['obj_id'], $ids, true) && in_array((int) $role['obj_id'], $assigned_roles, true)) {
                $rbacadmin->deassignUser((int) $role['obj_id'], $this->managed_user_id);
            }
        }
    }

    private function checkGlobalRoles(array $ids): bool
    {
        $rbacreview = $this->domain->rbac()->review();

        $tmp_obj = \ilObjectFactory::getInstanceByObjId($this->managed_user_id);
        if ($tmp_obj->getTimeLimitOwner() !== $this->ref_id &&
            !in_array(SYSTEM_ROLE_ID, $rbacreview->assignedRoles($this->managing_user_id), true)) {
            return true;
        }

        // new assignment by form
        $assigned = $rbacreview->assignedRoles($this->managed_user_id);

        // all assignable globals
        if (!in_array(SYSTEM_ROLE_ID, $rbacreview->assignedRoles($this->managing_user_id), true)) {
            $ga = $rbacreview->getGlobalAssignableRoles();
        } else {
            $ga = $rbacreview->getGlobalRolesArray();
        }
        $global_assignable = [];
        foreach ($ga as $role) {
            $global_assignable[] = $role['obj_id'];
        }

        $has_global_role = false;
        foreach ($rbacreview->getGlobalRoles() as $gb_role_id) {
            // global role will be switched on
            if (in_array($gb_role_id, $ids, true) &&
                !in_array($gb_role_id, $assigned, true)) {
                $has_global_role = true;
            }
            // global role was switched on and will not be switched off
            if (in_array($gb_role_id, $assigned, true) &&
                !in_array($gb_role_id, $ids, true)) {
                $has_global_role = true;
            }
        }
        return $has_global_role;
    }

}
