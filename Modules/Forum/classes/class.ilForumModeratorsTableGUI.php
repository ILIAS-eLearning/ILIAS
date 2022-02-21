<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilForumModeratorsTableGUI
 * @author    Michael Jansen <mjansen@databay.de>
 * @ingroup ModulesForum
 */
class ilForumModeratorsTableGUI extends ilTable2GUI
{
    public function __construct(object $a_parent_obj, string $a_parent_cmd, int $ref_id)
    {
        global $DIC;

        $this->setId('frm_show_mods_tbl_' . $ref_id);
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setFormAction($DIC->ctrl()->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setTitle($this->lng->txt('frm_moderators'));
        $this->setRowTemplate('tpl.forum_moderators_table_row.html', 'Modules/Forum');
        $this->setDefaultOrderField('login');
        $this->setNoEntriesText($this->lng->txt('frm_moderators_not_exist_yet'));

        $this->addColumn('', 'check', '1%', true);
        $this->addColumn($this->lng->txt('login'), 'login', '30%');
        $this->addColumn($this->lng->txt('firstname'), 'firstname', '30%');
        $this->addColumn($this->lng->txt('lastname'), 'lastname', '30%');

        $this->setSelectAllCheckbox('usr_id');
        $this->addMultiCommand('detachModeratorRole', $this->lng->txt('remove'));
    }
}
