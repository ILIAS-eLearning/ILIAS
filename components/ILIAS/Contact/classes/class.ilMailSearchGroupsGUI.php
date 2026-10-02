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

/**
* @author Jens Conze
* @ilCtrl_Calls ilMailSearchGroupsGUI: ilBuddySystemGUI
* @ingroup ServicesMail
*/
class ilMailSearchGroupsGUI extends ilMailSearchObjectGUI
{
    public function getObjectType(): string
    {
        return 'grp';
    }

    public function getObjectTypeLabel(): string
    {
        return $this->lng->txt('group');
    }

    public function getSearchTableTitle(): string
    {
        return $this->lng->txt('mail_my_groups');
    }

    public function doesExposeMembers(ilObject $object): bool
    {
        $is_privileged_user = $this->rbacsystem->checkAccess('write', $object->getRefId());

        return $object->getShowMembers() || $is_privileged_user;
    }

    protected function getLocalDefaultRolePrefixes(): array
    {
        return [
            'il_grp_member_',
            'il_grp_admin_',
        ];
    }
}
