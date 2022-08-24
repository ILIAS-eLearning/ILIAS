<?php

declare(strict_types=1);

/**
 * Class ilObjMainMenuAccess
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjMainMenuAccess extends ilObjectAccess implements ilMainMenuAccess
{
    private ilObjUser $user;
    private ilRbacSystem $rbacsystem;
    private ilRbacReview $rbacreview;
    private ?int $ref_id;

    /**
     * ilObjMainMenuAccess constructor.
     */
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

    /**
     * @param string $permission
     * @throws ilException
     */
    public function checkAccessAndThrowException(string $permission): void
    {
        if (!$this->hasUserPermissionTo($permission)) {
            throw new ilException('Permission denied');
        }
    }

    /**
     * @param string $permission
     * @return bool
     */
    public function hasUserPermissionTo(string $permission): bool
    {
        if ($this->ref_id === null) {
            return false;
        }
        return $this->rbacsystem->checkAccess($permission, $this->ref_id);
    }

    /**
     * @return array
     */
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

    /**
     * @param ilMMCustomItemStorage $item
     * @return Closure
     */
    public function isCurrentUserAllowedToSeeCustomItem(ilMMCustomItemStorage $item): Closure
    {
        return function () use ($item): bool {
            $roles_of_current_user = $this->rbacreview->assignedGlobalRoles($this->user->getId());
            if (!$item->hasRoleBasedVisibility()) {
                return true;
            }
            if (!empty($item->getGlobalRoleIDs())) {
                foreach ($roles_of_current_user as $role_of_current_user) {
                    if (in_array($role_of_current_user, $item->getGlobalRoleIDs())) {
                        return true;
                    }
                }
            }
            return false;
        };
    }
}
