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
class WeekGridGUI
{
    protected string $form_action;
    protected int $week_start;
    protected \ilDate $seed;
    protected string $seed_str;
    protected int $time_format;
    protected \ilLanguage $lng;
    protected int $day_end;
    protected int $day_start;
    protected string $title;
    protected array $entries;

    /**
     * @param WeekGridEntry[] $entries
     * @throws \ilDateTimeException
     */
    public function __construct(
        array $entries = [],
        \ilDate $seed = null,
        int $day_start = 8,
        int $day_end = 19,
        int $time_format = \ilCalendarSettings::TIME_FORMAT_24,
        int $week_start = \ilCalendarSettings::WEEK_START_MONDAY
    ) {
        global $DIC;

        $this->title = "Test";
        $this->form_action = "#";
        $this->lng = $DIC->language();
        $this->day_start = $day_start;
        $this->day_end = $day_end;
        $this->time_format = $time_format;
        $this->seed = $seed ?? new \ilDate(time(), IL_CAL_UNIX);
        $this->week_start = $week_start;
        $this->entries = $entries;
    }

    protected function getHoursOfDay() : array
    {
        $hours = array();
        $sep = "<br>-<br>";
        for ($i = $this->day_start;$i <= $this->day_end;$i++) {
            $caption = "";
            $start = sprintf('%02d:00', $i);
            if ($this->day_start > 0 && $i === $this->day_start) {
                $start = sprintf('%02d:00', 0);
                $end = sprintf('%02d:00', $i);
            } else {
                $end = sprintf('%02d:00', $i + 1);
            }
            if ($this->day_end < 23 && $i === $this->day_end) {
                $end = sprintf('%02d:00', 23);
            }
            switch ($this->time_format) {
                case \ilCalendarSettings::TIME_FORMAT_12:
                    if ($this->day_start > 0 && $i === $this->day_start) {
                        $caption = date('h a', mktime(0, 0, 0, 1, 1, 2000)) . $sep;
                    }
                    $caption .= date('h a', mktime($i, 0, 0, 1, 1, 2000));
                    if ($this->day_end < 23 && $i === $this->day_end) {
                        $caption .= $sep . date('h a', mktime(23, 0, 0, 1, 1, 2000));
                    }
                    break;

                default:
                    if ($this->day_start > 0 && $i === $this->day_start) {
                        $caption = sprintf('%02d:00', 0) . $sep;
                    }
                    $caption .= sprintf('%02d:00', $i);
                    if ($this->day_end < 23 && $i === $this->day_end) {
                        $caption .= $sep . sprintf('%02d:00', 23);
                    }
                    break;
            }
            $hours[$i] = [
                "caption" => $caption,
                "start" => $start,
                "end" => $end
            ];
        }
        return $hours;
    }

    /**
     * Build cell data
     * @param
     * @return
     */
    protected function buildCellData() : array
    {
        $morning_aggr = $this->day_start;
        $evening_aggr = $this->day_end;
        $hours = $this->getHoursOfDay();
        $week_start = $this->week_start;
        /** @var \ilDateTime $date */
        $cells = [];
        $week = 0;
        foreach (\ilCalendarUtil::_buildWeekDayList($this->seed, $week_start)->get() as $date) {
            foreach ($hours as $hour => $data) {
                $start = new \ilDateTime($date->get(IL_CAL_DATE) . " " . $data["start"] . ":00", IL_CAL_DATETIME);
                $end = new \ilDateTime($date->get(IL_CAL_DATE) . " " . $data["end"] . ":00", IL_CAL_DATETIME);
                $data["start_ts"] = $start->get(IL_CAL_UNIX);
                $data["end_ts"] = $end->get(IL_CAL_UNIX);
                $data["entries"] = $this->getEntriesForCell($data["start_ts"], $data["end_ts"]);
                $cells[$week][$hour] = $data;
                // store how much slots are max. to be displayed in parallel per day
                $cells[$week]["col_span"] = max(count($data["entries"]), $cells[$week]["col_span"] ?? 1);
            }
            $week++;
        }
        return $cells;
    }

    public function render() : string
    {
        $mytpl = new \ilTemplate(
            'tpl.week_grid.html',
            true,
            true,
            'Modules/BookingManager/BookingProcess'
        );

        $cells = $this->buildCellData();


        $weekday_list = \ilCalendarUtil::_buildWeekDayList($this->seed, $this->week_start)->get();
        $start = current($weekday_list);
        $end = end($weekday_list);
        $mytpl->setVariable("TXT_OBJECT", $this->lng->txt('week') . ' ' . $this->seed->get(IL_CAL_FKT_DATE, 'W') .
            ", " . \ilDatePresentation::formatDate($start) . " - " .
            \ilDatePresentation::formatDate($end));

        $mytpl->setVariable('TXT_TITLE', $this->lng->txt('book_reservation_title'));
        $mytpl->setVariable('TIME', $this->lng->txt('time'));


        $day_of_week = 0;
        reset($weekday_list);
        foreach ($weekday_list as $date) {
            $date_info = $date->get(IL_CAL_FKT_GETDATE, '', 'UTC');
            $mytpl->setCurrentBlock('weekdays');
            $mytpl->setVariable('TXT_WEEKDAY', \ilCalendarUtil::_numericDayToString((int) $date_info['wday']));
            $mytpl->setVariable('COL_SPAN', $cells[$day_of_week]["col_span"]);
            $mytpl->setVariable('WIDTH', "12");
            $mytpl->setVariable('TXT_DATE', $date_info['mday'] . ' ' . \ilCalendarUtil::_numericMonthToString($date_info['mon']));
            $mytpl->parseCurrentBlock();
            $day_of_week++;
        }

        $hours = $this->getHoursOfDay();

        foreach ($hours as $hour => $days) {
            $caption = $days["caption"];
            $day_of_week = 0;
            foreach (\ilCalendarUtil::_buildWeekDayList($this->seed, $this->week_start)->get() as $date) {
                $data = $cells[$day_of_week][$hour];
                $total_tds = $cells[$day_of_week]["col_span"];
                foreach ($data["entries"] as $e) {
                    // starting in cell? show it
                    /** @var WeekGridEntry $e */
                    if ($e->getStart() >= $data["start_ts"] && $e->getStart() < $data["end_ts"]) {
                        $mytpl->setCurrentBlock('dates');
                        $mytpl->setVariable('CONTENT', $e->getHTML());
                        $row_span = max(1, ceil(($e->getEnd() - $data["end_ts"]) / 3600) + 1);
                        $mytpl->setVariable('ROW_SPAN', $row_span);
                        $mytpl->parseCurrentBlock();
                    }
                    $total_tds--;
                }
                while ($total_tds > 0) {
                    $mytpl->setCurrentBlock('dates');
                    $mytpl->setVariable('CONTENT', "&nbsp;");
                    $mytpl->parseCurrentBlock();
                    $total_tds--;
                }
                $day_of_week++;
            }

            $mytpl->setCurrentBlock('slots');
            $mytpl->setVariable('TXT_HOUR', $caption);
            $mytpl->parseCurrentBlock();
        }
        //\ilPropertyFormGUI::initJavascript();
        return $mytpl->get();
    }

    /**
     * All entries for a cell (not only starting ones, start could be in an earlier cell)
     * @return WeekGridEntry[]
     */
    protected function getEntriesForCell(int $start_ts, int $end_ts) : array
    {
        return array_filter($this->entries, function ($e) use ($start_ts, $end_ts) {
            /** @var WeekGridEntry $e */
            return ($e->getStart() < $end_ts && $e->getEnd() > $start_ts);
        });
    }

    protected function renderCell(array $data)
    {
        return "&nbsp;";
    }
}
