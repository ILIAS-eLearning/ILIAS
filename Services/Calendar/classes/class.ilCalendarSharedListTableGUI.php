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
