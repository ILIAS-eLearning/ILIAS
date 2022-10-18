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

use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\HTTP\Services as HttpServices;

/**
 * Consultation hours editor
 * @author      Stefan Meyer <smeyer.ilias@gmx.de>
 * @ilCtrl_Calls ilConsultationHoursGUI: ilPublicUserProfileGUI, ilRepositorySearchGUI
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
    protected RefineryFactory $refinery;
    protected HttpServices $http;


    private int $user_id;
    private bool $search_assignment_to_appointments = false;
    private ?ilBookingEntry $booking = null;

    private ?ilPropertyFormGUI $form = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $user_id = 0;
        if ($this->http->wrapper()->query()->has('user_id')) {
            $user_id = $this->http->wrapper()->query()->retrieve(
                'user_id',
                $this->refinery->kindlyTo()->int()
            );
        }
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
        $this->global_user = $DIC->user();
    }

    protected function initSearchAssignmentToAppointments($a_default = false): bool
    {
        $this->search_assignment_to_appointments = $a_default;
        if ($this->http->wrapper()->query()->has('assignM')) {
            $this->search_assignment_to_appointments = $this->http->wrapper()->query()->retrieve(
                'assignM',
                $this->refinery->kindlyTo()->bool()
            );
        }
        return $this->search_assignment_to_appointments;
    }

    protected function initGroupIdFromQuery(): int
    {
        if ($this->http->wrapper()->query()->has('grp_id')) {
            return $this->http->wrapper()->query()->retrieve(
                'grp_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    protected function initAppointmentIdsFromQuery(): array
    {
        if ($this->http->wrapper()->query()->has('apps')) {
            return [$this->http->wrapper()->query()->retrieve('apps', $this->refinery->kindlyTo()->int())];
        }
        return [];
    }

    protected function initAppointmentIdsFromPost(): array
    {
        if ($this->http->wrapper()->post()->has('apps')) {
            return $this->http->wrapper()->post()->retrieve(
                'apps',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        return [];
    }

    protected function initAppointmentIdsFromPostString(): array
    {
        if ($this->http->wrapper()->post()->has('apps_string')) {
            $app_string = $this->http->wrapper()->post()->retrieve(
                'apps_string',
                $this->refinery->kindlyTo()->string()
            );
            return array_map('intval', explode(';', $app_string));
        }
        return [];
    }

    protected function initGroupIdsFromPost(): array
    {
        if ($this->http->wrapper()->post()->has('groups')) {
            return $this->http->wrapper()->post()->retrieve(
                'groups',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        return [];
    }

    /**
     * @return string[]
     */
    protected function initBookingUsersFromPost(): array
    {
        if ($this->http->wrapper()->post()->has('bookuser')) {
            return $this->http->wrapper()->post()->retrieve(
                'bookuser',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->string()
                )
            );
        }
        return [];
    }

    /**
     * @return string[]
     */
    protected function initBookingUsersFromQuery(): array
    {
        if ($this->http->wrapper()->query()->has('bookuser')) {
            return [
                $this->http->wrapper()->query()->retrieve(
                    'bookuser',
                    $this->refinery->kindlyTo()->string()
                )
            ];
        }
        return [];
    }

    public function executeCommand(): void
    {
        $this->help->setScreenIdComponent("cal");
        switch ($this->ctrl->getNextClass($this)) {
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
                if ($this->initSearchAssignmentToAppointments()) {
                    $rep_search->setCallback(
                        $this,
                        'assignUsersToAppointments',
                        array()
                    );
                    $this->ctrl->setParameter($this, 'assignM', 1);
                    $this->ctrl->setReturn($this, 'appointmentList');
                    $this->tabs->activateSubTab('cal_ch_app_list');
                } elseif ($this->initGroupIdFromQuery()) {
                    $rep_search->setCallback(
                        $this,
                        'assignUsersToGroup',
                        array()
                    );
                    $this->ctrl->saveParameter($this, 'grp_id');
                    $this->ctrl->setReturn($this, 'groupList');
                    $this->tabs->activateSubTab('cal_ch_app_grp');
                } elseif (count($this->initAppointmentIdsFromPost())) {
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

    public function getUserId(): int
    {
        return $this->user_id;
    }

    protected function searchUsersForAppointments(): void
    {
        $apps = [];
        if ($this->initAppointmentIdsFromPost() !== []) {
            $apps = $this->initAppointmentIdsFromPost();
        } elseif ($this->initAppointmentIdsFromQuery() !== []) {
            $apps = $this->initAppointmentIdsFromQuery();
        }

        if ($apps === []) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, 'appointmentList');
        }
        ilSession::set('ch_apps', $apps);
        $this->ctrl->setParameterByClass(ilRepositorySearchGUI::class, 'assignM', 1);
        $this->ctrl->redirectByClass(ilRepositorySearchGUI::class, '');
    }

    /**
     * Send info message about unassigned users
     * @param int[] $unassigned
     */
    protected function sendInfoAboutUnassignedUsers(array $unassigned): bool
    {
        if (!$unassigned) {
            return true;
        }
        $users = array();
        foreach ($unassigned as $user_id) {
            $users[] = ilObjUser::_lookupFullname($user_id);
        }
        $this->tpl->setOnScreenMessage('info', $this->lng->txt('cal_ch_user_assignment_failed_info') .
        '<br />' . implode('<br />', $users), true);
        return true;
    }

    /**
     * Assign users to multiple appointments
     */
    public function assignUsersToAppointments(array $users)
    {
        $unassigned_users = [];
        $ch_apps = (array) (ilSession::get('ch_apps') ?? []);
        foreach ($ch_apps as $app) {
            $unassigned_users = array_unique(array_merge(
                $unassigned_users,
                $this->assignUsersToAppointment($users, $app, false)
            ));
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
    public function assignUsersToAppointment(array $users, int $a_app = 0, bool $a_redirect = true): array
    {
        if ($a_app) {
            $app = $a_app;
        } else {
            $app = $this->initAppointmentIdsFromPost();
        }

        if (!count($users)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
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
    public function assignUsersToGroup(array $usr_ids): void
    {
        $group_id = $this->initGroupIdFromQuery();

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
    protected function groupList(): void
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
    protected function addGroup(?ilPropertyFormGUI $form = null): void
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
    protected function saveGroup(): void
    {
        $form = $this->initGroupForm();
        if ($form->checkInput()) {
            $group = new ilConsultationHourGroup();
            $group->setTitle($form->getInput('title'));
            $group->setMaxAssignments((int) $form->getInput('multiple'));
            $group->setUserId($this->getUserId());
            $group->save();

            $this->tpl->setOnScreenMessage('success', $GLOBALS['DIC']['lng']->txt('settings_saved'), true);
            $GLOBALS['DIC']['ilCtrl']->redirect($this, 'groupList');
        }

        $this->tpl->setOnScreenMessage('failure', $GLOBALS['DIC']['lng']->txt('err_check_input'), true);
        $this->addGroup($form);
    }

    /**
     * Edit group
     */
    protected function editGroup(?ilPropertyFormGUI $form = null): void
    {
        $this->ctrl->setParameter($this, 'grp_id', $this->initGroupIdFromQuery());
        $this->setSubTabs();
        $this->tabs->activateSubTab('cal_ch_app_grp');

        if ($form == null) {
            $form = $this->initGroupForm($this->initGroupIdFromQuery());
        }
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Update group
     */
    protected function updateGroup(): void
    {
        $group_id = $this->initGroupIdFromQuery();
        $this->ctrl->setParameter($this, 'grp_id', $group_id);

        $form = $this->initGroupForm($group_id);
        if ($form->checkInput()) {
            $group = new ilConsultationHourGroup($group_id);
            $group->setTitle($form->getInput('title'));
            $group->setMaxAssignments((int) $form->getInput('multiple'));
            $group->setUserId($this->getUserId());
            $group->update();

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'groupList');
        }

        $this->tpl->setOnScreenMessage('failure', $GLOBALS['DIC']['lng']->txt('err_check_input'), true);
        $this->editGroup($form);
    }

    /**
     * Confirm delete
     */
    protected function confirmDeleteGroup(): void
    {
        $group_id = $this->initGroupIdFromQuery();
        $this->ctrl->setParameter($this, 'grp_id', $group_id);
        $groups = array($group_id);

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
    protected function deleteGroup(): void
    {
        foreach ($this->initGroupIdsFromPost() as $grp_id) {
            $group = new ilConsultationHourGroup($grp_id);
            $group->delete();
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('cal_ch_grp_deleted'));
        $this->ctrl->redirect($this, 'groupList');
    }

    protected function initGroupForm(int $a_group_id = 0): ilPropertyFormGUI
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
    protected function bookingList(): void
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
    protected function confirmDeleteBooking(): void
    {
        $this->confirmRejectBooking(false);
    }

    /**
     * Show delete booking confirmation
     */
    protected function confirmRejectBooking(bool $a_send_notification = true): void
    {
        $bookusers = [];
        if ($this->initBookingUsersFromPost() !== []) {
            $bookusers = $this->initBookingUsersFromPost();
        } elseif ($this->initBookingUsersFromQuery() !== []) {
            $bookusers = $this->initBookingUsersFromQuery();
        }

        if ($bookusers === []) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->bookingList();
            return;
        }
        $this->setSubTabs();
        $this->tabs->activateSubTab('cal_ch_app_bookings');

        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this));

        if ($a_send_notification) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('cal_ch_cancel_booking_info'));
            $confirm->setHeaderText($this->lng->txt('cal_ch_cancel_booking_sure'));
            $confirm->setConfirm($this->lng->txt('cal_ch_reject_booking'), 'rejectBooking');
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('cal_ch_delete_booking_info'));
            $confirm->setHeaderText($this->lng->txt('cal_ch_delete_booking_sure'));
            $confirm->setConfirm($this->lng->txt('cal_ch_delete_booking'), 'deleteBooking');
        }

        $confirm->setCancel($this->lng->txt('cancel'), 'bookingList');

        foreach ($bookusers as $bookuser) {
            $ids = explode('_', $bookuser);

            $entry = new ilCalendarEntry((int) $ids[0]);
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
    protected function deleteBooking(): void
    {
        $this->rejectBooking(false);
    }

    protected function rejectBooking(bool $a_send_notification = true): void
    {
        foreach ($this->initBookingUsersFromPost() as $bookuser) {
            $ids = explode('_', $bookuser);
            ilConsultationHourUtils::cancelBooking((int) $ids[1], (int) $ids[0], $a_send_notification);
        }
        if ($a_send_notification) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('cal_ch_canceled_bookings'), true);
        } else {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('cal_ch_deleted_bookings'), true);
        }
        $this->ctrl->redirect($this, 'bookingList');
    }

    /**
     * Show settings of consultation hours
     * @todo add list/filter of consultation hours if user is responsible for more than one other consultation hour series.
     */
    protected function appointmentList(): void
    {
        $this->help->setScreenId("consultation_hours");

        $this->toolbar->setFormAction($this->ctrl->getFormAction($this));
        $this->toolbar->addButton(
            $this->lng->txt('cal_ch_add_sequence'),
            $this->ctrl->getLinkTarget($this, 'createSequence')
        );

        $this->setSubTabs();
        $this->tabs->activateSubTab('cal_ch_app_list');

        $tbl = new ilConsultationHoursTableGUI($this, 'appointmentList', $this->getUserId());
        $tbl->parse();
        $this->tpl->setContent($tbl->getHTML());
    }

    /**
     * Create new sequence
     */
    protected function createSequence(): void
    {
        $this->initFormSequence(self::MODE_CREATE);

        $this->booking = new ilBookingEntry();
        $this->form->getItemByPostVar('bo')->setValue((string) $this->booking->getNumberOfBookings());
        $this->form->getItemByPostVar('ap')->setValue("1");
        $this->form->getItemByPostVar('du')->setMinutes(15);
        $this->form->getItemByPostVar('st')->setDate(
            new ilDateTime(mktime(8, 0, 0, (int) date('n', time()), (int) date('d', time()), (int) date('Y', time())), IL_CAL_UNIX)
        );
        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * @todo get rid of $this->form
     */
    protected function initFormSequence(int $a_mode): ilPropertyFormGUI
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
    protected function saveSequence(): void
    {
        $this->initFormSequence(self::MODE_CREATE);

        if ($this->form->checkInput()) {
            $this->form->setValuesByPost();

            $booking = new ilBookingEntry();
            $booking->setObjId($this->getUserId());
            $booking->setNumberOfBookings((int) $this->form->getInput('bo'));

            $deadline = $this->form->getInput('dead');
            $deadline = $deadline['dd'] * 24 + $deadline['hh'];
            $booking->setDeadlineHours($deadline);

            // consultation hour group
            if (ilConsultationHourGroups::getGroupsOfUser($this->getUserId())) {
                $booking->setBookingGroup((int) $this->form->getInput('grp'));
            }

            $tgt = array_map('intval', explode(',', $this->form->getInput('tgt')));
            $obj_ids = array();
            foreach ($tgt as $ref_id) {
                if ($ref_id === 0) {
                    continue;
                }
                $obj_id = ilObject::_lookupObjId($ref_id);
                $type = ilObject::_lookupType($obj_id);
                $valid_types = array('crs', 'grp');
                if (!$obj_id or !in_array($type, $valid_types)) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('cal_ch_unknown_repository_object'));
                    $this->tpl->setContent($this->form->getHTML());
                    return;
                }

                $obj_ids[] = $obj_id;
            }
            $booking->setTargetObjIds($obj_ids);
            $booking->save();
            $this->createAppointments($booking);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'appointmentList');
        } else {
            $this->form->setValuesByPost();
            $this->tpl->setContent($this->form->getHTML());
        }
    }

    /**
     * Create calendar appointments
     */
    protected function createAppointments(ilBookingEntry $booking): void
    {
        $concurrent_dates = new ilDateList(ilDateList::TYPE_DATETIME);
        $start = clone $this->form->getItemByPostVar('st')->getDate();
        for ($i = 0; $i < $this->form->getItemByPostVar('ap')->getValue(); $i++) {
            $concurrent_dates->add(clone $start);

            $start->increment(ilDateTime::MINUTE, $this->form->getItemByPostVar('du')->getMinutes());
            $start->increment(ilDateTime::HOUR, $this->form->getItemByPostVar('du')->getHours());
            #$start = new ilDateTime(,IL_CAL_UNIX);
        }

        $def_cat = ilCalendarUtil::initDefaultCalendarByType(
            ilCalendarCategory::TYPE_CH,
            $this->getUserId(),
            $this->lng->txt('cal_ch_personal_ch'),
            true
        );

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

                $entry->setTranslationType(ilCalendarEntry::TRANSLATION_SYSTEM);
                $entry->save();

                $cat_assign = new ilCalendarCategoryAssignments($entry->getEntryId());
                $cat_assign->addAssignment($def_cat->getCategoryID());

                $num_appointments++;
            }
        }
    }

    protected function setTabs(): void
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

    protected function setSubTabs(): void
    {
        $this->ctrl->setParameter($this, 'user_id', $this->getUserId());
        $this->tabs->addSubTab(
            'cal_ch_app_list',
            $this->lng->txt('cal_ch_app_list'),
            $this->ctrl->getLinkTarget($this, 'appointmentList')
        );
        $this->tabs->addSubTab(
            'cal_ch_app_grp',
            $this->lng->txt('cal_ch_app_grp'),
            $this->ctrl->getLinkTarget($this, 'groupList')
        );
        $this->tabs->addSubTab(
            'cal_ch_app_bookings',
            $this->lng->txt('cal_ch_app_bookings'),
            $this->ctrl->getLinkTarget($this, 'bookingList')
        );
    }

    /**
     * Edit multiple sequence items
     */
    public function edit(): void
    {
        // first read from hidden input
        $apps = [];
        if ($this->initAppointmentIdsFromPostString() !== []) {
            $apps = $this->initAppointmentIdsFromPostString();
        } elseif ($this->initAppointmentIdsFromPost() !== []) {
            $apps = $this->initAppointmentIdsFromPost();
        } elseif ($this->initAppointmentIdsFromQuery()) {
            $apps = $this->initAppointmentIdsFromQuery();
        }
        if (!count($apps)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->appointmentList();
            return;
        }

        $this->initFormSequence(self::MODE_MULTI);
        $hidden = new ilHiddenInputGUI('apps_string');
        $hidden->setValue(implode(';', $apps));
        $this->form->addItem($hidden);

        $first = $apps;
        $first = array_shift($apps);
        $entry = new ilCalendarEntry($first);

        $this->form->getItemByPostVar('ti')->setValue($entry->getTitle());
        $this->form->getItemByPostVar('lo')->setValue($entry->getLocation());
        $this->form->getItemByPostVar('de')->setValue($entry->getDescription());

        $booking = new ilBookingEntry($entry->getContextId());

        $this->form->getItemByPostVar('bo')->setValue((string) $booking->getNumberOfBookings());

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

    protected function createNewBookingEntry(ilPropertyFormGUI $validate_form): ?ilBookingEntry
    {
        $booking = new \ilBookingEntry();
        $booking->setObjId($this->user_id);
        $booking->setNumberOfBookings((int) $this->form->getInput('bo'));

        $deadline = $this->form->getInput('dead');
        $deadline = $deadline['dd'] * 24 + $deadline['hh'];
        $booking->setDeadlineHours($deadline);

        $tgt = array_map('intval', explode(',', (string) $this->form->getInput('tgt')));
        $obj_ids = [];
        foreach ($tgt as $ref_id) {
            if ($ref_id === 0) {
                continue;
            }
            $obj_id = ilObject::_lookupObjId($ref_id);
            $type = ilObject::_lookupType($obj_id);
            $valid_types = ['crs', 'grp'];
            if (!$obj_id or !in_array($type, $valid_types)) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('cal_ch_unknown_repository_object'));
                return null;
            }
            $obj_ids[] = $obj_id;
        }
        $booking->setTargetObjIds($obj_ids);

        if (ilConsultationHourGroups::getCountGroupsOfUser($this->getUserId())) {
            $booking->setBookingGroup((int) $this->form->getInput('grp'));
        }
        $booking->save();
        return $booking;
    }

    protected function rewriteBookingIdsForAppointments(
        ilBookingEntry $booking,
        array $appointments,
        ilPropertyFormGUI $form
    ): void {
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
    protected function updateMulti(): void
    {
        $this->initFormSequence(self::MODE_MULTI);
        if (!$this->form->checkInput()) {
            $this->form->setValuesByPost();
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
            $this->edit();
            return;
        }

        $this->form->setValuesByPost();
        $apps = $this->initAppointmentIdsFromPostString();

        // create new booking
        $booking = $this->createNewBookingEntry($this->form);
        if (!$booking instanceof \ilBookingEntry) {
            $this->edit();
            return;
        }
        $this->rewriteBookingIdsForAppointments($booking, $apps, $this->form);
        ilBookingEntry::removeObsoleteEntries();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'appointmentList');
        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * confirm delete for multiple entries
     */
    public function confirmDelete(): void
    {
        $apps = [];
        if ($this->initAppointmentIdsFromPost() !== []) {
            $apps = $this->initAppointmentIdsFromPost();
        } elseif ($this->initAppointmentIdsFromQuery() !== []) {
            $apps = $this->initAppointmentIdsFromQuery();
        }
        if ($apps === []) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->appointmentList();
            return;
        }

        $this->ctrl->saveParameter($this, array('seed', 'app_id', 'dt'));
        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setHeaderText($this->lng->txt('cal_delete_app_sure'));
        $confirm->setCancel($this->lng->txt('cancel'), 'cancel');

        $bookings_available = array();
        foreach ($apps as $entry_id) {
            $entry = new ilCalendarEntry($entry_id);
            $confirm->addItem(
                'apps[]',
                (string) $entry_id,
                ilDatePresentation::formatDate($entry->getStart()) . ', ' . $entry->getTitle()
            );

            if (ilBookingEntry::lookupBookingsForAppointment($entry_id)) {
                $bookings_available[] = ilDatePresentation::formatDate($entry->getStart()) . ', ' . $entry->getTitle();
            }
        }
        if ($bookings_available) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('cal_ch_delete_app_booking_info') . '<br />' . implode(
                '<br />',
                $bookings_available
            ));
        }
        $confirm->setConfirm($this->lng->txt('delete'), 'delete');
        $confirm->setCancel($this->lng->txt('cancel'), 'appointmentList');
        $this->tpl->setContent($confirm->getHTML());
    }

    /**
     * delete multiple entries
     */
    public function delete(): void
    {
        if (!count($this->initAppointmentIdsFromPost())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->appointmentList();
            return;
        }
        foreach ($this->initAppointmentIdsFromPost() as $entry_id) {
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
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('cal_deleted_app'), true);
        $this->ctrl->redirect($this, 'appointmentList');
    }

    /**
     * show public profile of given user
     */
    public function showProfile(): void
    {
        $this->tabs->clearTargets();

        $user_id = 0;
        if ($this->http->wrapper()->query()->has('user_id')) {
            $user_id = $this->http->wrapper()->query()->retrieve(
                'user_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        $profile = new ilPublicUserProfileGUI($user_id);
        $profile->setBackUrl($this->getProfileBackUrl());
        $this->tpl->setContent($this->ctrl->getHTML($profile));
    }

    /**
     * Build context-sensitive profile back url
     */
    protected function getProfileBackUrl(): string
    {
        // from repository
        if ($this->http->wrapper()->query()->has('ref_id')) {
            $url = $this->ctrl->getLinkTargetByClass('ilCalendarMonthGUI');
        } // from panel
        elseif ($this->http->wrapper()->query()->has('panel')) {
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
    public function settings(): void
    {
        $this->help->setScreenId("consultation_hours_settings");
        $this->tabs->activateTab('ch_settings');

        $form = $this->initSettingsForm();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * build settings form
     */
    protected function initSettingsForm(): ilPropertyFormGUI
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
    public function updateSettings(): void
    {
        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            $mng = $form->getInput('mng');
            if (ilConsultationHourAppointments::setManager($mng)) {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
                $this->ctrl->redirect($this, 'settings');
            } else {
                $this->tabs->activateTab('ch_settings');

                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('cal_ch_unknown_user'));
                $field = $form->getItemByPostVar('mng');
                $field->setValue($mng);
                $this->tpl->setContent($form->getHTML());
                return;
            }
        }
    }
}
