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
 * Class ilObjMainMenuAccess
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjMainMenuAccess extends ilObjectAccess
{

    /**
     * @var \ILIAS\DI\HTTPServices
     */
    private $http;
    /**
     * @var ilObjUser
     */
    private $user;
    /**
     * @var ilRbacSystem
     */
    private $rbacsystem;
    /**
     * @var ilRbacReview
     */
    private $rbacreview;

    /**
     * ilObjMainMenuAccess constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->rbacreview = $DIC->rbac()->review();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->user = $DIC->user();
        $this->http = $DIC->http();
    }

    /**
     * @param string $permission
     * @throws ilException
     */
    public function checkAccessAndThrowException(string $permission) : void
    {
        if (!$this->hasUserPermissionTo($permission)) {
            throw new ilException('Permission denied');
        }
    }

    /**
     * @param string $permission
     * @return bool
     */
    public function hasUserPermissionTo(string $permission) : bool
    {
        return (bool) $this->rbacsystem->checkAccess($permission, $this->http->request()->getQueryParams()['ref_id']);
    }


    /**
     * @return array
     */
    public function getGlobalRoles() : array
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

    public function isCurrentUserAllowedToSeeCustomItem(ilMMCustomItemStorage $item, Closure $current) : Closure
    {
        return function () use ($item, $current) : bool {
            $roles_of_current_user = $this->rbacreview->assignedGlobalRoles($this->user->getId());
            if (!$item->hasRoleBasedVisibility()) {
                return $current();
            }
            if ($item->hasRoleBasedVisibility() && !empty($item->getGlobalRoleIDs())) {
                foreach ($roles_of_current_user as $role_of_current_user) {
                    if (in_array((int) $role_of_current_user, $item->getGlobalRoleIDs(), true)) {
                        return $current();
                    }
                }
            }
            return false;
        };
    }
}
