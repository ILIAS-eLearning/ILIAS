<?php

declare(strict_types=1);

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
 * Interface ilBuddySystemRelationState
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilBuddySystemRelationState
{
    public function isInitial(): bool;

    public function getName(): string;

    public function getAction(): string;

    public function getPossibleTargetStates(): ilBuddySystemRelationStateCollection;

    public function link(ilBuddySystemRelation $relation): void;

    public function unlink(ilBuddySystemRelation $relation): void;

    public function request(ilBuddySystemRelation $relation): void;

    public function ignore(ilBuddySystemRelation $relation): void;
}
