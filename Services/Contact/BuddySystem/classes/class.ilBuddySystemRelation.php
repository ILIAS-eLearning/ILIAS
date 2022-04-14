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
 * Class ilBuddySystemRelation
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemRelation
{
    protected ilBuddySystemRelationState $state;
    protected int $usrId;
    protected int $buddyUsrId;
    protected bool $isOwnedByActor;
    protected int $timestamp;
    protected ?ilBuddySystemRelationState $priorState = null;

    public function __construct(
        ilBuddySystemRelationState $state,
        int $usrId,
        int $buddyUsrId,
        bool $isOwnedByActor,
        int $timestamp
    ) {
        $this->usrId = $usrId;
        $this->buddyUsrId = $buddyUsrId;
        $this->isOwnedByActor = $isOwnedByActor;
        $this->timestamp = $timestamp;
        $this->setState($state, false);
    }

    private function setPriorState(ilBuddySystemRelationState $prior_state) : void
    {
        $this->priorState = $prior_state;
    }

    public function setState(ilBuddySystemRelationState $state, bool $rememberPriorState = true) : self
    {
        if ($rememberPriorState) {
            $this->setPriorState($this->getState());
        }

        $this->state = $state;
        return $this;
    }

    public function getState() : ilBuddySystemRelationState
    {
        return $this->state;
    }

    public function getPriorState() : ?ilBuddySystemRelationState
    {
        return $this->priorState;
    }

    public function isOwnedByActor() : bool
    {
        return $this->isOwnedByActor;
    }

    public function withIsOwnedByActor(bool $isOwnedByActor) : self
    {
        $clone = clone $this;
        $clone->isOwnedByActor = $isOwnedByActor;

        return $clone;
    }

    public function getBuddyUsrId() : int
    {
        return $this->buddyUsrId;
    }

    public function withBuddyUsrId(int $buddyUsrId) : self
    {
        $clone = clone $this;
        $clone->buddyUsrId = $buddyUsrId;

        return $clone;
    }

    public function getUsrId() : int
    {
        return $this->usrId;
    }

    public function withUsrId(int $usrId) : self
    {
        $clone = clone $this;
        $clone->usrId = $usrId;

        return $clone;
    }

    public function getTimestamp() : int
    {
        return $this->timestamp;
    }

    public function withTimestamp(int $timestamp) : self
    {
        $clone = clone $this;
        $clone->timestamp = $timestamp;

        return $clone;
    }

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

    public function isLinked() : bool
    {
        return $this->getState() instanceof ilBuddySystemLinkedRelationState;
    }

    public function isUnlinked() : bool
    {
        return $this->getState() instanceof ilBuddySystemUnlinkedRelationState;
    }

    public function isRequested() : bool
    {
        return $this->getState() instanceof ilBuddySystemRequestedRelationState;
    }

    public function isIgnored() : bool
    {
        return $this->getState() instanceof ilBuddySystemIgnoredRequestRelationState;
    }

    public function wasLinked() : bool
    {
        return $this->getPriorState() instanceof ilBuddySystemLinkedRelationState;
    }

    public function wasUnlinked() : bool
    {
        return $this->getPriorState() instanceof ilBuddySystemUnlinkedRelationState;
    }

    public function wasRequested() : bool
    {
        return $this->getPriorState() instanceof ilBuddySystemRequestedRelationState;
    }

    public function wasIgnored() : bool
    {
        return $this->getPriorState() instanceof ilBuddySystemIgnoredRequestRelationState;
    }
}
