<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('./Services/WebAccessChecker/interfaces/interface.ilWACCheckingClass.php');

/**
 * Class ilBadgeHandler
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @package ServicesBadge
 */
class ilBadgeWAC implements ilWACCheckingClass
{
    public function canBeDelivered(ilWACPath $ilWACPath)
    {
        global $DIC;

        if (strpos($ilWACPath->getPath(), '..') !== false) {
            return false;
        }

        if (!preg_match('@ilBadge\/badge(tmpl)?_(\d+)\/@ui', $ilWACPath->getPath())) {
            return false;
        }

        $obj_id = array_keys(ilObject::_getObjectsByType('bdga'))[0] ?? null;
        $admin_ref_id = null;
        if ($obj_id > 0) {
            $admin_ref_id = array_values(ilObject::_getAllReferences($obj_id))[0] ?? null;
        }

        $has_global_badge_administration_access = (
            $admin_ref_id > 0 &&
            $DIC->rbac()->system()->checkAccessOfUser($DIC->user()->getId(), 'read', $admin_ref_id)
        );

        if (preg_match('@\/badgetmpl_(\d+)\/@ui', $ilWACPath->getPath())) {
            // Badge template images must only be accessible for accounts with `read` permission on the badge administration node
            return $has_global_badge_administration_access;
        }

        if (preg_match('@\/badge_(\d+)\/@ui', $ilWACPath->getPath(), $matches)) {
            if ($has_global_badge_administration_access) {
                return true;
            }

            $badge_id = (int) $matches[1];

            return (
                $this->isAssignedBadge($DIC, $badge_id) ||
                $this->isAssignedBadgeOfPublishedUserProfile($DIC, $badge_id) ||
                $this->hasAccessToBadgeParentIdNode($DIC, $badge_id, $has_global_badge_administration_access)
            );
        }

        return false;
    }

    private function hasAccessToBadgeParentIdNode(
        \ILIAS\DI\Container $DIC,
        int $badge_id,
        bool $has_global_badge_administration_access
    ) : bool {
        // If the acting user still does not have access, check if the image is used in an object badge type
        $badge = new ilBadge($badge_id);
        if ($badge->getParentId() > 0) {
            return false;
        }

        $badge_handler = ilBadgeHandler::getInstance();
        if (!$badge_handler->isObjectActive((int) $badge->getParentId())) {
            return false;
        }

        $context_ref_id = array_values(ilObject::_getAllReferences((int) $badge->getParentId()))[0] ?? null;
        if (!($context_ref_id > 0)) {
            return false;
        }

        $context_ref_id = (int) $context_ref_id;
        if ($DIC->repositoryTree()->isGrandChild((int) SYSTEM_FOLDER_ID, $context_ref_id)) {
            $has_access = $has_global_badge_administration_access;
        } else {
            $has_access = $DIC->access()->checkAccessOfUser(
                $DIC->user()->getId(),
                'write',
                '',
                $context_ref_id
            );
        }

        return $has_access;
    }

    private function isAssignedBadge(\ILIAS\DI\Container $DIC, int $badge_id) : bool
    {
        // First, check all badge assignments of the current user for a match
        $badges_of_user = ilBadgeAssignment::getInstancesByUserId($DIC->user()->getId());
        foreach ($badges_of_user as $user_badge) {
            if ((int) $user_badge->getBadgeId() === $badge_id) {
                return true;
            }
        }

        return false;
    }

    private function isAssignedBadgeOfPublishedUserProfile(\ILIAS\DI\Container $DIC, int $badge_id) : bool
    {
        // It seems the badge is not  assigned to the curent user, so check if the profile of the badge user is made visible
        $assignments = ilBadgeAssignment::getInstancesByBadgeId($badge_id);
        foreach ($assignments as $assignment) {
            if (!$assignment->getPosition()) {
                continue;
            }

            $user = ilObjectFactory::getInstanceByObjId((int) $assignment->getUserId(), false);
            if (!$user instanceof ilObjUser) {
                continue;
            }

            $profile_visibility = $user->getPref('public_profile');
            if ($profile_visibility === 'g' || ($profile_visibility === 'y' && !$DIC->user()->isAnonymous())) {
                return true;
            }
        }

        return false;
    }
}
