<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUsersGalleryParticipants
 */
class ilUsersGalleryParticipants extends ilAbstractUsersGalleryCollectionProvider
{
    protected ilParticipants $participants;
    /** @var array<int, bool> */
    protected array $users = [];

    public function __construct(ilParticipants $participants)
    {
        $this->participants = $participants;
    }

    /**
     * @param int[] $usr_ids
     * @return array<int, ilObjUser>
     */
    protected function getUsers(array $usr_ids) : array
    {
        $users = [];

        foreach ($usr_ids as $usr_id) {
            if (isset($this->users[$usr_id])) {
                continue;
            }

            /** @var $user ilObjUser */
            if (!($user = ilObjectFactory::getInstanceByObjId($usr_id, false))) {
                continue;
            }

            if (!$user->getActive()) {
                continue;
            }

            $users[$user->getId()] = $user;
            $this->users[$user->getId()] = true;
        }

        return $users;
    }

    public function getGroupedCollections() : array
    {
        /**
         * @var $DIC ILIAS\DI\Container
         */
        global $DIC;

        $groups = [];

        foreach ([
            [$this->participants->getContacts(), true, $DIC->language()->txt('crs_mem_contact')],
            [$this->participants->getAdmins()  , false, ''],
            [$this->participants->getTutors()  , false, ''],
            [$this->participants->getMembers() , false, '']
        ] as $users) {
            $group = $this->getPopulatedGroup($this->getUsers($users[0]));
            $group->setHighlighted($users[1]);
            $group->setLabel($users[2]);
            $groups[] = $group;
        }

        return $groups;
    }
}
