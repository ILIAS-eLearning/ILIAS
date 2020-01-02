<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Mail/classes/Address/Type/class.ilBaseMailAddressType.php';
require_once 'Modules/Group/classes/class.ilObjGroup.php';

/**
 * Class ilMailGroupAddressType
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailGroupAddressType extends ilBaseMailAddressType
{
    /**
     * {@inheritdoc}
     */
    public function isValid($a_sender_id)
    {
        return ilUtil::groupNameExists(substr($this->address->getMailbox(), 1));
    }

    /**
     * {@inheritdoc}
     */
    public function resolve()
    {
        $usr_ids = array();

        $possibleGroupTitle = substr($this->address->getMailbox(), 1);
        $possibleGroupObjId = ilObjGroup::_lookupIdByTitle($possibleGroupTitle);

        $grp_object = null;
        foreach (ilObject::_getAllReferences($possibleGroupObjId) as $ref_id) {
            $grp_object = ilObjectFactory::getInstanceByRefId($ref_id);
            break;
        }

        if ($grp_object instanceof ilObjGroup) {
            foreach ($grp_object->getGroupMemberIds() as $usr_id) {
                $usr_ids[] = $usr_id;
            }

            ilLoggerFactory::getLogger('mail')->debug(sprintf(
                "Found the following group member user ids for address (object title) '%s' and obj_id %s: %s",
                $possibleGroupTitle,
                $possibleGroupObjId,
                implode(', ', array_unique($usr_ids))
            ));
        } else {
            ilLoggerFactory::getLogger('mail')->debug(sprintf(
                "Did not find any group object for address (object title) '%s'",
                $possibleGroupTitle
            ));
        }

        return array_unique($usr_ids);
    }
}
