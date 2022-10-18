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
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilConsultationHourBookingTableGUI extends ilTable2GUI
{
    private int $user_id = 0;

    private ilDateTime $today;

    public function __construct(object $a_parent_obj, string $a_parent_cmd, int $a_user_id)
    {
        $this->user_id = $a_user_id;
        $this->setId('chboo_' . $this->user_id);
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->initTable();
        $this->today = new ilDateTime(time(), IL_CAL_UNIX);
    }

    /**
     * Init table
     */
    protected function initTable(): void
    {
        $this->setRowTemplate('tpl.ch_booking_row.html', 'Services/Calendar');

        $this->setTitle($this->lng->txt('cal_ch_bookings_tbl'));
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject(), $this->getParentCmd()));

        $this->addColumn('', '', '1px');
        $this->addColumn($this->lng->txt('cal_start'), 'start');
        $this->addColumn($this->lng->txt('name'), 'name');
        $this->addColumn($this->lng->txt('cal_ch_booking_message_tbl'), 'comment');
        $this->addColumn($this->lng->txt('title'), 'title');
        $this->addColumn($this->lng->txt('actions'), '');

        $this->enable('sort');
        $this->enable('header');
        $this->enable('num_info');

        $this->setDefaultOrderField('start');
        $this->setSelectAllCheckbox('bookuser');
        $this->setShowRowsSelector(true);
        $this->addMultiCommand('confirmRejectBooking', $this->lng->txt('cal_ch_reject_booking'));
        $this->addMultiCommand('confirmDeleteBooking', $this->lng->txt('cal_ch_delete_booking'));
    }

    /**
     * @inheritDoc
     */
    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('START', $a_set['start_str']);
        $this->tpl->setVariable('NAME', $a_set['name']);
        $this->tpl->setVariable('COMMENT', $a_set['comment']);
        $this->tpl->setVariable('TITLE', $a_set['title']);
        $this->tpl->setVariable('VAL_ID', $a_set['id']);

        $list = new ilAdvancedSelectionListGUI();
        $list->setId('act_chboo_' . $a_set['id']);
        $list->setListTitle($this->lng->txt('actions'));

        $this->ctrl->setParameter($this->getParentObject(), 'bookuser', $a_set['id']);

        $start = new ilDateTime($a_set['start'], IL_CAL_UNIX);
        if (ilDateTime::_after($start, $this->today, IL_CAL_DAY)) {
            $list->addItem(
                $this->lng->txt('cal_ch_reject_booking'),
                '',
                $this->ctrl->getLinkTarget($this->getParentObject(), 'confirmRejectBooking')
            );
        }
        $list->addItem(
            $this->lng->txt('cal_ch_delete_booking'),
            '',
            $this->ctrl->getLinkTarget($this->getParentObject(), 'confirmDeleteBooking')
        );
        $this->tpl->setVariable('ACTIONS', $list->getHTML());
    }

    /**
     * Parse Groups
     * @param int[]
     */
    public function parse(array $appointments): void
    {
        $rows = array();
        $counter = 0;
        foreach ($appointments as $app) {
            $cal_entry = new ilCalendarEntry($app);

            foreach (ilBookingEntry::lookupBookingsForAppointment($app) as $user_id) {
                $rows[$counter]['name'] = ilUserUtil::getNamePresentation(
                    $user_id,
                    true,
                    true,
                    $this->ctrl->getLinkTarget($this->getParentObject(), $this->getParentCmd()),
                    true,
                    true
                );

                $message = ilBookingEntry::lookupBookingMessage($app, $user_id);
                $rows[$counter]['comment'] = '';
                if (strlen(trim($message))) {
                    $rows[$counter]['comment'] = ('"' . $message . '"');
                }
                $rows[$counter]['title'] = $cal_entry->getTitle();
                $rows[$counter]['start'] = $cal_entry->getStart()->get(IL_CAL_UNIX);
                $rows[$counter]['start_str'] = ilDatePresentation::formatDate($cal_entry->getStart());
                $rows[$counter]['id'] = $app . '_' . $user_id;
                ++$counter;
            }
        }
        $this->setData($rows);
    }
}
