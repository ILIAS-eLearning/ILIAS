<?php

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

declare(strict_types=1);

class ilMailGroupAddressType extends ilBaseMailAddressType
{
    protected function isValid(int $sender_id): bool
    {
        return $this->type_helper->doesGroupNameExists(substr($this->address->getMailbox(), 1));
    }

    public function resolve(): array
    {
        $usr_ids = [];

        $possible_grp_title = substr($this->address->getMailbox(), 1);
        $possible_grp_obj_id = $this->type_helper->getGroupObjIdByTitle($possible_grp_title);

        $group = null;
        foreach ($this->type_helper->getAllRefIdsForObjId($possible_grp_obj_id) as $ref_id) {
            $group = $this->type_helper->getInstanceByRefId($ref_id);
            break;
        }

        if ($group instanceof ilObjGroup) {
            $usr_ids = $group->getGroupMemberIds();

            $this->logger->debug(sprintf(
                "Found the following group member user ids for address (object title) '%s' and obj_id %s: %s",
                $possible_grp_title,
                $possible_grp_obj_id,
                implode(', ', array_unique($usr_ids))
            ));
        } else {
            $this->logger->debug(sprintf(
                "Did not find any group object for address (object title) '%s'",
                $possible_grp_title
            ));
        }

        return array_unique($usr_ids);
    }
}
