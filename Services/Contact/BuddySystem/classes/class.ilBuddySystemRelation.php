<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Contact/BuddySystem/interfaces/interface.ilBuddySystemRelationState.php';

/**
 * Class ilBuddySystemRelation
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemRelation
{
    /**
     * @var int
     */
    protected $is_owned_by_request = false;

    /**
     * @var int
     */
    protected $user_id;

    /**
     * @var int
     */
    protected $buddy_user_id;

    /**
     * @var int
     */
    protected $timestamp;

    /**
     * @var ilBuddySystemRelationState
     */
    protected $state;

    /**
     * @var ilBuddySystemRelationState|null
     */
    protected $prior_state;

    /**
     * @param ilBuddySystemRelationState $state
     */
    public function __construct(ilBuddySystemRelationState $state)
    {
        $this->setState($state, false);
    }

    /**
     * @param ilBuddySystemRelationState $state
     * @param $remember_prior_state boolean
     * @return self
     */
    public function setState(ilBuddySystemRelationState $state, $remember_prior_state = true)
    {
        if ($remember_prior_state) {
            $this->setPriorState($this->getState());
        }

        $this->state = $state;
        return $this;
    }

    /**
     * @return ilBuddySystemRelationState
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return ilBuddySystemRelationState|null
     */
    public function getPriorState()
    {
        return $this->prior_state;
    }

    /**
     * @param ilBuddySystemRelationState $prior_state
     */
    private function setPriorState($prior_state)
    {
        $this->prior_state = $prior_state;
    }

    /**
     * @return bool
     */
    public function isOwnedByRequest()
    {
        return $this->is_owned_by_request;
    }

    /**
     * @param bool $is_owned_by_request
     */
    public function setIsOwnedByRequest($is_owned_by_request)
    {
        $this->is_owned_by_request = $is_owned_by_request;
    }

    /**
     * @return int
     */
    public function getBuddyUserId()
    {
        return $this->buddy_user_id;
    }

    /**
     * @param int $buddy_user_id
     * @return self
     */
    public function setBuddyUserId($buddy_user_id)
    {
        $this->buddy_user_id = $buddy_user_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     * @return self
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param int $timestamp
     * @return self
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    /**
     * @return ilBuddySystemCollection|ilBuddySystemRelationState[]
     */
    public function getCurrentPossibleTargetStates()
    {
        require_once 'Services/Contact/BuddySystem/classes/states/class.ilBuddySystemRelationStateFilterRuleFactory.php';
        $state_filter = ilBuddySystemRelationStateFilterRuleFactory::getInstance()->getFilterRuleByRelation($this);
        return $state_filter->getStates();
    }

    /**
     * @throws ilBuddySystemRelationStateException
     * @return self
     */
    public function link()
    {
        if ($this->getUserId() == $this->getBuddyUserId()) {
            throw new ilBuddySystemRelationStateException("Can't change a state when the requester equals the requestee.");
        }

        $this->getState()->link($this);
        return $this;
    }

    /**
     * @throws ilBuddySystemRelationStateException
     * @return self
     */
    public function unlink()
    {
        if ($this->getUserId() == $this->getBuddyUserId()) {
            throw new ilBuddySystemRelationStateException("Can't change a state when the requester equals the requestee.");
        }

        $this->getState()->unlink($this);
        return $this;
    }

    /**
     * @throws ilBuddySystemRelationStateException
     * @return self
     */
    public function request()
    {
        if ($this->getUserId() == $this->getBuddyUserId()) {
            throw new ilBuddySystemRelationStateException("Can't change a state when the requester equals the requestee.");
        }

        $this->getState()->request($this);
        return $this;
    }

    /**
     * @throws ilBuddySystemRelationStateException
     * @return self
     */
    public function ignore()
    {
        if ($this->getUserId() == $this->getBuddyUserId()) {
            throw new ilBuddySystemRelationStateException("Can't change a state when the requester equals the requestee.");
        }

        $this->getState()->ignore($this);
        return $this;
    }

    /**
     * @return bool
     */
    public function isLinked()
    {
        return $this->getState() instanceof ilBuddySystemLinkedRelationState;
    }

    /**
     * @return bool
     */
    public function isUnlinked()
    {
        return $this->getState() instanceof ilBuddySystemUnlinkedRelationState;
    }

    /**
     * @return bool
     */
    public function isRequested()
    {
        return $this->getState() instanceof ilBuddySystemRequestedRelationState;
    }

    /**
     * @return bool
     */
    public function isIgnored()
    {
        return $this->getState() instanceof ilBuddySystemIgnoredRequestRelationState;
    }

    /**
     * @return bool
     */
    public function wasLinked()
    {
        return $this->getPriorState() instanceof ilBuddySystemLinkedRelationState;
    }

    /**
     * @return bool
     */
    public function wasUnlinked()
    {
        return $this->getPriorState() instanceof ilBuddySystemUnlinkedRelationState;
    }

    /**
     * @return bool
     */
    public function wasRequested()
    {
        return $this->getPriorState() instanceof ilBuddySystemRequestedRelationState;
    }

    /**
     * @return bool
     */
    public function wasIgnored()
    {
        return $this->getPriorState() instanceof ilBuddySystemIgnoredRequestRelationState;
    }
}
