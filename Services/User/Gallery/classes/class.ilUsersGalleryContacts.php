<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once 'Services/User/Gallery/classes/class.ilAbstractUsersGalleryCollectionProvider.php';
require_once 'Services/Contact/BuddySystem/classes/class.ilBuddyList.php';

/**
 * Class ilUsersGalleryUsers
 */
class ilUsersGalleryContacts extends ilAbstractUsersGalleryCollectionProvider
{
    /**
     * @return array
     */
    protected function getRelationSequence()
    {
        $requested_for_me = ilBuddyList::getInstanceByGlobalUser()->getRequestRelationsForOwner()->toArray();
        $linked           = ilBuddyList::getInstanceByGlobalUser()->getLinkedRelations()->toArray();
        $requested_by_me  = ilBuddyList::getInstanceByGlobalUser()->getRequestRelationsByOwner()->toArray();
        $me_ignored       = ilBuddyList::getInstanceByGlobalUser()->getIgnoredRelationsByOwner()->toArray();
        $ignored          = ilBuddyList::getInstanceByGlobalUser()->getIgnoredRelationsForOwner()->toArray();

        return [$requested_for_me, $linked, $requested_by_me + $me_ignored,  $ignored];
    }

    /**
     * @inheritdoc
     */
    public function getGroupedCollections($ignore_myself = false)
    {
        global $DIC;

        $relations = $this->getRelationSequence();
        $groups    = [];

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

                if ($ignore_myself && $user->getId() == $DIC->user()->getId()) {
                    continue;
                }

                $user_data[$user->getId()] = $user;
            }

            $groups[] = $this->getPopulatedGroup($user_data);
        }

        return $groups;
    }

    /**
     * @inheritdoc
     */
    public function hasRemovableUsers()
    {
        return true;
    }
}
