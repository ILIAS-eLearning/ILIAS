<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */
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

    public function getGroupedCollections(bool $ignore_myself = false) : array // Missing array type.
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
