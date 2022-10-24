<?php

declare(strict_types=1);

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
 * Deal with ilias rbac-system
 */
class ilIndividualAssessmentAccessHandler implements IndividualAssessmentAccessHandler
{
    public const DEFAULT_ROLE = 'il_iass_member';

    protected ilObjIndividualAssessment $iass;
    protected ilAccessHandler $handler;
    protected ilRbacAdmin $admin;
    protected ilRbacReview $review;
    protected ilObjUser $usr;
    protected array $mass_global_permissions_cache = [];

    public function __construct(
        ilObjIndividualAssessment $iass,
        ilAccessHandler $handler,
        ilRbacAdmin $admin,
        ilRbacReview $review,
        ilObjUser $usr
    ) {
        $this->iass = $iass;
        $this->handler = $handler;
        $this->admin = $admin;
        $this->review = $review;
        $this->usr = $usr;
    }

    /**
     * @inheritdoc
     */
    public function checkAccessToObj(string $operation): bool
    {
        if ($operation == "read_learning_progress") {
            return $this->handler->checkRbacOrPositionPermissionAccess(
                "read_learning_progress",
                "read_learning_progress",
                $this->iass->getRefId()
            );
        }

        if ($operation == "edit_learning_progress") {
            return $this->handler->checkRbacOrPositionPermissionAccess(
                "edit_learning_progress",
                "write_learning_progress",
                $this->iass->getRefId()
            );
        }

        return $this->handler->checkAccessOfUser($this->usr->getId(), $operation, '', $this->iass->getRefId(), 'iass');
    }

    /**
     * @inheritdoc
     */
    public function initDefaultRolesForObject(ilObjIndividualAssessment $iass): void
    {
        ilObjRole::createDefaultRole(
            $this->getRoleTitleByObj($iass),
            "Admin of iass obj_no." . $iass->getId(),
            self::DEFAULT_ROLE,
            $iass->getRefId()
        );
    }

    /**
     * @inheritdoc
     */
    public function assignUserToMemberRole(ilObjUser $usr, ilObjIndividualAssessment $iass): bool
    {
        $this->admin->assignUser($this->getMemberRoleIdForObj($iass), $usr->getId());
        return true;
    }

    /**
     * @inheritdoc
     */
    public function deassignUserFromMemberRole(ilObjUser $usr, ilObjIndividualAssessment $iass): bool
    {
        $this->admin->deassignUser($this->getMemberRoleIdForObj($iass), $usr->getId());
        return true;
    }

    protected function getRoleTitleByObj(ilObjIndividualAssessment $iass): string
    {
        return self::DEFAULT_ROLE . '_' . $iass->getRefId();
    }

    /**
     * @return false|mixed
     */
    protected function getMemberRoleIdForObj(ilObjIndividualAssessment $iass)
    {
        return current($this->review->getLocalRoles($iass->getRefId()));
    }

    public function mayViewObject(bool $use_cache = true): bool
    {
        if ($use_cache) {
            return $this->cacheCheckAccessToObj('read');
        }

        return $this->isSystemAdmin() || $this->checkAccessToObj('read');
    }

    public function mayEditObject(bool $use_cache = true): bool
    {
        if ($use_cache) {
            return $this->cacheCheckAccessToObj('write');
        }

        return $this->isSystemAdmin() || $this->checkAccessToObj('write');
    }

    public function mayEditPermissions(bool $use_cache = true): bool
    {
        if ($use_cache) {
            return $this->cacheCheckAccessToObj('edit_permission');
        }

        return $this->isSystemAdmin() || $this->checkAccessToObj('edit_permission');
    }

    public function mayEditMembers(bool $use_cache = true): bool
    {
        if ($use_cache) {
            return $this->cacheCheckAccessToObj('edit_members');
        }

        return $this->isSystemAdmin() || $this->checkAccessToObj('edit_members');
    }

    public function mayViewUser(bool $use_cache = true): bool
    {
        if ($use_cache) {
            return $this->cacheCheckAccessToObj('read_learning_progress');
        }

        return $this->isSystemAdmin() || $this->checkAccessToObj('read_learning_progress');
    }

    public function mayGradeUser(bool $use_cache = true): bool
    {
        if ($use_cache) {
            return $this->cacheCheckAccessToObj('edit_learning_progress');
        }

        return $this->isSystemAdmin() || $this->checkAccessToObj('edit_learning_progress');
    }

    public function mayGradeUserById(int $user_id): bool
    {
        return
            $this->isSystemAdmin() ||
            $this->mayGradeUser() ||
            (count(
                $this->handler->filterUserIdsByRbacOrPositionOfCurrentUser(
                    "edit_learning_progress",
                    "set_lp",
                    $this->iass->getRefId(),
                    [$user_id]
                )
            ) > 0);
    }

    public function mayAmendGradeUser(bool $use_cache = true): bool
    {
        if ($use_cache) {
            return $this->cacheCheckAccessToObj('amend_grading');
        }

        return $this->checkAccessToObj('amend_grading');
    }

    protected function cacheCheckAccessToObj(string $operation): bool
    {
        $iass_id = $this->iass->getId();
        $user_id = $this->usr->getId();

        if (!isset($this->mass_global_permissions_cache[$iass_id][$user_id][$operation])) {
            $this->mass_global_permissions_cache[$iass_id][$user_id][$operation]
                = $this->checkAccessToObj($operation);
        }

        return $this->mass_global_permissions_cache[$iass_id][$user_id][$operation];
    }

    public function isSystemAdmin(): bool
    {
        return $this->review->isAssigned($this->usr->getId(), SYSTEM_ROLE_ID);
    }
}
