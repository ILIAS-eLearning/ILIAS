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
 * Class ilBuddyList
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddyList
{
    /** @var self[] */
    protected static array $instances = [];

    protected int $ownerId;
    protected ilBuddySystemRelationRepository $repository;
    protected bool $relationsRead = false;
    protected ilAppEventHandler $eventHandler;
    protected ?ilBuddySystemRelationCollection $relations = null;

    /**
     * @param int $usrId
     * @return self
     * @throws ilBuddySystemException
     */
    public static function getInstanceByUserId(int $usrId): self
    {
        if (ilObjUser::_isAnonymous($usrId)) {
            throw new ilBuddySystemException(sprintf(
                'You cannot create an instance for the anonymous user (id: %s)',
                $usrId
            ));
        }

        if (isset(self::$instances[$usrId])) {
            return self::$instances[$usrId];
        }

        self::$instances[$usrId] = new self($usrId);
        return self::$instances[$usrId];
    }

    /**
     * @return self
     * @throws ilBuddySystemException
     */
    public static function getInstanceByGlobalUser(): self
    {
        global $DIC;

        return self::getInstanceByUserId($DIC->user()->getId());
    }

    protected function __construct(int $ownerId)
    {
        global $DIC;

        $this->setOwnerId($ownerId);
        $this->setRepository(new ilBuddySystemRelationRepository($this->getOwnerId()));

        $this->eventHandler = $DIC['ilAppEventHandler'];
    }

    /**
     * Remove the singleton instance from static array, used for unit tests
     */
    public function reset(): void
    {
        $this->relationsRead = false;
        $this->relations = null;
        unset(self::$instances[$this->getOwnerId()]);
    }

    public function getRepository(): ilBuddySystemRelationRepository
    {
        return $this->repository;
    }

    public function setRepository(ilBuddySystemRelationRepository $repository): void
    {
        $this->repository = $repository;
    }

    public function readFromRepository(): void
    {
        $this->setRelations(new ilBuddySystemRelationCollection($this->getRepository()->getAll()));
    }

    protected function performLazyLoading(): void
    {
        if (!$this->relationsRead) {
            $this->readFromRepository();
            $this->relationsRead = true;
        }
    }

    public function getRelations(): ilBuddySystemRelationCollection
    {
        if (null === $this->relations) {
            $this->performLazyLoading();
        }

        return $this->relations;
    }

    public function setRelations(ilBuddySystemRelationCollection $relations): void
    {
        $this->relations = $relations;
    }

    /**
     * Returns the user id of the buddy list owner
     * @return int
     */
    public function getOwnerId(): int
    {
        return $this->ownerId;
    }

    /**
     * Gets all linked/approved relations
     * @return ilBuddySystemRelationCollection
     */
    public function getLinkedRelations(): ilBuddySystemRelationCollection
    {
        return $this->getRelations()->filter(static function (ilBuddySystemRelation $relation): bool {
            return $relation->isLinked();
        });
    }

    /**
     * Gets all requested relations the buddy list owner has to interact with
     * @return ilBuddySystemRelationCollection
     */
    public function getRequestRelationsForOwner(): ilBuddySystemRelationCollection
    {
        return $this->getRequestedRelations()->filter(function (ilBuddySystemRelation $relation): bool {
            return $relation->getBuddyUsrId() === $this->getOwnerId();
        });
    }

    /**
     * Gets all requested relations the buddy list owner initiated
     * @return ilBuddySystemRelationCollection
     */
    public function getRequestRelationsByOwner(): ilBuddySystemRelationCollection
    {
        return $this->getRequestedRelations()->filter(function (ilBuddySystemRelation $relation): bool {
            return $relation->getUsrId() === $this->getOwnerId();
        });
    }

    /**
     * Gets all requested relations
     * @return ilBuddySystemRelationCollection
     */
    public function getRequestedRelations(): ilBuddySystemRelationCollection
    {
        return $this->getRelations()->filter(static function (ilBuddySystemRelation $relation): bool {
            return $relation->isRequested();
        });
    }

    /**
     * Gets all ignored relations the buddy list owner has to interact with
     * @return ilBuddySystemRelationCollection
     */
    public function getIgnoredRelationsForOwner(): ilBuddySystemRelationCollection
    {
        return $this->getIgnoredRelations()->filter(function (ilBuddySystemRelation $relation): bool {
            return $relation->getBuddyUsrId() === $this->getOwnerId();
        });
    }

    /**
     * Gets all ignored relations the buddy list owner initiated
     * @return ilBuddySystemRelationCollection
     */
    public function getIgnoredRelationsByOwner(): ilBuddySystemRelationCollection
    {
        return $this->getIgnoredRelations()->filter(function (ilBuddySystemRelation $relation): bool {
            return $relation->getUsrId() === $this->getOwnerId();
        });
    }

    /**
     * Gets all ignored relations: ilBuddySystemRelationCollection
     * @return ilBuddySystemRelationCollection
     */
    public function getIgnoredRelations(): ilBuddySystemRelationCollection
    {
        return $this->getRelations()->filter(static function (ilBuddySystemRelation $relation): bool {
            return $relation->isIgnored();
        });
    }

    /**
     * Returns an array of all user ids the buddy list owner has a relation with
     * @return int[]
     */
    public function getRelationUserIds(): array
    {
        return $this->getRelations()->getKeys();
    }

    public function setOwnerId(int $ownerId): void
    {
        $this->ownerId = $ownerId;
    }

    protected function getRelationTargetUserId(ilBuddySystemRelation $relation): int
    {
        return ($relation->getUsrId() === $this->getOwnerId() ? $relation->getBuddyUsrId() : $relation->getUsrId());
    }

    public function getRelationByUserId(int $usrId): ilBuddySystemRelation
    {
        if ($this->getRelations()->containsKey($usrId)) {
            return $this->getRelations()->get($usrId);
        }

        $relation = new ilBuddySystemRelation(
            ilBuddySystemRelationStateFactory::getInstance()->getInitialState(),
            $this->getOwnerId(),
            $usrId,
            true,
            time()
        );

        $this->getRelations()->set($usrId, $relation);

        return $relation;
    }

    /**
     * @param ilBuddySystemRelation $relation
     * @return self
     * @throws ilBuddySystemException
     */
    public function link(ilBuddySystemRelation $relation): self
    {
        if ($relation->isLinked()) {
            throw new ilBuddySystemRelationStateAlreadyGivenException('buddy_bs_action_already_linked');
        }

        if ($this->getOwnerId() === $relation->getUsrId()) {
            throw new ilBuddySystemException('You can only accept a request when you are not the initiator');
        }

        $relation->link();

        $this->getRepository()->save($relation);
        $this->getRelations()->set($this->getRelationTargetUserId($relation), $relation);

        return $this;
    }

    /**
     * @param ilBuddySystemRelation $relation
     * @return self
     * @throws ilBuddySystemException
     */
    public function unlink(ilBuddySystemRelation $relation): self
    {
        try {
            $relation->unlink();
            $this->getRepository()->save($relation);
            $this->getRelations()->set($this->getRelationTargetUserId($relation), $relation);
        } catch (ilBuddySystemException $e) {
            if ($relation->isUnlinked()) {
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
    public function request(ilBuddySystemRelation $relation): self
    {
        if (ilObjUser::_isAnonymous($this->getRelationTargetUserId($relation))) {
            throw new ilBuddySystemException(sprintf(
                'You cannot add the anonymous user to the list (id: %s)',
                $this->getRelationTargetUserId($relation)
            ));
        }

        $login = ilObjUser::_lookupLogin($this->getRelationTargetUserId($relation));
        if ($login === '') {
            throw new ilBuddySystemException(sprintf(
                'You cannot add a non existing user (id: %s)',
                $this->getRelationTargetUserId($relation)
            ));
        }

        try {
            $relation->request();
            $this->getRepository()->save($relation);
            $this->getRelations()->set($this->getRelationTargetUserId($relation), $relation);
        } catch (ilBuddySystemException $e) {
            if ($relation->isRequested()) {
                throw new ilBuddySystemRelationStateAlreadyGivenException('buddy_bs_action_already_requested');
            }

            throw $e;
        }

        $this->eventHandler->raise(
            'Services/Contact',
            'contactRequested',
            [
                'usr_id' => $this->getRelationTargetUserId($relation)
            ]
        );

        return $this;
    }

    /**
     * @param ilBuddySystemRelation $relation
     * @return self
     * @throws ilBuddySystemException
     */
    public function ignore(ilBuddySystemRelation $relation): self
    {
        try {
            if ($relation->isLinked()) {
                throw new ilBuddySystemRelationStateTransitionException('buddy_bs_action_already_linked');
            }

            if ($this->getOwnerId() === $relation->getUsrId()) {
                throw new ilBuddySystemException('You can only ignore a request when you are not the initiator');
            }

            $relation->ignore();

            $this->getRepository()->save($relation);
            $this->getRelations()->set($this->getRelationTargetUserId($relation), $relation);
        } catch (ilBuddySystemException $e) {
            if ($relation->isIgnored()) {
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
    public function destroy(): self
    {
        $this->getRepository()->destroy();
        $this->getRelations()->clear();
        return $this;
    }
}
