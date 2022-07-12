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
 * @author       Stefan Meyer <smeyer.ilias@gmx.de>
 * @ilCtrl_Calls ilCalendarInboxGUI: ilCalendarAppointmentGUI, ilCalendarAgendaListGUI
 * @ingroup      ServicesCalendar
 */
class ilCalendarInboxGUI extends ilCalendarViewGUI
{
    protected ?ilCalendarUserSettings $user_settings;
    protected string $timezone = 'UTC';
    protected ilCalendarAppointmentColors $app_colors;

    /**
     * Constructor
     * @access public
     * @param
     */
    public function __construct(ilDate $seed_date)
    {
        parent::__construct($seed_date, ilCalendarViewGUI::CAL_PRESENTATION_AGENDA_LIST);
    }

    /**
     * @inheritDoc
     */
    public function initialize(int $a_calendar_presentation_type) : void
    {
        parent::initialize($a_calendar_presentation_type);
        $this->user_settings = ilCalendarUserSettings::_getInstanceByUserId($this->user->getId());
        $this->app_colors = new ilCalendarAppointmentColors($this->user->getId());
        if ($this->user->getTimeZone()) {
            $this->timezone = $this->user->getTimeZone();
        }
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass();
        switch ($next_class) {
            case 'ilcalendarappointmentgui':
                $this->ctrl->setReturn($this, '');
                $this->tabs_gui->setSubTabActive((string) ilSession::get('cal_last_tab'));

                $app = new ilCalendarAppointmentGUI($this->seed, $this->seed, $this->initAppointmentIdFromQuery());
                $this->ctrl->forwardCommand($app);
                break;

            case 'ilcalendaragendalistgui':
                $cal_list = new ilCalendarAgendaListGUI($this->seed);
                $html = $this->ctrl->forwardCommand($cal_list);
                // this fixes 0027035 since many methods ilCalendarAppointmentGUI set their own content.
                if (strlen($html)) {
                    $this->main_tpl->setContent($html);
                }
                break;

            default:
                $cmd = $this->ctrl->getCmd("inbox");
                $this->$cmd();
                $this->main_tpl->setContent($this->tpl->get());
                break;
        }
    }

    protected function inbox() : void
    {
        $this->tpl = new ilTemplate('tpl.inbox.html', true, true, 'Services/Calendar');

        // agenda list
        $cal_list = new ilCalendarAgendaListGUI($this->seed);
        $html = $this->ctrl->getHTML($cal_list);
        $this->tpl->setVariable('CHANGED_TABLE', $html);
    }
}
