<?php declare(strict_types=1);

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
 ********************************************************************
 */

class ilForumNotificationParentMembersTableGUI extends ilTable2GUI
{
    public function __construct(ilForumSettingsGUI $cmd_class_instance, string $cmd, ilObjForum $forum, string $type)
    {
        $this->setId('frm_noti_mem_' . $type . '_' . $forum->getRefId());
        parent::__construct($cmd_class_instance, $cmd);

        $this->setFormAction($this->ctrl->getFormAction($cmd_class_instance, $cmd));
        $this->setTitle($this->lng->txt(strtolower($type)));

        $this->addColumn('', '', '1%', true);
        $this->addColumn($this->lng->txt('login'), '', '10%');
        $this->addColumn($this->lng->txt('firstname'), '', '10%');
        $this->addColumn($this->lng->txt('lastname'), '', '10%');
        $this->addColumn($this->lng->txt('allow_user_toggle_noti'), '', '10%');
        $this->setSelectAllCheckbox('user_id');

        $this->setRowTemplate('tpl.forums_members_row.html', 'Modules/Forum');
        $this->addMultiCommand('enableHideUserToggleNoti', $this->lng->txt('enable_hide_user_toggle'));
        $this->addMultiCommand('disableHideUserToggleNoti', $this->lng->txt('disable_hide_user_toggle'));
    }
}
