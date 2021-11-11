<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* @author Jens Conze
* @ilCtrl_Calls ilMailSearchGroupsGUI: ilBuddySystemGUI
* @ingroup ServicesMail
*/
class ilMailSearchGroupsGUI extends ilMailSearchObjectGUI
{
    protected function getObjectType() : string
    {
        return 'grp';
    }

    protected function getLocalDefaultRolePrefixes() : array
    {
        return [
            'il_grp_member_',
            'il_grp_admin_',
        ];
    }

    protected function doesExposeMembers(ilObject $object) : bool
    {
        $showMemberListEnabled = (bool) $object->getShowMembers();
        $hasUntrashedReferences = ilObject::_hasUntrashedReference($object->getId());
        $isPrivilegedUser = $this->rbacsystem->checkAccess('write', $object->getRefId());

        return $hasUntrashedReferences && ($showMemberListEnabled || $isPrivilegedUser);
    }
}
