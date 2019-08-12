<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilBuddySystemRelationState
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilBuddySystemRelationState
{
    /**
     * @return bool
     */
    public function isInitial() : bool;

    /**
     * @return string
     */
    public function getName() : string;

    /**
     * @return string
     */
    public function getAction() : string;

    /**
     * @return ilBuddySystemRelationStateCollection
     */
    public function getPossibleTargetStates() : ilBuddySystemRelationStateCollection;

    /**
     * @param ilBuddySystemRelation $relation
     */
    public function link(ilBuddySystemRelation $relation) : void;

    /**
     * @param ilBuddySystemRelation $relation
     */
    public function unlink(ilBuddySystemRelation $relation) : void;

    /**
     * @param ilBuddySystemRelation $relation
     */
    public function request(ilBuddySystemRelation $relation) : void;

    /**
     * @param ilBuddySystemRelation $relation
     */
    public function ignore(ilBuddySystemRelation $relation) : void;
}