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
 * Class ilBuddySystemRelationStateFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemRelationStateFactory
{
    protected static ?self $instance = null;
    /** @var ilBuddySystemRelationState[]|null */
    protected static ?array $validStates = null;
    /** @var array<string, string>[]|null */
    protected static ?array $stateOptions = null;
    protected ilLanguage $lng;

    public function __construct(ilLanguage $lng)
    {
        $this->lng = $lng;
    }

    public static function getInstance(?ilLanguage $lng = null) : self
    {
        global $DIC;

        if (null === self::$instance) {
            $lng = $lng ?? $DIC['lng'];

            self::$instance = new self($lng);
        }

        return self::$instance;
    }

    public function reset() : void
    {
        self::$instance = null;
    }

    /**
     * Get all valid states
     * @return ilBuddySystemRelationState[]
     */
    public function getValidStates() : array
    {
        return self::$validStates ?? (self::$validStates = [
            new ilBuddySystemUnlinkedRelationState(),
            new ilBuddySystemRequestedRelationState(),
            new ilBuddySystemIgnoredRequestRelationState(),
            new ilBuddySystemLinkedRelationState(),
        ]);
    }

    /**
     * @return ilBuddySystemRelationState
     * @throws ilBuddySystemException
     */
    public function getInitialState() : ilBuddySystemRelationState
    {
        foreach ($this->getValidStates() as $state) {
            if ($state->isInitial()) {
                return $state;
            }
        }

        throw new ilBuddySystemException('Could not find an initial state class');
    }

    public function getTableFilterStateMapper(ilBuddySystemRelationState $state) : ilBuddySystemRelationStateTableFilterMapper
    {
        $stateClass = get_class($state);
        $class = $stateClass . 'TableFilterMapper';

        return new $class($this->lng, $state);
    }

    public function getStateButtonRendererByOwnerAndRelation(
        int $ownerId,
        ilBuddySystemRelation $relation
    ) : ilBuddySystemRelationStateButtonRenderer {
        $stateClass = get_class($relation->getState());
        $class = $stateClass . 'ButtonRenderer';

        return new $class($ownerId, $relation);
    }
}
