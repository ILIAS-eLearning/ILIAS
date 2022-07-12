<?php declare(strict_types=1);
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
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup Services/Calendar
 */
class ilICalParser
{
    public const INPUT_STRING = 1;
    public const INPUT_FILE = 2;

    protected ilLogger $log;

    protected ?ilCalendarCategory $category = null;

    protected string $ical = '';
    protected string $file = '';
    protected ?ilTimeZone $default_timezone = null;
    protected array $container = array();

    public function __construct(string $a_ical, int $a_type)
    {
        global $DIC;
        if ($a_type == self::INPUT_STRING) {
            $this->ical = $a_ical;
        } elseif ($a_type == self::INPUT_FILE) {
            $this->file = $a_ical;
            $this->ical = file_get_contents($a_ical);

            if (!strlen($this->ical)) {
                throw new ilICalParserException('Cannot parse empty ical file: ' . $a_ical);
            }
        }
        $this->log = $DIC->logger()->cal();
        $this->default_timezone = ilTimeZone::_getInstance();
    }

    public function setCategoryId(int $a_id) : void
    {
        $this->category = new ilCalendarCategory($a_id);
    }

    public function parse() : void
    {
        $lines = $this->tokenize($this->ical, ilICalUtils::ICAL_EOL);
        if (count($lines) == 1) {
            $lines = $this->tokenize($this->ical, ilICalUtils::ICAL_EOL_FB);
        }
        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];

            // Check for next multilines (they start with a space)
            $offset = 1;
            while (isset($lines[$i + $offset]) and
                (strpos($lines[$i + $offset], ilICalUtils::ICAL_SPACE) === 0) or
                (strpos($lines[$i + $offset], ilICalUtils::ICAL_TAB) === 0)) {
                $lines[$i + $offset] = str_replace(ilICalUtils::ICAL_EOL, '', $lines[$i + $offset]);
                $line = $line . substr($lines[$i + $offset], 1);
                $offset++;
            }
            $i += ($offset - 1);

            // Parse this line
            $this->parseLine($line);
        }
    }

    protected function getContainer() : ?ilICalItem
    {
        if (count($this->container)) {
            return $this->container[count($this->container) - 1];
        }
        return null;
    }

    /**
     * @param ilICalItem
     */
    protected function setContainer(ilICalItem $a_container) : void
    {
        $this->container = array($a_container);
    }

    protected function dropContainer() : ?ilICalItem
    {
        if (is_array($this->container)) {
            return array_pop($this->container);
        }
        return null;
    }

    protected function pushContainer(ilICalItem $a_container) : void
    {
        $this->container[] = $a_container;
    }

    protected function parseLine(string $line) : void
    {
        switch (trim($line)) {
            case 'BEGIN:VCALENDAR':
                $this->log->debug('BEGIN VCALENDAR');
                $this->setContainer(new ilICalComponent('VCALENDAR'));
                break;

            case 'END:VCALENDAR':
                $this->log->debug('END VCALENDAR');
                break;

            case 'BEGIN:VEVENT':
                $this->log->debug('BEGIN VEVENT');
                $this->pushContainer(new ilICalComponent('VEVENT'));
                break;

            case 'END:VEVENT':
                $this->log->debug('END VEVENT');
                $this->writeEvent();
                $this->dropContainer();
                break;

            case 'BEGIN:VTIMEZONE':
                $this->log->debug('BEGIN VTIMEZONE');
                $container = new ilICalComponent('VTIMEZONE');
                $this->pushContainer($container);
                break;

            case 'END:VTIMEZONE':
                $this->log->debug('END VTIMEZONE');
                if ($tzid = $this->getContainer()->getItemsByName('TZID')) {
                    $this->default_timezone = $this->getTZ($tzid[0]->getValue());
                }
                $this->dropContainer();
                break;

            default:
                if (strpos(trim($line), 'BEGIN') === 0) {
                    $this->log->info('Do not handling line:' . $line);
                    break;
                }
                if (strpos(trim($line), 'X-WR-TIMEZONE') === 0) {
                    list($param, $value) = $this->splitLine($line);
                    $this->default_timezone = $this->getTZ($value);
                } else {
                    list($params, $values) = $this->splitLine($line);
                    $this->storeItems($params, $values);
                }
                break;
        }
    }

    protected function storeItems(string $a_param_part, string $a_value_part) : void
    {
        // Check for a semicolon in param part and split it.
        $items = array();
        if ($splitted_param = explode(';', $a_param_part)) {
            $counter = 0;
            foreach ($splitted_param as $param) {
                if (!$counter) {
                    $items[$counter]['param'] = $param;
                    $items[$counter]['value'] = $a_value_part;
                } elseif ($splitted_param_values = explode('=', $param)) {
                    $items[$counter]['param'] = $splitted_param_values[0];
                    $items[$counter]['value'] = $splitted_param_values[1];
                }
                ++$counter;
            }
        }
        // Split value part
        $substituted_values = str_replace('\;', '', $a_value_part);

        $values = array();
        if ($splitted_values = explode(';', $substituted_values)) {
            $counter = 0;
            foreach ($splitted_values as $value) {
                // Split by '='
                $splitted_value_values = explode('=', $value);
                if (is_array($splitted_value_values) && count($splitted_value_values) >= 2) {
                    $values[$counter]['param'] = $splitted_value_values[0];
                    $values[$counter]['value'] = $splitted_value_values[1];
                }
                ++$counter;
            }
        }

        // Return if there are no values
        if (!count($items)) {
            $this->log->write(__METHOD__ . ': Cannot parse parameter: ' . $a_param_part . ', value: ' . $a_value_part);
            return;
        }

        $counter = 0;
        foreach ($items as $item) {
            if (!$counter) {
                // First is ical-Parameter
                $parameter = new ilICalProperty($item['param'], $item['value']);

                if (!$this->getContainer() instanceof ilICalItem) {
                    continue;
                }

                $this->getContainer()->addItem($parameter);
                $this->pushContainer($parameter);

                if (count($values) > 1) {
                    foreach ($values as $value) {
                        $value = new ilICalValue($value['param'], $value['value']);
                        $this->getContainer()->addItem($value);
                    }
                }
            } else {
                $value = new ilICalParameter($item['param'], $item['value']);
                $this->getContainer()->addItem($value);
            }
            ++$counter;
        }
        $this->dropContainer();
    }

    protected function splitLine(string $a_line) : array
    {
        $matches = array();

        if (preg_match('/([^:]+):(.*)/', $a_line, $matches)) {
            return array($matches[1], $matches[2]);
        } else {
            $this->log->notice(' Found invalid parameter: ' . $a_line);
        }
        return array('', '');
    }

    protected function tokenize(string $a_string, string $a_tokenizer) : array
    {
        return explode($a_tokenizer, $a_string);
    }

    protected function getTZ(string $a_timezone) : ilTimeZone
    {
        $parts = explode('/', $a_timezone);
        $tz = array_pop($parts);
        $continent = array_pop($parts);
        if (isset($continent) and $continent) {
            $timezone = $continent . '/' . $tz;
        } else {
            $timezone = $a_timezone;
        }
        try {
            if ($this->default_timezone->getIdentifier() == $timezone) {
                return $this->default_timezone;
            } else {
                $this->log->info(': Found new timezone: ' . $timezone);
                return ilTimeZone::_getInstance(trim($timezone));
            }
        } catch (ilTimeZoneException $e) {
            $this->log->notice(': Found invalid timezone: ' . $timezone);
            return $this->default_timezone;
        }
    }

    protected function switchTZ(ilTimeZone $timezone) : void
    {
        try {
            $timezone->switchTZ();
        } catch (ilTimeZoneException $e) {
            $this->log->notice(': Found invalid timezone: ' . $timezone->getIdentifier());
        }
    }

    protected function restoreTZ() : void
    {
        $this->default_timezone->restoreTZ();
    }

    protected function writeEvent() : void
    {
        $entry = new ilCalendarEntry();

        // Search for summary
        foreach ($this->getContainer()->getItemsByName('SUMMARY', false) as $item) {
            if (is_a($item, 'ilICalProperty')) {
                $entry->setTitle($this->purgeString($item->getValue()));
                break;
            }
        }
        // Search description
        foreach ($this->getContainer()->getItemsByName('DESCRIPTION', false) as $item) {
            if (is_a($item, 'ilICalProperty')) {
                $entry->setDescription($this->purgeString($item->getValue()));
                break;
            }
        }

        // Search location
        foreach ($this->getContainer()->getItemsByName('LOCATION', false) as $item) {
            if (is_a($item, 'ilICalProperty')) {
                $entry->setLocation($this->purgeString($item->getValue()));
                break;
            }
        }

        foreach ($this->getContainer()->getItemsByName('DTSTART') as $start) {
            $fullday = false;
            foreach ($start->getItemsByName('VALUE') as $type) {
                if ($type->getValue() == 'DATE') {
                    $fullday = true;
                }
            }
            $start_tz = $this->default_timezone;
            foreach ($start->getItemsByName('TZID') as $param) {
                $start_tz = $this->getTZ($param->getValue());
            }
            if ($fullday) {
                $start = new ilDate(
                    $start->getValue(),
                    IL_CAL_DATE
                );
            } else {
                $start = new ilDateTime(
                    $start->getValue(),
                    IL_CAL_DATETIME,
                    $start_tz->getIdentifier()
                );
            }
            $entry->setStart($start);
            $entry->setFullday($fullday);
        }

        foreach ($this->getContainer()->getItemsByName('DTEND') as $end) {
            $fullday = false;
            foreach ($end->getItemsByName('VALUE') as $type) {
                if ($type->getValue() == 'DATE') {
                    $fullday = true;
                }
            }
            $end_tz = $this->default_timezone;
            foreach ($end->getItemsByName('TZID') as $param) {
                $end_tz = $this->getTZ($param->getValue());
            }
            if ($fullday) {
                $end = new ilDate(
                    $end->getValue(),
                    IL_CAL_DATE
                );
                $end->increment(IL_CAL_DAY, -1);
            } else {
                $end = new ilDateTime(
                    $end->getValue(),
                    IL_CAL_DATETIME,
                    $end_tz->getIdentifier()
                );
            }
            $entry->setEnd($end);
            $entry->setFullday($fullday);
        }

        if (!$entry->getStart() instanceof ilDateTime) {
            $this->log->warning('Cannot find start date. Event ignored.');
            return;
        }

        // check if end date is given otherwise replace with start
        if (
            !$entry->getEnd() instanceof ilDateTime &&
            $entry->getStart() instanceof ilDateTime
        ) {
            $entry->setEnd($entry->getStart());
        }

        // save calendar event
        if ($this->category->getLocationType() == ilCalendarCategory::LTYPE_REMOTE) {
            $entry->setAutoGenerated(true);
        }
        $entry->save();

        $ass = new ilCalendarCategoryAssignments($entry->getEntryId());
        $ass->addAssignment($this->category->getCategoryID());

        // Recurrences
        foreach ($this->getContainer()->getItemsByName('RRULE') as $recurrence) {
            $rec = new ilCalendarRecurrence();
            $rec->setEntryId($entry->getEntryId());

            foreach ($recurrence->getItemsByName('FREQ') as $freq) {
                switch ($freq->getValue()) {
                    case 'DAILY':
                    case 'WEEKLY':
                    case 'MONTHLY':
                    case 'YEARLY':
                        $rec->setFrequenceType((string) $freq->getValue());
                        break;

                    default:
                        $this->log->notice(': Cannot handle recurring event of type: ' . $freq->getValue());
                        break 3;
                }
            }

            foreach ($recurrence->getItemsByName('COUNT') as $value) {
                $rec->setFrequenceUntilCount((int) $value->getValue());
                break;
            }
            foreach ($recurrence->getItemsByName('UNTIL') as $until) {
                $rec->setFrequenceUntilDate(new ilDate($until->getValue(), IL_CAL_DATE));
                break;
            }
            foreach ($recurrence->getItemsByName('INTERVAL') as $value) {
                $rec->setInterval((int) $value->getValue());
                break;
            }
            foreach ($recurrence->getItemsByName('BYDAY') as $value) {
                $rec->setBYDAY((string) $value->getValue());
                break;
            }
            foreach ($recurrence->getItemsByName('BYWEEKNO') as $value) {
                $rec->setBYWEEKNO((string) $value->getValue());
                break;
            }
            foreach ($recurrence->getItemsByName('BYMONTH') as $value) {
                $rec->setBYMONTH((string) $value->getValue());
                break;
            }
            foreach ($recurrence->getItemsByName('BYMONTHDAY') as $value) {
                $rec->setBYMONTHDAY((string) $value->getValue());
                break;
            }
            foreach ($recurrence->getItemsByName('BYYEARDAY') as $value) {
                $rec->setBYYEARDAY((string) $value->getValue());
                break;
            }
            foreach ($recurrence->getItemsByName('BYSETPOS') as $value) {
                $rec->setBYSETPOS((string) $value->getValue());
                break;
            }
            foreach ($recurrence->getItemsByName('WKST') as $value) {
                $rec->setWeekstart((string) $value->getValue());
                break;
            }
            $rec->save();
        }
    }

    protected function purgeString(string $a_string) : string
    {
        $a_string = str_replace("\;", ";", $a_string);
        $a_string = str_replace("\,", ",", $a_string);
        $a_string = str_replace("\:", ":", $a_string);
        return ilUtil::stripSlashes($a_string);
    }
}
