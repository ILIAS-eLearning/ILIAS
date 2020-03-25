<?php
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

include_once './Services/Calendar/classes/class.ilCalendarRecurrence.php';
include_once './Services/Booking/classes/class.ilBookingEntry.php';
include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourAppointments.php';

/**
 * Consultation hours editor
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 * @ilCtrl_Calls: ilConsultationHoursGUI: ilPublicUserProfileGUI, ilRepositorySearchGUI
 */
class ilConsultationHoursGUI
{
    const MODE_CREATE = 1;
    const MODE_UPDATE = 2;
    const MODE_MULTI = 3;
    
    const MAX_APPOINTMENTS_PER_SEQUENCE = 1000;
    
    protected $user_id;
    protected $ctrl;

    protected $booking = null;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $ilUser = $DIC['ilUser'];

        $user_id = (int) $_GET['user_id'];
        if ($user_id) {
            if (in_array($user_id, array_keys(ilConsultationHourAppointments::getManagedUsers()))) {
                $this->user_id = $user_id;
            } else {
                $user_id = false;
            }
        }
        if (!$user_id) {
            $this->user_id = $ilUser->getId();
        }
        
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
    }
    
    /**
     * Execute command
     * @return
     */
    public function executeCommand()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $ilHelp = $DIC['ilHelp'];
        $ilTabs = $DIC['ilTabs'];
        
        $ilHelp->setScreenIdComponent("cal");
        
        switch ($this->ctrl->getNextClass()) {
            case "ilpublicuserprofilegui":
                include_once('./Services/User/classes/class.ilPublicUserProfileGUI.php');
                #22168 don't send the current user if no GET user_id
                //$profile = new ilPublicUserProfileGUI($this->user_id);
                $profile = new ilPublicUserProfileGUI();
                $profile->setBackUrl($this->getProfileBackUrl());
                $ret = $ilCtrl->forwardCommand($profile);
                $tpl->setContent($ret);
                break;
            
            case 'ilrepositorysearchgui':
                
                include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
                $rep_search = new ilRepositorySearchGUI();
                
                if (isset($_REQUEST['assignM'])) {
                    $rep_search->setCallback(
                        $this,
                        'assignUsersToAppointments',
                        array()
                    );
                    $ilCtrl->setParameter($this, 'assignM', 1);
                    $ilCtrl->setReturn($this, 'appointmentList');
                    $ilTabs->activateSubTab('cal_ch_app_list');
                } elseif (isset($_REQUEST['grp_id'])) {
                    $rep_search->setCallback(
                        $this,
                        'assignUsersToGroup',
                        array()
                    );
                    $ilCtrl->saveParameter($this, 'grp_id');
                    $ilCtrl->setReturn($this, 'groupList');
                    $ilTabs->activateSubTab('cal_ch_app_grp');
                } elseif (isset($_REQUEST['apps'])) {
                    $rep_search->setCallback(
                        $this,
                        'assignUsersToAppointment',
                        array()
                    );
                    $ilCtrl->saveParameter($this, 'apps');
                    $ilCtrl->setReturn($this, 'appointmentList');
                    $ilTabs->activateSubTab('cal_ch_app_list');
                }
                $ilCtrl->forwardCommand($rep_search);
                break;
            
            default:
                $tpl->setTitle($this->lng->txt("cal_ch_form_header")); // #12220
        
                $this->setTabs();
                if ($ilUser->getId() != $this->user_id) {
                    $ilCtrl->setParameter($this, 'user_id', $this->user_id);
                }
                
                $cmd = $this->ctrl->getCmd('appointmentList');
                $this->$cmd();
        }
    }
    
    /**
     * Get user id
     * @return
     */
    public function getUserId()
    {
        return $this->user_id;
    }
    
    /**
     * start searching for users
     */
    protected function searchUsersForAppointments()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $ilTabs = $DIC['ilTabs'];
        
        $_SESSION['ch_apps'] = $_REQUEST['apps'];
        
        if (empty($_SESSION['ch_apps'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $GLOBALS['DIC']['ilCtrl']->redirect($this, 'appointmentList');
        }
        $_REQUEST['assignM'] = 1;
        $ilCtrl->setCmdClass('ilrepositorysearchgui');
        $ilCtrl->setcmd('');
        $this->executeCommand();
    }
    
    /**
     * Send info message about unassigned users
     * @param array $unassigned
     */
    protected function sendInfoAboutUnassignedUsers($unassigned)
    {
        if (!$unassigned) {
            return true;
        }
        $users = array();
        foreach ($unassigned as $user_id) {
            include_once './Services/User/classes/class.ilObjUser.php';
            $users[] = ilObjUser::_lookupFullname($user_id);
        }
        ilUtil::sendInfo($this->lng->txt('cal_ch_user_assignment_failed_info') . '<br />' . implode('<br />', $users), true);
        return true;
    }
    
    /**
     * Assign users to multiple appointments
     * @param type $users
     */
    public function assignUsersToAppointments(array $users)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        
        $unassigned_users = array();
        foreach ($_SESSION['ch_apps'] as $app) {
            $unassigned_users = array_unique(array_merge($unassigned_users, $this->assignUsersToAppointment($users, $app, false)));
        }
        
        $this->sendInfoAboutUnassignedUsers($unassigned_users);
        $ilCtrl->redirect($this, 'appointmentList');
    }


    /**
     * Assign users to an appointment
     * @param array $usr_ids
     * @return array $unassigned_users
     */
    public function assignUsersToAppointment(array $users, $a_app = 0, $a_redirect = true)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        
        if ($a_app) {
            $app = $a_app;
        } else {
            $app = $_REQUEST['apps'];
        }
        
        if (!count($users)) {
            ilUtil::sendFailure($GLOBALS['DIC']->language()->txt('select_one'), true);
            return false;
        }
        
        
        include_once './Services/Booking/classes/class.ilBookingEntry.php';
        $booking = ilBookingEntry::getInstanceByCalendarEntryId($app);
        
        $assigned_users = array();
        foreach ($users as $user) {
            if ($booking->getCurrentNumberOfBookings($app) >= $booking->getNumberOfBookings()) {
                break;
            }
            if (!ilBookingEntry::lookupBookingsOfUser((array) $app, $user)) {
                include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourUtils.php';
                ilConsultationHourUtils::bookAppointment($user, $app);
                $assigned_users[] = $user;
            }
        }
        
        $unassigned_users = array_diff($users, $assigned_users);
        
        if ($a_redirect) {
            $this->sendInfoAboutUnassignedUsers($unassigned_users);
            $ilCtrl->redirect($this, 'appointmentList');
        } else {
            return $unassigned_users;
        }
    }

    /**
     *
     * @param array $usr_ids
     * @param type $type
     */
    public function assignUsersToGroup(array $usr_ids)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        $group_id = (int) $_REQUEST['grp_id'];
        
        $tomorrow = new ilDateTime(time(), IL_CAL_UNIX);
        $tomorrow->increment(IL_CAL_DAY, 1);
        
        // Get all future consultation hours
        include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourAppointments.php';
        include_once './Services/Booking/classes/class.ilBookingEntry.php';
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
                    include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourUtils.php';
                    ilConsultationHourUtils::bookAppointment($user, $app);
                    $assigned_users[] = $user;
                }
            }
        }
        
        $this->sendInfoAboutUnassignedUsers(array_diff($users, $assigned_users));
        $ilCtrl->redirect($this, 'bookingList');
    }


    /**
     * Show consultation hour group
     * @global type $ilToolbar
     */
    protected function groupList()
    {
        global $DIC;

        $ilToolbar = $DIC['ilToolbar'];
        $ilTabs = $DIC['ilTabs'];
        $tpl = $DIC['tpl'];

        $ilToolbar->setFormAction($this->ctrl->getFormAction($this));
        $ilToolbar->addButton($this->lng->txt('cal_ch_add_grp'), $this->ctrl->getLinkTarget($this, 'addGroup'));
        
        $this->setSubTabs();
        $ilTabs->activateSubTab('cal_ch_app_grp');
        
        include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourGroupTableGUI.php';
        include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourGroups.php';
        $gtbl = new ilConsultationHourGroupTableGUI($this, 'groupList', $this->getUserId());
        $gtbl->parse(ilConsultationHourGroups::getGroupsOfUser($this->getUserId()));
        
        $tpl->setContent($gtbl->getHTML());
    }
    
    /**
     * Show add group form
     * @global type $ilToolbar
     * @global type $ilTabs
     */
    protected function addGroup(ilPropertyFormGUI $form = null)
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];
        $tpl = $DIC['tpl'];

        $this->setSubTabs();
        $ilTabs->activateSubTab('cal_ch_app_grp');
        
        if ($form == null) {
            $form = $this->initGroupForm();
        }
        $tpl->setContent($form->getHTML());
    }
    
    /**
     * Save new group
     */
    protected function saveGroup()
    {
        $form = $this->initGroupForm();
        if ($form->checkInput()) {
            include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourGroup.php';
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
     * @global type $ilCtrl
     * @param ilPropertyFormGUI $form
     */
    protected function editGroup(ilPropertyFormGUI $form = null)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $ilTabs = $DIC['ilTabs'];
        
        $ilCtrl->setParameter($this, 'grp_id', (int) $_REQUEST['grp_id']);
        $this->setSubTabs();
        $ilTabs->activateSubTab('cal_ch_app_grp');
        
        if ($form == null) {
            $form = $this->initGroupForm((int) $_REQUEST['grp_id']);
        }
        $tpl->setContent($form->getHTML());
    }
    
    /**
     * Update group
     * @global type $ilCtrl
     * @global type $tpl
     * @global type $ilTabs
     */
    protected function updateGroup()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $ilTabs = $DIC['ilTabs'];
        
        $ilCtrl->setParameter($this, 'grp_id', (int) $_REQUEST['grp_id']);
        
        $form = $this->initGroupForm((int) $_REQUEST['grp_id']);
        if ($form->checkInput()) {
            include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourGroup.php';
            $group = new ilConsultationHourGroup((int) $_REQUEST['grp_id']);
            $group->setTitle($form->getInput('title'));
            $group->setMaxAssignments($form->getInput('multiple'));
            $group->setUserId($this->getUserId());
            $group->update();
            
            ilUtil::sendSuccess($GLOBALS['DIC']['lng']->txt('settings_saved'), true);
            $GLOBALS['DIC']['ilCtrl']->redirect($this, 'groupList');
        }
        
        ilUtil::sendFailure($GLOBALS['DIC']['lng']->txt('err_check_input'), true);
        $this->editGroup($form);
    }
    
    /**
     * Confirm delete
     * @global type $ilCtrl
     * @global type $ilTabs
     */
    protected function confirmDeleteGroup()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $ilTabs = $DIC['ilTabs'];
        $tpl = $DIC['tpl'];
        
        $ilCtrl->setParameter($this, 'grp_id', (int) $_REQUEST['grp_id']);
        $groups = array((int) $_REQUEST['grp_id']);
        
        $this->setSubTabs();
        $ilTabs->activateSubTab('cal_ch_app_grp');
        

        include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($ilCtrl->getFormAction($this));
        $confirm->setHeaderText($GLOBALS['DIC']['lng']->txt('cal_ch_grp_delete_sure'));
        $confirm->setConfirm($GLOBALS['DIC']['lng']->txt('delete'), 'deleteGroup');
        $confirm->setCancel($GLOBALS['DIC']['lng']->txt('cancel'), 'groupList');
        
        foreach ($groups as $grp_id) {
            include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourGroup.php';
            $group = new ilConsultationHourGroup($grp_id);
            
            $confirm->addItem('groups[]', $grp_id, $group->getTitle());
        }
        $tpl->setContent($confirm->getHTML());
    }
    
    /**
     * Delete groups
     */
    protected function deleteGroup()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        
        foreach ((array) $_REQUEST['groups'] as $grp_id) {
            include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourGroup.php';
            $group = new ilConsultationHourGroup($grp_id);
            $group->delete();
        }
        ilUtil::sendSuccess($GLOBALS['DIC']['lng']->txt('cal_ch_grp_deleted'));
        $ilCtrl->redirect($this, 'groupList');
    }

    /**
     * Init new/update group form
     */
    protected function initGroupForm($a_group_id = 0)
    {
        include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourGroup.php';
        $group = new ilConsultationHourGroup($a_group_id);
        
        include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
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
        $multiple->setValue($group->getMaxAssignments());
        $form->addItem($multiple);
        
        return $form;
    }
    
    /**
     * Show list of bookings
     */
    protected function bookingList()
    {
        global $DIC;

        $ilToolbar = $DIC['ilToolbar'];
        $ilTabs = $DIC['ilTabs'];
        $tpl = $DIC['tpl'];
        
        $this->setSubTabs();
        $ilTabs->activateSubTab('cal_ch_app_bookings');
        
        include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourBookingTableGUI.php';
        $btable = new ilConsultationHourBookingTableGUI($this, 'bookingList', $this->getUserId());
        $btable->parse(ilConsultationHourAppointments::getAppointmentIds($this->getUserId()));
        $tpl->setContent($btable->getHTML());
    }
    
    /**
     * Show delete booking confirmation
     */
    protected function confirmDeleteBooking()
    {
        $this->confirmRejectBooking(false);
    }

    /**
     * Show delete booking confirmation
     */
    protected function confirmRejectBooking($a_send_notification = true)
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];
        $tpl = $DIC['tpl'];
        
        $this->setSubTabs();
        $ilTabs->activateSubTab('cal_ch_app_bookings');
        
        include_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');

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

        include_once 'Services/Calendar/classes/class.ilCalendarEntry.php';
        foreach ((array) $_REQUEST['bookuser'] as $bookuser) {
            $ids = explode('_', $bookuser);
            
            include_once './Services/Calendar/classes/class.ilCalendarEntry.php';
            include_once './Services/User/classes/class.ilUserUtil.php';
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
        $tpl->setContent($confirm->getHTML());
    }
    
    /**
     * Delete booking
     */
    protected function deleteBooking()
    {
        $this->rejectBooking(false);
    }
    
    /**
     *
     * @param type $a_send_notification
     */
    protected function rejectBooking($a_send_notification = true)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        
        foreach ((array) $_REQUEST['bookuser'] as $bookuser) {
            $ids = explode('_', $bookuser);
            
            include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourUtils.php';
            ilConsultationHourUtils::cancelBooking($ids[1], $ids[0], $a_send_notification);
        }
        if ($a_send_notification) {
            ilUtil::sendSuccess($this->lng->txt('cal_ch_canceled_bookings'), true);
        } else {
            ilUtil::sendSuccess($this->lng->txt('cal_ch_deleted_bookings'), true);
        }
        $ilCtrl->redirect($this, 'bookingList');
    }
    
    /**
     * Show settings of consultation hours
     * @todo add list/filter of consultation hours if user is responsible for more than one other consultation hour series.
     * @return
     */
    protected function appointmentList()
    {
        global $DIC;

        $ilToolbar = $DIC['ilToolbar'];
        $ilHelp = $DIC['ilHelp'];
        $ilTabs = $DIC['ilTabs'];

        $ilHelp->setScreenId("consultation_hours");
        
        $ilToolbar->setFormAction($this->ctrl->getFormAction($this));
        $ilToolbar->addButton($this->lng->txt('cal_ch_add_sequence'), $this->ctrl->getLinkTarget($this, 'createSequence'));
        
        $this->setSubTabs();
        $ilTabs->activateSubTab('cal_ch_app_list');
        
        include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHoursTableGUI.php';
        $tbl = new ilConsultationHoursTableGUI($this, 'appointmentList', $this->getUserId());
        $tbl->parse();
        $this->tpl->setContent($tbl->getHTML());
    }
    
    /**
     * Create new sequence
     * @return
     */
    protected function createSequence()
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
     * Init form
     * @param int $a_mode
     * @return
     */
    protected function initFormSequence($a_mode)
    {
        include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
        
        include_once('./Services/YUI/classes/class.ilYuiUtil.php');
        ilYuiUtil::initDomEvent();
        
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        
        switch ($a_mode) {
            case self::MODE_CREATE:
                $this->form->setTitle($this->lng->txt('cal_ch_add_sequence'));
                $this->form->addCommandButton('saveSequence', $this->lng->txt('save'));
                $this->form->addCommandButton('appointmentList', $this->lng->txt('cancel'));
                break;

            /*
            case self::MODE_UPDATE:
                $this->form->setTitle($this->lng->txt('cal_ch_edit_sequence'));
                $this->form->addCommandButton('updateSequence', $this->lng->txt('save'));
                $this->form->addCommandButton('appointmentList', $this->lng->txt('cancel'));
                break;
             */

            case self::MODE_MULTI:
                $this->form->setTitle($this->lng->txt('cal_ch_multi_edit_sequence'));
                $this->form->addCommandButton('updateMulti', $this->lng->txt('save'));
                $this->form->addCommandButton('appointmentList', $this->lng->txt('cancel'));
                break;
        }
        
        // in case of existing groups show a selection
        include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourGroups.php';
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
            include_once './Services/Form/classes/class.ilDateTimeInputGUI.php';
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
            include_once('./Services/Calendar/classes/Form/class.ilRecurrenceInputGUI.php');
            $rec = new ilRecurrenceInputGUI($this->lng->txt('cal_recurrences'), 'frequence');
            $rec->setEnabledSubForms(
                array(
                    IL_CAL_FREQ_DAILY,
                    IL_CAL_FREQ_WEEKLY,
                    IL_CAL_FREQ_MONTHLY
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
    }
    
    /**
     * Save new sequence
     * @return
     */
    protected function saveSequence()
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];
        
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
            include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourGroups.php';
            if (ilConsultationHourGroups::getGroupsOfUser($this->getUserId())) {
                $booking->setBookingGroup((int) $this->form->getInput('grp'));
            }

            $tgt = explode(',', $this->form->getInput('tgt'));
            $obj_ids = array();
            foreach ((array) $tgt as $ref_id) {
                if (!trim($ref_id)) {
                    continue;
                }
                $obj_id = $ilObjDataCache->lookupObjId($ref_id);
                $type = ilObject::_lookupType($obj_id);
                $valid_types = array('crs','grp');
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
     * @param ilBookingEntry $booking
     * @return
     */
    protected function createAppointments(ilBookingEntry $booking)
    {
        include_once './Services/Calendar/classes/class.ilDateList.php';
        $concurrent_dates = new ilDateList(ilDateList::TYPE_DATETIME);
        $start = clone $this->form->getItemByPostVar('st')->getDate();
        for ($i = 0; $i < $this->form->getItemByPostVar('ap')->getValue(); $i++) {
            $concurrent_dates->add(clone $start);
            
            $start->increment(ilDateTime::MINUTE, $this->form->getItemByPostVar('du')->getMinutes());
            $start->increment(ilDateTime::HOUR, $this->form->getItemByPostVar('du')->getHours());
            #$start = new ilDateTime(,IL_CAL_UNIX);
        }
        
        include_once './Services/Calendar/classes/class.ilCalendarUtil.php';
        $def_cat = ilCalendarUtil::initDefaultCalendarByType(ilCalendarCategory::TYPE_CH, $this->getUserId(), $this->lng->txt('cal_ch_personal_ch'), true);
        
        // Add calendar appointment for each
        include_once './Services/Calendar/classes/class.ilCalendarCategoryAssignments.php';
        include_once './Services/Calendar/classes/class.ilCalendarEntry.php';
        include_once './Services/Calendar/classes/class.ilCalendarRecurrenceCalculator.php';
        include_once './Services/Booking/classes/class.ilBookingPeriod.php';
        
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
    
    /**
     * Set tabs
     * @return
     */
    protected function setTabs()
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];
        $ilUser = $DIC['ilUser'];
        $ilCtrl = $DIC['ilCtrl'];

        $ilCtrl->setParameter($this, 'user_id', '');
        $ilTabs->addTab('consultation_hours_' . $ilUser->getId(), $this->lng->txt('cal_ch_ch'), $this->ctrl->getLinkTarget($this, 'appointmentList'));

        foreach (ilConsultationHourAppointments::getManagedUsers() as $user_id => $login) {
            $ilCtrl->setParameter($this, 'user_id', $user_id);
            $ilTabs->addTab('consultation_hours_' . $user_id, $this->lng->txt('cal_ch_ch') . ': ' . $login, $this->ctrl->getLinkTarget($this, 'appointmentList'));
        }
        $ilCtrl->setParameter($this, 'user_id', '');

        $ilTabs->addTab('ch_settings', $this->lng->txt('settings'), $this->ctrl->getLinkTarget($this, 'settings'));

        $ilTabs->activateTab('consultation_hours_' . $this->getUserId());
    }

    /**
     * Set sub tabs
     * @global type $ilTabs
     * @global type $ilCtrl
     */
    protected function setSubTabs()
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];
        $ilCtrl = $DIC['ilCtrl'];
        
        $ilCtrl->setParameter($this, 'user_id', $this->getUserId());
        $ilTabs->addSubTab('cal_ch_app_list', $this->lng->txt('cal_ch_app_list'), $ilCtrl->getLinkTarget($this, 'appointmentList'));
        $ilTabs->addSubTab('cal_ch_app_grp', $this->lng->txt('cal_ch_app_grp'), $ilCtrl->getLinkTarget($this, 'groupList'));
        $ilTabs->addSubTab('cal_ch_app_bookings', $this->lng->txt('cal_ch_app_bookings'), $ilCtrl->getLinkTarget($this, 'bookingList'));
    }

    /**
     * Edit multiple sequence items
     */
    public function edit()
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];
        
        if (!isset($_REQUEST['apps'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            return $this->appointmentList();
        }

        $this->initFormSequence(self::MODE_MULTI);
        
        if ($_REQUEST['apps'] && !is_array($_REQUEST['apps'])) {
            $_REQUEST['apps'] = explode(';', $_REQUEST['apps']);
        }

        $hidden = new ilHiddenInputGUI('apps');
        $hidden->setValue(implode(';', $_REQUEST['apps']));
        $this->form->addItem($hidden);
        
        include_once 'Services/Calendar/classes/class.ilCalendarEntry.php';
        $first = $_REQUEST['apps'];
        $first = array_shift($_REQUEST['apps']);
        $entry = new ilCalendarEntry($first);

        $this->form->getItemByPostVar('ti')->setValue($entry->getTitle());
        $this->form->getItemByPostVar('lo')->setValue($entry->getLocation());
        $this->form->getItemByPostVar('de')->setValue($entry->getDescription());
        
        include_once 'Services/Booking/classes/class.ilBookingEntry.php';
        $booking = new ilBookingEntry($entry->getContextId());

        $this->form->getItemByPostVar('bo')->setValue($booking->getNumberOfBookings());
        
        $ref_ids = array();
        foreach ($booking->getTargetObjIds() as $obj_id) {
            $refs = ilObject::_getAllReferences($obj_id);
            $ref_ids[] = end($refs);
        }
        $this->form->getItemByPostVar('tgt')->setValue(implode(',', $ref_ids));
        
        $deadline = $booking->getDeadlineHours();
        $this->form->getItemByPostVar('dead')->setDays(floor($deadline / 24));
        $this->form->getItemByPostVar('dead')->setHours($deadline % 24);
        
        if ($booking->getBookingGroup()) {
            $this->form->getItemByPostVar('grp')->setValue($booking->getBookingGroup());
        }

        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * Update multiple sequence items
     * @return
     */
    protected function updateMulti()
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];
        
        $this->initFormSequence(self::MODE_MULTI);

        if ($this->form->checkInput()) {
            $this->form->setValuesByPost();
            $apps = explode(';', $_POST['apps']);
            
            include_once 'Services/Booking/classes/class.ilBookingEntry.php';
            include_once 'Services/Calendar/classes/class.ilCalendarEntry.php';

            // do collision-check if max bookings were reduced
            // no collision check
            $first = $apps;
            $first = array_shift($first);
            $entry = ilBookingEntry::getInstanceByCalendarEntryId($first);
            #if($this->form->getInput('bo') < $entry->getNumberOfBookings())
            #{
            #   $this->edit();
            #   return;
            #}

            #22195 (if we create a new context instead of update the existing one we will mess up the calendar entries)
            $booking = new ilBookingEntry($entry->getId());
            // create new context
            //$booking = new ilBookingEntry();
            
            $booking->setObjId($this->getUserId());
            $booking->setNumberOfBookings($this->form->getInput('bo'));

            $deadline = $this->form->getInput('dead');
            $deadline = $deadline['dd'] * 24 + $deadline['hh'];
            $booking->setDeadlineHours($deadline);

            $tgt = explode(',', $this->form->getInput('tgt'));
            $obj_ids = array();
            foreach ((array) $tgt as $ref_id) {
                if (!trim($ref_id)) {
                    continue;
                }
                $obj_id = $ilObjDataCache->lookupObjId($ref_id);
                $type = ilObject::_lookupType($obj_id);
                $valid_types = array('crs','grp');
                if (!$obj_id or !in_array($type, $valid_types)) {
                    ilUtil::sendFailure($this->lng->txt('cal_ch_unknown_repository_object'));
                    $this->edit();
                    return;
                }
                $obj_ids[] = $obj_id;
            }
            $booking->setTargetObjIds($obj_ids);
            
            include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourGroups.php';
            if (ilConsultationHourGroups::getCountGroupsOfUser($this->getUserId())) {
                $booking->setBookingGroup($this->form->getInput('grp'));
            }
            #22195 update the booking instead of save new one.
            $booking->update();
            //$booking->save();


            // update entries
            $title = $this->form->getInput('ti');
            $location = $this->form->getInput('lo');
            $description = $this->form->getInput('de');
            
            foreach ($apps as $item_id) {
                $entry = new ilCalendarEntry($item_id);
                $entry->setContextId($booking->getId());
                $entry->setTitle($title);
                $entry->setLocation($location);
                $entry->setDescription($description);
                $entry->update();
            }

            ilBookingEntry::removeObsoleteEntries();

            ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'appointmentList');
        }
        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * confirm delete for multiple entries
     */
    public function confirmDelete()
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        
        if (!isset($_REQUEST['apps'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            return $this->appointmentList();
        }

        include_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
        
        
        $this->ctrl->saveParameter($this, array('seed','app_id','dt'));

        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setHeaderText($this->lng->txt('cal_delete_app_sure'));
        $confirm->setCancel($this->lng->txt('cancel'), 'cancel');

        include_once 'Services/Calendar/classes/class.ilCalendarEntry.php';
        
        $bookings_available = array();
        foreach ((array) $_REQUEST['apps'] as $entry_id) {
            $entry = new ilCalendarEntry($entry_id);
            $confirm->addItem('apps[]', $entry_id, ilDatePresentation::formatDate($entry->getStart()) . ', ' . $entry->getTitle());
            
            include_once './Services/Booking/classes/class.ilBookingEntry.php';
            if (ilBookingEntry::lookupBookingsForAppointment($entry_id)) {
                $bookings_available[] = ilDatePresentation::formatDate($entry->getStart()) . ', ' . $entry->getTitle();
            }
        }
        
        if ($bookings_available) {
            ilUtil::sendInfo($this->lng->txt('cal_ch_delete_app_booking_info') . '<br />' . implode('<br />', $bookings_available));
        }

        $confirm->setConfirm($this->lng->txt('delete'), 'delete');
        $confirm->setCancel($this->lng->txt('cancel'), 'appointmentList');
        
        $tpl->setContent($confirm->getHTML());
    }

    /**
     * delete multiple entries
     */
    public function delete()
    {
        if (!isset($_POST['apps'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            return $this->appointmentList();
        }

        include_once 'Services/Calendar/classes/class.ilCalendarEntry.php';
        include_once 'Services/Calendar/classes/class.ilCalendarCategoryAssignments.php';
        foreach ($_POST['apps'] as $entry_id) {
            // cancel booking for users
            $booking = ilBookingEntry::getInstanceByCalendarEntryId($entry_id);
            if ($booking) {
                foreach ($booking->getCurrentBookings($entry_id) as $user_id) {
                    include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourUtils.php';
                    ilConsultationHourUtils::cancelBooking($user_id, $entry_id, false);
                }
            }
            // remove calendar entries
            include_once './Services/Calendar/classes/class.ilCalendarEntry.php';
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
    public function showProfile()
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        $ilTabs = $DIC['ilTabs'];
        $ilCtrl = $DIC['ilCtrl'];

        $ilTabs->clearTargets();

        $user_id = (int) $_GET['user'];
    
        include_once 'Services/User/classes/class.ilPublicUserProfileGUI.php';
        $profile = new ilPublicUserProfileGUI($user_id);
        $profile->setBackUrl($this->getProfileBackUrl());
        $tpl->setContent($ilCtrl->getHTML($profile));
    }
    
    /**
     * Build context-sensitive profile back url
     *
     * @return string
     */
    protected function getProfileBackUrl()
    {
        // from repository
        if (isset($_REQUEST["ref_id"])) {
            $url = $this->ctrl->getLinkTargetByClass('ilCalendarMonthGUI');
        }
        // from panel
        elseif (isset($_GET['panel'])) {
            $url = $this->ctrl->getLinkTargetByClass('ilCalendarPresentationGUI');
        }
        // from appointments
        else {
            $url = $this->ctrl->getLinkTarget($this, 'appointmentList');
        }
        return $url;
    }

    /**
     * display settings gui
     */
    public function settings()
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        $ilTabs = $DIC['ilTabs'];
        $ilHelp = $DIC['ilHelp'];

        $ilHelp->setScreenId("consultation_hours_settings");
        $ilTabs->activateTab('ch_settings');
        
        $form = $this->initSettingsForm();
        $tpl->setContent($form->getHTML());
    }

    /**
     * build settings form
     * @return object
     */
    protected function initSettingsForm()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];

        include_once './Services/Form/classes/class.ilPropertyFormGUI.php';

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        $mng = new ilTextInputGUI($this->lng->txt('cal_ch_manager'), 'mng');
        $mng->setInfo($this->lng->txt('cal_ch_manager_info'));
        $form->addItem($mng);

        $mng->setValue(ilConsultationHourAppointments::getManager(true));

        $form->setTitle($this->lng->txt('settings'));
        $form->addCommandButton('updateSettings', $this->lng->txt('save'));
        // $form->addCommandButton('appointmentList', $this->lng->txt('cancel'));
        return $form;
    }

    /**
     * save settings
     */
    public function updateSettings()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilUser = $DIC['ilUser'];
        $tpl = $DIC['tpl'];
        $ilTabs = $DIC['ilTabs'];
        
        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            $mng = $form->getInput('mng');
            if (ilConsultationHourAppointments::setManager($mng)) {
                ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
                $ilCtrl->redirect($this, 'settings');
            } else {
                $ilTabs->activateTab('ch_settings');

                ilUtil::sendFailure($this->lng->txt('cal_ch_unknown_user'));
                $field = $form->getItemByPostVar('mng');
                $field->setValue($mng);
                $tpl->setContent($form->getHTML());
                return;
            }
        }
    }
}
