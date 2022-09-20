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
* @author Jens Conze
* @ilCtrl_Calls ilMailSearchGroupsGUI: ilBuddySystemGUI
* @ingroup ServicesMail
*/
class ilMailSearchGroupsGUI extends ilMailSearchObjectGUI
{
    protected function getObjectType(): string
    {
        return 'grp';
    }

    protected function getLocalDefaultRolePrefixes(): array
    {
        return [
            'il_grp_member_',
            'il_grp_admin_',
        ];
    }

    protected function doesExposeMembers(ilObject $object): bool
    {
        $showMemberListEnabled = (bool) $object->getShowMembers();
        $hasUntrashedReferences = ilObject::_hasUntrashedReference($object->getId());
        $isPrivilegedUser = $this->rbacsystem->checkAccess('write', $object->getRefId());

        return $hasUntrashedReferences && ($showMemberListEnabled || $isPrivilegedUser);
    }
}
