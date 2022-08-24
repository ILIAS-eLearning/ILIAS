<?php

declare(strict_types=1);

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Presentation day view
 * @author       Stefan Meyer <smeyer.ilias@gmx.de>
 * @ilCtrl_Calls ilCalendarDayGUI: ilCalendarAppointmentGUI
 * @ilCtrl_Calls ilCalendarDayGUI: ilCalendarAppointmentPresentationGUI
 * @ingroup      ServicesCalendar
 */
class ilCalendarDayGUI extends ilCalendarViewGUI
{
    protected array $seed_info = [];
    protected ilCalendarUserSettings $user_settings;
    protected int $num_appointments = 1;
    protected string $timezone = 'UTC';
    protected ilCalendarAppointmentColors $app_colors;

    public function __construct(ilDate $seed_date)
    {
        parent::__construct($seed_date, ilCalendarViewGUI::CAL_PRESENTATION_DAY);
    }

    public function initialize(int $a_calendar_presentation_type): void
    {
        global $DIC;

        parent::initialize($a_calendar_presentation_type);
        $this->seed_info = (array) $this->seed->get(IL_CAL_FKT_GETDATE);
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
            case "ilcalendarappointmentpresentationgui":
                $this->ctrl->setReturn($this, "");
                $this->logger->debug("-ExecCommand - representation of ilDate: this->seed->get(IL_CAL_DATE) = " . $this->seed->get(IL_CAL_DATE));
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
                $cmd = $this->ctrl->getCmd("show");
                $this->$cmd();
                $this->main_tpl->setContent($this->tpl->get());
                break;
        }
    }

    /**
     * fill data section
     * @access protected
     */
    protected function show(): void
    {
        // config
        $raster = 15;
        if ($this->user_settings->getDayStart()) {
            // push starting point to last "slot" of hour BEFORE morning aggregation
            $morning_aggr = ($this->user_settings->getDayStart() - 1) * 60 + (60 - $raster);
        } else {
            $morning_aggr = 0;
        }
        $evening_aggr = $this->user_settings->getDayEnd() * 60;

        $this->tpl = new ilTemplate('tpl.day_view.html', true, true, 'Services/Calendar');

        ilYuiUtil::initDragDrop();

        $bkid = $this->initBookingUserFromQuery();
        if ($bkid) {
            $user_id = $bkid;
            $no_add = true;
        } elseif ($this->user->getId() == ANONYMOUS_USER_ID) {
            $user_id = $this->user->getId();
            $no_add = true;
        } else {
            $user_id = $this->user->getId();
            $no_add = false;
        }
        $scheduler = new ilCalendarSchedule($this->seed, ilCalendarSchedule::TYPE_DAY, $user_id);
        $scheduler->addSubitemCalendars(true);
        $scheduler->calculate();
        $daily_apps = $scheduler->getByDay($this->seed, $this->timezone);

        //display the download files button.
        if (count($daily_apps)) {
            $this->view_with_appointments = true;
        }

        $hours = $this->parseInfoIntoRaster(
            $daily_apps,
            $morning_aggr,
            $evening_aggr,
            $raster
        );

        $colspan = $this->calculateColspan($hours);

        $navigation = new ilCalendarHeaderNavigationGUI($this, $this->seed, ilDateTime::DAY);
        $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'seed', $this->seed->get(IL_CAL_DATE));

        // add milestone link
        $settings = ilCalendarSettings::_getInstance();

        if (!$no_add) {
            $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'seed', $this->seed->get(IL_CAL_DATE));
            $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'idate', $this->seed->get(IL_CAL_DATE));
            $new_app_url = $this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui', 'add');

            if ($settings->getEnableGroupMilestones()) {
                $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'seed', $this->seed->get(IL_CAL_DATE));
                $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'idate', $this->seed->get(IL_CAL_DATE));
                $new_ms_url = $this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui', 'addMilestone');

                $this->tpl->setCurrentBlock("new_ms");
                $this->tpl->setVariable('DD_ID', $this->seed->get(IL_CAL_UNIX));
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
                $this->tpl->setCurrentBlock("new_app1");
                $this->tpl->setVariable(
                    'H_NEW_APP_GLYPH',
                    $this->ui_renderer->render($this->ui_factory->symbol()->glyph()->add($new_app_url))
                );
                $this->tpl->parseCurrentBlock();
            }

            $this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
        }

        $this->tpl->setVariable('NAVIGATION', $navigation->getHTML());

        $this->tpl->setVariable(
            'HEADER_DATE',
            $this->seed_info['mday'] . ' ' . ilCalendarUtil::_numericMonthToString($this->seed_info['mon'], false)
        );
        $this->tpl->setVariable(
            'HEADER_DAY',
            ilCalendarUtil::_numericDayToString((int) $this->seed_info['wday'], true)
        );
        $this->tpl->setVariable('HCOLSPAN', $colspan - 1);

        $this->tpl->setVariable('TXT_TIME', $this->lng->txt("time"));

        // show fullday events
        foreach ($daily_apps as $event) {
            if ($event['fullday']) {
                $this->showFulldayAppointment($event);
            }
        }
        $this->tpl->setCurrentBlock('fullday_apps');
        $this->tpl->setVariable('TXT_F_DAY', $this->lng->txt("cal_all_day"));
        $this->tpl->setVariable('COLSPAN', $colspan - 1);
        $this->tpl->parseCurrentBlock();

        // parse the hour rows
        foreach ($hours as $numeric => $hour) {
            if (!($numeric % 60) || ($numeric == $morning_aggr && $morning_aggr) ||
                ($numeric == $evening_aggr && $evening_aggr)) {
                if (!$no_add) {
                    $this->tpl->setCurrentBlock("new_app2");
                    $this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
                    $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'seed', $this->seed->get(IL_CAL_DATE));
                    $this->ctrl->setParameterByClass(
                        'ilcalendarappointmentgui',
                        'idate',
                        $this->seed->get(IL_CAL_DATE)
                    );
                    $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'hour', floor($numeric / 60));
                    $this->tpl->setVariable(
                        'NEW_APP_GLYPH',
                        $this->ui_renderer->render($this->ui_factory->symbol()->glyph()->add(
                            $this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui', 'add')
                        ))
                    );
                    $this->tpl->parseCurrentBlock();
                }

                // aggregation rows
                if (($numeric == $morning_aggr && $morning_aggr) ||
                    ($numeric == $evening_aggr && $evening_aggr)) {
                    $this->tpl->setVariable('TIME_ROWSPAN', 1);
                } // rastered hour
                else {
                    $this->tpl->setVariable('TIME_ROWSPAN', 60 / $raster);
                }

                $this->tpl->setCurrentBlock('time_txt');

                $this->tpl->setVariable('TIME', $hour['txt']);
                $this->tpl->parseCurrentBlock();
            }

            foreach ($hour['apps_start'] as $app) {
                $this->showAppointment($app);
            }

            for ($i = ($colspan - 1); $i > $hour['apps_num']; $i--) {
                $this->tpl->setCurrentBlock('empty_cell');
                $this->tpl->setVariable('EMPTY_WIDTH', (100 / ($colspan - 1)) . '%');

                // last "slot" of hour needs border
                if ($numeric % 60 == 60 - $raster ||
                    ($numeric == $morning_aggr && $morning_aggr) ||
                    ($numeric == $evening_aggr && $evening_aggr)) {
                    $this->tpl->setVariable('EMPTY_STYLE', ' calempty_border');
                }

                $this->tpl->parseCurrentBlock();
            }

            $this->tpl->touchBlock('time_row');
        }
    }

    protected function showFulldayAppointment(array $a_app): void
    {
        $event_tpl = new ilTemplate('tpl.day_event_view.html', true, true, 'Services/Calendar');

        // milestone icon
        if ($a_app['event']->isMilestone()) {
            $event_tpl->setCurrentBlock('fullday_ms_icon');
            $event_tpl->setVariable('ALT_FD_MS', $this->lng->txt("cal_milestone"));
            $event_tpl->setVariable('SRC_FD_MS', ilUtil::getImagePath("icon_ms.svg"));
            $event_tpl->parseCurrentBlock();
        }

        $event_tpl->setCurrentBlock('fullday_app');

        $compl = ($a_app['event']->isMilestone() && $a_app['event']->getCompletion() > 0)
            ? " (" . $a_app['event']->getCompletion() . "%)"
            : "";

        $shy = $this->getAppointmentShyButton($a_app['event'], (string) $a_app['dstart'], "");

        //$title = ($new_title = $this->getContentByPlugins($a_app['event'], $a_app['dstart'], $shy))? $new_title : $shy;

        $content = $shy . $compl;

        $event_tpl->setVariable('EVENT_CONTENT', $content);

        $color = $this->app_colors->getColorByAppointment($a_app['event']->getEntryId());
        $event_tpl->setVariable('F_APP_BGCOLOR', $color);
        $event_tpl->setVariable('F_APP_FONTCOLOR', ilCalendarUtil::calculateFontColor($color));

        $this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
        $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'seed', $this->seed->get(IL_CAL_DATE));
        $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'app_id', $a_app['event']->getEntryId());
        $event_tpl->setVariable(
            'F_APP_EDIT_LINK',
            $this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui', 'edit')
        );

        if ($event_html_by_plugin = $this->getContentByPlugins(
            $a_app['event'],
            $a_app['dstart'],
            $content,
            $event_tpl
        )) {
            $body_html = $event_html_by_plugin;
        } else {
            $event_tpl->parseCurrentBlock();
            $body_html = $event_tpl->get();
        }

        $this->tpl->setCurrentBlock("content_fd");
        $this->tpl->setVariable("CONTENT_EVENT", $body_html);
        $this->tpl->parseCurrentBlock();
        $this->num_appointments++;
    }

    protected function showAppointment(array $a_app): void
    {
        $event_tpl = new ilTemplate('tpl.day_event_view.html', true, true, 'Services/Calendar');
        $event_tpl->setCurrentBlock('app');
        $this->tpl->setVariable('APP_ROWSPAN', $a_app['rowspan']);
        $time = '';
        switch ($this->user_settings->getTimeFormat()) {
            case ilCalendarSettings::TIME_FORMAT_24:
                $time = $a_app['event']->getStart()->get(IL_CAL_FKT_DATE, 'H:i', $this->timezone);
                break;

            case ilCalendarSettings::TIME_FORMAT_12:
                $time = $a_app['event']->getStart()->get(IL_CAL_FKT_DATE, 'h:ia', $this->timezone);
                break;
        }

        $shy = $this->getAppointmentShyButton($a_app['event'], (string) $a_app['dstart'], "");

        $title = $shy;
        $content = ($time != "") ? $time . " " . $title : $title;
        $event_tpl->setVariable('EVENT_CONTENT', $content);

        $color = $this->app_colors->getColorByAppointment($a_app['event']->getEntryId());
        $event_tpl->setVariable('APP_BGCOLOR', $color);
        $event_tpl->setVariable('APP_COLOR', ilCalendarUtil::calculateFontColor($color));
        $event_tpl->setVariable('APP_ADD_STYLES', $a_app['event']->getPresentationStyle());

        $this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
        $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'seed', $this->seed->get(IL_CAL_DATE));
        $this->ctrl->setParameterByClass('ilcalendarappointmentgui', 'app_id', $a_app['event']->getEntryId());
        $event_tpl->setVariable('APP_EDIT_LINK', $this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui', 'edit'));

        if ($event_html_by_plugin = $this->getContentByPlugins(
            $a_app['event'],
            $a_app['dstart'],
            $content,
            $event_tpl
        )) {
            $event_html = $event_html_by_plugin;
        } else {
            $event_tpl->parseCurrentBlock();
            $event_html = $event_tpl->get();
        }

        $this->tpl->setCurrentBlock("event_nfd");
        $this->tpl->setVariable("CONTENT_EVENT_NFD", $event_html);
        $this->tpl->parseCurrentBlock();

        $this->num_appointments++;
    }

    /**
     * calculate overlapping hours
     */
    protected function parseInfoIntoRaster(array $daily_apps, int $morning_aggr, int $evening_aggr, int $raster): array
    {
        $hours = array();
        for ($i = $morning_aggr; $i <= $evening_aggr; $i += $raster) {
            $hours[$i]['apps_start'] = array();
            $hours[$i]['apps_num'] = 0;

            switch ($this->user_settings->getTimeFormat()) {
                case ilCalendarSettings::TIME_FORMAT_24:
                    if ($morning_aggr > 0 && $i == $morning_aggr) {
                        $hours[$i]['txt'] = sprintf('%02d:00', 0) . ' - ' .
                            sprintf('%02d:00', ceil(($i + 1) / 60));
                    } else {
                        if (!isset($hours[$i]['txt'])) {
                            $hours[$i]['txt'] = sprintf('%02d:%02d', floor($i / 60), $i % 60);
                        } else {
                            $hours[$i]['txt'] .= sprintf('%02d:%02d', floor($i / 60), $i % 60);
                        }
                    }
                    if ($evening_aggr < 23 * 60 && $i == $evening_aggr) {
                        if (!isset($hours[$i]['txt'])) {
                            $hours[$i]['txt'] = ' - ' . sprintf('%02d:00', 0);
                        } else {
                            $hours[$i]['txt'] .= ' - ' . sprintf('%02d:00', 0);
                        }
                    }
                    break;

                case ilCalendarSettings::TIME_FORMAT_12:
                    $this->logger->notice('Morning: ' . $morning_aggr . ' and $i:' . $i);
                    if ($morning_aggr > 0 && $i == $morning_aggr) {
                        $hours[$i]['txt'] =
                            date('h a', mktime(0, 0, 0, 1, 1, 2000)) . ' - ' .
                            date('h a', mktime($this->user_settings->getDayStart(), 0, 0, 1, 1, 2000));
                    } else {
                        $hours[$i]['txt'] = date('h a', mktime((int) floor($i / 60), $i % 60, 0, 1, 1, 2000));
                    }
                    if ($evening_aggr < 23 * 60 && $i == $evening_aggr) {
                        if (!isset($hours[$i]['txt'])) {
                            $hours[$i]['txt'] = ' - ' . date('h a', mktime(0, 0, 0, 1, 1, 2000));
                        } else {
                            $hours[$i]['txt'] .= ' - ' . date('h a', mktime(0, 0, 0, 1, 1, 2000));
                        }
                    }
                    break;
            }
        }

        foreach ($daily_apps as $app) {
            // fullday appointment are not relavant
            if ($app['fullday']) {
                continue;
            }
            // start hour for this day
            #21132 #21636
            if ($app['start_info']['mday'] != $this->seed_info['mday']) {
                $start = 0;
            } else {
                $start = $app['start_info']['hours'] * 60 + $app['start_info']['minutes'];
            }
            #21636

            // end hour for this day
            #21132
            if ($app['end_info']['mday'] != $this->seed_info['mday']) {
                $end = 23 * 60;
            } elseif ($app['start_info']['hours'] == $app['end_info']['hours']) {
                $end = $start + $raster;
            } else {
                $end = $app['end_info']['hours'] * 60 + $app['end_info']['minutes'];
            }

            if ($start < $morning_aggr) {
                $start = $morning_aggr;
            }
            if ($end <= $morning_aggr) {
                $end = $morning_aggr + $raster;
            }
            if ($start > $evening_aggr) {
                $start = $evening_aggr;
            }
            if ($end > $evening_aggr + $raster) {
                $end = $evening_aggr + $raster;
            }
            if ($end <= $start) {
                $end = $start + $raster;
            }

            // map start and end to raster
            $start = floor($start / $raster) * $raster;
            $end = ceil($end / $raster) * $raster;

            $first = true;
            for ($i = $start; $i < $end; $i += $raster) {
                if ($first) {
                    $app['rowspan'] = ceil(($end - $start) / $raster);
                    $hours[$i]['apps_start'][] = $app;
                    $first = false;
                }
                $hours[$i]['apps_num']++;
            }
        }
        return $hours;
    }

    protected function calculateColspan(array $hours): int
    {
        $colspan = 1;
        foreach ($hours as $hour) {
            $colspan = max($colspan, $hour['apps_num'] + 1);
        }
        return max($colspan, 2);
    }
}
