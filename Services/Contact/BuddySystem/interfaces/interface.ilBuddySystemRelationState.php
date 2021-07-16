<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilBuddySystemRelationState
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilBuddySystemRelationState
{
    public function isInitial() : bool;

    public function getName() : string;

    public function getAction() : string;

    public function getPossibleTargetStates() : ilBuddySystemRelationStateCollection;

    public function link(ilBuddySystemRelation $relation) : void;

    public function unlink(ilBuddySystemRelation $relation) : void;

    public function request(ilBuddySystemRelation $relation) : void;

    public function ignore(ilBuddySystemRelation $relation) : void;
}
