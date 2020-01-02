<?php

/**
 * Class ilOrgUnitOperationContextQueries
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitOperationContextQueries
{

    /**
     * @param      $context_name
     *
     * @param null $parent_context
     *
     * @throws \ilException
     */
    public static function registerNewContext($context_name, $parent_context = null)
    {
        if (ilOrgUnitOperationContext::where(array( 'context' => $context_name ))->hasSets()) {
            throw new ilException('Context already registered');
        }

        $parentList = ilOrgUnitOperationContext::where(array( 'context' => $parent_context ));
        $parent_id = 0;
        if ($parent_context !== null && $parentList->hasSets()) {
            /**
             * @var $parent self
             */
            $parent = $parentList->first();
            $parent_id = $parent->getId();
        }

        $context = new ilOrgUnitOperationContext();
        $context->setContext($context_name);
        $context->setParentContextId($parent_id);
        $context->create();
    }


    /**
     * @var array
     */
    protected static $instance_by_name = array();


    /**
     * @param $context_name
     *
     * @return \ilOrgUnitOperationContext
     */
    public static function findByName($context_name)
    {
        if (!isset(self::$instance_by_name[$context_name])) {
            self::$instance_by_name[$context_name] = ilOrgUnitOperationContext::where(array( 'context' => $context_name ))
                                                                              ->first();
        }

        return self::$instance_by_name[$context_name];
    }


    /**
     * @param int $id
     *
     * @return \ilOrgUnitOperationContext
     */
    public static function findById($id)
    {
        return ilOrgUnitOperationContext::find($id);
    }


    /**
     * @param int $ref_id
     *
     * @return \ilOrgUnitOperationContext
     */
    public static function findByRefId($ref_id)
    {
        $type_context = ilObject2::_lookupType($ref_id, true);

        return self::findByName($type_context);
    }


    /**
     * @param int $obj_id
     *
     * @return \ilOrgUnitOperationContext
     */
    public static function findByObjId($obj_id)
    {
        $type_context = ilObject2::_lookupType($obj_id, false);

        return self::findByName($type_context);
    }
}
