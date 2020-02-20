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

include_once './Services/Calendar/classes/class.ilCalendarSettings.php';

/**
* GUI class for YUI appointment panels
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesCalendar
*/
class ilCalendarAppointmentPanelGUI
{
    protected $seed = null;

    protected static $counter = 0;
    protected static $instance = null;

    protected $settings = null;
    
    protected $tpl = null;
    protected $lng = null;
    protected $ctrl = null;

    /**
     * Singleton
     *
     * @access public
     * @param
     * @return
     */
    protected function __construct(ilDate $seed = null)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->settings = ilCalendarSettings::_getInstance();

        $this->seed = $seed;
    }
    
    /**
     * get singleton instance
     *
     * @access public
     * @param
     * @return
     * @static
     */
    public static function _getInstance(ilDate $seed)
    {
        if (isset(self::$instance) and self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilCalendarAppointmentPanelGUI($seed);
    }

    /**
     * Get seed date
     */
    public function getSeed()
    {
        return $this->seed;
    }
    
    
    /**
     * get HTML
     *
     * @access public
     * @param
     * @return
     */
    public function getHTML($a_app)
    {
        global $DIC;

        $tree = $DIC['tree'];
        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];
        
        self::$counter++;
        
        $this->tpl = new ilTemplate('tpl.appointment_panel.html', true, true, 'Services/Calendar');
        
        // Panel variables
        $this->tpl->setVariable('PANEL_NUM', self::$counter);
        $this->tpl->setVariable('PANEL_TITLE', str_replace(' ()', '', $a_app['event']->getPresentationTitle(false)));
        if ($a_app["event"]->isMilestone()) {
            $this->tpl->setVariable('PANEL_DETAILS', $this->lng->txt('cal_ms_details'));
        } else {
            $this->tpl->setVariable('PANEL_DETAILS', $this->lng->txt('cal_details'));
        }
        $this->tpl->setVariable('PANEL_TXT_DATE', $this->lng->txt('date'));
        
        if ($a_app['fullday']) {
            $this->tpl->setVariable('PANEL_DATE', ilDatePresentation::formatPeriod(
                new ilDate($a_app['dstart'], IL_CAL_UNIX),
                new ilDate($a_app['dend'], IL_CAL_UNIX)
            ));
        } else {
            $this->tpl->setVariable('PANEL_DATE', ilDatePresentation::formatPeriod(
                new ilDateTime($a_app['dstart'], IL_CAL_UNIX),
                new ilDateTime($a_app['dend'], IL_CAL_UNIX)
            ));
        }
        if ($a_app['event']->getLocation()) {
            $this->tpl->setVariable('PANEL_TXT_WHERE', $this->lng->txt('cal_where'));
            $this->tpl->setVariable('PANEL_WHERE', ilUtil::makeClickable($a_app['event']->getLocation()), true);
        }
        if ($a_app['event']->getDescription()) {
            $this->tpl->setVariable('PANEL_TXT_DESC', $this->lng->txt('description'));
            $this->tpl->setVariable('PANEL_DESC', ilUtil::makeClickable(nl2br($a_app['event']->getDescription())));
        }

        if ($a_app['event']->isMilestone() && $a_app['event']->getCompletion() > 0) {
            $this->tpl->setVariable('PANEL_TXT_COMPL', $this->lng->txt('cal_task_completion'));
            $this->tpl->setVariable('PANEL_COMPL', $a_app['event']->getCompletion() . " %");
        }

        if ($a_app['event']->isMilestone()) {
            // users responsible
            $users = $a_app['event']->readResponsibleUsers();
            $delim = "";
            foreach ($users as $r) {
                $value.= $delim . $r["lastname"] . ", " . $r["firstname"] . " [" . $r["login"] . "]";
                $delim = "<br />";
            }
            if (count($users) > 0) {
                $this->tpl->setVariable('PANEL_TXT_RESP', $this->lng->txt('cal_responsible'));
                $this->tpl->setVariable('PANEL_RESP', $value);
            }
        }

        include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
        $cat_id = ilCalendarCategoryAssignments::_lookupCategory($a_app['event']->getEntryId());
        $cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($cat_id);
        $entry_obj_id = isset($cat_info['subitem_obj_ids'][$cat_id]) ?
            $cat_info['subitem_obj_ids'][$cat_id] :
            $cat_info['obj_id'];

        $this->tpl->setVariable('PANEL_TXT_CAL_TYPE', $this->lng->txt('cal_cal_type'));
        switch ($cat_info['type']) {
            case ilCalendarCategory::TYPE_GLOBAL:
                $this->tpl->setVariable('PANEL_CAL_TYPE', $this->lng->txt('cal_type_system'));
                break;
                
            case ilCalendarCategory::TYPE_USR:
                $this->tpl->setVariable('PANEL_CAL_TYPE', $this->lng->txt('cal_type_personal'));
                break;
            
            case ilCalendarCategory::TYPE_OBJ:
                $type = ilObject::_lookupType($cat_info['obj_id']);
                $this->tpl->setVariable('PANEL_CAL_TYPE', $this->lng->txt('cal_type_' . $type));
                
                // Course group appointment registration
                if ($this->settings->isCGRegistrationEnabled() and $type == 'crs' or $type == 'grp') {
                    if (!$a_app['event']->isAutoGenerated()) {
                        include_once './Services/Calendar/classes/class.ilCalendarRegistration.php';
                        $reg = new ilCalendarRegistration($a_app['event']->getEntryId());
                        
                        if ($reg->isRegistered($ilUser->getId(), new ilDateTime($a_app['dstart'], IL_CAL_UNIX), new ilDateTime($a_app['dend'], IL_CAL_UNIX))) {
                            $this->tpl->setCurrentBlock('panel_cancel_book_link');
                            $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'seed', $this->getSeed()->get(IL_CAL_DATE));
                            $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'app_id', $a_app['event']->getEntryId());
                            $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'dstart', $a_app['dstart']);
                            $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'dend', $a_app['dend']);
                            
                            $this->tpl->setVariable('TXT_PANEL_CANCELBOOK', $this->lng->txt('cal_reg_unregister'));
                            $this->tpl->setVariable('PANEL_CANCELBOOK_HREF', $this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui', 'confirmUnregister'));
                            $this->tpl->parseCurrentBlock();
                        } else {
                            $this->tpl->setCurrentBlock('panel_book_link');
                            $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'seed', $this->getSeed()->get(IL_CAL_DATE));
                            $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'app_id', $a_app['event']->getEntryId());
                            $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'dstart', $a_app['dstart']);
                            $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'dend', $a_app['dend']);
                            $this->tpl->setVariable('TXT_PANEL_BOOK', $this->lng->txt('cal_reg_register'));
                            $this->tpl->setVariable('PANEL_BOOK_HREF', $this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui', 'confirmRegister'));
                            $this->tpl->parseCurrentBlock();
                        }
                                            
                        include_once './Services/Link/classes/class.ilLink.php';
                        $registrations = array();
                        foreach ($reg->getRegisteredUsers(new ilDateTime($a_app['dstart'], IL_CAL_UNIX), new ilDateTime($a_app['dend'], IL_CAL_UNIX)) as $usr_data) {
                            $usr_id = $usr_data;
                            $this->ctrl->setParameterByClass('ilconsultationhoursgui', 'user', $usr_id);
                            $registrations[] = '<a href="' . $this->ctrl->getLinkTargetByClass('ilconsultationhoursgui', 'showprofile') . '">' . ilObjUser::_lookupFullname($usr_id);
                            $this->ctrl->setParameterByClass('ilconsultationhoursgui', 'user', '');
                        }
                        if (count($registrations)) {
                            $this->tpl->setCurrentBlock('panel_current_booking');
                            $this->tpl->setVariable('PANEL_TXT_CURRENT_BOOKING', $this->lng->txt('cal_reg_registered_users'));
                            $this->tpl->setVariable('PANEL_CURRENT_BOOKING', implode('<br />', $registrations));
                            $this->tpl->parseCurrentBlock();
                        }
                    }
                }
                break;
                
            case ilCalendarCategory::TYPE_CH:
                $this->tpl->setVariable('PANEL_CAL_TYPE', $this->lng->txt('cal_ch_ch'));

                include_once 'Services/Booking/classes/class.ilBookingEntry.php';
                $entry = new ilBookingEntry($a_app['event']->getContextId());

                $is_owner = $entry->isOwner();
                $user_entry = ($cat_info['obj_id'] == $ilUser->getId());

                if ($user_entry && !$is_owner) {
                    // find source calendar entry in owner calendar
                    include_once 'Services/Calendar/classes/ConsultationHours/class.ilConsultationHourAppointments.php';
                    $apps = ilConsultationHourAppointments::getAppointmentIds($entry->getObjId(), $a_app['event']->getContextId(), $a_app['event']->getStart());
                    $ref_event = $apps[0];
                } else {
                    $ref_event = $a_app['event']->getEntryId();
                }

                $this->tpl->setCurrentBlock('panel_booking_owner');
                $this->tpl->setVariable('PANEL_TXT_BOOKING_OWNER', $this->lng->txt('cal_ch_booking_owner'));
                $this->tpl->setVariable('PANEL_BOOKING_OWNER', ilObjUser::_lookupFullname($entry->getObjId()));
                $this->tpl->parseCurrentBlock();

                $this->tpl->setCurrentBlock('panel_max_booking');
                $this->tpl->setVariable('PANEL_TXT_MAX_BOOKING', $this->lng->txt('cal_ch_num_bookings'));
                $this->tpl->setVariable('PANEL_MAX_BOOKING', $entry->getNumberOfBookings());
                $this->tpl->parseCurrentBlock();

                if (!$is_owner) {
                    if ($entry->hasBooked($ref_event)) {
                        if (ilDateTime::_after($a_app['event']->getStart(), new ilDateTime(time(), IL_CAL_UNIX))) {
                            $this->tpl->setCurrentBlock('panel_cancel_book_link');
                            $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'app_id', $ref_event);
                            $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'seed', $this->getSeed()->get(IL_CAL_DATE));
                            $this->tpl->setVariable('TXT_PANEL_CANCELBOOK', $this->lng->txt('cal_ch_cancel_booking'));
                            $this->tpl->setVariable('PANEL_CANCELBOOK_HREF', $this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui', 'cancelBooking'));
                            $this->tpl->parseCurrentBlock();
                        }
                    }
                    #else if(!$entry->isBookedOut($ref_event))
                    elseif ($entry->isAppointmentBookableForUser($ref_event, $GLOBALS['DIC']['ilUser']->getId())) {
                        $this->tpl->setCurrentBlock('panel_book_link');
                        $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'app_id', $ref_event);
                        $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'seed', $this->getSeed()->get(IL_CAL_DATE));
                        $this->tpl->setVariable('TXT_PANEL_BOOK', $this->lng->txt('cal_ch_book'));
                        $this->tpl->setVariable('PANEL_BOOK_HREF', $this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui', 'book'));
                        $this->tpl->parseCurrentBlock();
                    }

                    $this->tpl->setCurrentBlock('panel_current_booking');
                    $this->tpl->setVariable('PANEL_TXT_CURRENT_BOOKING', $this->lng->txt('cal_ch_current_bookings'));
                    $this->tpl->setVariable('PANEL_CURRENT_BOOKING', $entry->getCurrentNumberOfBookings($ref_event));
                    $this->tpl->parseCurrentBlock();
                } else {
                    $obj_ids = $entry->getTargetObjIds();
                    foreach ($obj_ids as $obj_id) {
                        $title = ilObject::_lookupTitle($obj_id);
                        $refs = ilObject::_getAllReferences($obj_id);
                        include_once './Services/Link/classes/class.ilLink.php';
                        $this->tpl->setCurrentBlock('panel_booking_target_row');
                        $this->tpl->setVariable('PANEL_BOOKING_TARGET_TITLE', $title);
                        $this->tpl->setVariable('PANEL_BOOKING_TARGET', ilLink::_getLink(end($refs)));
                        $this->tpl->parseCurrentBlock();
                    }
                    if ($obj_ids) {
                        $this->tpl->setCurrentBlock('panel_booking_target');
                        $this->tpl->setVariable('PANEL_TXT_BOOKING_TARGET', $this->lng->txt('cal_ch_target_object'));
                        $this->tpl->parseCurrentBlock();
                    }

                    $link_users = true;
                    if (ilCalendarCategories::_getInstance()->getMode() == ilCalendarCategories::MODE_PORTFOLIO_CONSULTATION) {
                        $link_users = false;
                    }
                    
                    include_once './Services/Link/classes/class.ilLink.php';
                    $bookings = array();
                    $this->ctrl->setParameterByClass('ilconsultationhoursgui', 'panel', 1);
                    foreach ($entry->getCurrentBookings($a_app['event']->getEntryId()) as $user_id) {
                        if ($link_users) {
                            $this->ctrl->setParameterByClass('ilconsultationhoursgui', 'user', $user_id);
                            $bookings[] = '<a href="' . $this->ctrl->getLinkTargetByClass('ilconsultationhoursgui', 'showprofile') . '">' .
                                ilObjUser::_lookupFullname($user_id) . '</a>';
                            $this->ctrl->setParameterByClass('ilconsultationhoursgui', 'user', '');
                        } else {
                            $bookings[] = ilObjUser::_lookupFullname($user_id);
                        }
                    }
                    $this->ctrl->setParameterByClass('ilconsultationhoursgui', 'panel', '');
                    $this->tpl->setCurrentBlock('panel_current_booking');
                    $this->tpl->setVariable('PANEL_TXT_CURRENT_BOOKING', $this->lng->txt('cal_ch_current_bookings'));
                    $this->tpl->setVariable('PANEL_CURRENT_BOOKING', implode('<br />', $bookings));
                    $this->tpl->parseCurrentBlock();
                }
                break;

            case ilCalendarCategory::TYPE_BOOK:
                $this->tpl->setVariable('PANEL_CAL_TYPE', $this->lng->txt('cal_ch_booking'));

                $this->tpl->setCurrentBlock('panel_cancel_book_link');
                $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'app_id', $a_app['event']->getEntryId());
                $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'seed', $this->getSeed()->get(IL_CAL_DATE));
                $this->tpl->setVariable('TXT_PANEL_CANCELBOOK', $this->lng->txt('cal_ch_cancel_booking'));
                $this->tpl->setVariable('PANEL_CANCELBOOK_HREF', $this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui', 'cancelBooking'));
                $this->tpl->parseCurrentBlock();
                break;
        }

        $this->tpl->setVariable('PANEL_TXT_CAL_NAME', $this->lng->txt('cal_calendar_name'));
        $this->tpl->setVariable('PANEL_CAL_NAME', $cat_info['title']);
        

        if ($cat_info['editable'] and !$a_app['event']->isAutoGenerated()) {
            $this->tpl->setCurrentBlock('panel_edit_link');
            $this->tpl->setVariable('TXT_PANEL_EDIT', $this->lng->txt('edit'));
            
            $this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
            $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'seed', $this->getSeed()->get(IL_CAL_DATE));
            $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'app_id', $a_app['event']->getEntryId());
            $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'dt', $a_app['dstart']);
            $this->tpl->setVariable('PANEL_EDIT_HREF', $this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui', 'askEdit'));

            $this->tpl->setCurrentBlock('panel_delete_link');
            $this->tpl->setVariable('TXT_PANEL_DELETE', $this->lng->txt('delete'));

            $this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
            $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'seed', $this->getSeed()->get(IL_CAL_DATE));
            $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'app_id', $a_app['event']->getEntryId());
            $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'dt', $a_app['dstart']);
            $this->tpl->setVariable('PANEL_DELETE_HREF', $this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui', 'askdelete'));
            $this->tpl->parseCurrentBlock();
        }
        include_once('./Services/Calendar/classes/class.ilCalendarCategory.php');
        if ($cat_info['type'] == ilCalendarCategory::TYPE_OBJ) {
            $refs = ilObject::_getAllReferences($entry_obj_id);
            $type = ilObject::_lookupType($entry_obj_id);
            $title = ilObject::_lookupTitle($entry_obj_id) ?
                ilObject::_lookupTitle($entry_obj_id) :
                $lng->txt('obj_' . $type);

            include_once('./Services/Link/classes/class.ilLink.php');
            $href = ilLink::_getStaticLink(current($refs), ilObject::_lookupType($entry_obj_id));
            $parent = $tree->getParentId(current($refs));
            $parent_title = ilObject::_lookupTitle(ilObject::_lookupObjId($parent));
            $this->tpl->setVariable('PANEL_TXT_LINK', $this->lng->txt('ext_link'));
            $this->tpl->setVariable('PANEL_LINK_HREF', $href);
            $this->tpl->setVariable('PANEL_LINK_NAME', $title);
            $this->tpl->setVariable('PANEL_PARENT', $parent_title);
        }
        
        return $this->tpl->get();
    }
}
