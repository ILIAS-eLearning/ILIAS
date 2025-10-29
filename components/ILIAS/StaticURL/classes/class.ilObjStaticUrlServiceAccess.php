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

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilObjStaticUrlServiceAccess extends ilObjectAccess
{
    private ilObjUser $user;
    private ilRbacSystem $rbacsystem;
    private ilRbacReview $rbacreview;
    private ?int $ref_id;
    private ?array $global_roles = null;

    public function __construct()
    {
        global $DIC;
        $this->rbacreview = $DIC->rbac()->review();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->user = $DIC->user();
        $this->ref_id = $DIC->http()->wrapper()->query()->has('ref_id')
            ? $DIC->http()->wrapper()->query()->retrieve('ref_id', $DIC->refinery()->kindlyTo()->int())
            : null;
    }

    public function checkAccessAndThrowException(string $permission): void
    {
        if (!$this->hasUserPermissionTo($permission)) {
            throw new ilException('Permission denied');
        }
    }

    public function hasUserPermissionTo(string $permission): bool
    {
        if ($this->ref_id === null) {
            return false;
        }
        return $this->rbacsystem->checkAccess($permission, $this->ref_id);
    }

    public function getGlobalRoles(): array
    {
        $global_roles = $this->rbacreview->getRolesForIDs(
            $this->rbacreview->getGlobalRoles(),
            false
        );

        $roles = [];
        foreach ($global_roles as $global_role) {
            $roles[$global_role['rol_id']] = $global_role['title'];
        }

        return $roles;
    }

    private function resolveUsersGlobalRoles(): array
    {
        return $this->global_roles
            ?? $this->global_roles = $this->rbacreview->assignedGlobalRoles($this->user->getId());
    }
}
