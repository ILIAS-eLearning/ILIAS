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
 * Class ilAbstractBuddySystemRelationState
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilAbstractBuddySystemRelationState implements ilBuddySystemRelationState
{
    public function isInitial() : bool
    {
        return false;
    }

    /**
     * @throws ilBuddySystemRelationStateException
     */
    public function request(ilBuddySystemRelation $relation) : void
    {
        throw new ilBuddySystemRelationStateException('Invalid state transition: ' . __FUNCTION__);
    }

    /**
     * @throws ilBuddySystemRelationStateException
     */
    public function ignore(ilBuddySystemRelation $relation) : void
    {
        throw new ilBuddySystemRelationStateException('Invalid state transition: ' . __FUNCTION__);
    }

    /**
     * @throws ilBuddySystemRelationStateException
     */
    public function link(ilBuddySystemRelation $relation) : void
    {
        throw new ilBuddySystemRelationStateException('Invalid state transition: ' . __FUNCTION__);
    }

    /**
     * @throws ilBuddySystemRelationStateException
     */
    public function unlink(ilBuddySystemRelation $relation) : void
    {
        throw new ilBuddySystemRelationStateException('Invalid state transition: ' . __FUNCTION__);
    }
}
