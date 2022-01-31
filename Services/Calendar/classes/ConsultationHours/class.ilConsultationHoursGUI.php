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
 * Consultation hours editor
 * @author      Stefan Meyer <smeyer.ilias@gmx.de>
 * @ilCtrl_Calls: ilConsultationHoursGUI ilPublicUserProfileGUI, ilRepositorySearchGUI
 */
class ilConsultationHoursGUI
{
    protected const MODE_CREATE = 1;
    protected const MODE_UPDATE = 2;
    protected const MODE_MULTI = 3;

    protected const MAX_APPOINTMENTS_PER_SEQUENCE = 1000;

    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilObjUser $global_user;
    protected ilHelpGUI $help;
    protected ilTabsGUI $tabs;
    protected ilToolbarGUI $toolbar;

    private int $user_id;
    private ?ilBookingEntry $booking = null;

    private ?ilPropertyFormGUI $form = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $user_id = (int) $_GET['user_id'];
        if ($user_id) {
            if (in_array($user_id, array_keys(ilConsultationHourAppointments::getManagedUsers()))) {
                $this->user_id = $user_id;
            } else {
                $user_id = false;
            }
        }
        if (!$user_id) {
            $this->user_id = $DIC->user()->getId();
        }

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->help = $DIC->help();
        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
    }

    public function executeCommand() : void
    {
        $this->help->setScreenIdComponent("cal");

        switch ($this->ctrl->getNextClass()) {
            case "ilpublicuserprofilegui":
                #22168 don't send the current user if no GET user_id
                //$profile = new ilPublicUserProfileGUI($this->user_id);
                $profile = new ilPublicUserProfileGUI();
                $profile->setBackUrl($this->getProfileBackUrl());
                $ret = $this->ctrl->forwardCommand($profile);
                $this->tpl->setContent($ret);
                break;

            case 'ilrepositorysearchgui':

                $rep_search = new ilRepositorySearchGUI();

                if (isset($_REQUEST['assignM'])) {
                    $rep_search->setCallback(
                        $this,
                        'assignUsersToAppointments',
                        array()
                    );
                    $this->ctrl->setParameter($this, 'assignM', 1);
                    $this->ctrl->setReturn($this, 'appointmentList');
                    $this->tabs->activateSubTab('cal_ch_app_list');
                } elseif (isset($_REQUEST['grp_id'])) {
                    $rep_search->setCallback(
                        $this,
                        'assignUsersToGroup',
                        array()
                    );
                    $this->ctrl->saveParameter($this, 'grp_id');
                    $this->ctrl->setReturn($this, 'groupList');
                    $this->tabs->activateSubTab('cal_ch_app_grp');
                } elseif (isset($_REQUEST['apps'])) {
                    $rep_search->setCallback(
                        $this,
                        'assignUsersToAppointment',
                        array()
                    );
                    $this->ctrl->saveParameter($this, 'apps');
                    $this->ctrl->setReturn($this, 'appointmentList');
                    $this->tabs->activateSubTab('cal_ch_app_list');
                }
                $this->ctrl->forwardCommand($rep_search);
                break;

            default:
                $this->tpl->setTitle($this->lng->txt("cal_ch_form_header")); // #12220

                $this->setTabs();
                if ($this->global_user->getId() != $this->user_id) {
                    $this->ctrl->setParameter($this, 'user_id', $this->user_id);
                }
                $cmd = $this->ctrl->getCmd('appointmentList');
                $this->$cmd();
        }
    }

    public function getUserId() : int
    {
        return $this->user_id;
    }

    protected function searchUsersForAppointments() : void
    {
        $_SESSION['ch_apps'] = $_REQUEST['apps'];
        if (empty($_SESSION['ch_apps'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, 'appointmentList');
        }
        $_REQUEST['assignM'] = 1;
        $this->ctrl->setCmdClass('ilrepositorysearchgui');
        $this->ctrl->setcmd('');
        $this->executeCommand();
    }

    /**
     * Send info message about unassigned users
     * @param int[] $unassigned
     */
    protected function sendInfoAboutUnassignedUsers(array $unassigned) : bool
    {
        if (!$unassigned) {
            return true;
        }
        $users = array();
        foreach ($unassigned as $user_id) {
            $users[] = ilObjUser::_lookupFullname($user_id);
        }
        ilUtil::sendInfo(
            $this->lng->txt('cal_ch_user_assignment_failed_info') .
            '<br />' . implode('<br />', $users), true);
        return true;
    }

    /**
     * Assign users to multiple appointments
     */
    public function assignUsersToAppointments(array $users)
    {
        $unassigned_users = array();
        foreach ($_SESSION['ch_apps'] as $app) {
            $unassigned_users = array_unique(array_merge($unassigned_users,
                $this->assignUsersToAppointment($users, $app, false)));
        }

        $this->sendInfoAboutUnassignedUsers($unassigned_users);
        $this->ctrl->redirect($this, 'appointmentList');
    }

    /**
     * Assign users to an appointment
     * @param array $users
     * @param int   $a_app
     * @param bool  $a_redirect
     * @return int[] $unassigned_users
     * @throws ilCtrlException
     */
    public function assignUsersToAppointment(array $users, int $a_app = 0, bool $a_redirect = true) : array
    {
        if ($a_app) {
            $app = $a_app;
        } else {
            $app = $_REQUEST['apps'];
        }

        if (!count($users)) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            return [];
        }

        $booking = ilBookingEntry::getInstanceByCalendarEntryId($app);
        $assigned_users = array();
        foreach ($users as $user) {
            if ($booking->getCurrentNumberOfBookings($app) >= $booking->getNumberOfBookings()) {
                break;
            }
            if (!ilBookingEntry::lookupBookingsOfUser((array) $app, $user)) {
                ilConsultationHourUtils::bookAppointment($user, $app);
                $assigned_users[] = $user;
            }
        }

        $unassigned_users = array_diff($users, $assigned_users);

        if ($a_redirect) {
            $this->sendInfoAboutUnassignedUsers($unassigned_users);
            $this->ctrl->redirect($this, 'appointmentList');
        } else {
            return $unassigned_users;
        }
        return [];
    }

    /**
     * @param int[] $usr_ids
     */
    public function assignUsersToGroup(array $usr_ids) : void
    {
        $group_id = (int) $_REQUEST['grp_id'];

        $tomorrow = new ilDateTime(time(), IL_CAL_UNIX);
        $tomorrow->increment(IL_CAL_DAY, 1);

        // Get all future consultation hours
        $apps = ilConsultationHourAppointments::getAppointmentIdsByGroup(
            $this->user_id,
            $group_id,
            $tomorrow
        );
        $users = $usr_ids;
        $assigned_users = array();
        foreach ($apps as $app) {
            $booking = ilBookingEntry::getInstanceByCalendarEntryId($app);
            foreach ($users as $user) {
                if ($booking->getCurrentNumberOfBookings($app) >= $booking->getNumberOfBookings()) {
                    break;
                }
                if (!ilBookingEntry::lookupBookingsOfUser($apps, $user)) {
                    ilConsultationHourUtils::bookAppointment($user, $app);
                    $assigned_users[] = $user;
                }
            }
        }

        $this->sendInfoAboutUnassignedUsers(array_diff($users, $assigned_users));
        $this->ctrl->redirect($this, 'bookingList');
    }

    /**
     * Show consultation hour group
     */
    protected function groupList() : void
    {
        $this->help->setScreenId("consultation_hours");

        $this->toolbar->setFormAction($this->ctrl->getFormAction($this));
        $this->toolbar->addButton($this->lng->txt('cal_ch_add_grp'), $this->ctrl->getLinkTarget($this, 'addGroup'));

        $this->setSubTabs();
        $this->tabs->activateSubTab('cal_ch_app_grp');

        $gtbl = new ilConsultationHourGroupTableGUI($this, 'groupList', $this->getUserId());
        $gtbl->parse(ilConsultationHourGroups::getGroupsOfUser($this->getUserId()));

        $this->tpl->setContent($gtbl->getHTML());
    }

    /**
     * Show add group form
     */
    protected function addGroup(?ilPropertyFormGUI $form = null) : void
    {
        $this->setSubTabs();
        $this->tabs->activateSubTab('cal_ch_app_grp');

        if ($form == null) {
            $form = $this->initGroupForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Save new group
     */
    protected function saveGroup() : void
    {
        $form = $this->initGroupForm();
        if ($form->checkInput()) {
            $group = new ilConsultationHourGroup();
            $group->setTitle($form->getInput('title'));
            $group->setMaxAssignments($form->getInput('multiple'));
            $group->setUserId($this->getUserId());
            $group->save();

            ilUtil::sendSuccess($GLOBALS['DIC']['lng']->txt('settings_saved'), true);
            $GLOBALS['DIC']['ilCtrl']->redirect($this, 'groupList');
        }

        ilUtil::sendFailure($GLOBALS['DIC']['lng']->txt('err_check_input'), true);
        $this->addGroup($form);
    }

    /**
     * Edit group
     */
    protected function editGroup(?ilPropertyFormGUI $form = null) : void
    {
        $this->ctrl->setParameter($this, 'grp_id', (int) $_REQUEST['grp_id']);
        $this->setSubTabs();
        $this->tabs->activateSubTab('cal_ch_app_grp');

        if ($form == null) {
            $form = $this->initGroupForm((int) $_REQUEST['grp_id']);
        }
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Update group
     */
    protected function updateGroup() : void
    {
        $this->ctrl->setParameter($this, 'grp_id', (int) $_REQUEST['grp_id']);

        $form = $this->initGroupForm((int) $_REQUEST['grp_id']);
        if ($form->checkInput()) {
            $group = new ilConsultationHourGroup((int) $_REQUEST['grp_id']);
            $group->setTitle($form->getInput('title'));
            $group->setMaxAssignments($form->getInput('multiple'));
            $group->setUserId($this->getUserId());
            $group->update();

            ilUtil::sendSuccess($GLOBALS['DIC']['lng']->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'groupList');
        }

        ilUtil::sendFailure($GLOBALS['DIC']['lng']->txt('err_check_input'), true);
        $this->editGroup($form);
    }

    /**
     * Confirm delete
     */
    protected function confirmDeleteGroup() : void
    {
        $this->ctrl->setParameter($this, 'grp_id', (int) $_REQUEST['grp_id']);
        $groups = array((int) $_REQUEST['grp_id']);

        $this->setSubTabs();
        $this->tabs->activateSubTab('cal_ch_app_grp');
        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setHeaderText($GLOBALS['DIC']['lng']->txt('cal_ch_grp_delete_sure'));
        $confirm->setConfirm($GLOBALS['DIC']['lng']->txt('delete'), 'deleteGroup');
        $confirm->setCancel($GLOBALS['DIC']['lng']->txt('cancel'), 'groupList');

        foreach ($groups as $grp_id) {
            $group = new ilConsultationHourGroup($grp_id);

            $confirm->addItem('groups[]', (string) $grp_id, $group->getTitle());
        }
        $this->tpl->setContent($confirm->getHTML());
    }

    /**
     * Delete groups
     */
    protected function deleteGroup() : void
    {
        foreach ((array) $_REQUEST['groups'] as $grp_id) {
            $group = new ilConsultationHourGroup($grp_id);
            $group->delete();
        }
        ilUtil::sendSuccess($GLOBALS['DIC']['lng']->txt('cal_ch_grp_deleted'));
        $this->ctrl->redirect($this, 'groupList');
    }

    protected function initGroupForm(int $a_group_id = 0) : ilPropertyFormGUI
    {
        $group = new ilConsultationHourGroup($a_group_id);

        $form = new ilPropertyFormGUI();
        $form->setFormAction($GLOBALS['DIC']['ilCtrl']->getFormAction($this));

        if ($a_group_id) {
            $form->setTitle($GLOBALS['DIC']['lng']->txt('cal_ch_grp_update_tbl'));
            $form->addCommandButton('updateGroup', $GLOBALS['DIC']['lng']->txt('save'));
            $form->addCommandButton('groupList', $GLOBALS['DIC']['lng']->txt('cancel'));
        } else {
            $form->setTitle($GLOBALS['DIC']['lng']->txt('cal_ch_grp_add_tbl'));
            $form->addCommandButton('saveGroup', $GLOBALS['DIC']['lng']->txt('save'));
            $form->addCommandButton('appointmentList', $GLOBALS['DIC']['lng']->txt('cancel'));
        }

        $title = new ilTextInputGUI($GLOBALS['DIC']['lng']->txt('title'), 'title');
        $title->setMaxLength(128);
        $title->setSize(40);
        $title->setRequired(true);
        $title->setValue($group->getTitle());
        $form->addItem($title);

        $multiple = new ilNumberInputGUI($GLOBALS['DIC']['lng']->txt('cal_ch_grp_multiple'), 'multiple');
        $multiple->setRequired(true);
        $multiple->setMinValue(1);
        $multiple->setSize(1);
        $multiple->setMaxLength(2);
        $multiple->setInfo($GLOBALS['DIC']['lng']->txt('cal_ch_grp_multiple_info'));
        $multiple->setValue((string) $group->getMaxAssignments());
        $form->addItem($multiple);

        return $form;
    }

    /**
     * Show list of bookings
     */
    protected function bookingList() : void
    {
        $this->help->setScreenId("consultation_hours");

        $this->setSubTabs();
        $this->tabs->activateSubTab('cal_ch_app_bookings');

        $btable = new ilConsultationHourBookingTableGUI($this, 'bookingList', $this->getUserId());
        $btable->parse(ilConsultationHourAppointments::getAppointmentIds($this->getUserId()));
        $this->tpl->setContent($btable->getHTML());
    }

    /**
     * Show delete booking confirmation
     */
    protected function confirmDeleteBooking() : void
    {
        $this->confirmRejectBooking(false);
    }

    /**
     * Show delete booking confirmation
     */
    protected function confirmRejectBooking(bool $a_send_notification = true) : void
    {
        $this->setSubTabs();
        $this->tabs->activateSubTab('cal_ch_app_bookings');

        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this));

        if ($a_send_notification) {
            ilUtil::sendInfo($this->lng->txt('cal_ch_cancel_booking_info'));
            $confirm->setHeaderText($this->lng->txt('cal_ch_cancel_booking_sure'));
            $confirm->setConfirm($this->lng->txt('cal_ch_reject_booking'), 'rejectBooking');
        } else {
            ilUtil::sendInfo($this->lng->txt('cal_ch_delete_booking_info'));
            $confirm->setHeaderText($this->lng->txt('cal_ch_delete_booking_sure'));
            $confirm->setConfirm($this->lng->txt('cal_ch_delete_booking'), 'deleteBooking');
        }

        $confirm->setCancel($this->lng->txt('cancel'), 'bookingList');

        foreach ((array) $_REQUEST['bookuser'] as $bookuser) {
            $ids = explode('_', $bookuser);

            $entry = new ilCalendarEntry($ids[0]);
            $confirm->addItem(
                'bookuser[]',
                $bookuser,
                ilUserUtil::getNamePresentation(
                    $ids[1],
                    true,
                    false,
                    '',
                    true,
                    true
                ) . ', ' . ilDatePresentation::formatDate($entry->getStart())
            );
        }
        $this->tpl->setContent($confirm->getHTML());
    }

    /**
     * Delete booking
     */
    protected function deleteBooking() : void
    {
        $this->rejectBooking(false);
    }

    protected function rejectBooking(bool $a_send_notification = true) : void
    {
        foreach ((array) $_REQUEST['bookuser'] as $bookuser) {
            $ids = explode('_', $bookuser);

            ilConsultationHourUtils::cancelBooking($ids[1], $ids[0], $a_send_notification);
        }
        if ($a_send_notification) {
            ilUtil::sendSuccess($this->lng->txt('cal_ch_canceled_bookings'), true);
        } else {
            ilUtil::sendSuccess($this->lng->txt('cal_ch_deleted_bookings'), true);
        }
        $this->ctrl->redirect($this, 'bookingList');
    }

    /**
     * Show settings of consultation hours
     * @todo add list/filter of consultation hours if user is responsible for more than one other consultation hour series.
     */
    protected function appointmentList() : void
    {
        $this->help->setScreenId("consultation_hours");

        $this->toolbar->setFormAction($this->ctrl->getFormAction($this));
        $this->toolbar->addButton($this->lng->txt('cal_ch_add_sequence'),
            $this->ctrl->getLinkTarget($this, 'createSequence'));

        $this->setSubTabs();
        $this->tabs->activateSubTab('cal_ch_app_list');

        $tbl = new ilConsultationHoursTableGUI($this, 'appointmentList', $this->getUserId());
        $tbl->parse();
        $this->tpl->setContent($tbl->getHTML());
    }

    /**
     * Create new sequence
     */
    protected function createSequence() : void
    {
        $this->initFormSequence(self::MODE_CREATE);

        $this->booking = new ilBookingEntry();
        $this->form->getItemByPostVar('bo')->setValue($this->booking->getNumberOfBookings());
        $this->form->getItemByPostVar('ap')->setValue(1);
        $this->form->getItemByPostVar('du')->setMinutes(15);
        $this->form->getItemByPostVar('st')->setDate(
            new ilDateTime(mktime(8, 0, 0, date('n', time()), date('d', time()), date('Y', time())), IL_CAL_UNIX)
        );
        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * @todo get rid of $this->form
     */
    protected function initFormSequence(int $a_mode) : ilPropertyFormGUI
    {
        ilYuiUtil::initDomEvent();

        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this));

        switch ($a_mode) {
            case self::MODE_CREATE:
                $this->form->setTitle($this->lng->txt('cal_ch_add_sequence'));
                $this->form->addCommandButton('saveSequence', $this->lng->txt('save'));
                $this->form->addCommandButton('appointmentList', $this->lng->txt('cancel'));
                break;

            case self::MODE_MULTI:
                $this->form->setTitle($this->lng->txt('cal_ch_multi_edit_sequence'));
                $this->form->addCommandButton('updateMulti', $this->lng->txt('save'));
                $this->form->addCommandButton('appointmentList', $this->lng->txt('cancel'));
                break;
        }

        // in case of existing groups show a selection
        if (count($options = ilConsultationHourGroups::getGroupSelectOptions($this->getUserId()))) {
            $group = new ilSelectInputGUI($this->lng->txt('cal_ch_grp_selection'), 'grp');
            $group->setOptions($options);
            $group->setRequired(false);
            $this->form->addItem($group);
        }

        // Title
        $ti = new ilTextInputGUI($this->lng->txt('title'), 'ti');
        $ti->setSize(32);
        $ti->setMaxLength(128);
        $ti->setRequired(true);
        $this->form->addItem($ti);

        if ($a_mode != self::MODE_MULTI) {
            // Start
            $dur = new ilDateTimeInputGUI($this->lng->txt('cal_start'), 'st');
            $dur->setShowTime(true);
            $dur->setRequired(true);
            $this->form->addItem($dur);

            // Duration
            $du = new ilDurationInputGUI($this->lng->txt('cal_ch_duration'), 'du');
            $du->setShowMinutes(true);
            $du->setShowHours(true);
            $this->form->addItem($du);

            // Number of appointments
            $nu = new ilNumberInputGUI($this->lng->txt('cal_ch_num_appointments'), 'ap');
            $nu->setInfo($this->lng->txt('cal_ch_num_appointments_info'));
            $nu->setSize(2);
            $nu->setMaxLength(2);
            $nu->setRequired(true);
            $nu->setMinValue(1);
            $this->form->addItem($nu);

            // Recurrence
            $rec = new ilRecurrenceInputGUI($this->lng->txt('cal_recurrences'), 'frequence');
            $rec->setEnabledSubForms(
                array(
                    ilCalendarRecurrence::FREQ_DAILY,
                    ilCalendarRecurrence::FREQ_WEEKLY,
                    ilCalendarRecurrence::FREQ_MONTHLY
                )
            );
            $this->form->addItem($rec);
        }

        // Number of bookings
        $nu = new ilNumberInputGUI($this->lng->txt('cal_ch_num_bookings'), 'bo');
        $nu->setSize(2);
        $nu->setMaxLength(2);
        $nu->setMinValue(1);
        $nu->setRequired(true);
        $this->form->addItem($nu);

        // Deadline
        $dead = new ilDurationInputGUI($this->lng->txt('cal_ch_deadline'), 'dead');
        $dead->setInfo($this->lng->txt('cal_ch_deadline_info'));
        $dead->setShowMinutes(false);
        $dead->setShowHours(true);
        $dead->setShowDays(true);
        $this->form->addItem($dead);

        // Location
        $lo = new ilTextInputGUI($this->lng->txt('cal_where'), 'lo');
        $lo->setSize(32);
        $lo->setMaxLength(128);
        $this->form->addItem($lo);

        // Description
        $de = new ilTextAreaInputGUI($this->lng->txt('description'), 'de');
        $de->setRows(10);
        $de->setCols(60);
        $this->form->addItem($de);

        // Target Object
        $tgt = new ilTextInputGUI($this->lng->txt('cal_ch_target_object'), 'tgt');
        $tgt->setInfo($this->lng->txt('cal_ch_target_object_info'));
        $tgt->setSize(16);
        $tgt->setMaxLength(128);
        $this->form->addItem($tgt);
        return $this->form;
    }

    /**
     * Save new sequence
     */
    protected function saveSequence() : void
    {
        $this->initFormSequence(self::MODE_CREATE);

        if ($this->form->checkInput()) {
            $this->form->setValuesByPost();

            $booking = new ilBookingEntry();
            $booking->setObjId($this->getUserId());
            $booking->setNumberOfBookings($this->form->getInput('bo'));

            $deadline = $this->form->getInput('dead');
            $deadline = $deadline['dd'] * 24 + $deadline['hh'];
            $booking->setDeadlineHours($deadline);

            // consultation hour group
            if (ilConsultationHourGroups::getGroupsOfUser($this->getUserId())) {
                $booking->setBookingGroup((int) $this->form->getInput('grp'));
            }

            $tgt = explode(',', $this->form->getInput('tgt'));
            $obj_ids = array();
            foreach ((array) $tgt as $ref_id) {
                if (!trim($ref_id)) {
                    continue;
                }
                $obj_id = ilObject::_lookupObjId($ref_id);
                $type = ilObject::_lookupType($obj_id);
                $valid_types = array('crs', 'grp');
                if (!$obj_id or !in_array($type, $valid_types)) {
                    ilUtil::sendFailure($this->lng->txt('cal_ch_unknown_repository_object'));
                    $this->tpl->setContent($this->form->getHTML());
                    return;
                }

                $obj_ids[] = $obj_id;
            }
            $booking->setTargetObjIds($obj_ids);

            $booking->save();
            $this->createAppointments($booking);

            ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'appointmentList');
        } else {
            $this->form->setValuesByPost();
            $this->tpl->setContent($this->form->getHTML());
        }
    }

    /**
     * Create calendar appointments
     */
    protected function createAppointments(ilBookingEntry $booking) : void
    {
        $concurrent_dates = new ilDateList(ilDateList::TYPE_DATETIME);
        $start = clone $this->form->getItemByPostVar('st')->getDate();
        for ($i = 0; $i < $this->form->getItemByPostVar('ap')->getValue(); $i++) {
            $concurrent_dates->add(clone $start);

            $start->increment(ilDateTime::MINUTE, $this->form->getItemByPostVar('du')->getMinutes());
            $start->increment(ilDateTime::HOUR, $this->form->getItemByPostVar('du')->getHours());
            #$start = new ilDateTime(,IL_CAL_UNIX);
        }

        $def_cat = ilCalendarUtil::initDefaultCalendarByType(ilCalendarCategory::TYPE_CH, $this->getUserId(),
            $this->lng->txt('cal_ch_personal_ch'), true);

        // Add calendar appointment for each

        $num_appointments = 0;
        foreach ($concurrent_dates as $dt) {
            if ($num_appointments >= self::MAX_APPOINTMENTS_PER_SEQUENCE) {
                break;
            }

            $end = clone $dt;
            $end->increment(ilDateTime::MINUTE, $this->form->getItemByPostVar('du')->getMinutes());
            $end->increment(ilDateTime::HOUR, $this->form->getItemByPostVar('du')->getHours());

            $calc = new ilCalendarRecurrenceCalculator(
                new ilBookingPeriod($dt, $end),
                $this->form->getItemByPostVar('frequence')->getRecurrence()
            );

            // Calculate with one year limit
            $limit = clone $dt;
            $limit->increment(ilDateTime::YEAR, 1);

            $date_list = $calc->calculateDateList($dt, $limit);

            $num = 0;
            foreach ($date_list as $app_start) {
                $app_end = clone $app_start;
                $app_end->increment(ilDateTime::MINUTE, $this->form->getItemByPostVar('du')->getMinutes());
                $app_end->increment(ilDateTime::HOUR, $this->form->getItemByPostVar('du')->getHours());

                $entry = new ilCalendarEntry();
                $entry->setContextId($booking->getId());
                $entry->setTitle($this->form->getInput('ti'));
                $entry->setSubtitle("#consultationhour#"); // dynamic, see ilCalendarEntry
                $entry->setDescription($this->form->getInput('de'));
                $entry->setLocation($this->form->getInput('lo'));
                $entry->setStart($app_start);
                $entry->setEnd($app_end);

                $entry->setTranslationType(IL_CAL_TRANSLATION_SYSTEM);
                $entry->save();

                $cat_assign = new ilCalendarCategoryAssignments($entry->getEntryId());
                $cat_assign->addAssignment($def_cat->getCategoryID());

                $num_appointments++;
            }
        }
    }

    protected function setTabs() : void
    {
        $this->ctrl->setParameter($this, 'user_id', '');
        $this->tabs->addTab(
            'consultation_hours_' . $this->user_id,
            $this->lng->txt('cal_ch_ch'),
            $this->ctrl->getLinkTarget($this, 'appointmentList')
        );

        foreach (ilConsultationHourAppointments::getManagedUsers() as $user_id => $login) {
            $this->ctrl->setParameter($this, 'user_id', $user_id);
            $this->tabs->addTab(
                'consultation_hours_' . $user_id,
                $this->lng->txt('cal_ch_ch') . ': ' . $login,
                $this->ctrl->getLinkTarget($this, 'appointmentList')
            );
        }
        $this->ctrl->setParameter($this, 'user_id', '');
        $this->tabs->addTab('ch_settings', $this->lng->txt('settings'), $this->ctrl->getLinkTarget($this, 'settings'));
        $this->tabs->activateTab('consultation_hours_' . $this->getUserId());
    }

    protected function setSubTabs() : void
    {
        $this->ctrl->setParameter($this, 'user_id', $this->getUserId());
        $this->tabs->addSubTab('cal_ch_app_list', $this->lng->txt('cal_ch_app_list'),
            $this->ctrl->getLinkTarget($this, 'appointmentList'));
        $this->tabs->addSubTab('cal_ch_app_grp', $this->lng->txt('cal_ch_app_grp'),
            $this->ctrl->getLinkTarget($this, 'groupList'));
        $this->tabs->addSubTab('cal_ch_app_bookings', $this->lng->txt('cal_ch_app_bookings'),
            $this->ctrl->getLinkTarget($this, 'bookingList'));
    }

    /**
     * Edit multiple sequence items
     */
    public function edit() : void
    {
        if (!isset($_REQUEST['apps'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->appointmentList();
            return;
        }

        $this->initFormSequence(self::MODE_MULTI);

        if ($_REQUEST['apps'] && !is_array($_REQUEST['apps'])) {
            $_REQUEST['apps'] = explode(';', $_REQUEST['apps']);
        }

        $hidden = new ilHiddenInputGUI('apps');
        $hidden->setValue(implode(';', $_REQUEST['apps']));
        $this->form->addItem($hidden);

        $first = $_REQUEST['apps'];
        $first = array_shift($_REQUEST['apps']);
        $entry = new ilCalendarEntry($first);

        $this->form->getItemByPostVar('ti')->setValue($entry->getTitle());
        $this->form->getItemByPostVar('lo')->setValue($entry->getLocation());
        $this->form->getItemByPostVar('de')->setValue($entry->getDescription());

        $booking = new ilBookingEntry($entry->getContextId());

        $this->form->getItemByPostVar('bo')->setValue($booking->getNumberOfBookings());

        $ref_ids = array();
        foreach ($booking->getTargetObjIds() as $obj_id) {
            $refs = ilObject::_getAllReferences($obj_id);
            $ref_ids[] = end($refs);
        }
        $this->form->getItemByPostVar('tgt')->setValue(implode(',', $ref_ids));

        $deadline = $booking->getDeadlineHours();
        $this->form->getItemByPostVar('dead')->setDays((int) floor($deadline / 24));
        $this->form->getItemByPostVar('dead')->setHours($deadline % 24);

        if ($booking->getBookingGroup()) {
            $this->form->getItemByPostVar('grp')->setValue($booking->getBookingGroup());
        }

        $this->tpl->setContent($this->form->getHTML());
    }

    protected function createNewBookingEntry(ilPropertyFormGUI $validate_form) : ?ilBookingEntry
    {
        $booking = new \ilBookingEntry();
        $booking->setObjId($this->user_id);
        $booking->setNumberOfBookings((int) $this->form->getInput('bo'));

        $deadline = $this->form->getInput('dead');
        $deadline = $deadline['dd'] * 24 + $deadline['hh'];
        $booking->setDeadlineHours($deadline);

        $tgt = explode(',', (string) $this->form->getInput('tgt'));
        $obj_ids = [];
        foreach ((array) $tgt as $ref_id) {
            if (!trim($ref_id)) {
                continue;
            }
            $obj_id = ilObject::_lookupObjId($ref_id);
            $type = ilObject::_lookupType($obj_id);
            $valid_types = ['crs', 'grp'];
            if (!$obj_id or !in_array($type, $valid_types)) {
                ilUtil::sendFailure($this->lng->txt('cal_ch_unknown_repository_object'));
                return null;
            }
            $obj_ids[] = $obj_id;
        }
        $booking->setTargetObjIds($obj_ids);

        if (ilConsultationHourGroups::getCountGroupsOfUser($this->getUserId())) {
            $booking->setBookingGroup($this->form->getInput('grp'));
        }
        $booking->save();
        return $booking;
    }

    protected function rewriteBookingIdsForAppointments(
        ilBookingEntry $booking,
        array $appointments,
        ilPropertyFormGUI $form
    ) : void {
        foreach ($appointments as $appointment_id) {
            $booking_appointment = new \ilCalendarEntry($appointment_id);
            $booking_start = $booking_appointment->getStart();
            $booking_end = $booking_appointment->getEnd();

            $deprecatedBooking = \ilBookingEntry::getInstanceByCalendarEntryId($appointment_id);
            if (!$deprecatedBooking instanceof \ilBookingEntry) {
                // @todo error handling
                continue;
            }

            $relevant_appointments = \ilConsultationHourUtils::findCalendarAppointmentsForBooking(
                $deprecatedBooking,
                $booking_start,
                $booking_end
            );
            foreach ($relevant_appointments as $relevant_appointment_id) {
                $entry = new \ilCalendarEntry($relevant_appointment_id);
                $entry->setContextId($booking->getId());
                $entry->setTitle($form->getInput('ti'));
                $entry->setLocation($form->getInput('lo'));
                $entry->setDescription($form->getInput('de'));
                $entry->update();
            }
        }
    }

    /**
     * Update multiple sequence items
     */
    protected function updateMulti() : void
    {
        $this->initFormSequence(self::MODE_MULTI);

        if ($this->form->checkInput()) {
            $this->form->setValuesByPost();
            $apps = explode(';', (string) $_POST['apps']);

            // create new booking
            $booking = $this->createNewBookingEntry($this->form);
            if (!$booking instanceof \ilBookingEntry) {
                $this->edit();
                return;
            }
            $this->rewriteBookingIdsForAppointments($booking, $apps, $this->form);
            ilBookingEntry::removeObsoleteEntries();

            ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'appointmentList');
        }
        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * confirm delete for multiple entries
     */
    public function confirmDelete() : void
    {
        if (!isset($_REQUEST['apps'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->appointmentList();
            return;
        }

        $this->ctrl->saveParameter($this, array('seed', 'app_id', 'dt'));
        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setHeaderText($this->lng->txt('cal_delete_app_sure'));
        $confirm->setCancel($this->lng->txt('cancel'), 'cancel');

        $bookings_available = array();
        foreach ((array) $_REQUEST['apps'] as $entry_id) {
            $entry = new ilCalendarEntry($entry_id);
            $confirm->addItem('apps[]', $entry_id,
                ilDatePresentation::formatDate($entry->getStart()) . ', ' . $entry->getTitle());

            if (ilBookingEntry::lookupBookingsForAppointment($entry_id)) {
                $bookings_available[] = ilDatePresentation::formatDate($entry->getStart()) . ', ' . $entry->getTitle();
            }
        }

        if ($bookings_available) {
            ilUtil::sendInfo($this->lng->txt('cal_ch_delete_app_booking_info') . '<br />' . implode('<br />',
                    $bookings_available));
        }

        $confirm->setConfirm($this->lng->txt('delete'), 'delete');
        $confirm->setCancel($this->lng->txt('cancel'), 'appointmentList');

        $this->tpl->setContent($confirm->getHTML());
    }

    /**
     * delete multiple entries
     */
    public function delete() : void
    {
        if (!isset($_POST['apps'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->appointmentList();
            return;
        }
        foreach ($_POST['apps'] as $entry_id) {
            // cancel booking for users
            $booking = ilBookingEntry::getInstanceByCalendarEntryId($entry_id);
            if ($booking) {
                foreach ($booking->getCurrentBookings($entry_id) as $user_id) {
                    ilConsultationHourUtils::cancelBooking($user_id, $entry_id, false);
                }
            }
            // remove calendar entries
            $entry = new ilCalendarEntry($entry_id);
            $entry->delete();

            ilCalendarCategoryAssignments::_deleteByAppointmentId($entry_id);
        }

        ilBookingEntry::removeObsoleteEntries();

        ilUtil::sendSuccess($this->lng->txt('cal_deleted_app'), true);
        $this->ctrl->redirect($this, 'appointmentList');
    }

    /**
     * show public profile of given user
     */
    public function showProfile() : void
    {
        $this->tabs->clearTargets();
        $user_id = (int) $_GET['user'];

        $profile = new ilPublicUserProfileGUI($user_id);
        $profile->setBackUrl($this->getProfileBackUrl());
        $this->tpl->setContent($this->ctrl->getHTML($profile));
    }

    /**
     * Build context-sensitive profile back url
     */
    protected function getProfileBackUrl() : string
    {
        // from repository
        if (isset($_REQUEST["ref_id"])) {
            $url = $this->ctrl->getLinkTargetByClass('ilCalendarMonthGUI');
        } // from panel
        elseif (isset($_GET['panel'])) {
            $url = $this->ctrl->getLinkTargetByClass('ilCalendarPresentationGUI');
        } // from appointments
        else {
            $url = $this->ctrl->getLinkTarget($this, 'appointmentList');
        }
        return $url;
    }

    /**
     * display settings gui
     */
    public function settings() : void
    {
        $this->help->setScreenId("consultation_hours_settings");
        $this->tabs->activateTab('ch_settings');

        $form = $this->initSettingsForm();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * build settings form
     */
    protected function initSettingsForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        $mng = new ilTextInputGUI($this->lng->txt('cal_ch_manager'), 'mng');
        $mng->setInfo($this->lng->txt('cal_ch_manager_info'));
        $form->addItem($mng);

        $mng->setValue(ilConsultationHourAppointments::getManager(true));

        $form->setTitle($this->lng->txt('settings'));
        $form->addCommandButton('updateSettings', $this->lng->txt('save'));
        return $form;
    }

    /**
     * save settings
     */
    public function updateSettings() : void
    {
        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            $mng = $form->getInput('mng');
            if (ilConsultationHourAppointments::setManager($mng)) {
                ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
                $this->ctrl->redirect($this, 'settings');
            } else {
                $this->tabs->activateTab('ch_settings');

                ilUtil::sendFailure($this->lng->txt('cal_ch_unknown_user'));
                $field = $form->getItemByPostVar('mng');
                $field->setValue($mng);
                $this->tpl->setContent($form->getHTML());
                return;
            }
        }
    }
}
