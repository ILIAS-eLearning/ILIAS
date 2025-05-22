<?php

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

declare(strict_types=1);
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
    public function initialize(int $a_calendar_presentation_type): void
    {
        parent::initialize($a_calendar_presentation_type);
        $this->user_settings = ilCalendarUserSettings::_getInstanceByUserId($this->user->getId());
        $this->app_colors = new ilCalendarAppointmentColors($this->user->getId());
        if ($this->user->getTimeZone()) {
            $this->timezone = $this->user->getTimeZone();
        }
    }

    public function executeCommand(): void
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

    protected function inbox(): void
    {
        $this->tpl = new ilTemplate('tpl.inbox.html', true, true, 'components/ILIAS/Calendar');

        // agenda list
        $cal_list = new ilCalendarAgendaListGUI($this->seed);
        $html = $this->ctrl->getHTML($cal_list);
        $this->tpl->setVariable('CHANGED_TABLE', $html);
    }
}
