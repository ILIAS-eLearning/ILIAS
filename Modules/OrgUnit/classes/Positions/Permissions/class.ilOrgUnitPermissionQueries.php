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

    /**
     * @throws ilException
     */
    public static function getTemplateSetForContextName(string $context_name, string $position_id, bool $editable = false): ilOrgUnitPermission
    {
        $context = ilOrgUnitOperationContextQueries::findByName($context_name);
        if (!$context) {
            throw new ilException('No context found');
        }
        if (!$position_id) {
            throw new ilException('$position_id cannot be null');
        }

        $template_set = ilOrgUnitPermission::where([
            'parent_id' => ilOrgUnitPermission::PARENT_TEMPLATE,
            'context_id' => $context->getId(),
            'position_id' => $position_id,
        ])->first();

        if (!$template_set) {
            $template_set = new ilOrgUnitPermission();
            $template_set->setParentId(ilOrgUnitPermission::PARENT_TEMPLATE);
            $template_set->setContextId($context->getId());
            $template_set->setPositionId($position_id);
            $template_set->setNewlyCreated(true);
            $template_set->create();
            $template_set->afterObjectLoad();
        }

        $template_set->setProtected(!$editable);

        return $template_set;
    }

    public static function hasLocalSet(int $ref_id, int $position_id): bool
    {
        return (ilOrgUnitPermission::where([
            'parent_id' => $ref_id,
            'position_id' => $position_id,
        ])->hasSets());
    }

    /**
     * @throws ilPositionPermissionsNotActive
     * @throws ilException
     */
    public static function getSetForRefId(int $ref_id, int $position_id): ilOrgUnitPermission
    {
        // TODO write performant query
        self::assertRefIdAndPositionIdIsNotNull($ref_id, $position_id);

        $context = self::getContextByRefId($ref_id);

        $ilOrgUnitGlobalSettings = ilOrgUnitGlobalSettings::getInstance();
        $ilOrgUnitObjectPositionSetting = $ilOrgUnitGlobalSettings->getObjectPositionSettingsByType($context->getContext());

        if (!$ilOrgUnitObjectPositionSetting->isActive()) {
            throw new ilPositionPermissionsNotActive("Postion-related permissions not active in {$context->getContext()}",
                $context->getContext());
        }

        /**
         * @var $dedicated_set ilOrgUnitPermission
         */
        $dedicated_set = ilOrgUnitPermission::where([
            'parent_id' => $ref_id,
            'context_id' => $context->getId(),
            'position_id' => $position_id,
        ])->first();
        if ($dedicated_set) {
            return $dedicated_set;
        }

        return self::getTemplateSetForContextName($context->getContext(), $position_id);
    }

    /**
     * @throws ilPositionPermissionsNotActive
     * @throws ilException
     */
    public static function findOrCreateSetForRefId(int $ref_id, int $position_id): ilOrgUnitPermission
    {
        /**
         * @var $dedicated_set ilOrgUnitPermission
         */
        self::assertRefIdAndPositionIdIsNotNull($ref_id, $position_id);

        $context = self::getContextByRefId($ref_id);

        $ilOrgUnitGlobalSettings = ilOrgUnitGlobalSettings::getInstance();
        $ilOrgUnitObjectPositionSetting = $ilOrgUnitGlobalSettings->getObjectPositionSettingsByType($context->getContext());

        if (!$ilOrgUnitObjectPositionSetting->isActive()) {
            throw new ilPositionPermissionsNotActive("Position-related permissions not active in {$context->getContext()}",
                $context->getContext());
        }

        $dedicated_set = ilOrgUnitPermission::where([
            'parent_id' => $ref_id,
            'context_id' => $context->getId(),
            'position_id' => $position_id,
        ])->first();
        if ($dedicated_set) {
            return $dedicated_set;
        }

        $template = self::getTemplateSetForContextName($context->getContext(), $position_id);

        $set = new ilOrgUnitPermission();
        $set->setProtected(false);
        $set->setParentId($ref_id);
        $set->setPositionId($position_id);
        $set->setContextId($context->getId());
        $set->setOperations($template->getOperations());
        $set->setNewlyCreated(true);
        $set->create();

        return $set;
    }

    /**
     * @throws ilPositionPermissionsNotActive
     * @throws ilException
     */
    public static function removeLocalSetForRefId(int $ref_id, int $position_id): bool
    {
        /**
         * @var $dedicated_set ilOrgUnitPermission
         */
        self::assertRefIdAndPositionIdIsNotNull($ref_id, $position_id);

        $context = self::getContextByRefId($ref_id);

        $ilOrgUnitGlobalSettings = ilOrgUnitGlobalSettings::getInstance();
        $ilOrgUnitObjectPositionSetting = $ilOrgUnitGlobalSettings->getObjectPositionSettingsByType($context->getContext());

        if (!$ilOrgUnitObjectPositionSetting->isActive()) {
            throw new ilPositionPermissionsNotActive("Position-related permissions not active in {$context->getContext()}",
                $context->getContext());
        }

        $dedicated_set = ilOrgUnitPermission::where([
            'parent_id' => $ref_id,
            'context_id' => $context->getId(),
            'position_id' => $position_id,
            'protected' => false,
        ])->first();
        if ($dedicated_set) {
            $dedicated_set->delete();

            return true;
        }

        return false;
    }

    /**
     * @return ilOrgUnitPermission[]
     * @throws ilException
     */
    public static function getAllTemplateSetsForAllActivedContexts(int $position_id, bool $editable = false): array
    {
        $activated_components = [];
        foreach (ilOrgUnitGlobalSettings::getInstance()->getPositionSettings() as $ilOrgUnitObjectPositionSetting) {
            if ($ilOrgUnitObjectPositionSetting->isActive()) {
                $activated_components[] = $ilOrgUnitObjectPositionSetting->getType();
            }
        }
        $sets = [];
        foreach ($activated_components as $context) {
            $sets[] = self::getTemplateSetForContextName($context, $position_id, $editable);
        }

        return $sets;
    }

    /**
     * @throws ilException
     */
    private static function getContextByRefId(int $ref_id): ilOrgUnitOperationContext
    {
        $context = ilOrgUnitOperationContextQueries::findByRefId($ref_id);
        if (!$context) {
            throw new ilException('Context not found');
        }

        return $context;
    }

    /**
     * @throws ilException
     */
    private static function assertRefIdAndPositionIdIsNotNull(int $ref_id, int $position_id): void
    {
        if (!$ref_id) {
            throw new ilException('$ref_id cannot be null');
        }
        if (!$position_id) {
            throw new ilException('$position_id cannot be null');
        }
    }
}
