<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once 'Services/User/Gallery/classes/class.ilAbstractUsersGalleryCollectionProvider.php';

/**
 * Class ilUsersGalleryParticipants
 */
class ilUsersGalleryParticipants extends ilAbstractUsersGalleryCollectionProvider
{
    /**
     * @var ilParticipants
     */
    protected $participants;

    /**
     * @var array
     */
    protected $users = array();

    /**
     * @param ilParticipants $participants
     */
    public function __construct(ilParticipants $participants)
    {
        $this->participants = $participants;
    }

    /**
     * @param int[] $usr_ids
     * @return ilObjUser[]
     */
    protected function getUsers(array $usr_ids)
    {
        $users = [];

        foreach ($usr_ids as $usr_id) {
            if (isset($this->users[$usr_id])) {
                continue;
            }

            /**
             * @var $user ilObjUser
             */
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

    /**
     * @inheritdoc
     */
    public function getGroupedCollections()
    {
        /**
         * @var $DIC ILIAS\DI\Container
         */
        global $DIC;

        $groups = [];

        foreach ([
            array($this->participants->getContacts(), true, $DIC->language()->txt('crs_mem_contact')),
            array($this->participants->getAdmins()  , false, ''),
            array($this->participants->getTutors()  , false, ''),
            array($this->participants->getMembers() , false, '')
        ] as $users) {
            $group = $this->getPopulatedGroup($this->getUsers($users[0]));
            $group->setHighlighted($users[1]);
            $group->setLabel($users[2]);
            $groups[] = $group;
        }

        return $groups;
    }
}
