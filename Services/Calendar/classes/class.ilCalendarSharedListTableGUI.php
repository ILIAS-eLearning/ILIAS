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
class ilCalendarSharedListTableGUI extends ilTable2GUI
{
    protected int $calendar_id;

    public function __construct(object $parent_obj, string $parent_cmd)
    {
        parent::__construct($parent_obj, $parent_cmd);

        $this->setRowTemplate('tpl.calendar_shared_list_row.html', 'Services/Calendar');
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject()));

        $this->addColumn('', 'id', '1px');
        $this->addColumn($this->lng->txt('type'), 'type', '1px');
        $this->addColumn($this->lng->txt('title'), 'title', '80%');
        $this->addColumn($this->lng->txt('cal_shared_access_table_col'), 'access', '20%');

        $this->addMultiCommand('shareDeassign', $this->lng->txt('cal_unshare_cal'));
        $this->setPrefix('shared');
        $this->setSelectAllCheckbox('obj_ids');
    }

    /**
     * set id
     */
    public function setCalendarId(int $a_calendar_id): void
    {
        $this->calendar_id = $a_calendar_id;
    }

    /**
     * @inheritDoc
     */
    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('VAL_ID', $a_set['obj_id']);
        $this->tpl->setVariable('NAME', $a_set['title']);

        if (strlen($a_set['description'])) {
            $this->tpl->setVariable('DESCRIPTION', $a_set['description']);
        }

        $this->tpl->setVariable('TYPE_IMG', ilUtil::getImagePath('icon_' . $a_set['type'] . '.svg'));
        $this->tpl->setVariable('ALT_IMG', $this->lng->txt('obj_' . $a_set['type']));

        if ($a_set['writable']) {
            $this->tpl->setVariable('CAL_ACCESS', $this->lng->txt('cal_shared_access_read_write'));
        } else {
            $this->tpl->setVariable('CAL_ACCESS', $this->lng->txt('cal_shared_access_read_only'));
        }
    }

    public function parse(): void
    {
        $shared = new ilCalendarShared($this->calendar_id);
        $items = array();
        foreach ($shared->getShared() as $item) {
            switch ($item['obj_type']) {
                case ilCalendarShared::TYPE_USR:
                    $data['type'] = 'usr';

                    $name = ilObjUser::_lookupName($item['obj_id']);
                    $data['title'] = $name['lastname'] . ', ' . $name['firstname'];
                    $data['description'] = '';
                    break;

                case ilCalendarShared::TYPE_ROLE:
                    $data['type'] = 'role';
                    $data['title'] = ilObject::_lookupTitle($item['obj_id']);
                    $data['description'] = ilObject::_lookupDescription($item['obj_id']);
                    break;
            }
            $data['obj_id'] = $item['obj_id'];
            $data['create_date'] = $item['create_date'];
            $data['writable'] = $item['writable'];

            $items[] = $data;
        }
        $this->setData($items);
    }
}
