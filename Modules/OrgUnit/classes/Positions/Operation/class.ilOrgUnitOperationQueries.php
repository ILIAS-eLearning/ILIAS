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
 * Class ilOrgUnitOperationQueries
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitOperationQueries
{
    protected static ilOrgUnitOperationDBRepository $operationRepo;

    protected static function getOperationRepo()
    {
        if (!isset(self::$operationRepo)) {
            $dic = ilOrgUnitLocalDIC::dic();
            self::$operationRepo = $dic["repo.Operations"];
        }

        return self::$operationRepo;
    }

    /**
     * @deprecated Please use registerNewOperation() from ilOrgUnitOperationDBRepository
     */
    public static function registerNewOperation(
        string $operation_name,
        string $description,
        string $context = ilOrgUnitOperationContext::CONTEXT_OBJECT
    ): void {
        self::getOperationRepo()->registerNewOperation($operation_name, $description, [$context]);
    }

    /**
     * @deprecated Please use registerNewOperation() from ilOrgUnitOperationDBRepository
     */
    public static function registerNewOperationForMultipleContexts(
        string $operation_name,
        string $description,
        array $contexts
    ): void {
        self::getOperationRepo()->registerNewOperation($operation_name, $description, $contexts);
    }

    /**
     * @deprecated Please use findOperationsByContextName() from ilOrgUnitOperationDBRepository
     * @return ilOrgUnitOperation[]
     */
    public static function getOperationsForContextName(string $context_name): array
    {
        return self::getOperationRepo()->findOperationsByContextName($context_name);
    }

    /**
     * @deprecated Please use findOperationsByContextId() from ilOrgUnitOperationDBRepository
     * @return ilOrgUnitOperation[]
     */
    public static function getOperationsForContextId(string $context_id): array
    {
        return self::getOperationRepo()->findOperationsByContextId($context_id);
    }

    /**
     * @@deprecated Please use getOperationById() from ilOrgUnitOperationDBRepository
     */
    public static function findById(int $operation_id): ?ilOrgUnitOperation
    {
        return self::getOperationRepo()->findOperationById($operation_id);
    }

    /**
     * @@deprecated Please use findOperationByNameAndContext() from ilOrgUnitOperationDBRepository
     */
    public static function findByOperationString(
        string $operation_string,
        string $context_name
    ): ?ilOrgUnitOperation {
        return self::getOperationRepo()->findOperationByNameAndContext(
            $operation_string,
            $context_name
        );
    }
}
