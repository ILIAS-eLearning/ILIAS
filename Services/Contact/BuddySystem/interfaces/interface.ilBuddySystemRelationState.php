<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilBuddySystemRelationState
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilBuddySystemRelationState
{
    /**
     * @return boolean
     */
    public function isInitial();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getAction();

    /**
     * @return ilBuddySystemCollection|ilBuddySystemRelationState[]
     */
    public function getPossibleTargetStates();

    /**
     * @param ilBuddySystemRelation $relation
     */
    public function link(ilBuddySystemRelation $relation);

    /**
     * @param ilBuddySystemRelation $relation
     */
    public function unlink(ilBuddySystemRelation $relation);

    /**
     * @param ilBuddySystemRelation $relation
     */
    public function request(ilBuddySystemRelation $relation);

    /**
     * @param ilBuddySystemRelation $relation
     */
    public function ignore(ilBuddySystemRelation $relation);
}
