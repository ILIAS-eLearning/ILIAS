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
 * @ingroup ServicesCalendar
 */
class ilCalendarSharedRoleListTableGUI extends ilTable2GUI
{
    protected ilRbacReview $rbacreview;
    protected array $role_ids = array();

    public function __construct(object $parent_obj, string $parent_cmd)
    {
        global $DIC;

        $this->rbacreview = $DIC->rbac()->review();

        parent::__construct($parent_obj, $parent_cmd);

        $this->setRowTemplate('tpl.calendar_shared_role_list_row.html', 'Services/Calendar');
        $this->addColumn('', 'id', '1px');
        $this->addColumn($this->lng->txt('objs_role'), 'title', '75%');
        $this->addColumn($this->lng->txt('assigned_members'), 'num', '25%');

        $this->addMultiCommand('shareAssignRoles', $this->lng->txt('cal_share_cal'));
        $this->addMultiCommand('shareAssignRolesEditable', $this->lng->txt('cal_share_cal_editable'));
        $this->setSelectAllCheckbox('role_ids');
        $this->setPrefix('search');
    }

    public function setRoles(array $a_role_ids): void
    {
        $this->role_ids = $a_role_ids;
    }

    /**
     * @inheritDoc
     */
    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('VAL_ID', $a_set['id']);
        $this->tpl->setVariable('TITLE', $a_set['title']);
        if (strlen($a_set['description'])) {
            $this->tpl->setVariable('DESCRIPTION', $a_set['description']);
        }
        $this->tpl->setVariable('NUM_USERS', $a_set['num']);
    }

    public function parse(): void
    {
        $users = $roles = array();
        foreach ($this->role_ids as $id) {
            $tmp_data['title'] = ilObject::_lookupTitle($id);
            $tmp_data['description'] = ilObject::_lookupDescription($id);
            $tmp_data['id'] = $id;
            $tmp_data['num'] = count($this->rbacreview->assignedUsers($id));

            $roles[] = $tmp_data;
        }

        $this->setData($roles);
    }
}
