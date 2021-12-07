<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

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

    protected function __construct(ilLanguage $lng)
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
        if (null !== self::$validStates) {
            return self::$validStates;
        }

        return (self::$validStates = [
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

    /**
     * @param bool $withInitialState
     * @return array<string, string>
     */
    public function getStatesAsOptionArray(bool $withInitialState = false) : array
    {
        if (isset(self::$stateOptions[$withInitialState]) && is_array(self::$stateOptions[$withInitialState])) {
            return self::$stateOptions[$withInitialState];
        }

        $options = [];

        foreach ($this->getValidStates() as $state) {
            if ($withInitialState || !$state->isInitial()) {
                $options[get_class($state)] = $this->lng->txt('buddy_bs_state_' . strtolower($state->getName()));
            }
        }

        return (self::$stateOptions[$withInitialState] = $options);
    }

    public function getRendererByOwnerAndRelation(
        int $ownerId,
        ilBuddySystemRelation $relation
    ) : ilBuddySystemRelationStateButtonRenderer {
        $stateClass = get_class($relation->getState());
        $rendererClass = $stateClass . 'ButtonRenderer';

        return new $rendererClass($ownerId, $relation);
    }
}
