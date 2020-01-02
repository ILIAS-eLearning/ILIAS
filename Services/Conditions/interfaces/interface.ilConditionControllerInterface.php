<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface for containers that take over control of conditions for repository object targets
 *
 * @author @leifos.de
 * @ingroup ServicesCondition
 */
interface ilConditionControllerInterface
{
    /**
     * Returns true, if the a container controls the conditions of its childrens
     *
     * @param int $a_container_ref_id ref id of a container
     * @return bool
     */
    public function isContainerConditionController($a_container_ref_id) : bool;

    /**
     * Returns condition set for a repository object which is children under a container that controls the conditions
     *
     * @param int $a_container_child_ref_id ref if of children object
     * @return ilConditionSet
     */
    public function getConditionSetForRepositoryObject($a_container_child_ref_id) : ilConditionSet;
}
