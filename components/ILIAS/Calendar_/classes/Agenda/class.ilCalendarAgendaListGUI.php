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

use ILIAS\UI\Component\Item\Item;

/**
 * Calendar agenda list
 * @author       Alex Killing <killing@leifos.de>
 * @ingroup      ServicesCalendar
 * @ilCtrl_Calls ilCalendarAgendaListGUI: ilCalendarAppointmentPresentationGUI
 */
class ilCalendarAgendaListGUI extends ilCalendarViewGUI
{
    public const PERIOD_DAY = 1;
    public const PERIOD_WEEK = 2;
    public const PERIOD_MONTH = 3;
    public const PERIOD_HALF_YEAR = 4;

    protected int $period = self::PERIOD_WEEK;

    public function __construct(ilDate $seed)
    {
        parent::__construct($seed, ilCalendarViewGUI::CAL_PRESENTATION_AGENDA_LIST);
        $this->ctrl->saveParameter($this, "cal_agenda_per");
        $this->initPeriod();
        $this->ctrl->setParameterByClass("ilcalendarinboxgui", "seed", $this->seed->get(IL_CAL_DATE));
        $this->initEndPeriod();
    }

    protected function initCalendarPeriodFromRequest(): int
    {
        if ($this->http->wrapper()->query()->has('cal_agenda_per')) {
            return $this->http->wrapper()->query()->retrieve(
                'cal_agenda_per',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    protected function initPeriod(): void
    {
        global $DIC;

        $cal_setting = ilCalendarSettings::_getInstance();

        $calendar_period = $this->initCalendarPeriodFromRequest();
        if ($calendar_period > 0 && $calendar_period <= 4) {
            $this->period = $calendar_period;
        } elseif (!empty($this->user->getPref('cal_list_view'))) {
            $this->period = intval($this->user->getPref('cal_list_view'));
        } else {
            $this->period = $cal_setting->getDefaultPeriod();
        }
        $this->user->writePref('cal_list_view', (string) $this->period);
    }

    /**
     * Initialises end date for calendar list view
     */
    protected function initEndPeriod(): void
    {
        $end_date = clone $this->seed;
        switch ($this->period) {
            case self::PERIOD_DAY:
                $end_date->increment(IL_CAL_DAY, 1);
                break;

            case self::PERIOD_WEEK:
                $end_date->increment(IL_CAL_WEEK, 1);
                break;

            case self::PERIOD_MONTH:
                $end_date->increment(IL_CAL_MONTH, 1);
                break;

            case self::PERIOD_HALF_YEAR:
                $end_date->increment(IL_CAL_MONTH, 6);
                break;
        }
        $this->period_end_day = $end_date->get(IL_CAL_DATE);
    }

    public function executeCommand(): ?string
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("getHTML");

        switch ($next_class) {
            case "ilcalendarappointmentpresentationgui":
                $this->ctrl->setReturn($this, "");
                $gui = ilCalendarAppointmentPresentationGUI::_getInstance(
                    new ilDate(
                        $this->seed->get(IL_CAL_DATE),
                        IL_CAL_DATE
                    ),
                    (array) $this->getCurrentApp()
                );
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                $this->ctrl->setReturn($this, "");
                if (in_array($cmd, array("getHTML", "getModalForApp"))) {
                    return $this->$cmd();
                }
        }
        return '';
    }

    public function getHTML(): string
    {
        $navigation = new ilCalendarHeaderNavigationGUI(
            $this,
            new ilDate($this->seed->get(IL_CAL_DATE), IL_CAL_DATE),
            ilDateTime::DAY
        );
        $navigation->getHTML();

        // set return now (after header navigation) to the list (e.g. for profile links)
        $this->ctrl->setReturn($this, "");

        // get events
        $events = $this->getEvents();
        $events = ilArrayUtil::sortArray($events, "dstart", "asc", true);

        $df = new \ILIAS\Data\Factory();
        $items = array();
        $groups = array();
        $modals = array();
        $group_date = new ilDate(0, IL_CAL_UNIX);
        $end_day = new ilDate($this->period_end_day, IL_CAL_DATE);
        $end_day->increment(ilDateTime::DAY, -1);
        foreach ($events as $e) {
            if ($e['event']->isFullDay()) {
                // begin/end is Date (without timzone)
                $begin = new ilDate($e['dstart'], IL_CAL_UNIX);
                $end = new ilDate($e['dend'], IL_CAL_UNIX);
            } else {
                // begin/end is DateTime (with timezone conversion)
                $begin = new ilDateTime($e['dstart'], IL_CAL_UNIX);
                $end = new ilDateTime($e['dend'], IL_CAL_UNIX);
            }

            //  if the begin is before seed date (due to timezone conversion) => continue
            if (ilDateTime::_before(
                $begin,
                $this->seed,
                ilDateTime::DAY,
                $this->user->getTimeZone()
            )) {
                continue;
            }

            if (ilDateTime::_after(
                $begin,
                $end_day,
                ilDateTime::DAY,
                $this->user->getTimeZone()
            )
            ) {
                break;
            }

            // initialize group date for first iteration
            if ($group_date->isNull()) {
                $group_date = new ilDate(
                    $begin->get(IL_CAL_DATE, '', $this->user->getTimeZone()),
                    IL_CAL_DATE
                );
            }

            if (!ilDateTime::_equals($group_date, $begin, IL_CAL_DAY, $this->user->getTimeZone())) {
                // create new group
                $groups[] = $this->ui_factory->item()->group(
                    ilDatePresentation::formatDate($group_date, false, true),
                    $items
                );

                $group_date = new ilDate(
                    $begin->get(IL_CAL_DATE, '', $GLOBALS['DIC']->user()->getTimezone()),
                    IL_CAL_DATE
                );
                $items = [];
            }

            // get calendar
            $cat_id = ilCalendarCategoryAssignments::_lookupCategory($e["event"]->getEntryId());
            $cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($cat_id);

            /*TODO:
             * All this code related with the ctrl and shy button can be centralized in
             * ilCalendarViewGUI refactoring the method getAppointmentShyButton or
             * if we want extend this class from ilCalendarInboxGUI we can just keep it here.
             */

            // shy button for title
            $this->ctrl->setParameter($this, 'app_id', $e["event"]->getEntryId());
            $this->ctrl->setParameter($this, 'dt', $e['dstart']);
            $this->ctrl->setParameter($this, 'seed', $this->seed->get(IL_CAL_DATE));


            $url = $this->ctrl->getLinkTarget($this, "getModalForApp", "", true, false);
            $this->ctrl->setParameter($this, "app_id", $this->initAppointmentIdFromQuery());
            $this->ctrl->setParameter($this, "dt", $this->initInitialDateTimeFromQuery());
            $this->ctrl->setParameter($this, "idate", (new ilDate(time(), IL_CAL_UNIX))->get(IL_CAL_DATE));

            $modal_title = '';
            if ($this->http->wrapper()->query()->has('modal_title')) {
                $modal_title = $this->http->wrapper()->query()->retrieve(
                    'modal_title',
                    $this->refinery->kindlyTo()->string()
                );
            }
            $this->ctrl->setParameter($this, 'modal_title', $modal_title);
            $modal = $this->ui_factory->modal()->roundtrip('', [])->withAsyncRenderUrl($url);
            $shy = $this->ui_factory->button()->shy(
                $e["event"]->getPresentationTitle(false),
                ""
            )->withOnClick($modal->getShowSignal());

            $modals[] = $modal;
            if ($e['event']->isFullDay()) {
                $lead_text = $this->lng->txt("cal_all_day");
            } else {
                $lead_text = ilDatePresentation::formatPeriod($begin, $end, true);
            }
            $li = $this->ui_factory->item()->standard($shy)
                                   ->withDescription("" . nl2br(strip_tags($e["event"]->getDescription())))
                                   ->withLeadText($lead_text)
                                   ->withProperties([])
                                   ->withColor($df->color('#' . $cat_info["color"]));

            if ($li_edited_by_plugin = $this->getPluginAgendaItem($li, $e['event'])) {
                $li = $li_edited_by_plugin;
            }

            // add type specific actions/properties
            $app_gui = ilCalendarAppointmentPresentationGUI::_getInstance(
                new ilDate(
                    $this->seed->get(IL_CAL_DATE),
                    IL_CAL_DATE
                ),
                $e
            );
            $app_gui->setListItemMode($li);
            $this->ctrl->getHTML($app_gui);
            $items[] = $app_gui->getListItem();
        }
        // terminate last group
        if (!$group_date->isNull()) {
            $groups[] = $this->ui_factory->item()->group(
                ilDatePresentation::formatDate($group_date, false, true),
                $items
            );
        }

        // list actions
        $images = array_fill(1, 4, "<span class=\"ilAdvNoImg\"></span>");

        $cal_agenda_per = $this->initCalendarPeriodFromRequest();
        if ($cal_agenda_per > 0) {
            $images[$cal_agenda_per] = "<img src='./templates/default/images/icon_checked.svg' alt='Month'>";
        } else {
            $images[$this->period] = "<img src='./templates/default/images/icon_checked.svg' alt='Month'>";
        }

        #21479 Set seed if the view does not contain any event.
        $this->ctrl->setParameter($this, 'seed', $this->seed->get(IL_CAL_DATE));

        $items = [];
        $this->ctrl->setParameter($this, "cal_agenda_per", self::PERIOD_DAY);
        $items[] = $this->ui_factory->button()->shy(
            $images[1] . "1 " . $this->lng->txt("day"),
            $this->ctrl->getLinkTarget($this, "getHTML")
        );
        $this->ctrl->setParameter($this, "cal_agenda_per", self::PERIOD_WEEK);
        $items[] = $this->ui_factory->button()->shy(
            $images[2] . "1 " . $this->lng->txt("week"),
            $this->ctrl->getLinkTarget($this, "getHTML")
        );
        $this->ctrl->setParameter($this, "cal_agenda_per", self::PERIOD_MONTH);
        $items[] = $this->ui_factory->button()->shy(
            $images[3] . "1 " . $this->lng->txt("month"),
            $this->ctrl->getLinkTarget($this, "getHTML")
        );
        $this->ctrl->setParameter($this, "cal_agenda_per", self::PERIOD_HALF_YEAR);
        $items[] = $this->ui_factory->button()->shy(
            $images[4] . "6 " . $this->lng->txt("months"),
            $this->ctrl->getLinkTarget($this, "getHTML")
        );
        $this->ctrl->setParameter($this, "cal_agenda_per", $this->period);

        $actions = $this->ui_factory->dropdown()->standard($items)->withLabel($this->lng->txt("cal_period"));

        $list_title =
            $this->lng->txt("cal_agenda") . ": " . ilDatePresentation::formatDate(new ilDate(
                $this->seed->get(IL_CAL_DATE),
                IL_CAL_DATE
            ));
        if ($this->period != self::PERIOD_DAY) {
            $end_day = new ilDate($this->period_end_day, IL_CAL_DATE);
            $end_day->increment(ilDateTime::DAY, -1);
            $list_title .= " - " . ilDatePresentation::formatDate($end_day);
        }

        $list = $this->ui_factory->panel()->listing()->standard($list_title, $groups)
                                 ->withActions($actions);
        $comps = array_merge($modals, array($list));
        $html = $this->ui_renderer->render($comps);
        if (count($groups) == 0) {
            $html .= ilUtil::getSystemMessageHTML($this->lng->txt("cal_no_events_info"));
        }
        return $html;
    }

    public function getPluginAgendaItem(Item $a_item, ilCalendarEntry $appointment): ?Item
    {
        //"capg" is the plugin slot id for AppointmentCustomGrid
        $li = null;
        foreach ($this->getActivePlugins("capg") as $plugin) {
            $plugin->setAppointment($appointment, $appointment->getStart());
            // @todo check if last wins is desired
            $li = $plugin->editAgendaItem($a_item);
        }
        return $li;
    }

    /**
     * needed in CalendarInboxGUI to get events using a proper period.
     * todo define default period only once (self::PERIOD_WEEK, protected $period = self::PERIOD_WEEK)
     */
    public static function getPeriod(): int
    {
        global $DIC;

        $user = $DIC->user();

        $settings = ilCalendarSettings::_getInstance();

        $calendar_agenda_period = (int) ($DIC->http()->request()->getQueryParams()['cal_agenda_per'] ?? 0);
        if ($calendar_agenda_period > 0 && $calendar_agenda_period <= 4) {
            return $calendar_agenda_period;
        } elseif ($period = $user->getPref('cal_list_view')) {
            return (int) $period;
        } else {
            return $settings->getDefaultPeriod();
        }
    }
}
