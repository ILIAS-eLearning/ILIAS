<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */


/**
 * Only for testing purposes...
 *
 * @author Alexander Killing <killing@leifos.de>
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
