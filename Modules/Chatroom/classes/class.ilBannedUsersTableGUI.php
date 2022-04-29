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
 *********************************************************************/

/**
 * Class ilBannedUsersTableGUI
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilBannedUsersTableGUI extends ilTable2GUI
{
    public function __construct(ilChatroomObjectGUI $a_parent_obj, string $a_parent_cmd)
    {
        $this->setId('banned_users');

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle($this->lng->txt('ban_table_title'));
        $this->setExternalSegmentation(true);
        $this->setExternalSorting(false);

        $this->addColumn('', '', '', true);
        $this->addColumn($this->lng->txt('login'), 'login');
        $this->addColumn($this->lng->txt('firstname'), 'firstname');
        $this->addColumn($this->lng->txt('lastname'), 'lastname');
        $this->addColumn($this->lng->txt('chtr_ban_ts_tbl_head'), 'timestamp');
        $this->addColumn($this->lng->txt('chtr_ban_actor_tbl_head'), 'actor');

        $this->setSelectAllCheckbox('banned_user_id');
        $this->setRowTemplate('tpl.banned_user_table_row.html', 'Modules/Chatroom');

        $this->addMultiCommand('ban-delete', $this->lng->txt('unban'));
    }

    protected function fillRow(array $a_set) : void
    {
        if (is_numeric($a_set['timestamp']) && $a_set['timestamp'] > 0) {
            $a_set['timestamp'] = ilDatePresentation::formatDate(new ilDateTime($a_set['timestamp'], IL_CAL_UNIX));
        }

        parent::fillRow($a_set);
    }
}
