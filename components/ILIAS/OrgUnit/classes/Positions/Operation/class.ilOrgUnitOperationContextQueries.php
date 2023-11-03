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
 * Class ilOrgUnitOperationContextQueries
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @deprecated Please use OrgUnitOperationContextRepository
 */
class ilOrgUnitOperationContextQueries
{
    protected static array $instance_by_name = array();
    protected static ilOrgUnitOperationContextDBRepository $contextRepo;

    protected static function getContextRepo()
    {
        if (!isset(self::$contextRepo)) {
            $dic = ilOrgUnitLocalDIC::dic();
            self::$contextRepo = $dic["repo.OperationContexts"];
        }

        return self::$contextRepo;
    }

    /**
     * @deprecated Please use get() from OrgUnitOperationContextRepository
     */
    public static function registerNewContext(string $context_name, ?string $parent_context = null): void
    {
        $context = self::getContextRepo()->get($context_name, $parent_context);
    }


    /**
     * @deprecated Please use find() from OrgUnitOperationContextRepository
     * @throws ilException
     */
    public static function findByName(string $context_name): ilOrgUnitOperationContext
    {
        if (!isset(self::$instance_by_name[$context_name])) {
            $context = self::getContextRepo()->find($context_name);
            if (!$context) {
                throw new ilException("Context not found");
            }
            self::$instance_by_name[$context_name] = $context;
        }

        return self::$instance_by_name[$context_name];
    }

    /**
     * @deprecated Please use find() from OrgUnitOperationContextRepository for context name
     * Contexts should not be referenced by Id
     */
    public static function findById(int $id): ilOrgUnitOperationContext
    {
        return self::getContextRepo()->getById($id);
    }

    /**
     * @deprecated Please use getByRefId() from OrgUnitOperationContextRepository
     */
    public static function findByRefId(int $ref_id): ilOrgUnitOperationContext
    {
        $type_context = ilObject2::_lookupType($ref_id, true);

        return self::findByName($type_context);
    }

    /**
     * @deprecated Please use getByObjId() from OrgUnitOperationContextRepository
     */
    public static function findByObjId(int $obj_id): ilOrgUnitOperationContext
    {
        $type_context = ilObject2::_lookupType($obj_id, false);

        return self::findByName($type_context);
    }
}
