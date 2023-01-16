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
     * @deprecated Please use getTemplateByContext() from ilOrgUnitPermissionDBRepository
     */
    public static function getTemplateSetForContextName(string $context_name, string $position_id, bool $editable = false): ilOrgUnitPermission
    {
        return self::getPermissionRepo()->getTemplateByContext($context_name, (int) $position_id, $editable);
    }

    /**
     * @deprecated Please use hasLocalPermission() from ilOrgUnitPermissionDBRepository
     */
    public static function hasLocalSet(int $ref_id, int $position_id): bool
    {
        return self::getPermissionRepo()->hasLocalPermission($ref_id, $position_id);
    }

    /**
     * @deprecated Please use getPermissionByRefId() from ilOrgUnitPermissionDBRepository
     */
    public static function getSetForRefId(int $ref_id, int $position_id): ilOrgUnitPermission
    {
        return self::getPermissionRepo()->getPermissionByRefId($ref_id, $position_id);
    }

    /**
     * @deprecated Please use createPermissionByRefId() from ilOrgUnitPermissionDBRepository
     */
    public static function findOrCreateSetForRefId(int $ref_id, int $position_id): ilOrgUnitPermission
    {
        return self::getPermissionRepo()->createPermissionByRefId($ref_id, $position_id);
    }

    /**
     * @deprecated Please use deletePermissionByRefId() from ilOrgUnitPermissionDBRepository
     */
    public static function removeLocalSetForRefId(int $ref_id, int $position_id): bool
    {
        return self::getPermissionRepo()->deletePermissionByRefId($ref_id, $position_id);
    }

    /**
     * @deprecated Please use getTemplatesForActiveContexts() from ilOrgUnitPermissionDBRepository
     */
    public static function getAllTemplateSetsForAllActivedContexts(int $position_id, bool $editable = false): array
    {
        return self::getPermissionRepo()->getTemplatesForActiveContexts($position_id, $editable);
    }
}
