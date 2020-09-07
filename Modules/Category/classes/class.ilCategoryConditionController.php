<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Conditions/interfaces/interface.ilConditionControllerInterface.php");

/**
 * Only for testing purposes...
 *
 * @author killing@leifos.de
 * @ingroup ModulesCategory
 */
class ilCategoryConditionController implements ilConditionControllerInterface
{
    /**
     * @inheritdoc
     */
    public function isContainerConditionController($container_ref_id) : bool
    {
        return false;
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getConditionSetForRepositoryObject($ref_id) : ilConditionSet
    {
        global $DIC;

        $f = $DIC->conditions()->factory();

        $conditions = array();
        if ($ref_id == 72) {
            //			$conditions[] = $f->condition($f->repositoryTrigger(73), $f->operator()->passed());
        }



        return $f->set($conditions);
    }
}
