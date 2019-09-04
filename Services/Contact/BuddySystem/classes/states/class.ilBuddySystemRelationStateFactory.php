<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddySystemRelationStateFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemRelationStateFactory
{
    /** @var self */
    protected static $instance;

    /** @var array|null */
    protected static $validStates;

    /** @var array|null */
    protected static $stateOptions;

    /** @var ilLanguage */
    protected $lng;

    /**
     * ilBuddySystemRelationStateFactory constructor.
     */
    protected function __construct()
    {
        global $DIC;

        $this->lng = $DIC['lng'];
    }

    /**
     * @return self
     */
    public static function getInstance() : self
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
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

        throw new ilBuddySystemException("Could not find an initial state class");
    }

    /**
     * @param bool $withInitialState
     * @return string[]
     */
    public function getStatesAsOptionArray($withInitialState = false) : array
    {
        if (null !== self::$stateOptions[$withInitialState]) {
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

    /**
     * @param int $ownerId
     * @param ilBuddySystemRelation $relation
     * @return ilBuddySystemRelationStateButtonRenderer
     */
    public function getRendererByOwnerAndRelation(
        int $ownerId,
        ilBuddySystemRelation $relation
    ) : ilBuddySystemRelationStateButtonRenderer {
        $stateClass = get_class($relation->getState());
        $rendererClass = $stateClass . 'ButtonRenderer';

        return new $rendererClass($ownerId, $relation);
    }
}