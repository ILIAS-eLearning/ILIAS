<?php declare(strict_types=1);
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
class ilCalendarAppointmentsTableGUI extends ilTable2GUI
{
    private int $cat_id = 0;
    private ilCalendarCategories $categories;
    private bool $is_editable = false;

    public function __construct(object $a_parent_obj, string $a_parent_cmd, int $a_category_id)
    {
        global $DIC;

        $this->categories = ilCalendarCategories::_getInstance();
        $this->cat_id = $a_category_id;
        $this->is_editable = $this->categories->isEditable($this->cat_id);

        $this->setId('calcalapps');
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->lng->loadLanguageModule('dateplaner');

        $this->setFormName('appointments');
        $this->addColumn('', 'f', "1");
        $this->addColumn($this->lng->txt('cal_start'), 'dt_sort', "30%");
        $this->addColumn($this->lng->txt('title'), 'title', "60%");
        $this->addColumn($this->lng->txt('cal_duration'), 'duration', "20%");
        $this->addColumn($this->lng->txt('cal_recurrences'), 'frequence', "10%");

        if ($this->is_editable) {
            $this->addMultiCommand('askDeleteAppointments', $this->lng->txt('delete'));
            $this->enable('select_all');
        } else {
            $this->disable('select_all');
        }

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.show_appointment_row.html", "Services/Calendar");

        $this->setShowRowsSelector(true);
        $this->enable('sort');
        $this->enable('header');
        $this->enable('numinfo');

        $this->setDefaultOrderField('dt_sort');
        $this->setSelectAllCheckbox('appointments');
    }

    protected function fillRow(array $a_set) : void
    {
        if ($a_set['deletable']) {
            $this->tpl->setVariable('VAL_ID', $a_set['id']);
        }

        $this->tpl->setVariable('VAL_DESCRIPTION', $a_set['description']);

        $this->tpl->setVariable('VAL_TITLE_LINK', $a_set['title']);
        $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'app_id', $a_set['id']);
        $this->tpl->setVariable('VAL_LINK', $this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui', 'edit'));

        switch ($a_set['frequence']) {
            case ilCalendarRecurrence::FREQ_DAILY:
                $this->tpl->setVariable('VAL_FREQUENCE', $this->lng->txt('cal_daily'));
                break;

            case ilCalendarRecurrence::FREQ_WEEKLY:
                $this->tpl->setVariable('VAL_FREQUENCE', $this->lng->txt('cal_weekly'));
                break;

            case ilCalendarRecurrence::FREQ_MONTHLY:
                $this->tpl->setVariable('VAL_FREQUENCE', $this->lng->txt('cal_monthly'));
                break;

            case ilCalendarRecurrence::FREQ_YEARLY:
                $this->tpl->setVariable('VAL_FREQUENCE', $this->lng->txt('cal_yearly'));
                break;

            default:
                //$this->tpl->setVariable('VAL_FREQUENCE',$this->lng->txt('cal_no_recurrence'));
                $this->tpl->setVariable('VAL_FREQUENCE', '');
                break;
        }
        $this->tpl->setVariable('VAL_BEGIN', $a_set['dt']);
        if ($a_set['duration']) {
            $this->tpl->setVariable('VAL_DURATION', ilDatePresentation::secondsToString($a_set['duration']));
        } else {
            $this->tpl->setVariable('VAL_DURATION', '');
        }
    }

    /**
     * set appointments
     * @param int[]
     */
    public function setAppointments(array $a_apps) : void
    {
        $cat = new ilCalendarCategory($this->cat_id);

        $appointments = [];
        foreach ($a_apps as $cal_entry_id) {
            $entry = new ilCalendarEntry($cal_entry_id);
            $rec = ilCalendarRecurrences::_getFirstRecurrence($entry->getEntryId());

            // booking
            $title = '';
            if ($cat->getType() == ilCalendarCategory::TYPE_CH) {
                $book = new ilBookingEntry($entry->getContextId());
                if ($book) {
                    $title = $entry->getTitle();
                    if ($book->isOwner()) {
                        $max = $book->getNumberOfBookings();
                        $current = $book->getCurrentNumberOfBookings($entry->getEntryId());
                        if ($max > 1) {
                            $title .= ' (' . $current . '/' . $max . ')';
                        } elseif ($current == $max) {
                            $title .= ' (' . $this->lng->txt('cal_booked_out') . ')';
                        } else {
                            $title .= ' (' . $this->lng->txt('cal_book_free') . ')';
                        }
                    } elseif ($book->hasBooked($entry->getEntryId())) {
                        $title .= ' (' . $this->lng->txt('cal_date_booked') . ')';
                    }
                }
            } else {
                $title = $entry->getPresentationTitle();
            }

            $tmp_arr['id'] = $entry->getEntryId();
            $tmp_arr['title'] = $title;
            $tmp_arr['description'] = $entry->getDescription();
            $tmp_arr['fullday'] = $entry->isFullday();
            $tmp_arr['begin'] = $entry->getStart()->get(IL_CAL_UNIX);
            $tmp_arr['end'] = $entry->getEnd()->get(IL_CAL_UNIX);

            $tmp_arr['dt_sort'] = $entry->getStart()->get(IL_CAL_UNIX);
            $tmp_arr['dt'] = ilDatePresentation::formatPeriod(
                $entry->getStart(),
                $entry->getEnd()
            );

            $tmp_arr['duration'] = $tmp_arr['end'] - $tmp_arr['begin'];
            if ($tmp_arr['fullday']) {
                $tmp_arr['duration'] += (60 * 60 * 24);
            }

            if (!$tmp_arr['fullday'] and $tmp_arr['end'] == $tmp_arr['begin']) {
                $tmp_arr['duration'] = '';
            }
            $tmp_arr['frequence'] = $rec->getFrequenceType();
            $tmp_arr['deletable'] = (!$entry->isAutoGenerated() and $this->is_editable);

            $appointments[] = $tmp_arr;
        }
        $this->setData($appointments);
    }
}
