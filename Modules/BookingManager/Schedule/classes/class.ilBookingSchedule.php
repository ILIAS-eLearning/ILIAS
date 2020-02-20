<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * schedule for booking ressource
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesBookingManager
 */
class ilBookingSchedule
{
    /**
     * @var ilDB
     */
    protected $db;

    protected $id;			// int
    protected $title;		// string
    protected $pool_id;		// int
    protected $raster;		// int
    protected $rent_min;	// int
    protected $rent_max;	// int
    protected $auto_break;	// int
    protected $deadline;	// int
    protected $definition;  // array
    protected $av_from;		// ildatetime
    protected $av_to;		// ildatetime

    /**
     * Constructor
     *
     * if id is given will read dataset from db
     *
     * @param	int	$a_id
     */
    public function __construct($a_id = null)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->id = (int) $a_id;
        $this->read();
    }

    /**
     * Set object title
     * @param	string	$a_title
     */
    public function setTitle($a_title)
    {
        $this->title = $a_title;
    }

    /**
     * Get object title
     * @return	string
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * Set booking pool id (aka parent obj ref id)
     * @param	int	$a_pool_id
     */
    public function setPoolId($a_pool_id)
    {
        $this->pool_id = (int) $a_pool_id;
    }

    /**
     * Get booking pool id
     * @return	int
     */
    public function getPoolId()
    {
        return $this->pool_id;
    }

    /**
     * Set booking raster (in minutes)
     * @param	int	$a_raster
     */
    public function setRaster($a_raster)
    {
        $this->raster = (int) $a_raster;
    }

    /**
     * Get booking raster
     * @return	int
     */
    public function getRaster()
    {
        return $this->raster;
    }

    /**
     * Set minimum rental time
     * @param	int	$a_min
     */
    public function setMinRental($a_min)
    {
        $this->rent_min = (int) $a_min;
    }

    /**
     * Get minimum rental time
     * @return	int
     */
    public function getMinRental()
    {
        return $this->rent_min;
    }

    /**
     * Set maximum rental time
     * @param	int	$a_max
     */
    public function setMaxRental($a_max)
    {
        $this->rent_max = (int) $a_max;
    }

    /**
     * Get maximum rental time
     * @return	int
     */
    public function getMaxRental()
    {
        return $this->rent_max;
    }

    /**
     * Set break time
     * @param	int	$a_break
     */
    public function setAutoBreak($a_break)
    {
        $this->auto_break = (int) $a_break;
    }

    /**
     * Get break time
     * @return	int
     */
    public function getAutoBreak()
    {
        return $this->auto_break;
    }

    /**
     * Set deadline
     * @param	int	$a_deadline
     */
    public function setDeadline($a_deadline)
    {
        $this->deadline = (int) $a_deadline;
    }

    /**
     * Get deadline
     * @return	int
     */
    public function getDeadline()
    {
        return $this->deadline;
    }

    /**
     * Set definition
     * @param	array	$a_definition
     */
    public function setDefinition($a_definition)
    {
        $this->definition = $a_definition;
    }

    /**
     * Get definition
     * @return	array
     */
    public function getDefinition()
    {
        return $this->definition;
    }
    
    /**
     * Set availability start
     *
     * @param ilDateTime $a_date
     */
    public function setAvailabilityFrom(ilDateTime $a_date = null)
    {
        $this->av_from = $a_date;
    }
    
    /**
     * Get availability start
     *
     * @return ilDateTime
     */
    public function getAvailabilityFrom()
    {
        return $this->av_from;
    }
    
    /**
     * Set availability end
     *
     * @param ilDateTime $a_date
     */
    public function setAvailabilityTo(ilDateTime $a_date = null)
    {
        $this->av_to = $a_date;
    }
    
    /**
     * Get availability end
     *
     * @return ilDateTime
     */
    public function getAvailabilityTo()
    {
        return $this->av_to;
    }

    /**
     * Get dataset from db
     */
    protected function read()
    {
        $ilDB = $this->db;
        
        if ($this->id) {
            $set = $ilDB->query('SELECT title,raster,rent_min,rent_max,auto_break,' .
                'deadline,av_from,av_to' .
                ' FROM booking_schedule' .
                ' WHERE booking_schedule_id = ' . $ilDB->quote($this->id, 'integer'));
            $row = $ilDB->fetchAssoc($set);
            $this->setTitle($row['title']);
            $this->setDeadline($row['deadline']);
            $this->setAvailabilityFrom($row['av_from'] ? new ilDateTime($row['av_from'], IL_CAL_UNIX) : null);
            $this->setAvailabilityTo($row['av_to'] ? new ilDateTime($row['av_to'], IL_CAL_UNIX) : null);
            if ($row['raster']) {
                $this->setRaster($row['raster']);
                $this->setMinRental($row['rent_min']);
                $this->setMaxRental($row['rent_max']);
                $this->setAutoBreak($row['auto_break']);
            }

            // load definition
            $definition = array();
            $set = $ilDB->query('SELECT day_id,slot_id,times' .
                ' FROM booking_schedule_slot' .
                ' WHERE booking_schedule_id = ' . $ilDB->quote($this->id, 'integer'));
            while ($row = $ilDB->fetchAssoc($set)) {
                $definition[$row["day_id"]][$row["slot_id"]] = $row["times"];
            }
            $this->setDefinition($definition);
        }
    }

    /**
     * Create new entry in db
     * @return	bool
     */
    public function save()
    {
        $ilDB = $this->db;

        if ($this->id) {
            return false;
        }

        $this->id = $ilDB->nextId('booking_schedule');

        $av_from = ($this->getAvailabilityFrom() && !$this->getAvailabilityFrom()->isNull())
            ? $this->getAvailabilityFrom()->get(IL_CAL_UNIX)
            : null;
        $av_to = ($this->getAvailabilityTo() && !$this->getAvailabilityTo()->isNull())
            ? $this->getAvailabilityTo()->get(IL_CAL_UNIX)
            : null;

        $ilDB->manipulate('INSERT INTO booking_schedule' .
            ' (booking_schedule_id,title,pool_id,raster,rent_min,rent_max,auto_break,' .
            'deadline,av_from,av_to)' .
            ' VALUES (' . $ilDB->quote($this->id, 'integer') . ',' . $ilDB->quote($this->getTitle(), 'text') .
            ',' . $ilDB->quote($this->getPoolId(), 'integer') . ',' . $ilDB->quote($this->getRaster(), 'integer') .
            ',' . $ilDB->quote($this->getMinRental(), 'integer') . ',' . $ilDB->quote($this->getMaxRental(), 'integer') .
            ',' . $ilDB->quote($this->getAutoBreak(), 'integer') . ',' . $ilDB->quote($this->getDeadline(), 'integer') .
            ',' . $ilDB->quote($av_from, 'integer') . ',' . $ilDB->quote($av_to, 'integer') . ')');

        $this->saveDefinition();
        
        return $this->id;
    }

    /**
     * Update entry in db
     * @return	bool
     */
    public function update()
    {
        $ilDB = $this->db;

        if (!$this->id) {
            return false;
        }

        $av_from = ($this->getAvailabilityFrom() && !$this->getAvailabilityFrom()->isNull())
            ? $this->getAvailabilityFrom()->get(IL_CAL_UNIX)
            : null;
        $av_to = ($this->getAvailabilityTo() && !$this->getAvailabilityTo()->isNull())
            ? $this->getAvailabilityTo()->get(IL_CAL_UNIX)
            : null;
        
        $ilDB->manipulate('UPDATE booking_schedule' .
            ' SET title = ' . $ilDB->quote($this->getTitle(), 'text') .
            ', pool_id = ' . $ilDB->quote($this->getPoolId(), 'integer') .
            ', raster = ' . $ilDB->quote($this->getRaster(), 'integer') .
            ', rent_min = ' . $ilDB->quote($this->getMinRental(), 'integer') .
            ', rent_max = ' . $ilDB->quote($this->getMaxRental(), 'integer') .
            ', auto_break = ' . $ilDB->quote($this->getAutoBreak(), 'integer') .
            ', deadline = ' . $ilDB->quote($this->getDeadline(), 'integer') .
            ', av_from = ' . $ilDB->quote($av_from, 'integer') .
            ', av_to = ' . $ilDB->quote($av_to, 'integer') .
            ' WHERE booking_schedule_id = ' . $ilDB->quote($this->id, 'integer'));

        $this->saveDefinition();
    }
    
    public function doClone($a_pool_id)
    {
        $new_obj = new self();
        $new_obj->setPoolId($a_pool_id);
        $new_obj->setTitle($this->getTitle());
        $new_obj->setRaster($this->getRaster());
        $new_obj->setMinRental($this->getMinRental());
        $new_obj->setMaxRental($this->getMaxRental());
        $new_obj->setAutoBreak($this->getAutoBreak());
        $new_obj->setDeadline($this->getDeadline());
        $new_obj->setDefinition($this->getDefinition());
        $new_obj->setAvailabilityFrom($this->getAvailabilityFrom());
        $new_obj->setAvailabilityTo($this->getAvailabilityTo());
        return $new_obj->save();
    }

    /**
     * Save current definition
     */
    protected function saveDefinition()
    {
        $ilDB = $this->db;

        if (!$this->id) {
            return false;
        }

        $ilDB->manipulate('DELETE FROM booking_schedule_slot' .
            ' WHERE booking_schedule_id = ' . $ilDB->quote($this->id, 'integer'));

        $definition = $this->getDefinition();
        if ($definition) {
            foreach ($definition as $day_id => $slots) {
                foreach ($slots as $slot_id => $times) {
                    $fields = array(
                        "booking_schedule_id" => array('integer', $this->id),
                        "day_id" => array('text', $day_id),
                        "slot_id" => array('integer', $slot_id),
                        "times" => array('text', $times)
                        );
                    $ilDB->insert('booking_schedule_slot', $fields);
                }
            }
        }
    }

    /**
     * Check if given pool has any defined schedules
     * @param int $a_pool_id
     * @return bool
     */
    public static function hasExistingSchedules($a_pool_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT booking_schedule_id" .
            " FROM booking_schedule" .
            " WHERE pool_id = " . $ilDB->quote($a_pool_id, 'integer'));
        return (bool) $ilDB->numRows($set);
    }

    /**
     * Get list of booking objects for given pool
     * @param	int	$a_pool_id
     * @return	array
     */
    public static function getList($a_pool_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query('SELECT s.booking_schedule_id,s.title,' .
            'MAX(o.schedule_id) AS object_has_schedule' .
            ' FROM booking_schedule s' .
            ' LEFT JOIN booking_object o ON (s.booking_schedule_id = o.schedule_id)' .
            ' WHERE s.pool_id = ' . $ilDB->quote($a_pool_id, 'integer') .
            ' GROUP BY s.booking_schedule_id,s.title' .
            ' ORDER BY s.title');
        $res = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            if (!$row['object_has_schedule']) {
                $row['is_used'] = false;
            } else {
                $row['is_used'] = true;
            }
            $res[] = $row;
        }
        return $res;
    }

    /**
     * Delete single entry
     * @return bool
     */
    public function delete()
    {
        $ilDB = $this->db;

        if ($this->id) {
            return $ilDB->manipulate('DELETE FROM booking_schedule' .
                ' WHERE booking_schedule_id = ' . $ilDB->quote($this->id, 'integer'));
        }
    }
    
    /**
     * Return definition grouped by slots (not days)
     *
     * @return array
     */
    public function getDefinitionBySlots()
    {
        $def = $this->getDefinition();
        $slots = array();
        foreach ($def as $day => $times) {
            foreach ($times as $time) {
                $slots[$time][] = $day;
            }
        }
        foreach ($slots as $time => $days) {
            $slots[$time] = array_unique($days);
        }
        ksort($slots);
        return $slots;
    }
    
    public function setDefinitionBySlots(array $a_def)
    {
        $slots = array();
        foreach ($a_def as $time => $days) {
            foreach ($days as $day) {
                $slots[$day][] = $time;
            }
        }
        $this->setDefinition($slots);
    }
}
