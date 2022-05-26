<?php

/**
 * Class ilOrgUnitOperationQueries
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitOperationQueries
{

    /**
     * @param string $context ilOrgUnitOperationContext::CONTEXT_OBJECT will provide this new
     *                        operation to all contexts such as
     *                        ilOrgUnitOperationContext::CONTEXT_GRP or
     *                        ilOrgUnitOperationContext::CONTEXT_CRS
     *                        use a more specific for your object type but the related context must
     *                        exist. Register a new context using
     *                        ilOrgUnitOperationContext::registerNewContext() for plugins
     * @throws ilException
     */
    public static function registerNewOperation(
        string $operation_name,
        string $description,
        string $context = ilOrgUnitOperationContext::CONTEXT_OBJECT
    ) : void {
        $contextList = ilOrgUnitOperationContext::where(array('context' => $context));
        if (!$contextList->hasSets()) {
            throw new ilException('Context does not exist! register context first using ilOrgUnitOperationContext::registerNewContext()');
        }
        /**
         * @var $ilOrgUnitOperationContext ilOrgUnitOperationContext
         */
        $ilOrgUnitOperationContext = $contextList->first();

        if (ilOrgUnitOperation::where(array(
            'context_id' => $ilOrgUnitOperationContext->getId(),
            'operation_string' => $operation_name,
        ))->hasSets()
        ) {
            throw new ilException('This operation in this context has already been registered.');
        }
        $operation = new ilOrgUnitOperation();
        $operation->setOperationString($operation_name);
        $operation->setContextId($ilOrgUnitOperationContext->getId());
        $operation->setDescription($description);
        $operation->create();
    }

    /**
     * @param array $contexts
     * @throws ilException
     * @see registerNewOperation
     */
    public static function registerNewOperationForMultipleContexts(
        string $operation_name,
        string $description,
        array $contexts
    ) : void {
        foreach ($contexts as $context) {
            self::registerNewOperation($operation_name, $description, $context);
        }
    }

    /**
     * @return ilOrgUnitOperation[]
     */
    public static function getOperationsForContextName(string $context_name) : array
    {
        $context = ilOrgUnitOperationContextQueries::findByName($context_name);
        return ilOrgUnitOperation::where(array('context_id' => $context->getPopulatedContextIds()))
                                 ->get();
    }

    /**
     * @return ilOrgUnitOperation[]
     */
    public static function getOperationsForContextId(string $context_id) : array
    {
        $context = ilOrgUnitOperationContextQueries::findById($context_id);
        return ilOrgUnitOperation::where(array('context_id' => $context->getPopulatedContextIds()))
                                 ->get();
    }

    /**
     * @throws arException
     */
    public static function findById(int $operation_id) : ActiveRecord /* ilOrgUnitOperation|ActiveRecord */
    {
        return ilOrgUnitOperation::findOrFail($operation_id);
    }

    public static function findByOperationString(
        string $operation_string,
        string $context_name
    ) : ?ActiveRecord /*ilOrgUnitOperation|ActiveRecord*/ {
        $context = ilOrgUnitOperationContextQueries::findByName($context_name);

        return ilOrgUnitOperation::where(['operation_string' => $operation_string,
                                          'context_id' => $context->getId()
        ])->first();
    }
}
