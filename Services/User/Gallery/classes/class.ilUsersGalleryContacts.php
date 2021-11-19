<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilUsersGalleryContacts extends ilAbstractUsersGalleryCollectionProvider
{
    /**
     * @return ilBuddySystemRelationCollection[]
     */
    protected function getRelationSequence() : array
    {
        $requested_for_me = ilBuddyList::getInstanceByGlobalUser()->getRequestRelationsForOwner()->toArray();
        $linked = ilBuddyList::getInstanceByGlobalUser()->getLinkedRelations()->toArray();
        $requested_by_me = ilBuddyList::getInstanceByGlobalUser()->getRequestRelationsByOwner()->toArray();
        $me_ignored = ilBuddyList::getInstanceByGlobalUser()->getIgnoredRelationsByOwner()->toArray();
        $ignored = ilBuddyList::getInstanceByGlobalUser()->getIgnoredRelationsForOwner()->toArray();

        return [$requested_for_me, $linked, $requested_by_me + $me_ignored,  $ignored];
    }

    public function getGroupedCollections(bool $ignore_myself = false) : array
    {
        global $DIC;

        $relations = $this->getRelationSequence();
        $groups = [];

        foreach ($relations as $sorted_relation) {
            $user_data = [];

            foreach ($sorted_relation as $usr_id => $users) {
                /** @var $user ilObjUser */
                if (!($user = ilObjectFactory::getInstanceByObjId($usr_id, false))) {
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

    public function hasRemovableUsers() : bool
    {
        return true;
    }
}
