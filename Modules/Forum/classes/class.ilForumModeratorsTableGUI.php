<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilForumModeratorsTableGUI
 * @author	Michael Jansen <mjansen@databay.de>
 * @version	$Id$
 * @ingroup ModulesForum
 */
class ilForumModeratorsTableGUI extends ilTable2GUI
{
    /**
     * {@inheritdoc}
     */
    public function __construct($a_parent_obj, $a_parent_cmd = "", $a_template_context = "", $ref_id = 0)
    {
        global $DIC;

        $this->setId('frm_show_mods_tbl_' . $ref_id);
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

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
        $this->addMultiCommand('detachModeratorRole', $this->lng->txt('frm_detach_moderator_role'));
    }
}
