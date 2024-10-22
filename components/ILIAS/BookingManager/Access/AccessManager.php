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

namespace ILIAS\BookingManager\Access;

use ILIAS\BookingManager\InternalDomainService;

class AccessManager
{
    protected \ilTree $tree;
    protected int $user_id;
    protected int $ref_id;
    protected \ilAccessHandler $access;
    protected InternalDomainService $domain;

    public function __construct(
        InternalDomainService $domain,
        \ilAccessHandler $access
    ) {
        $this->domain = $domain;
        $this->access = $access;
        $this->tree = $this->domain->repositoryTree();
    }

    protected function getCurrentUserId(int $user_id): int
    {
        if ($user_id > 0) {
            return $user_id;
        }
        return $this->domain->user()->getId();
    }

    public function canManageObjects(
        int $ref_id,
        int $current_user = 0
    ): bool {
        $current_user = $this->getCurrentUserId($current_user);
        return $this->access->checkAccessOfUser($current_user, "write", "", $ref_id);
    }

    public function canManageSettings(
        int $ref_id,
        int $current_user = 0
    ): bool {
        $current_user = $this->getCurrentUserId($current_user);
        return $this->access->checkAccessOfUser($current_user, "write", "", $ref_id);
    }

    public function canManageParticipants(
        int $ref_id,
        int $current_user = 0
    ): bool {
        $current_user = $this->getCurrentUserId($current_user);
        return $this->access->checkAccessOfUser($current_user, "write", "", $ref_id);
    }

    public function filterManageableParticipants(
        int $ref_id,
        array $participant_ids
    ): array {
        return $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
            'render',
            'render',
            $ref_id,
            $participant_ids
        );
    }

    public function canManageAllReservations(
        int $ref_id,
        int $current_user = 0
    ): bool {
        $current_user = $this->getCurrentUserId($current_user);
        return $this->access->checkAccessOfUser($current_user, "manage_all_reservations", "", $ref_id);
    }

    public function canManageOwnReservations(
        int $ref_id,
        int $current_user = 0
    ): bool {
        $current_user = $this->getCurrentUserId($current_user);
        return $this->access->checkAccessOfUser($current_user, "manage_own_reservations", "", $ref_id) ||
            $this->access->checkAccessOfUser($current_user, "manage_all_reservations", "", $ref_id);
    }

    public function canManageReservationForUser(
        int $ref_id,
        int $target_user,
        int $current_user = 0
    ): bool {
        $current_user = $this->getCurrentUserId($current_user);
        if ($target_user === $current_user) {
            return $this->access->checkAccessOfUser($current_user, "manage_own_reservations", "", $ref_id) ||
                $this->access->checkAccessOfUser($current_user, "manage_all_reservations", "", $ref_id);
        }
        return $this->access->checkAccessOfUser($current_user, "manage_all_reservations", "", $ref_id);
    }

    public function canRetrieveNotificationsForOwnReservationsByObjId(
        int $book_obj_id,
        int $current_user = 0
    ): bool {
        $current_user = $this->getCurrentUserId($current_user);
        return $this->hasPermissionOnAnyReference("manage_own_reservations", $current_user, $book_obj_id);
    }

    public function canRetrieveNotificationsForAllReservationsByObjId(
        int $book_obj_id,
        int $current_user = 0
    ): bool {
        $current_user = $this->getCurrentUserId($current_user);
        return $this->hasPermissionOnAnyReference("manage_all_reservations", $current_user, $book_obj_id);
    }

    protected function hasPermissionOnAnyReference(
        string $perm,
        int $uid,
        int $obj_id
    ): bool {
        $access = $this->access;
        foreach (\ilObject::_getAllReferences($obj_id) as $ref_id) {
            if ($access->checkAccessOfUser($uid, $perm, "", $ref_id)) {
                return true;
            }
        }
        return false;
    }

    public function getParentGroupCourse(int $ref_id): ?array
    {
        $tree = $this->tree;
        if (($par_ref_id = $tree->checkForParentType($ref_id, "grp")) > 0) {
            return [
                "ref_id" => $par_ref_id,
                "type" => "grp"
            ];
        }
        if (($par_ref_id = $tree->checkForParentType($ref_id, "crs")) > 0) {
            return [
                "ref_id" => $par_ref_id,
                "type" => "crs"
            ];
        }
        return null;
    }

    public function canManageMembersOfParent(int $ref_id): bool
    {
        if (($parent = $this->getParentGroupCourse($ref_id)) !== null) {
            return ($this->access->checkAccess("manage_members", "", (int) $parent["ref_id"])) ;
        }
        return false;
    }
}
