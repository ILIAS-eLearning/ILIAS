<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddySystemRelation
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemRelation
{
    /** @var bool */
    protected $isOwnedByActor = false;

    /** @var int */
    protected $usrId;

    /** @var int */
    protected $buddyUsrId;

    /** @var int */
    protected $timestamp;

    /** @var ilBuddySystemRelationState */
    protected $state;

    /** @var ilBuddySystemRelationState|null */
    protected $priorState;

    /**
     * ilBuddySystemRelation constructor.
     * @param ilBuddySystemRelationState $state
     */
    public function __construct(ilBuddySystemRelationState $state)
    {
        $this->setState($state, false);
    }

    /**
     * @param ilBuddySystemRelationState $state
     * @param $rememberPriorState boolean
     * @return self
     */
    public function setState(ilBuddySystemRelationState $state, bool $rememberPriorState = true) : self
    {
        if ($rememberPriorState) {
            $this->setPriorState($this->getState());
        }

        $this->state = $state;
        return $this;
    }

    /**
     * @return ilBuddySystemRelationState
     */
    public function getState() : ilBuddySystemRelationState
    {
        return $this->state;
    }

    /**
     * @return ilBuddySystemRelationState|null
     */
    public function getPriorState() : ?ilBuddySystemRelationState
    {
        return $this->priorState;
    }

    /**
     * @param ilBuddySystemRelationState $prior_state
     * @return ilBuddySystemRelation
     */
    private function setPriorState(ilBuddySystemRelationState $prior_state) : self
    {
        $this->priorState = $prior_state;
        return $this;
    }

    /**
     * @return bool
     */
    public function isOwnedByActor() : bool
    {
        return $this->isOwnedByActor;
    }

    /**
     * @param bool $isOwnedByActor
     * @return ilBuddySystemRelation
     */
    public function setIsOwnedByActor(bool $isOwnedByActor) : self
    {
        $this->isOwnedByActor = $isOwnedByActor;
        return $this;
    }

    /**
     * @return int
     */
    public function getBuddyUsrId() : int
    {
        return $this->buddyUsrId;
    }

    /**
     * @param int $buddyUsrId
     * @return self
     */
    public function setBuddyUsrId(int $buddyUsrId) : self
    {
        $this->buddyUsrId = $buddyUsrId;
        return $this;
    }

    /**
     * @return int
     */
    public function getUsrId() : int
    {
        return $this->usrId;
    }

    /**
     * @param int $usrId
     * @return self
     */
    public function setUsrId(int $usrId) : self
    {
        $this->usrId = $usrId;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimestamp() : int
    {
        return $this->timestamp;
    }

    /**
     * @param int $timestamp
     * @return self
     */
    public function setTimestamp(int $timestamp) : self
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    /**
     * @return ilBuddySystemCollection
     */
    public function getCurrentPossibleTargetStates() : ilBuddySystemCollection
    {
        return ilBuddySystemRelationStateFilterRuleFactory::getInstance()->getFilterRuleByRelation($this)->getStates();
    }

    /**
     * @return self
     * @throws ilBuddySystemRelationStateException
     */
    public function link() : self
    {
        if ($this->getUsrId() === $this->getBuddyUsrId()) {
            throw new ilBuddySystemRelationStateException("Can't change a state when the requester equals the requestee.");
        }

        $this->getState()->link($this);
        return $this;
    }

    /**
     * @return self
     * @throws ilBuddySystemRelationStateException
     */
    public function unlink() : self
    {
        if ($this->getUsrId() === $this->getBuddyUsrId()) {
            throw new ilBuddySystemRelationStateException("Can't change a state when the requester equals the requestee.");
        }

        $this->getState()->unlink($this);
        return $this;
    }

    /**
     * @return self
     * @throws ilBuddySystemRelationStateException
     */
    public function request() : self
    {
        if ($this->getUsrId() === $this->getBuddyUsrId()) {
            throw new ilBuddySystemRelationStateException("Can't change a state when the requester equals the requestee.");
        }

        $this->getState()->request($this);
        return $this;
    }

    /**
     * @return self
     * @throws ilBuddySystemRelationStateException
     */
    public function ignore() : self
    {
        if ($this->getUsrId() === $this->getBuddyUsrId()) {
            throw new ilBuddySystemRelationStateException("Can't change a state when the requester equals the requestee.");
        }

        $this->getState()->ignore($this);
        return $this;
    }

    /**
     * @return bool
     */
    public function isLinked() : bool
    {
        return $this->getState() instanceof ilBuddySystemLinkedRelationState;
    }

    /**
     * @return bool
     */
    public function isUnlinked() : bool
    {
        return $this->getState() instanceof ilBuddySystemUnlinkedRelationState;
    }

    /**
     * @return bool
     */
    public function isRequested() : bool
    {
        return $this->getState() instanceof ilBuddySystemRequestedRelationState;
    }

    /**
     * @return bool
     */
    public function isIgnored() : bool
    {
        return $this->getState() instanceof ilBuddySystemIgnoredRequestRelationState;
    }

    /**
     * @return bool
     */
    public function wasLinked() : bool
    {
        return $this->getPriorState() instanceof ilBuddySystemLinkedRelationState;
    }

    /**
     * @return bool
     */
    public function wasUnlinked() : bool
    {
        return $this->getPriorState() instanceof ilBuddySystemUnlinkedRelationState;
    }

    /**
     * @return bool
     */
    public function wasRequested() : bool
    {
        return $this->getPriorState() instanceof ilBuddySystemRequestedRelationState;
    }

    /**
     * @return bool
     */
    public function wasIgnored() : bool
    {
        return $this->getPriorState() instanceof ilBuddySystemIgnoredRequestRelationState;
    }
}