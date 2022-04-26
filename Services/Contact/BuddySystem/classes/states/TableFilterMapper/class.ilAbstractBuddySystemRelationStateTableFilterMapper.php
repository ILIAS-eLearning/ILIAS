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

abstract class ilAbstractBuddySystemRelationStateTableFilterMapper implements ilBuddySystemRelationStateTableFilterMapper
{
    /** @var ilLanguage */
    protected $lng;
    /** @var ilBuddySystemRelationState */
    protected $state;

    final public function __construct(ilLanguage $lng, ilBuddySystemRelationState $state)
    {
        $this->lng = $lng;
        $this->state = $state;
    }

    public function optionsForState() : array
    {
        return [
            get_class($this->state) => $this->lng->txt('buddy_bs_state_' . strtolower($this->state->getName()))
        ];
    }

    public function filterMatchesRelation(string $filterKey, ilBuddySystemRelation $relation) : bool
    {
        return (
            strtolower($filterKey) === strtolower(get_class($this->state))
        );
    }
}
