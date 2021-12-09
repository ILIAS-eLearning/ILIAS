<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Portfolio\Administration;

/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class PortfolioRoleAssignmentManager
{
    /**
     * @var \ilRbacReview
     */
    protected $rbacreview;

    /**
     * @var PortfolioRoleAssignmentDBRepository
     */
    protected $repo;

    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->rbacreview = $DIC->rbac()->review();
        $this->repo = new PortfolioRoleAssignmentDBRepository();
    }

    public function add(
        int $template_ref_id,
        int $role_id
    ) : void {
        $this->repo->add(
            $template_ref_id,
            $role_id
        );
    }

    public function delete(
        int $template_ref_id,
        int $role_id
    ) : void {
        $this->repo->delete(
            $template_ref_id,
            $role_id
        );
    }

    public function getTemplatesForRoles(
        array $role_ids
    ) : array {
        return $this->repo->getTemplatesForRoles($role_ids);
    }

    public function getAllAssignmentData(
    ) : array {
        return $this->repo->getAllAssignmentData();
    }

    public function getAvailableRoles()
    {
        $rbacreview = $this->rbacreview;
        $global_roles = $rbacreview->getGlobalRoles();
        $roles = array();
        foreach ($global_roles as $roleid) {
            $role_obj = new \ilObjRole($roleid);
            $roles[$role_obj->getId()] = $role_obj->getTitle();
        }
        return $roles;
    }

    public function assignPortfoliosOnLogin(int $user_id) : void
    {
        $rbacreview = $this->rbacreview;
        // get roles of user
        $role_ids = $rbacreview->assignedRoles($user_id);
        // get portfolio templates
        $template_ref_ids = $this->getTemplatesForRoles($role_ids);
        // create portfolios
        foreach ($template_ref_ids as $template_ref_id) {
            if (\ilObject::_lookupType($template_ref_id, true) == "prtt") {
                $source = new \ilObjPortfolioTemplate($template_ref_id, true);
                // create portfolio
                $target = new \ilObjPortfolio();
                $target->setTitle($source->getTitle());
                $target->setOwner($user_id);
                $target->create();
                $target_id = $target->getId();
                $source->clonePagesAndSettings($source, $target, null, true);
            }
        }
    }
}
