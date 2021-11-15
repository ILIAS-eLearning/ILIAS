<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;

/**
* @author Jens Conze
* @ilCtrl_Calls ilMailSearchCoursesGUI: ilBuddySystemGUI
* @ingroup ServicesMail
*/
class ilMailSearchCoursesGUI extends ilMailSearchObjectGUI
{
    protected function getObjectType() : string
    {
        return 'crs';
    }

    protected function getLocalDefaultRolePrefixes() : array
    {
        return [
            'il_crs_member_',
            'il_crs_tutor_',
            'il_crs_admin_',
        ];
    }

    protected function doesExposeMembers(ilObject $object) : bool
    {
        $isOffline = !$object->isActivated();
        $showMemberListEnabled = (bool) $object->getShowMembers();
        $hasUntrashedReferences = ilObject::_hasUntrashedReference($object->getId());
        $isPrivilegedUser = $this->rbacsystem->checkAccess('write', $object->getRefId());

        return $hasUntrashedReferences && ((!$isOffline && $showMemberListEnabled) || $isPrivilegedUser);
    }
}
