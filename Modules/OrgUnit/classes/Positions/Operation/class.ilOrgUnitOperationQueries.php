<?php

/**
 * Class ilOrgUnitOperationQueries
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitOperationQueries
{

    /**
     * @param        $operation_name
     * @param        $description
     * @param string $context ilOrgUnitOperationContext::CONTEXT_OBJECT will provide this new
     *                        operation to all contexts such as
     *                        ilOrgUnitOperationContext::CONTEXT_GRP or
     *                        ilOrgUnitOperationContext::CONTEXT_CRS
     *                        use a more specific for your object type but the related context must
     *                        exist. Register a new context using
     *                        ilOrgUnitOperationContext::registerNewContext() for plugins
     *
     * @throws \ilException
     */
    public static function registerNewOperation($operation_name, $description, $context = ilOrgUnitOperationContext::CONTEXT_OBJECT)
    {
        $contextList = ilOrgUnitOperationContext::where(array( 'context' => $context ));
        if (!$contextList->hasSets()) {
            throw new ilException('Context does not exist! register context first using ilOrgUnitOperationContext::registerNewContext()');
        }
        /**
         * @var $ilOrgUnitOperationContext \ilOrgUnitOperationContext
         */
        $ilOrgUnitOperationContext = $contextList->first();

        if (ilOrgUnitOperation::where(array(
            'context_id'       => $ilOrgUnitOperationContext->getId(),
            'operation_string' => $operation_name,
        ))->hasSets()) {
            throw new ilException('This operation in this context has already been registered.');
        }
        $operation = new ilOrgUnitOperation();
        $operation->setOperationString($operation_name);
        $operation->setContextId($ilOrgUnitOperationContext->getId());
        $operation->setDescription($description);
        $operation->create();
    }


    /**
     * @param       $operation_name
     * @param       $description
     * @param array $contexts
     *
     * @see registerNewOperation
     */
    public static function registerNewOperationForMultipleContexts($operation_name, $description, array $contexts)
    {
        foreach ($contexts as $context) {
            self::registerNewOperation($operation_name, $description, $context);
        }
    }


    /**
     * @param $context_name
     *
     * @return ilOrgUnitOperation[]
     */
    public static function getOperationsForContextName($context_name)
    {
        /**
         * @var $context ilOrgUnitOperationContext
         */
        $context = ilOrgUnitOperationContextQueries::findByName($context_name);

        return ilOrgUnitOperation::where(array( 'context_id' => $context->getPopulatedContextIds() ))
                                 ->get();
    }


    /**
     * @param $context_id
     *
     * @return \ilOrgUnitOperation[]
     */
    public static function getOperationsForContextId($context_id)
    {
        /**
         * @var $context ilOrgUnitOperationContext
         */
        $context = ilOrgUnitOperationContextQueries::findById($context_id);

        return ilOrgUnitOperation::where(array( 'context_id' => $context->getPopulatedContextIds() ))
                                 ->get();
    }


    /**
     * @param int $operation_id
     *
     * @return \ilOrgUnitOperation
     */
    public static function findById($operation_id)
    {
        return ilOrgUnitOperation::findOrFail($operation_id);
    }


    /**
     * @param string $operation_string
     *
     * @return \ilOrgUnitOperation
     */
    public static function findByOperationString($operation_string, $context_name)
    {
        $context = ilOrgUnitOperationContextQueries::findByName($context_name);

        return ilOrgUnitOperation::where([ 'operation_string' => $operation_string, 'context_id'=>$context->getId() ])->first();
    }
}
