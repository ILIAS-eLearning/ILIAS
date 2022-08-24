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
 * Both role and OrgU-based permissions are relevant in many places of the PRG.
 * This is to bundle permission-checks.
 *
 * Please note that the 'manage_members'-permission granted via global or local roles
 * will include all the ORGU_OPERATIONS listed here and is thus very different
 * from the OrgUnitOperation 'manage_members'.
 */
class ilPRGPermissionsHelper
{
    public const ORGU_OPERATIONS = [
        ilOrgUnitOperation::OP_VIEW_MEMBERS,
        ilOrgUnitOperation::OP_READ_LEARNING_PROGRESS,
        ilOrgUnitOperation::OP_VIEW_INDIVIDUAL_PLAN,
        ilOrgUnitOperation::OP_EDIT_INDIVIDUAL_PLAN,
        ilOrgUnitOperation::OP_MANAGE_MEMBERS
    ];

    public const ROLEPERM_VIEW = 'rp_visible';
    public const ROLEPERM_READ = 'rp_read';
    public const ROLEPERM_WRITE = 'rp_write';
    //both org-unit and rbac permission read "manage_members";
    //however, rbac-manage_members does include all of the orgu-permissions listed here.
    public const ROLEPERM_MANAGE_MEMBERS = 'rp_manage_members';

    private const ROLEMAPPINGS = [
        'rp_visible' => 'visible',
        'rp_read' => 'read',
        'rp_write' => 'write',
        'rp_manage_members' => 'manage_members'
    ];

    protected ilAccess $access;
    protected ilOrgUnitPositionAccess $orgu_access;
    protected ilObjStudyProgramme $programme;
    protected array $cache = [];

    /**
      * @var array <mixed, array>
      */
    protected array $user_id_cache;

    public function __construct(
        ilAccess $access,
        ilOrgUnitPositionAccess $orgu_access,
        ilObjStudyProgramme $programme
    ) {
        $this->access = $access;
        $this->orgu_access = $orgu_access;
        $this->programme = $programme;
    }

    public function may(string $operation): bool
    {
        $this->throwForInvalidOperation($operation);
        if (in_array($operation, self::ORGU_OPERATIONS)) {
            return
                $this->access->checkAccess( //RBAC overrides OrgUs
                    self::ROLEMAPPINGS[self::ROLEPERM_MANAGE_MEMBERS],
                    '',
                    $this->getProgrammeRefId()
                )
                || $this->access->checkPositionAccess($operation, $this->getProgrammeRefId());
        }

        return $this->access->checkAccess(self::ROLEMAPPINGS[$operation], '', $this->getProgrammeRefId());
    }

    /**
     * @param string[] $operations
     */
    public function mayAnyOf(array $operations): bool
    {
        foreach ($operations as $operation) {
            if ($this->may($operation)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return int[]
     */
    public function getUserIdsSusceptibleTo(string $operation): array
    {
        $this->throwForInvalidOperation($operation);

        if ($this->may(self::ROLEPERM_MANAGE_MEMBERS)) { //RBAC overrides OrgUs
            return $this->getAllAssignedUserIds();
        }

        if (in_array($operation, self::ORGU_OPERATIONS) && $this->may($operation)) {
            return $this->getUserIdsInPrgAccessibleForOperation($operation);
        }
        return [];
    }

    /**
     * @param int[] $user_ids
     */
    public function filterUserIds(array $user_ids, string $operation): array
    {
        if ($this->may(self::ROLEPERM_MANAGE_MEMBERS)) { //RBAC overrides OrgUs
            return $user_ids;
        }

        return $this->orgu_access->filterUserIdsByPositionOfCurrentUser(
            $operation,
            $this->getProgrammeRefId(),
            $user_ids
        );
    }

    protected function throwForInvalidOperation(string $operation): void
    {
        $valid = array_merge(
            self::ORGU_OPERATIONS,
            [
                self::ROLEPERM_VIEW,
                self::ROLEPERM_READ,
                self::ROLEPERM_WRITE,
                self::ROLEPERM_MANAGE_MEMBERS
            ]
        );

        if (!in_array($operation, $valid)) {
            throw new ilException('prg does not provide this permission: ' . $operation);
        }
    }

    protected function getUserIdsInPrgAccessibleForOperation(string $orgu_operation): array
    {
        if (!isset($this->cache[$orgu_operation])) {
            $user_ids = array_map(
                'intval',
                $this->orgu_access->filterUserIdsByPositionOfCurrentUser(
                    $orgu_operation,
                    $this->getProgrammeRefId(),
                    $this->getAllAssignedUserIds()
                )
            );
            $this->cache[$orgu_operation] = array_unique($user_ids);
        }
        return $this->cache[$orgu_operation];
    }

    /**
     * @return int[]
     */
    protected function getAllAssignedUserIds(): array
    {
        if (!isset($this->cache[self::ROLEPERM_MANAGE_MEMBERS])) {
            $this->cache[self::ROLEPERM_MANAGE_MEMBERS] = array_unique($this->programme->getMembers());
        }
        return $this->cache[self::ROLEPERM_MANAGE_MEMBERS];
    }

    protected function getProgrammeRefId(): int
    {
        return $this->programme->getRefId();
    }
}
