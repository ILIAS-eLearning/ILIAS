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
