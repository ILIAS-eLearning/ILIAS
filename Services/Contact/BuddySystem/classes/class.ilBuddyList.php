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

    public static function getInstanceByGlobalUser(ilObjUser $user = null): self
    {
        global $DIC;

        if (null === $user) {
            $user = $DIC->user();
        }

        return self::getInstanceByUserId($user->getId());
    }

    protected function __construct(int $ownerId, ilAppEventHandler $event_handler = null)
    {
        global $DIC;

        $this->eventHandler = $event_handler ?? $DIC['ilAppEventHandler'];

        $this->setOwnerId($ownerId);
        $this->setRepository(new ilBuddySystemRelationRepository($this->getOwnerId()));
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
     */
    public function getOwnerId(): int
    {
        return $this->ownerId;
    }

    /**
     * Gets all linked/approved relations
     */
    public function getLinkedRelations(): ilBuddySystemRelationCollection
    {
        return $this->getRelations()->filter(static function (ilBuddySystemRelation $relation): bool {
            return $relation->isLinked();
        });
    }

    /**
     * Gets all requested relations the buddy list owner has to interact with
     */
    public function getRequestRelationsForOwner(): ilBuddySystemRelationCollection
    {
        return $this->getRequestedRelations()->filter(function (ilBuddySystemRelation $relation): bool {
            return $relation->getBuddyUsrId() === $this->getOwnerId();
        });
    }

    /**
     * Gets all requested relations the buddy list owner initiated
     */
    public function getRequestRelationsByOwner(): ilBuddySystemRelationCollection
    {
        return $this->getRequestedRelations()->filter(function (ilBuddySystemRelation $relation): bool {
            return $relation->getUsrId() === $this->getOwnerId();
        });
    }

    /**
     * Gets all requested relations
     */
    public function getRequestedRelations(): ilBuddySystemRelationCollection
    {
        return $this->getRelations()->filter(static function (ilBuddySystemRelation $relation): bool {
            return $relation->isRequested();
        });
    }

    /**
     * Gets all ignored relations the buddy list owner has to interact with
     */
    public function getIgnoredRelationsForOwner(): ilBuddySystemRelationCollection
    {
        return $this->getIgnoredRelations()->filter(function (ilBuddySystemRelation $relation): bool {
            return $relation->getBuddyUsrId() === $this->getOwnerId();
        });
    }

    /**
     * Gets all ignored relations the buddy list owner initiated
     */
    public function getIgnoredRelationsByOwner(): ilBuddySystemRelationCollection
    {
        return $this->getIgnoredRelations()->filter(function (ilBuddySystemRelation $relation): bool {
            return $relation->getUsrId() === $this->getOwnerId();
        });
    }

    /**
     * Gets all ignored relations: ilBuddySystemRelationCollection
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
                throw new ilBuddySystemRelationStateAlreadyGivenException('buddy_bs_action_already_unlinked', $e->getCode(), $e);
            }

            throw $e;
        }

        return $this;
    }

    /**
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
                throw new ilBuddySystemRelationStateAlreadyGivenException('buddy_bs_action_already_requested', $e->getCode(), $e);
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
                throw new ilBuddySystemRelationStateAlreadyGivenException('buddy_bs_action_already_ignored', $e->getCode(), $e);
            }

            throw $e;
        }

        return $this;
    }

    /**
     * Removes all buddy system references of the user (persistently)
     */
    public function destroy(): self
    {
        $this->getRepository()->destroy();
        $this->getRelations()->clear();
        return $this;
    }
}
