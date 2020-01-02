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

include_once('Services/Calendar/classes/class.ilDate.php');
include_once('Services/Calendar/classes/class.ilCalendarHeaderNavigationGUI.php');
include_once('Services/Calendar/classes/class.ilCalendarUserSettings.php');
include_once('Services/Calendar/classes/class.ilCalendarAppointmentColors.php');
include_once('./Services/Calendar/classes/class.ilCalendarSchedule.php');
include_once './Services/Calendar/classes/class.ilCalendarViewGUI.php';



/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilCalendarInboxGUI: ilCalendarAppointmentGUI, ilCalendarAgendaListGUI
*
* @ingroup ServicesCalendar
*/
class ilCalendarInboxGUI extends ilCalendarViewGUI
{
    protected $user_settings = null;
        
    protected $lng;
    protected $ctrl;
    protected $tabs_gui;
    protected $tpl;
    protected $user;
    protected $toolbar;
    protected $timezone = 'UTC';

    /**
     * Constructor
     *
     * @access public
     * @param
     * @todo make parent constructor (initialize) and init also seed and other common stuff
     */
    public function __construct(ilDate $seed_date)
    {
        parent::__construct($seed_date, ilCalendarViewGUI::CAL_PRESENTATION_AGENDA_LIST);
        $this->user_settings = ilCalendarUserSettings::_getInstanceByUserId($this->user->getId());
        $this->app_colors = new ilCalendarAppointmentColors($this->user->getId());
        $this->timezone = $this->user->getTimeZone();
    }
    
    /**
     * Execute command
     *
     * @access public
     *
     */
    public function executeCommand()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];

        $next_class = $ilCtrl->getNextClass();
        switch ($next_class) {
            case 'ilcalendarappointmentgui':
                $this->ctrl->setReturn($this, '');
                $this->tabs_gui->setSubTabActive($_SESSION['cal_last_tab']);
                
                include_once('./Services/Calendar/classes/class.ilCalendarAppointmentGUI.php');
                $app = new ilCalendarAppointmentGUI($this->seed, $this->seed, (int) $_GET['app_id']);
                $this->ctrl->forwardCommand($app);
                break;

            case 'ilcalendaragendalistgui':
                include_once("./Services/Calendar/classes/Agenda/class.ilCalendarAgendaListGUI.php");
                $cal_list = new ilCalendarAgendaListGUI($this->seed);
                $html = $this->ctrl->forwardCommand($cal_list);
                $tpl->setContent($html);
                break;

            default:
                $cmd = $this->ctrl->getCmd("inbox");
                $this->$cmd();
                $tpl->setContent($this->tpl->get());
                break;
        }
        
        return true;
    }
    
    /**
     * show inbox
     */
    protected function inbox()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        $this->tpl = new ilTemplate('tpl.inbox.html', true, true, 'Services/Calendar');

        // agenda list
        $cal_list = new ilCalendarAgendaListGUI($this->seed);
        $html = $ilCtrl->getHTML($cal_list);
        $this->tpl->setVariable('CHANGED_TABLE', $html);
    }
}
