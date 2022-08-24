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

namespace ILIAS\Portfolio\Administration;

/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class PortfolioRoleAssignmentManager
{
    protected \ilRbacReview $rbacreview;
    protected PortfolioRoleAssignmentDBRepository $repo;

    public function __construct()
    {
        global $DIC;

        $this->rbacreview = $DIC->rbac()->review();
        $this->repo = new PortfolioRoleAssignmentDBRepository();
    }

    public function add(
        int $template_ref_id,
        int $role_id
    ): void {
        $this->repo->add(
            $template_ref_id,
            $role_id
        );
    }

    public function delete(
        int $template_ref_id,
        int $role_id
    ): void {
        $this->repo->delete(
            $template_ref_id,
            $role_id
        );
    }

    public function getTemplatesForRoles(
        array $role_ids
    ): array {
        return $this->repo->getTemplatesForRoles($role_ids);
    }

    public function getAllAssignmentData(): array
    {
        return $this->repo->getAllAssignmentData();
    }

    /**
     * @return array<int,string>
     */
    public function getAvailableRoles(): array
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

    public function assignPortfoliosOnLogin(int $user_id): void
    {
        $rbacreview = $this->rbacreview;
        // get roles of user
        $role_ids = $rbacreview->assignedRoles($user_id);
        // get portfolio templates
        $template_ref_ids = $this->getTemplatesForRoles($role_ids);
        // create portfolios
        foreach ($template_ref_ids as $template_ref_id) {
            if (\ilObject::_lookupType($template_ref_id, true) === "prtt") {
                $source = new \ilObjPortfolioTemplate($template_ref_id, true);
                // create portfolio
                $target = new \ilObjPortfolio();
                $target->setTitle($source->getTitle());
                $target->setOwner($user_id);
                $target->create();
                $target_id = $target->getId();
                \ilObjPortfolioTemplate::clonePagesAndSettings($source, $target, null, true);
            }
        }
    }
}
