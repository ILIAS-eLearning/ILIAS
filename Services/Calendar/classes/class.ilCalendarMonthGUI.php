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
 * @author       Stefan Meyer <meyer@leifos.com>
 * @ilCtrl_Calls ilCalendarMonthGUI: ilCalendarAppointmentGUI
 * @ilCtrl_Calls ilCalendarMonthGUI: ilCalendarAppointmentPresentationGUI
 * @ingroup      ServicesCalendar
 */
class ilCalendarMonthGUI extends ilCalendarViewGUI
{
    protected int $bkid;    // booking user
    protected int $num_appointments = 1;
    protected array $schedule_filters = array();

    protected ilCalendarUserSettings $user_settings;
    protected ilCalendarAppointmentColors $app_colors;
    protected string $timezone = 'UTC';

    public function __construct(ilDate $seed_date, int $bkid = 0)
    {
        parent::__construct($seed_date, ilCalendarViewGUI::CAL_PRESENTATION_MONTH);
        // init booking user
        if ($bkid > 0) {    // e.g. portfolio sets this
            $this->bkid = $bkid;
        } else {
            $this->bkid = $this->initBookingUserFromQuery();
        }
    }

    public function initialize(int $a_calendar_presentation_type): void
    {
        parent::initialize($a_calendar_presentation_type);
        $this->tabs_gui->setSubTabActive('app_month');
        $this->user_settings = ilCalendarUserSettings::_getInstanceByUserId($this->user->getId());
        $this->app_colors = new ilCalendarAppointmentColors($this->user->getId());
        if ($this->user->getTimeZone()) {
            $this->timezone = $this->user->getTimeZone();
        }
    }

    public function executeCommand(): void
    {
        $this->ctrl->saveParameter($this, 'seed');

        $next_class = $this->ctrl->getNextClass();
        switch ($next_class) {
            case "ilcalendarappointmentpresentationgui":
                $this->ctrl->setReturn($this, "");
                $gui = ilCalendarAppointmentPresentationGUI::_getInstance($this->seed, $this->getCurrentApp());
                $this->ctrl->forwardCommand($gui);
                break;
            case 'ilcalendarappointmentgui':
                $this->ctrl->setReturn($this, '');
                $this->tabs_gui->setSubTabActive((string) ilSession::get('cal_last_tab'));
                // initial date for new calendar appointments
                $idate = new ilDate($this->initInitialDateFromQuery(), IL_CAL_DATE);
                $app = new ilCalendarAppointmentGUI($this->seed, $idate, $this->initAppointmentIdFromQuery());
                $this->ctrl->forwardCommand($app);
                break;

            default:
                $time = microtime(true);
                $cmd = $this->ctrl->getCmd("show");
                $this->$cmd();
                $this->main_tpl->setContent($this->tpl->get());
                break;
        }
    }

    /**
     * Add schedule filter
     */
    public function addScheduleFilter(ilCalendarScheduleFilter $a_filter): void
    {
        $this->schedule_filters[] = $a_filter;
    }

    public function show(): void
    {
        $this->tpl = new ilTemplate('tpl.month_view.html', true, true, 'Services/Calendar');

        ilYuiUtil::initDragDrop();

        $navigation = new ilCalendarHeaderNavigationGUI($this, $this->seed, ilDateTime::MONTH);
        $this->tpl->setVariable('NAVIGATION', $navigation->getHTML());

        for ($i = $this->user_settings->getWeekStart(); $i < (7 + $this->user_settings->getWeekStart()); $i++) {
            $this->tpl->setCurrentBlock('month_header_col');
            $this->tpl->setVariable('TXT_WEEKDAY', ilCalendarUtil::_numericDayToString($i, true));
            $this->tpl->parseCurrentBlock();
        }

        if ($this->bkid) {
            $user_id = $this->bkid;
            $disable_empty = true;
            $no_add = true;
        } else {
            if ($this->user->getId() == ANONYMOUS_USER_ID) {
                $user_id = $this->user->getId();
                $disable_empty = false;
                $no_add = true;
            } else {
                $user_id = $this->user->getId();
                $disable_empty = false;
                $no_add = false;
            }
        }

        $is_portfolio_embedded = false;
        if (ilCalendarCategories::_getInstance()->getMode() == ilCalendarCategories::MODE_PORTFOLIO_CONSULTATION) {
            $no_add = true;
            $is_portfolio_embedded = true;
        }
        $scheduler = new ilCalendarSchedule($this->seed, ilCalendarSchedule::TYPE_MONTH, $user_id);
        $scheduler->addSubitemCalendars(true);
        if (sizeof($this->schedule_filters)) {
            foreach ($this->schedule_filters as $filter) {
                $scheduler->addFilter($filter);
            }
        }
        $scheduler->calculate();

        $settings = ilCalendarSettings::_getInstance();

        $counter = 0;
        foreach (ilCalendarUtil::_buildMonthDayList(
            (int) $this->seed->get(IL_CAL_FKT_DATE, 'm'),
            (int) $this->seed->get(IL_CAL_FKT_DATE, 'Y'),
            $this->user_settings->getWeekStart()
        )->get() as $date) {
            $counter++;
            $has_events = (bool) $this->showEvents($scheduler, $date);

            if (!$this->view_with_appointments && $has_events) {
                $this->view_with_appointments = true;
            }

            if (!$no_add) {
                $this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
                $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'idate', $date->get(IL_CAL_DATE));
                $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'seed', $this->seed->get(IL_CAL_DATE));
                $new_app_url = $this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui', 'add');

                if ($settings->getEnableGroupMilestones()) {
                    $this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
                    $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'seed', $this->seed->get(IL_CAL_DATE));
                    $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'idate', $date->get(IL_CAL_DATE));
                    $new_ms_url = $this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui', 'addMilestone');

                    $this->tpl->setCurrentBlock("new_ms");
                    $this->tpl->setVariable('DD_ID', $date->get(IL_CAL_UNIX));
                    $this->tpl->setVariable(
                        'DD_TRIGGER',
                        $this->ui_renderer->render($this->ui_factory->symbol()->glyph()->add())
                    );
                    $this->tpl->setVariable('URL_DD_NEW_APP', $new_app_url);
                    $this->tpl->setVariable('TXT_DD_NEW_APP', $this->lng->txt('cal_new_app'));
                    $this->tpl->setVariable('URL_DD_NEW_MS', $new_ms_url);
                    $this->tpl->setVariable('TXT_DD_NEW_MS', $this->lng->txt('cal_new_ms'));
                    $this->tpl->parseCurrentBlock();
                } else {
                    $this->tpl->setCurrentBlock("new_app");
                    $this->tpl->setVariable(
                        'NEW_GLYPH',
                        $this->ui_renderer->render($this->ui_factory->symbol()->glyph()->add($new_app_url))
                    );
                    $this->tpl->parseCurrentBlock();
                }
            }

            $day = (int) $date->get(IL_CAL_FKT_DATE, 'j');
            $month = (int) $date->get(IL_CAL_FKT_DATE, 'n');

            if ($day == 1) {
                $month_day = '1 ' . ilCalendarUtil::_numericMonthToString($month, false);
            } else {
                $month_day = $day;
            }

            if (!$is_portfolio_embedded &&
                (!$disable_empty || $has_events)) {
                $this->tpl->setCurrentBlock('month_day_link');
                $this->ctrl->clearParametersByClass('ilcalendardaygui');
                $this->ctrl->setParameterByClass('ilcalendardaygui', 'seed', $date->get(IL_CAL_DATE));
                $this->tpl->setVariable('OPEN_DAY_VIEW', $this->ctrl->getLinkTargetByClass('ilcalendardaygui', ''));
                $this->ctrl->clearParametersByClass('ilcalendardaygui');
            } else {
                $this->tpl->setCurrentBlock('month_day_no_link');
            }

            $this->tpl->setVariable('MONTH_DAY', $month_day);

            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock('month_col');

            if (ilCalendarUtil::_isToday($date)) {
                $this->tpl->setVariable('TD_CLASS', 'caltoday');
            } elseif (ilDateTime::_equals($date, $this->seed, IL_CAL_MONTH)) {
                $this->tpl->setVariable('TD_CLASS', 'calstd');
            } elseif (ilDateTime::_before($date, $this->seed, IL_CAL_MONTH)) {
                $this->tpl->setVariable('TD_CLASS', 'calprev');
            } else {
                $this->tpl->setVariable('TD_CLASS', 'calnext');
            }
            $this->tpl->parseCurrentBlock();
            if ($counter && !($counter % 7)) {
                $this->tpl->setCurrentBlock('month_row');
                $this->tpl->parseCurrentBlock();
            }
        }
    }

    public function getHTML(): string
    {
        $this->show();
        return $this->tpl->get();
    }

    protected function showEvents(ilCalendarSchedule $scheduler, ilDate $date): int
    {
        $count = 0;
        $time = '';
        foreach ($scheduler->getByDay($date, $this->timezone) as $item) {
            $this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
            $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'app_id', $item['event']->getEntryId());

            $event_tpl = new ilTemplate('tpl.month_event_view.html', true, true, 'Services/Calendar');
            // milestone icon
            if ($item['event']->isMilestone()) {
                $event_tpl->setCurrentBlock('fullday_ms_icon');
                $event_tpl->setVariable('ALT_FD_MS', $this->lng->txt("cal_milestone"));
                $event_tpl->setVariable('SRC_FD_MS', ilUtil::getImagePath("icon_ms.svg"));
                $event_tpl->parseCurrentBlock();
            }

            $compl = ($item['event']->isMilestone() && $item['event']->getCompletion() > 0)
                ? " (" . $item['event']->getCompletion() . "%)"
                : "";

            if (!$item['event']->isFullDay()) {
                switch ($this->user_settings->getTimeFormat()) {
                    case ilCalendarSettings::TIME_FORMAT_24:
                        $time = $item['event']->getStart()->get(IL_CAL_FKT_DATE, 'H:i', $this->timezone);
                        break;

                    case ilCalendarSettings::TIME_FORMAT_12:
                        $time = $item['event']->getStart()->get(IL_CAL_FKT_DATE, 'h:ia', $this->timezone);
                        break;
                }
            }

            //plugins can change the modal title.
            $shy = $this->getAppointmentShyButton($item['event'], (string) $item['dstart'], "");
            $title = ($time != "") ? $time . " " . $shy : $shy;
            $event_html = $title . $compl;
            $event_tpl->setCurrentBlock('il_event');

            //Start configuring the default template
            $event_tpl->setVariable(
                'EVENT_EDIT_LINK',
                $this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui', 'edit')
            );
            $event_tpl->setVariable('EVENT_NUM', $item['event']->getEntryId());
            $event_tpl->setVariable('EVENT_CONTENT', $event_html);
            $color = $this->app_colors->getColorByAppointment($item['event']->getEntryId());
            $event_tpl->setVariable('EVENT_BGCOLOR', $color);
            $event_tpl->setVariable('EVENT_ADD_STYLES', $item['event']->getPresentationStyle());
            $event_tpl->setVariable('EVENT_FONTCOLOR', ilCalendarUtil::calculateFontColor($color));

            //plugins can override the previous template variables. The plugin slot parses the current block because
            //it needs to call the template get method to use the resulting HTML in the replaceContent method.
            if ($event_html_by_plugin = $this->getContentByPlugins(
                $item['event'],
                $item['dstart'],
                $event_html,
                $event_tpl
            )) {
                $event_body_html = $event_html_by_plugin;
            } else {
                $event_tpl->parseCurrentBlock();
                $event_body_html = $event_tpl->get();
            }

            $this->tpl->setCurrentBlock("event_nfd");
            $this->tpl->setVariable("EVENT_CONTENT", $event_body_html);
            $this->tpl->parseCurrentBlock();

            $this->num_appointments++;
            $count++;
        }
        return $count;
    }
}
