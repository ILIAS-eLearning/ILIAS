<?php

/**
 * Class ilOrgUnitPermissionQueries
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPermissionQueries
{

    /**
     * @param      $context_name
     *
     * @param      $position_id
     *
     * @param bool $editable
     *
     * @return \ilOrgUnitPermission
     * @throws \ilException
     */
    public static function getTemplateSetForContextName($context_name, $position_id, $editable = false)
    {
        // TODO write performant query
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


    /**
     * @param $ref_id
     * @param $position_id
     *
     * @return bool
     */
    public static function hasLocalSet($ref_id, $position_id)
    {
        return (ilOrgUnitPermission::where([
            'parent_id' => $ref_id,
            'position_id' => $position_id,
        ])->hasSets());
    }


    /**
     * @param $ref_id
     *
     * @param $position_id
     *
     * @return \ilOrgUnitPermission
     *
     * @throws \ilException
     */
    public static function getSetForRefId($ref_id, $position_id)
    {
        // TODO write performant query
        self::checkRefIdAndPositionId($ref_id, $position_id);

        $context = self::getContextByRefId($ref_id);

        $ilOrgUnitGlobalSettings = ilOrgUnitGlobalSettings::getInstance();
        $ilOrgUnitObjectPositionSetting = $ilOrgUnitGlobalSettings->getObjectPositionSettingsByType($context->getContext());

        if (!$ilOrgUnitObjectPositionSetting->isActive()) {
            throw new ilPositionPermissionsNotActive("Postion-related permissions not active in {$context->getContext()}", $context->getContext());
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

        return ilOrgUnitPermissionQueries::getTemplateSetForContextName($context->getContext(), $position_id);
    }


    /**
     * @param $ref_id
     * @param $position_id
     *
     * @return \ilOrgUnitPermission
     * @throws \ilException
     */
    public static function findOrCreateSetForRefId($ref_id, $position_id)
    {
        /**
         * @var $dedicated_set ilOrgUnitPermission
         */
        self::checkRefIdAndPositionId($ref_id, $position_id);

        $context = self::getContextByRefId($ref_id);

        $ilOrgUnitGlobalSettings = ilOrgUnitGlobalSettings::getInstance();
        $ilOrgUnitObjectPositionSetting = $ilOrgUnitGlobalSettings->getObjectPositionSettingsByType($context->getContext());

        if (!$ilOrgUnitObjectPositionSetting->isActive()) {
            throw new ilPositionPermissionsNotActive("Position-related permissions not active in {$context->getContext()}", $context->getContext());
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
     * @param $ref_id
     * @param $position_id
     *
     * @return bool
     * @throws \ilException
     */
    public static function removeLocalSetForRefId($ref_id, $position_id)
    {
        /**
         * @var $dedicated_set ilOrgUnitPermission
         */
        self::checkRefIdAndPositionId($ref_id, $position_id);

        $context = self::getContextByRefId($ref_id);

        $ilOrgUnitGlobalSettings = ilOrgUnitGlobalSettings::getInstance();
        $ilOrgUnitObjectPositionSetting = $ilOrgUnitGlobalSettings->getObjectPositionSettingsByType($context->getContext());

        if (!$ilOrgUnitObjectPositionSetting->isActive()) {
            throw new ilPositionPermissionsNotActive("Position-related permissions not active in {$context->getContext()}", $context->getContext());
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
     * @param      $position_id
     *
     * @param bool $editable
     *
     * @return \ilOrgUnitPermission[]
     */
    public static function getAllTemplateSetsForAllActivedContexts($position_id, $editable = false)
    {
        $activated_components = [];
        foreach (ilOrgUnitGlobalSettings::getInstance()->getPositionSettings() as $ilOrgUnitObjectPositionSetting) {
            if ($ilOrgUnitObjectPositionSetting->isActive()) {
                $activated_components[] = $ilOrgUnitObjectPositionSetting->getType();
            }
        }
        $sets = [];
        foreach ($activated_components as $context) {
            $sets[] = ilOrgUnitPermissionQueries::getTemplateSetForContextName($context, $position_id, $editable);
        }

        return $sets;
    }


    /**
     * @param $user_id
     * @param $ref_id
     * @param $operation_string
     */
    public static function getRelevantPermissionSetsForUserIdAndRefIdAndOperation($user_id, $ref_id, $operation_string)
    {
        $q = 'SELECT @OP_ID:= CONCAT("%\"",operation_id, "\"%") FROM il_orgu_op_contexts
JOIN il_orgu_operations ON il_orgu_operations.context_id = il_orgu_op_contexts.id
WHERE il_orgu_op_contexts.context IN(\'crs\', \'object\') AND operation_string = \'viewmembers\';';
    }


    private static function getAllowedOperationsOnRefIdAndPosition($ref_id, $position_id)
    {
        global $DIC;
        $db = $DIC->database();

        $q = 'SELECT @CONTEXT_TYPE:= object_data.type
		 FROM object_reference
		 JOIN object_data ON object_data.obj_id = object_reference.obj_id
		 WHERE object_reference.ref_id = %s;';
        $db->queryF($q, [ 'integer' ], [ $ref_id ]);

        $q = 'SELECT @OP_ID:= CONCAT("%\"", il_orgu_operations.operation_id, "%\"")
					FROM il_orgu_operations 
					JOIN il_orgu_op_contexts ON il_orgu_op_contexts.context = @CONTEXT_TYPE -- AND il_orgu_op_contexts.id = il_orgu_operations.context_id
				WHERE il_orgu_operations.operation_string = %s';
        $db->queryF($q, [ 'text' ], [ $pos_perm ]);
        $q = 'SELECT * FROM il_orgu_permissions WHERE operations LIKE @OP_ID AND position_id = %s;';
        $r = $db->queryF($q, [ 'integer' ], [ $position_id ]);

        ($r->numRows() > 0);
    }


    /**
     * @param $ref_id
     *
     * @return \ilOrgUnitOperationContext
     * @throws \ilException
     */
    protected static function getContextByRefId($ref_id)
    {
        $context = ilOrgUnitOperationContextQueries::findByRefId($ref_id);
        if (!$context) {
            throw new ilException('Context not found');
        }

        return $context;
    }


    /**
     * @param $ref_id
     * @param $position_id
     *
     * @throws \ilException
     */
    protected static function checkRefIdAndPositionId($ref_id, $position_id)
    {
        if (!$ref_id) {
            throw new ilException('$ref_id cannot be null');
        }
        if (!$position_id) {
            throw new ilException('$position_id cannot be null');
        }
    }
}
