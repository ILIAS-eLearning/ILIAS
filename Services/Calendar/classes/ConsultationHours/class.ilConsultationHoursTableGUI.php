<?php

declare(strict_types=1);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Consultation hours administration
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesCalendar
 */
class ilConsultationHoursTableGUI extends ilTable2GUI
{
    private int $user_id = 0;
    private bool $has_groups = false;

    public function __construct(object $a_gui, string $a_cmd, int $a_user_id)
    {
        $this->user_id = $a_user_id;

        $this->has_groups = (bool) ilConsultationHourGroups::getCountGroupsOfUser($a_user_id);

        $this->setId('chtg_' . $this->getUserId());
        parent::__construct($a_gui, $a_cmd);

        $this->addColumn('', 'f', '1');
        $this->addColumn($this->lng->txt('appointment'), 'start');

        if ($this->hasGroups()) {
            $this->addColumn($this->lng->txt('cal_ch_grp_header'), 'group');
        }

        $this->addColumn($this->lng->txt('title'), 'title');
        $this->addColumn($this->lng->txt('cal_ch_num_bookings'), 'num_bookings');
        $this->addColumn($this->lng->txt('cal_ch_bookings'), 'participants');
        $this->addColumn($this->lng->txt('cal_ch_target_object'), 'target');
        $this->addColumn('');

        $this->setRowTemplate('tpl.ch_upcoming_row.html', 'Services/Calendar');
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject(), $this->getParentCmd()));
        $this->setTitle($this->lng->txt('cal_ch_ch'));

        $this->enable('sort');
        $this->enable('header');
        $this->enable('numinfo');

        $this->setDefaultOrderField('start');
        $this->setSelectAllCheckbox('apps');
        $this->setShowRowsSelector(true);
        $this->addMultiCommand('edit', $this->lng->txt('edit'));
        $this->addMultiCommand('searchUsersForAppointments', $this->lng->txt('cal_ch_assign_participants'));
        $this->addMultiCommand('confirmDelete', $this->lng->txt('delete'));
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function hasGroups(): bool
    {
        return $this->has_groups;
    }

    /**
     * @inheritDoc
     */
    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('VAL_ID', $a_set['id']);
        $this->tpl->setVariable('START', $a_set['start_p']);
        $this->tpl->setVariable('TITLE', $a_set['title']);

        if ($this->hasGroups()) {
            $this->tpl->setVariable('TITLE_GROUP', $a_set['group']);
        }

        $this->tpl->setVariable('NUM_BOOKINGS', $a_set['num_bookings']);

        foreach ((array) ($a_set['target_links'] ?? []) as $link) {
            $this->tpl->setCurrentBlock('links');
            $this->tpl->setVariable('TARGET', $link['title']);
            $this->tpl->setVariable('URL_TARGET', $link['link']);
            $this->tpl->parseCurrentBlock();
        }
        if ($a_set['bookings']) {
            foreach ($a_set['bookings'] as $user_id => $name) {
                $user_profile_prefs = ilObjUser::_getPreferences($user_id);
                if ($user_profile_prefs["public_profile"] == "y") {
                    $this->tpl->setCurrentBlock('booking_with_link');
                    $this->ctrl->setParameter($this->getParentObject(), 'user', $user_id);
                    $this->tpl->setVariable(
                        'URL_BOOKING',
                        $this->ctrl->getLinkTarget($this->getParentObject(), 'showprofile')
                    );
                } else {
                    $this->tpl->setCurrentBlock('booking_without_link');
                }
                $this->ctrl->setParameter($this->getParentObject(), 'user', '');
                $this->tpl->setVariable('TXT_BOOKING', $name);
                $this->tpl->parseCurrentBlock();
            }
        }

        $this->tpl->setVariable('BOOKINGS', implode(', ', $a_set['bookings']));

        $list = new ilAdvancedSelectionListGUI();
        $list->setId('act_cht_' . $a_set['id']);
        $list->setListTitle($this->lng->txt('actions'));

        $this->ctrl->setParameter($this->getParentObject(), 'apps', $a_set['id']);
        $list->addItem(
            $this->lng->txt('edit'),
            '',
            $this->ctrl->getLinkTarget($this->getParentObject(), 'edit')
        );
        $list->addItem(
            $this->lng->txt('cal_ch_assign_participants'),
            '',
            $this->ctrl->getLinkTargetByClass('ilRepositorySearchGUI', '')
        );
        $list->addItem(
            $this->lng->txt('delete'),
            '',
            $this->ctrl->getLinkTarget($this->getParentObject(), 'confirmDelete')
        );
        $this->tpl->setVariable('ACTIONS', $list->getHTML());
    }

    public function parse()
    {
        $data = array();
        $counter = 0;
        foreach (ilConsultationHourAppointments::getAppointments($this->getUserId()) as $app) {
            $data[$counter]['id'] = $app->getEntryId();
            $data[$counter]['title'] = $app->getTitle();
            $data[$counter]['description'] = $app->getDescription();
            $data[$counter]['start'] = $app->getStart()->get(IL_CAL_UNIX);
            $data[$counter]['start_p'] = ilDatePresentation::formatPeriod($app->getStart(), $app->getEnd());

            $booking = new ilBookingEntry($app->getContextId());

            $booked_user_ids = $booking->getCurrentBookings($app->getEntryId());
            $booked_user_ids = ilUtil::_sortIds($booked_user_ids, 'usr_data', 'lastname', 'usr_id');
            $users = array();
            $data[$counter]['participants'] = '';
            $user_counter = 0;
            foreach ($booked_user_ids as $user_id) {
                if (!$user_counter) {
                    $name = ilObjUser::_lookupName($user_id);
                    $data[$counter]['participants'] = $name['lastname'];
                }
                $users[$user_id] = ilObjUser::_lookupFullname($user_id);
                $user_counter++;
            }
            $data[$counter]['bookings'] = $users;
            $data[$counter]['num_bookings'] = $booking->getNumberOfBookings();

            $data[$counter]['group'] = '';
            $group_id = $booking->getBookingGroup();
            if ($this->hasGroups() && $group_id) {
                $data[$counter]['group'] = ilConsultationHourGroups::lookupTitle($group_id);
            }

            // obj assignments
            $refs_counter = 0;
            $obj_ids = ilUtil::_sortIds($booking->getTargetObjIds(), 'object_data', 'title', 'obj_id');
            foreach ($obj_ids as $obj_id) {
                if ($refs_counter) {
                    $data[$counter]['target'] = ilObject::_lookupTitle($obj_id);
                }

                $refs = ilObject::_getAllReferences($obj_id);
                $data[$counter]['target_links'][$refs_counter]['title'] = ilObject::_lookupTitle($obj_id);
                $data[$counter]['target_links'][$refs_counter]['link'] = ilLink::_getLink(end($refs));
                ++$refs_counter;
            }
            $counter++;
        }
        $this->setData($data);
    }
}
