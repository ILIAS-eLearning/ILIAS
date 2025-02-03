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
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * @ingroup ServicesCalendar
 */
class ilCalendarSharedUserListTableGUI extends ilTable2GUI
{
    protected array $user_ids = array();

    public function __construct(object $parent_obj, string $parent_cmd)
    {
        parent::__construct($parent_obj, $parent_cmd);

        $this->setRowTemplate('tpl.calendar_shared_user_list_row.html', 'components/ILIAS/Calendar');

        $this->addColumn('', 'id', '1px');
        $this->addColumn($this->lng->txt('name'), 'last_firstname', '60%');
        $this->addColumn($this->lng->txt('login'), 'login', '40%');

        $this->addMultiCommand('shareAssign', $this->lng->txt('cal_share_cal'));
        $this->addMultiCommand('shareAssignEditable', $this->lng->txt('cal_share_cal_editable'));
        $this->setSelectAllCheckbox('user_ids');
        $this->setPrefix('search');
    }

    public function setUsers(array $a_user_ids)
    {
        $this->user_ids = $a_user_ids;
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('VAL_ID', $a_set['id']);

        $this->tpl->setVariable('LASTNAME', $a_set['lastname']);
        $this->tpl->setVariable('FIRSTNAME', $a_set['firstname']);
        $this->tpl->setVariable('LOGIN', $a_set['login']);
    }

    public function parse(): void
    {
        $users = array();
        foreach ($this->user_ids as $id) {
            $id = (int) $id;
            $name = ilObjUser::_lookupName($id);

            $tmp_data['id'] = $id;
            $tmp_data['lastname'] = $name['lastname'];
            $tmp_data['firstname'] = $name['firstname'];
            $tmp_data['login'] = ilObjUser::_lookupLogin($id);
            $tmp_data['last_firstname'] = $tmp_data['lastname'] . $tmp_data['firstname'] . $tmp_data['login'];

            $users[] = $tmp_data;
        }
        $this->setData($users);
    }
}
