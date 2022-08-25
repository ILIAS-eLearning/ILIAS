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
    protected function getUsers(array $usr_ids): array
    {
        $users = [];

        foreach ($usr_ids as $usr_id) {
            if (isset($this->users[$usr_id])) {
                continue;
            }

            if (!($user = ilObjectFactory::getInstanceByObjId($usr_id, false)) || !($user instanceof ilObjUser)) {
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

    public function getGroupedCollections(): array
    {
        /** @var ILIAS\DI\Container $DIC */
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
