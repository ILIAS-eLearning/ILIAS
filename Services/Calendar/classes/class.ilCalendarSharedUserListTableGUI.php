<?php

declare(strict_types=1);
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
        |                                                                             |
        | This program is free software; you can redistribute it and/or               |
        | modify it under the terms of the GNU General Public License                 |
        | as published by the Free Software Foundation; either version 2              |
        | of the License, or (at your option) any later version.                      |
        |                                                                             |
        | This program is distributed in the hope that it will be useful,             |
        | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
        | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
        | GNU General Public License for more details.                                |
        |                                                                             |
        | You should have received a copy of the GNU General Public License           |
        | along with this program; if not, write to the Free Software                 |
        | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
        +-----------------------------------------------------------------------------+
*/

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

        $this->setRowTemplate('tpl.calendar_shared_user_list_row.html', 'Services/Calendar');

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
