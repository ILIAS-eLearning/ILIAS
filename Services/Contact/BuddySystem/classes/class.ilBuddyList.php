<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Contact/BuddySystem/classes/class.ilBuddySystemRelationRepository.php';
require_once 'Services/Contact/BuddySystem/classes/class.ilBuddySystemRelationCollection.php';
require_once 'Services/Contact/BuddySystem/classes/class.ilBuddySystemRelation.php';
require_once 'Services/Contact/BuddySystem/exceptions/class.ilBuddySystemException.php';
require_once 'Services/Contact/BuddySystem/exceptions/class.ilBuddySystemRelationStateAlreadyGivenException.php';
require_once 'Services/Contact/BuddySystem/classes/states/class.ilBuddySystemLinkedRelationState.php';
require_once 'Services/Contact/BuddySystem/classes/states/class.ilBuddySystemUnlinkedRelationState.php';
require_once 'Services/Contact/BuddySystem/classes/states/class.ilBuddySystemRequestedRelationState.php';
require_once 'Services/Contact/BuddySystem/classes/states/class.ilBuddySystemIgnoredRequestRelationState.php';

/**
 * Class ilBuddyList
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddyList
{
    /**
     * @var int
     */
    protected $owner_id;

    /**
     * @var ilBuddySystemRelationCollection
     */
    protected $relations;

    /**
     * @var ilBuddySystemRelationRepository
     */
    protected $repository;

    /**
     * @var self[]
     */
    protected static $instances = array();

    /**
     * @var bool
     */
    protected $relations_read = false;

    /**
     * @var ilAppEventHandler
     */
    protected $event_handler;

    /**
     * @param int $usr_id
     * @return self
     * @throws ilBuddySystemException
     */
    public static function getInstanceByUserId($usr_id)
    {
        if (ilObjUser::_isAnonymous($usr_id)) {
            throw new ilBuddySystemException(sprintf("You cannot create an instance for the anonymous user (id: %s)", $usr_id));
        }

        if (isset(self::$instances[$usr_id])) {
            return self::$instances[$usr_id];
        }

        self::$instances[$usr_id] = new self($usr_id);
        return self::$instances[$usr_id];
    }

    /**
     * @return self
     */
    public static function getInstanceByGlobalUser()
    {
        global $DIC;

        return self::getInstanceByUserId($DIC->user()->getId());
    }

    /**
     * @var int $owner_id
     */
    protected function __construct($owner_id)
    {
        global $DIC;

        $this->setOwnerId($owner_id);
        $this->setRepository(new ilBuddySystemRelationRepository($this->getOwnerId()));

        $this->event_handler = $DIC['ilAppEventHandler'];
    }

    /**
     * Remove the singleton instance from static array, used for unit tests
     */
    public function reset()
    {
        $this->relations_read = false;
        $this->relations = null;
        unset(self::$instances[$this->getOwnerId()]);
    }

    /**
     * @return ilBuddySystemRelationRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param ilBuddySystemRelationRepository $repository
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;
    }

    /**
     *
     */
    public function readFromRepository()
    {
        $this->setRelations(new ilBuddySystemRelationCollection((array) $this->getRepository()->getAll()));
    }

    /**
     *
     */
    protected function performLazyLoading()
    {
        if (!$this->relations_read) {
            $this->readFromRepository();
            $this->relations_read = true;
        }
    }

    /**
     * @return ilBuddySystemRelationCollection
     */
    public function getRelations()
    {
        if (null === $this->relations) {
            $this->performLazyLoading();
        }

        return $this->relations;
    }

    /**
     * @param ilBuddySystemRelationCollection $relations
     */
    public function setRelations(ilBuddySystemRelationCollection $relations)
    {
        $this->relations = $relations;
    }

    /**
     * Returns the user id of the buddy list owner
     * @return int
     */
    public function getOwnerId()
    {
        return $this->owner_id;
    }

    /**
     * Gets all linked/approved relations
     * @return ilBuddySystemRelationCollection
     */
    public function getLinkedRelations()
    {
        return $this->getRelations()->filter(function (ilBuddySystemRelation $relation) {
            return $relation->isLinked();
        });
    }

    /**
     * Gets all requested relations the buddy list owner has to interact with
     * @return ilBuddySystemRelationCollection
     */
    public function getRequestRelationsForOwner()
    {
        $owner = $this->getOwnerId();
        return $this->getRequestedRelations()->filter(function (ilBuddySystemRelation $relation) use ($owner) {
            return $relation->getBuddyUserId() == $owner;
        });
    }

    /**
     * Gets all requested relations the buddy list owner initiated
     * @return ilBuddySystemRelationCollection
     */
    public function getRequestRelationsByOwner()
    {
        $owner = $this->getOwnerId();
        return $this->getRequestedRelations()->filter(function (ilBuddySystemRelation $relation) use ($owner) {
            return $relation->getUserId() == $owner;
        });
    }

    /**
     * Gets all requested relations
     * @return ilBuddySystemRelationCollection
     */
    public function getRequestedRelations()
    {
        return $this->getRelations()->filter(function (ilBuddySystemRelation $relation) {
            return $relation->isRequested();
        });
    }

    /**
     * Gets all ignored relations the buddy list owner has to interact with
     * @return ilBuddySystemRelationCollection
     */
    public function getIgnoredRelationsForOwner()
    {
        $owner = $this->getOwnerId();
        return $this->getIgnoredRelations()->filter(function (ilBuddySystemRelation $relation) use ($owner) {
            return $relation->getBuddyUserId() == $owner;
        });
    }

    /**
     * Gets all ignored relations the buddy list owner initiated
     * @return ilBuddySystemRelationCollection
     */
    public function getIgnoredRelationsByOwner()
    {
        $owner = $this->getOwnerId();
        return $this->getIgnoredRelations()->filter(function (ilBuddySystemRelation $relation) use ($owner) {
            return $relation->getUserId() == $owner;
        });
    }

    /**
     * Gets all ignored relations
     * @return ilBuddySystemRelationCollection
     */
    public function getIgnoredRelations()
    {
        return $this->getRelations()->filter(function (ilBuddySystemRelation $relation) {
            return $relation->isIgnored();
        });
    }

    /**
     * Returns an array of all user ids the buddy list owner has a relation with
     * @return int[]
     */
    public function getRelationUserIds()
    {
        return $this->getRelations()->getKeys();
    }

    /**
     * @param int $owner_id
     * @throws InvalidArgumentException
     */
    public function setOwnerId($owner_id)
    {
        if (!is_numeric($owner_id)) {
            throw new InvalidArgumentException(sprintf("Please pass a numeric owner id, given: %s", var_export($owner_id, 1)));
        }

        $this->owner_id = $owner_id;
    }

    /**
     * @param ilBuddySystemRelation $relation
     * @return int
     */
    protected function getRelationTargetUserId(ilBuddySystemRelation $relation)
    {
        return ($relation->getUserId() == $this->getOwnerId() ? $relation->getBuddyUserId() : $relation->getUserId());
    }

    /**
     * @param int $usr_id
     * @throws InvalidArgumentException
     * @return ilBuddySystemRelation
     */
    public function getRelationByUserId($usr_id)
    {
        if (!is_numeric($usr_id)) {
            throw new InvalidArgumentException(sprintf("Please pass a numeric owner id, given: %s", var_export($usr_id, 1)));
        }

        if ($this->getRelations()->containsKey($usr_id)) {
            return $this->getRelations()->get($usr_id);
        }

        require_once 'Services/Contact/BuddySystem/classes/states/class.ilBuddySystemRelationStateFactory.php';
        $relation = new ilBuddySystemRelation(ilBuddySystemRelationStateFactory::getInstance()->getInitialState());
        $relation->setIsOwnedByRequest(true);
        $relation->setUserId($this->getOwnerId());
        $relation->setBuddyUserId($usr_id);
        $relation->setTimestamp(time());

        $this->getRelations()->set($usr_id, $relation);

        return $relation;
    }

    /**
     * @param ilBuddySystemRelation $relation
     * @return self
     * @throws ilBuddySystemException
     */
    public function link(ilBuddySystemRelation $relation)
    {
        try {
            if ($relation->isLinked()) {
                require_once 'Services/Contact/BuddySystem/exceptions/class.ilBuddySystemRelationStateAlreadyGivenException.php';
                throw new ilBuddySystemRelationStateAlreadyGivenException('buddy_bs_action_already_linked');
            }

            if ($this->getOwnerId() == $relation->getUserId()) {
                throw new ilBuddySystemException("You can only accept a request when you are not the initiator");
            }

            $relation->link();

            $this->getRepository()->save($relation);
            $this->getRelations()->set($this->getRelationTargetUserId($relation), $relation);
        } catch (ilBuddySystemRelationStateException $e) {
            throw $e;
        }

        return $this;
    }

    /**
     * @param ilBuddySystemRelation $relation
     * @throws ilBuddySystemException
     * @return self
     */
    public function unlink(ilBuddySystemRelation $relation)
    {
        try {
            $relation->unlink();
            $this->getRepository()->save($relation);
            $this->getRelations()->set($this->getRelationTargetUserId($relation), $relation);
        } catch (ilBuddySystemException $e) {
            if ($relation->isUnlinked()) {
                require_once 'Services/Contact/BuddySystem/exceptions/class.ilBuddySystemRelationStateAlreadyGivenException.php';
                throw new ilBuddySystemRelationStateAlreadyGivenException('buddy_bs_action_already_unlinked');
            }

            throw $e;
        }

        return $this;
    }

    /**
     * @param ilBuddySystemRelation $relation
     * @return self
     * @throws ilBuddySystemException
     */
    public function request(ilBuddySystemRelation $relation)
    {
        if (ilObjUser::_isAnonymous($this->getRelationTargetUserId($relation))) {
            throw new ilBuddySystemException(sprintf("You cannot add the anonymous user to the list (id: %s)", $this->getRelationTargetUserId($relation)));
        }

        if (!strlen(ilObjUser::_lookupLogin($this->getRelationTargetUserId($relation)))) {
            throw new ilBuddySystemException(sprintf("You cannot add a non existing user (id: %s)", $this->getRelationTargetUserId($relation)));
        }

        try {
            $relation->request();
            $this->getRepository()->save($relation);
            $this->getRelations()->set($this->getRelationTargetUserId($relation), $relation);
        } catch (ilBuddySystemException $e) {
            if ($relation->isRequested()) {
                require_once 'Services/Contact/BuddySystem/exceptions/class.ilBuddySystemRelationStateAlreadyGivenException.php';
                throw new ilBuddySystemRelationStateAlreadyGivenException('buddy_bs_action_already_requested');
            }

            throw $e;
        }

        $this->event_handler->raise(
            'Services/Contact',
            'contactRequested',
            array(
                'usr_id' => $this->getRelationTargetUserId($relation)
            )
        );

        return $this;
    }

    /**
     * @param ilBuddySystemRelation $relation
     * @return self
     * @throws ilBuddySystemException
     */
    public function ignore(ilBuddySystemRelation $relation)
    {
        try {
            if ($relation->isLinked()) {
                require_once 'Services/Contact/BuddySystem/exceptions/class.ilBuddySystemRelationStateTransitionException.php';
                throw new ilBuddySystemRelationStateTransitionException('buddy_bs_action_already_linked');
            }

            if ($this->getOwnerId() == $relation->getUserId()) {
                throw new ilBuddySystemException("You can only ignore a request when you are not the initiator");
            }

            $relation->ignore();

            $this->getRepository()->save($relation);
            $this->getRelations()->set($this->getRelationTargetUserId($relation), $relation);
        } catch (ilBuddySystemException $e) {
            if ($relation->isIgnored()) {
                require_once 'Services/Contact/BuddySystem/exceptions/class.ilBuddySystemRelationStateAlreadyGivenException.php';
                throw new ilBuddySystemRelationStateAlreadyGivenException('buddy_bs_action_already_ignored');
            }

            throw $e;
        }

        return $this;
    }

    /**
     * Removes all buddy system references of the user (persistently)
     * @return self
     */
    public function destroy()
    {
        $this->getRepository()->destroy();
        $this->getRelations()->clear();
        return $this;
    }
}
