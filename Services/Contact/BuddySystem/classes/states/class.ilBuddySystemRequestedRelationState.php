<?php declare(strict_types=1);

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
 *********************************************************************/

/**
 * Class ilBuddySystemRequestedRelationState
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemRequestedRelationState extends ilAbstractBuddySystemRelationState
{
    public function getName() : string
    {
        return 'Requested';
    }

    public function getAction() : string
    {
        return 'request';
    }

    public function getPossibleTargetStates() : ilBuddySystemRelationStateCollection
    {
        return new ilBuddySystemRelationStateCollection([
            new ilBuddySystemLinkedRelationState(),
            new ilBuddySystemIgnoredRequestRelationState(),
            new ilBuddySystemUnlinkedRelationState(),
        ]);
    }

    public function unlink(ilBuddySystemRelation $relation) : void
    {
        $relation->setState(new ilBuddySystemUnlinkedRelationState());
    }

    public function ignore(ilBuddySystemRelation $relation) : void
    {
        $relation->setState(new ilBuddySystemIgnoredRequestRelationState());
    }

    public function link(ilBuddySystemRelation $relation) : void
    {
        $relation->setState(new ilBuddySystemLinkedRelationState());
    }
}
