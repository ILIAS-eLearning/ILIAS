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
class ilUsersGalleryContacts extends ilAbstractUsersGalleryCollectionProvider
{
    /**
     * @return Generator<array<int, ilBuddySystemRelation>>
     */
    protected function getRelationSequence(): Generator
    {
        yield ilBuddyList::getInstanceByGlobalUser()->getRequestRelationsForOwner()->toArray();
        yield ilBuddyList::getInstanceByGlobalUser()->getLinkedRelations()->toArray();
        yield ilBuddyList::getInstanceByGlobalUser()->getRequestRelationsByOwner()->toArray() + ilBuddyList::getInstanceByGlobalUser()->getIgnoredRelationsByOwner()->toArray();
        yield ilBuddyList::getInstanceByGlobalUser()->getIgnoredRelationsForOwner()->toArray();
    }

    public function getGroupedCollections(bool $ignore_myself = false): array
    {
        global $DIC;

        $groups = [];

        foreach ($this->getRelationSequence() as $relations) {
            $user_data = [];

            foreach ($relations as $usr_id => $relation) {
                if (!($user = ilObjectFactory::getInstanceByObjId($usr_id, false)) || !($user instanceof ilObjUser)) {
                    continue;
                }

                if (!$user->getActive()) {
                    continue;
                }

                if ($ignore_myself && $user->getId() === $DIC->user()->getId()) {
                    continue;
                }

                $user_data[$user->getId()] = $user;
            }

            $groups[] = $this->getPopulatedGroup($user_data);
        }

        return $groups;
    }

    public function hasRemovableUsers(): bool
    {
        return true;
    }
}
