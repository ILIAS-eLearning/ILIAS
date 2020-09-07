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

include_once('./Services/Calendar/classes/iCal/class.ilICalUtils.php');
include_once('./Services/Calendar/classes/class.ilDateTime.php');
include_once('./Services/Calendar/classes/class.ilCalendarEntry.php');
include_once('./Services/Calendar/classes/class.ilTimeZone.php');
include_once('./Services/Calendar/classes/class.ilTimeZoneException.php');

include_once('./Services/Calendar/classes/iCal/class.ilICalComponent.php');
include_once('./Services/Calendar/classes/iCal/class.ilICalProperty.php');
include_once('./Services/Calendar/classes/iCal/class.ilICalParameter.php');
include_once('./Services/Calendar/classes/iCal/class.ilICalValue.php');

include_once './Services/Calendar/exceptions/class.ilICalParserException.php';

/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
*
* @ingroup Services/Calendar
*/
class ilICalParser
{
    const INPUT_STRING = 1;
    const INPUT_FILE = 2;
    
    /**
     * @var ilLogger
     */
    protected $log = null;
    
    protected $category = null;

    protected $ical = '';
    protected $file = '';
    protected $default_timezone = null;

    protected $container = array();

    /**
     * Constructor
     *
     * @access public
     * @param string ical string
     *
     * @throws ilICalParserException
     *
     */
    public function __construct($a_ical, $a_type)
    {
        if ($a_type == self::INPUT_STRING) {
            $this->ical = $a_ical;
        } elseif ($a_type == self::INPUT_FILE) {
            $this->file = $a_ical;
            $this->ical = file_get_contents($a_ical);
            
            if (!strlen($this->ical)) {
                throw new ilICalParserException($GLOBALS['DIC']['cal_err_no_input']);
            }
            #$GLOBALS['DIC']['ilLog']->write(__METHOD__.': Ical content: '. $this->ical);
        }
        $this->log = $GLOBALS['DIC']->logger()->cal();
    }
    
    /**
     * set category id
     *
     * @access public
     * @param int category id
     * @return
     */
    public function setCategoryId($a_id)
    {
        include_once('./Services/Calendar/classes/class.ilCalendarCategory.php');
        $this->category = new ilCalendarCategory($a_id);
    }
    
    /**
     * Parse input
     *
     * @access public
     *
     */
    public function parse()
    {
        $this->default_timezone = ilTimeZone::_getInstance();
        
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
    
    /**
     * get container
     *
     * @access protected
     */
    protected function getContainer()
    {
        return $this->container[count($this->container) - 1];
    }
    
    /**
     * set container
     *
     * @access protected
     * @param ilICalItem
     */
    protected function setContainer($a_container)
    {
        $this->container = array($a_container);
    }
    
    /**
     * pop la
     *
     * @access protected
     */
    protected function dropContainer()
    {
        return array_pop($this->container);
    }
    
    /**
     * push container
     *
     * @access protected
     * @param ilICalItem
     */
    protected function pushContainer($a_container)
    {
        $this->container[] = $a_container;
    }
    
    
    /**
     * parse a line
     *
     * @access protected
     */
    protected function parseLine($line)
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
    
    /**
     * store items
     *
     * @access protected
     */
    protected function storeItems($a_param_part, $a_value_part)
    {
        // Check for a semicolon in param part and split it.
        
        $items = array();
        if ($splitted_param = explode(';', $a_param_part)) {
            $counter = 0;
            foreach ($splitted_param as $param) {
                if (!$counter) {
                    $items[$counter]['param'] = $param;
                    $items[$counter]['value'] = $a_value_part;
                } else {
                    // Split by '='
                    if ($splitted_param_values = explode('=', $param)) {
                        $items[$counter]['param'] = $splitted_param_values[0];
                        $items[$counter]['value'] = $splitted_param_values[1];
                    }
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
                if ($splitted_value_values = explode('=', $value)) {
                    $values[$counter]['param'] = $splitted_value_values[0];
                    $values[$counter]['value'] = $splitted_value_values[1];
                }
                ++$counter;
            }
        }

        // Return if there are no values
        if (!count($items)) {
            $this->log->write(__METHOD__ . ': Cannot parse parameter: ' . $a_param_part . ', value: ' . $a_value_part);
            return false;
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
    
    
    /**
     * parse parameters
     *
     * @access protected
     * @param string a line
     */
    protected function splitLine($a_line)
    {
        $matches = array();
        
        if (preg_match('/([^:]+):(.*)/', $a_line, $matches)) {
            return array($matches[1],$matches[2]);
        } else {
            $this->log->write(__METHOD__ . ': Found invalid parameter: ' . $a_line);
        }
        
        return array('','');
    }
    
    /**
     * tokenize string
     *
     * @access protected
     */
    protected function tokenize($a_string, $a_tokenizer)
    {
        return explode($a_tokenizer, $a_string);
    }
    
    /**
     * get timezone
     *
     * @access protected
     */
    protected function getTZ($a_timezone)
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
                $this->log->write(__METHOD__ . ': Found new timezone: ' . $timezone);
                return ilTimeZone::_getInstance(trim($timezone));
            }
        } catch (ilTimeZoneException $e) {
            $this->log->write(__METHOD__ . ': Found invalid timezone: ' . $timezone);
            return $this->default_timezone;
        }
    }
    
    /**
     * Switch timezone
     *
     * @access protected
     */
    protected function switchTZ(ilTimeZone $timezone)
    {
        try {
            $timezone->switchTZ();
        } catch (ilTimeZoneException $e) {
            $this->log->write(__METHOD__ . ': Found invalid timezone: ' . $timezone);
            return false;
        }
    }
    
    /**
     * restore time
     *
     * @access protected
     */
    protected function restoreTZ()
    {
        $this->default_timezone->restoreTZ();
    }
    
    /**
     * write a new event
     *
     * @access protected
     */
    protected function writeEvent()
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
            return false;
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
        
        include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
        $ass = new ilCalendarCategoryAssignments($entry->getEntryId());
        $ass->addAssignment($this->category->getCategoryID());
        
        
        // Recurrences
        foreach ($this->getContainer()->getItemsByName('RRULE') as $recurrence) {
            #var_dump("<pre>",$recurrence,"</pre>");
            
            
            include_once('./Services/Calendar/classes/class.ilCalendarRecurrence.php');
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
                        $this->log->write(__METHOD__ . ': Cannot handle recurring event of type: ' . $freq->getValue());
                        break 3;
                }
            }
            
            foreach ($recurrence->getItemsByName('COUNT') as $value) {
                $rec->setFrequenceUntilCount((string) $value->getValue());
                break;
            }
            foreach ($recurrence->getItemsByName('UNTIL') as $until) {
                $rec->setFrequenceUntilDate(new ilDate($until->getValue(), IL_CAL_DATE));
                break;
            }
            foreach ($recurrence->getItemsByName('INTERVAL') as $value) {
                $rec->setInterval((string) $value->getValue());
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
    
    /**
     * purge string
     *
     * @access protected
     */
    protected function purgeString($a_string)
    {
        $a_string = str_replace("\;", ";", $a_string);
        $a_string = str_replace("\,", ",", $a_string);
        $a_string = str_replace("\:", ":", $a_string);
        return ilUtil::stripSlashes($a_string);
    }
}
