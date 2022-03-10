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

    /** @var ilLanguage */
    protected $lng;

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

    public function getTableFilterStateMapper(ilBuddySystemRelationState $state) : ilBuddySystemRelationStateTableFilterMapper
    {
        $stateClass = get_class($state);
        $class = $stateClass . 'TableFilterMapper';

        return new $class($this->lng, $state);
    }

    /**
     * @param int $ownerId
     * @param ilBuddySystemRelation $relation
     * @return ilBuddySystemRelationStateButtonRenderer
     */
    public function getStateButtonRendererByOwnerAndRelation(
        int $ownerId,
        ilBuddySystemRelation $relation
    ) : ilBuddySystemRelationStateButtonRenderer {
        $stateClass = get_class($relation->getState());
        $class = $stateClass . 'ButtonRenderer';

        return new $class($ownerId, $relation);
    }
}
