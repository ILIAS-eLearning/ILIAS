<?php

/**
 * Class ilOrgUnitOperationContextQueries
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitOperationContextQueries
{

    /**
     * @throws ilException
     */
    public static function registerNewContext(string $context_name, ?string $parent_context = null): void
    {
        if (ilOrgUnitOperationContext::where(array('context' => $context_name))->hasSets()) {
            throw new ilException('Context already registered');
        }

        $parentList = ilOrgUnitOperationContext::where(array('context' => $parent_context));
        $parent_id = 0;
        if ($parent_context !== null && $parentList->hasSets()) {
            $parent = $parentList->first();
            if ($parent === null) {
                throw new ilException('No record found');
            }
            $parent_id = $parent->getId();
        }

        $context = new ilOrgUnitOperationContext();
        $context->setContext($context_name);
        $context->setParentContextId($parent_id);
        $context->create();
    }

    protected static array $instance_by_name = array();

    public static function findByName(string $context_name) : ilOrgUnitOperationContext
    {
        if (!isset(self::$instance_by_name[$context_name])) {
            self::$instance_by_name[$context_name] = ilOrgUnitOperationContext::where(array('context' => $context_name))
                                                                              ->first();
        }

        return self::$instance_by_name[$context_name];
    }

    public static function findById(int $id) : ilOrgUnitOperationContext|ActiveRecord
    {
        return ilOrgUnitOperationContext::find($id);
    }

    public static function findByRefId(int $ref_id) : ilOrgUnitOperationContext
    {
        $type_context = ilObject2::_lookupType($ref_id, true);

        return self::findByName($type_context);
    }

    public static function findByObjId(int $obj_id) : ilOrgUnitOperationContext
    {
        $type_context = ilObject2::_lookupType($obj_id, false);

        return self::findByName($type_context);
    }
}
