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
 ********************************************************************
 */

/**
 * Class ilOrgUnitPermissionQueries
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @deprecated Please use OrgUnitPermissionRepository
 */
class ilOrgUnitPermissionQueries
{
    protected static ilOrgUnitPermissionDBRepository $permissionRepo;

    protected static function getPermissionRepo()
    {
        if (!isset(self::$permissionRepo)) {
            $dic = ilOrgUnitLocalDIC::dic();
            self::$permissionRepo = $dic["repo.Permissions"];
        }

        return self::$permissionRepo;
    }

    /**
     * @deprecated Please use getDefaultForContext() from OrgUnitPermissionRepository
     */
    public static function getTemplateSetForContextName(string $context_name, string $position_id, bool $editable = false): ilOrgUnitPermission
    {
        return self::getPermissionRepo()->getDefaultForContext($context_name, (int) $position_id, $editable);
    }

    /**
     * @deprecated Please use find() from OrgUnitPermissionRepository
     */
    public static function hasLocalSet(int $ref_id, int $position_id): bool
    {
        if (self::getPermissionRepo()->find($ref_id, $position_id)) {
            return true;
        }

        return false;
    }

    /**
     * @deprecated Please use getLocalorDefault() from OrgUnitPermissionRepository
     */
    public static function getSetForRefId(int $ref_id, int $position_id): ilOrgUnitPermission
    {
        return self::getPermissionRepo()->getLocalorDefault($ref_id, $position_id);
    }

    /**
     * @deprecated Please use get() from OrgUnitPermissionRepository
     */
    public static function findOrCreateSetForRefId(int $ref_id, int $position_id): ilOrgUnitPermission
    {
        return self::getPermissionRepo()->get($ref_id, $position_id);
    }

    /**
     * @deprecated Please use delete() from OrgUnitPermissionRepository
     */
    public static function removeLocalSetForRefId(int $ref_id, int $position_id): bool
    {
        return self::getPermissionRepo()->delete($ref_id, $position_id);
    }

    /**
     * @deprecated Please use getDefaultsForActiveContexts() from OrgUnitPermissionRepository
     */
    public static function getAllTemplateSetsForAllActivedContexts(int $position_id, bool $editable = false): array
    {
        return self::getPermissionRepo()->getDefaultsForActiveContexts($position_id, $editable);
    }
}
