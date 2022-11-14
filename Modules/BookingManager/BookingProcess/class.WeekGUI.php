<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

namespace ILIAS\BookingManager\BookingProcess;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class WeekGUI
{
    protected const PROCESS_CLASS = \ilBookingProcessWithScheduleGUI::class;
    protected \ILIAS\BookingManager\Objects\ObjectsManager $object_manager;
    protected \ilCtrlInterface $ctrl;
    protected string $parent_cmd;
    protected object $parent_gui;
    /**
     * @var int[]
     */
    protected array $obj_ids = [];
    protected int $week_start;
    protected \ilDate $seed;
    protected string $seed_str;
    protected int $time_format;
    protected int $day_end;
    protected int $day_start;

    public function __construct(
        object $parent_gui,
        string $parent_cmd,
        array $obj_ids,
        int $pool_id,
        string $seed_str = "",
        int $week_start = \ilCalendarSettings::WEEK_START_MONDAY
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->parent_gui = $parent_gui;
        $this->parent_cmd = $parent_cmd;
        $this->obj_ids = $obj_ids;
        $this->day_start = 8;
        $this->day_end = 19;
        $this->time_format = \ilCalendarSettings::TIME_FORMAT_24;
        $this->seed_str = $seed_str;
        $this->seed = ($this->seed_str !== "")
            ? new \ilDate($this->seed_str, IL_CAL_DATE)
            : new \ilDate(time(), IL_CAL_UNIX);
        $this->week_start = $week_start;
        $this->object_manager = $DIC->bookingManager()->internal()
            ->domain()->objects($pool_id);
    }

    public function getHTML() : string
    {
        $navigation = new \ilCalendarHeaderNavigationGUI(
            $this->parent_gui,
            $this->seed,
            \ilDateTime::WEEK,
            $this->parent_cmd
        );
        $navigation->getHTML();


        /*
        $start1 = new \ilDateTime("2022-08-17 10:00:00", IL_CAL_DATETIME);
        $end1 = new \ilDateTime("2022-08-17 11:00:00", IL_CAL_DATETIME);

        $entry1 = new WeekGridEntry(
            $start1->get(IL_CAL_UNIX),
            $end1->get(IL_CAL_UNIX),
            "Moin 1"
        );

        $start2 = new \ilDateTime("2022-08-19 12:00:00", IL_CAL_DATETIME);
        $end2 = new \ilDateTime("2022-08-19 13:00:00", IL_CAL_DATETIME);

        $entry2 = new WeekGridEntry(
            $start2->get(IL_CAL_UNIX),
            $end2->get(IL_CAL_UNIX),
            "Moin 2"
        );*/

        $week_widget = new WeekGridGUI(
            $this->getWeekGridEntries($this->obj_ids),
            $this->seed,
            $this->day_start,
            $this->day_end,
            $this->time_format,
            $this->week_start
        );
        return $week_widget->render();
    }

    protected function getWeekGridEntries(
        array $object_ids
    ) : array {
        $week_grid_entries = [];

        foreach ($object_ids as $object_id) {
            $obj = new \ilBookingObject($object_id);
            $schedule = new \ilBookingSchedule($obj->getScheduleId());
            $map = array('mo', 'tu', 'we', 'th', 'fr', 'sa', 'su');
            $definition = $schedule->getDefinition();
            $av_from = ($schedule->getAvailabilityFrom() && !$schedule->getAvailabilityFrom()->isNull())
                ? $schedule->getAvailabilityFrom()->get(IL_CAL_DATE)
                : null;
            $av_to = ($schedule->getAvailabilityTo() && !$schedule->getAvailabilityTo()->isNull())
                ? $schedule->getAvailabilityTo()->get(IL_CAL_DATE)
                : null;

            $has_open_slot = false;
            /** @var \ilDateTime $date */
            foreach (\ilCalendarUtil::_buildWeekDayList($this->seed, $this->week_start)->get() as $date) {
                $date_info = $date->get(IL_CAL_FKT_GETDATE, '', 'UTC');

                #24045 and #24936
                if ($av_from || $av_to) {
                    $today = $date->get(IL_CAL_DATE);

                    if ($av_from && $av_from > $today) {
                        continue;
                    }

                    if ($av_to && $av_to < $today) {
                        continue;
                    }
                }

                $slots = [];
                if (isset($definition[$map[$date_info['isoday'] - 1]])) {
                    foreach ($definition[$map[$date_info['isoday'] - 1]] as $slot) {
                        $slot = explode('-', $slot);
                        $slots[] = array('from' => str_replace(':', '', $slot[0]),
                                         'to' => str_replace(':', '', $slot[1])
                        );
                    }
                }

                foreach ($slots as $slot) {
                    $slot_from = mktime(
                        (int) substr($slot['from'], 0, 2),
                        (int) substr($slot['from'], 2, 2),
                        0,
                        $date_info["mon"],
                        $date_info["mday"],
                        $date_info["year"]
                    );
                    $slot_to = mktime(
                        (int) substr($slot['to'], 0, 2),
                        (int) substr($slot['to'], 2, 2),
                        0,
                        $date_info["mon"],
                        $date_info["mday"],
                        $date_info["year"]
                    );

                    // always single object, we can sum up
                    $nr_available = \ilBookingReservation::getAvailableObject(
                        [$object_id],
                        $slot_from,
                        $slot_to - 1,
                        false,
                        true
                    );

                    // any objects available?
                    if (!array_sum($nr_available)) {
                        continue;
                    }

                    // check deadline
                    if ($schedule->getDeadline() >= 0) {
                        // 0-n hours before slots begins
                        if ($slot_from < (time() + $schedule->getDeadline() * 60 * 60)) {
                            continue;
                        }
                    } elseif ($slot_to < time()) {
                        continue;
                    }

                    $from = \ilDatePresentation::formatDate(new \ilDateTime($slot_from, IL_CAL_UNIX));
                    $from_a = explode(' ', $from);
                    $from = array_pop($from_a);
                    $to = \ilDatePresentation::formatDate(new \ilDateTime($slot_to, IL_CAL_UNIX));
                    $to_a = explode(' ', $to);
                    $to = array_pop($to_a);

                    $this->ctrl->setParameterByClass(self::PROCESS_CLASS, "slot", $slot_from . "_" . $slot_to);
                    $this->ctrl->setParameterByClass(self::PROCESS_CLASS, "object_id", $obj->getId());
                    $this->ctrl->setParameterByClass(self::PROCESS_CLASS, "seed", $this->seed_str);
                    $link = $this->ctrl->getLinkTargetByClass(self::PROCESS_CLASS, "showNumberForm", "", true);
                    $this->ctrl->setParameterByClass(self::PROCESS_CLASS, "slot", null);
                    $this->ctrl->setParameterByClass(self::PROCESS_CLASS, "object_id", null);
                    $slot_gui = new SlotGUI(
                        $link,
                        $from,
                        $to,
                        $slot_from,
                        $slot_to,
                        $obj->getTitle(),
                        array_sum($nr_available),
                        $this->object_manager->getColorNrForObject($obj->getId())
                    );
                    $week_grid_entries[] = new WeekGridEntry(
                        $slot_from,
                        $slot_to,
                        $slot_gui->render()
                    );
                }
            }
        }
        return $week_grid_entries;
    }
}
