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

/**
 * schedule for booking ressource
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBookingSchedule
{
    protected ilDBInterface $db;
    protected int $id = 0;
    protected string $title = "";
    protected int $pool_id = 0;
    protected int $raster = 0;
    protected int $rent_min = 0;
    protected int $rent_max = 0;
    protected int $auto_break = 0;
    protected int $deadline = 0;
    protected array $definition;
    protected ?ilDateTime $av_from;
    protected ?ilDateTime $av_to;

    public function __construct(
        int $a_id = null
    ) {
        global $DIC;

        $this->db = $DIC->database();
        $this->id = (int) $a_id;
        $this->read();
    }

    public function setTitle(
        string $a_title
    ) : void {
        $this->title = $a_title;
    }

    public function getTitle() : string
    {
        return $this->title;
    }
    
    public function setPoolId(
        int $a_pool_id
    ) : void {
        $this->pool_id = $a_pool_id;
    }

    public function getPoolId() : int
    {
        return $this->pool_id;
    }

    /**
     * Set booking raster (in minutes)
     */
    public function setRaster(
        int $a_raster
    ) : void {
        $this->raster = $a_raster;
    }

    public function getRaster() : int
    {
        return $this->raster;
    }

    /**
     * Set minimum rental time
     */
    public function setMinRental(
        int $a_min
    ) : void {
        $this->rent_min = $a_min;
    }

    public function getMinRental() : int
    {
        return $this->rent_min;
    }

    /**
     * Set maximum rental time
     */
    public function setMaxRental(
        int $a_max
    ) : void {
        $this->rent_max = $a_max;
    }

    public function getMaxRental() : int
    {
        return $this->rent_max;
    }

    // set break time
    public function setAutoBreak(int $a_break) : void
    {
        $this->auto_break = $a_break;
    }

    public function getAutoBreak() : int
    {
        return $this->auto_break;
    }

    /**
     * Set deadline
     */
    public function setDeadline(int $a_deadline) : void
    {
        $this->deadline = $a_deadline;
    }

    public function getDeadline() : int
    {
        return $this->deadline;
    }

    /**
     * Set definition
     */
    public function setDefinition(
        array $a_definition
    ) : void {
        $this->definition = $a_definition;
    }

    public function getDefinition() : array
    {
        return $this->definition;
    }
    
    public function setAvailabilityFrom(
        ?ilDateTime $a_date = null
    ) : void {
        $this->av_from = $a_date;
    }
    
    public function getAvailabilityFrom() : ?ilDateTime
    {
        return $this->av_from;
    }
    
    public function setAvailabilityTo(
        ?ilDateTime $a_date = null
    ) : void {
        $this->av_to = $a_date;
    }
    
    public function getAvailabilityTo() : ?ilDateTime
    {
        return $this->av_to;
    }

    protected function read() : void
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

    public function save() : bool
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

    public function update() : bool
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
        return true;
    }
    
    public function doClone(int $a_pool_id) : bool
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
     * Save current definition (slots)
     */
    protected function saveDefinition() : bool
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
        return true;
    }

    /**
     * Check if given pool has any defined schedules
     */
    public static function hasExistingSchedules(int $a_pool_id) : bool
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
     */
    public static function getList(int $a_pool_id) : array
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

    public function delete() : int
    {
        $ilDB = $this->db;

        if ($this->id) {
            return $ilDB->manipulate('DELETE FROM booking_schedule' .
                ' WHERE booking_schedule_id = ' . $ilDB->quote($this->id, 'integer'));
        }
        return 0;
    }
    
    /**
     * Return definition grouped by slots (not days)
     */
    public function getDefinitionBySlots() : array
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
    
    public function setDefinitionBySlots(array $a_def) : void
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
