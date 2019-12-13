<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';
include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourAppointments.php';

/**
* Consultation hours administration
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesCalendar
*/
class ilConsultationHoursTableGUI extends ilTable2GUI
{
    private $user_id = 0;
    private $has_groups = false;
    
    /**
     * Constructor
     * @param object $a_gui
     * @param object $a_cmd
     * @param object $a_user_id
     * @return
     */
    public function __construct($a_gui, $a_cmd, $a_user_id)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        
        $this->user_id = $a_user_id;
        
        include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourGroups.php';
        $this->has_groups = ilConsultationHourGroups::getCountGroupsOfUser($a_user_id);
        
        $this->setId('chtg_' . $this->getUserId());
        parent::__construct($a_gui, $a_cmd);
        
        $this->addColumn('', 'f', 1);
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
        $this->setFormAction($ilCtrl->getFormAction($this->getParentObject(), $this->getParentCmd()));
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
    
    /**
     * get user id
     * @return
     */
    public function getUserId()
    {
        return $this->user_id;
    }
    
    /**
     * Check if user has created groups
     */
    public function hasGroups()
    {
        return $this->has_groups;
    }
    
    /**
     * Fill row
     * @return
     */
    public function fillRow($row)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        
        $this->tpl->setVariable('VAL_ID', $row['id']);
        $this->tpl->setVariable('START', $row['start_p']);
        $this->tpl->setVariable('TITLE', $row['title']);
        
        if ($this->hasGroups()) {
            $this->tpl->setVariable('TITLE_GROUP', $row['group']);
        }
        
        $this->tpl->setVariable('NUM_BOOKINGS', $row['num_bookings']);
        
        foreach ((array) $row['target_links'] as $link) {
            $this->tpl->setCurrentBlock('links');
            $this->tpl->setVariable('TARGET', $link['title']);
            $this->tpl->setVariable('URL_TARGET', $link['link']);
            $this->tpl->parseCurrentBlock();
        }
        if ($row['bookings']) {
            foreach ($row['bookings'] as $user_id => $name) {
                $user_profile_prefs = ilObjUser::_getPreferences($user_id);
                if ($user_profile_prefs["public_profile"] == "y") {
                    $this->tpl->setCurrentBlock('booking_with_link');
                    $ilCtrl->setParameter($this->getParentObject(), 'user', $user_id);
                    $this->tpl->setVariable('URL_BOOKING', $ilCtrl->getLinkTarget($this->getParentObject(), 'showprofile'));
                } else {
                    $this->tpl->setCurrentBlock('booking_without_link');
                }
                $ilCtrl->setParameter($this->getParentObject(), 'user', '');
                $this->tpl->setVariable('TXT_BOOKING', $name);
                $this->tpl->parseCurrentBlock();
            }
        }

        $this->tpl->setVariable('BOOKINGS', implode(', ', $row['bookings']));
        
        include_once './Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
        $list = new ilAdvancedSelectionListGUI();
        $list->setId('act_cht_' . $row['id']);
        $list->setListTitle($this->lng->txt('actions'));

        $ilCtrl->setParameter($this->getParentObject(), 'apps', $row['id']);
        $list->addItem(
            $this->lng->txt('edit'),
            '',
            $ilCtrl->getLinkTarget($this->getParentObject(), 'edit')
        );
        $list->addItem(
            $this->lng->txt('cal_ch_assign_participants'),
            '',
            $ilCtrl->getLinkTargetByClass('ilRepositorySearchGUI', '')
        );
        $list->addItem(
            $this->lng->txt('delete'),
            '',
            $ilCtrl->getLinkTarget($this->getParentObject(), 'confirmDelete')
        );
        $this->tpl->setVariable('ACTIONS', $list->getHTML());
    }
    
    /**
     * Parse appointments
     * @return
     */
    public function parse()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilObjDataCache = $DIC['ilObjDataCache'];

        include_once 'Services/Booking/classes/class.ilBookingEntry.php';

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
                include_once './Services/Link/classes/class.ilLink.php';
                $data[$counter]['target_links'][$refs_counter]['title'] = ilObject::_lookupTitle($obj_id);
                $data[$counter]['target_links'][$refs_counter]['link'] = ilLink::_getLink(end($refs));
                ++$refs_counter;
            }
            $counter++;
        }
        $this->setData($data);
    }
}
