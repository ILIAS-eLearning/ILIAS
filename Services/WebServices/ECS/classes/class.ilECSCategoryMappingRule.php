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

include_once './Services/Calendar/classes/class.ilDate.php';

/**
* Defines a rule for the assignment of ECS remote courses to categories.
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesWebServicesECS
*/
class ilECSCategoryMappingRule
{
    const ATTR_STRING = 1;
    const ATTR_INT = 2;
    const ATTR_ARRAY = 3;
    
    const TYPE_FIXED = 0;
    const TYPE_DURATION = 1;
    const TYPE_BY_TYPE = 2;
    
    const ERR_MISSING_VALUE = 'ecs_err_missing_value';
    const ERR_INVALID_DATES = 'ecs_err_invalid_dates';
    const ERR_INVALID_TYPE = 'ecs_err_invalid_type';
    const ERR_MISSING_BY_TYPE = 'ecs_err_invalid_by_type';
    
    protected $db;
    
    private $mapping_id;
    private $container_id;
    private $field_name;
    private $mapping_type;
    private $mapping_value;
    private $range_dt_start;
    private $range_dt_end;
    private $by_type;
    
    /**
     * Constructor
     * @param int mapping id
     */
    public function __construct($a_mapping_id = 0)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $this->mapping_id = $a_mapping_id;
        
        $this->db = $ilDB;
        $this->read();
    }
    
    /**
     * set mapping id
     * @param	int	$a_mapping_id	mapping id
     * @return void
     */
    protected function setMappingId($a_id)
    {
        $this->mapping_id = $a_id;
    }
    
    /**
     * get mapping id
     * @return
     */
    public function getMappingId()
    {
        return $this->mapping_id;
    }
    
    /**
     * set container id
     * @param int	$a_id	$a_container_id
     * @return
     */
    public function setContainerId($a_id)
    {
        $this->container_id = $a_id;
    }
    
    /**
     * get container id
     * @return
     */
    public function getContainerId()
    {
        return $this->container_id;
    }
    
    /**
     * set date range start
     * @param  object $start ilDate
     * @return
     */
    public function setDateRangeStart($start)
    {
        $this->range_dt_start = $start;
    }
    
    /**
     * get date range start
     * @return
     */
    public function getDateRangeStart()
    {
        return $this->range_dt_start ? $this->range_dt_start : new ilDate(time(), IL_CAL_UNIX);
    }
    
    /**
     * set date range end
     * @param  object $start ilDate
     * @return
     */
    public function setDateRangeEnd($end)
    {
        $this->range_dt_end = $end;
    }
    
    /**
     * get date range end
     * @return
     */
    public function getDateRangeEnd()
    {
        if ($this->range_dt_end) {
            return $this->range_dt_end;
        }
        $this->range_dt_end = $this->getDateRangeStart();
        $this->range_dt_end->increment(IL_CAL_MONTH, 6);
        return $this->range_dt_end;
    }

    /**
     * set field name
     * @param string	$a_field	field name
     * @return
     */
    public function setFieldName($a_field)
    {
        $this->field_name = $a_field;
    }
    
    /**
     * get field name
     * @return
     */
    public function getFieldName()
    {
        return $this->field_name;
    }
    
    /**
     * set mapping type
     * @param int	$type	Mapping type
     * @return
     */
    public function setMappingType($a_type)
    {
        $this->mapping_type = $a_type;
    }
    
    /**
     * get mapping type
     * @return
     */
    public function getMappingType()
    {
        return $this->mapping_type;
    }
    
    /**
     * set mapping value
     * @param string	$val	Mapping value
     * @return
     */
    public function setMappingValue($a_value)
    {
        $this->mapping_value = $a_value;
    }
    
    /**
     * get mapping value
     * @return
     */
    public function getMappingValue()
    {
        return $this->mapping_value;
    }
    
    /**
     * get mapping values as array
     * @return
     */
    public function getMappingAsArray()
    {
        return explode(',', $this->getMappingValue());
    }
    
    /**
     * set mapping by type
     * @param string	$type	Mapping type
     * @return
     */
    public function setByType($a_type)
    {
        $this->by_type = $a_type;
    }
    
    /**
     * get mapping by type
     * @return string
     */
    public function getByType()
    {
        return $this->by_type;
    }
    
    /**
     * delete rule
     * @return
     */
    public function delete()
    {
        $sta = $this->db->manipulateF(
            'DELETE FROM ecs_container_mapping WHERE mapping_id = %s ',
            array('integer'),
            array($this->getMappingId())
        );
    }
    
    /**
     * update
     * @return
     */
    public function update()
    {
        if ($this->getMappingType() == self::TYPE_BY_TYPE) {
            $mapping_value = $this->getByType();
        } else {
            $mapping_value = $this->getMappingValue();
        }
        
        $sta = $this->db->manipulateF(
            'UPDATE ecs_container_mapping SET ' .
            'container_id = %s, ' .
            'field_name = %s, ' .
            'mapping_type = %s, ' .
            'mapping_value = %s, ' .
            'date_range_start = %s,' .
            'date_range_end = %s ' .
            'WHERE mapping_id = %s',
            array('integer','text','integer','text','integer','integer','integer'),
            array(
            $this->getContainerId(),
            $this->getFieldName(),
            $this->getMappingType(),
            $mapping_value,
            $this->getDateRangeStart()->get(IL_CAL_UNIX),
            $this->getDateRangeEnd()->get(IL_CAL_UNIX),
            $this->getMappingId())
        );
    }
    
    /**
     * save
     * @return
     */
    public function save()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if ($this->getMappingType() == self::TYPE_BY_TYPE) {
            $mapping_value = $this->getByType();
        } else {
            $mapping_value = $this->getMappingValue();
        }
        
        $mapping_id = $ilDB->nextId('ecs_container_mapping');
        $sta = $this->db->manipulateF(
            'INSERT INTO ecs_container_mapping  ' .
            '(mapping_id,container_id,field_name,mapping_type,mapping_value,date_range_start,date_range_end) ' .
            'VALUES(%s,%s,%s,%s,%s,%s,%s) ',
            array('integer','integer','text','integer','text','integer','integer'),
            array(
                $mapping_id,
                $this->getContainerId(),
                $this->getFieldName(),
                $this->getMappingType(),
                $mapping_value,
                $this->getDateRangeStart()->get(IL_CAL_UNIX),
                $this->getDateRangeEnd()->get(IL_CAL_UNIX))
        );
    }
    
    /**
     * validate rule
     * @return
     */
    public function validate()
    {
        if (ilObject::_lookupType(ilObject::_lookupObjId($this->getContainerId())) != 'cat') {
            return self::ERR_INVALID_TYPE;
        }
        if (!ilDateTime::_after($this->getDateRangeEnd(), $this->getDateRangeStart(), IL_CAL_DAY)) {
            return self::ERR_INVALID_DATES;
        }
        if ($this->getMappingType() == self::TYPE_DURATION && !in_array($this->getFieldName(), array('begin', 'end'))) {
            return self::ERR_MISSING_VALUE;
        }
        // handled by form gui?
        if ($this->getMappingType() == self::TYPE_FIXED and !$this->getMappingValue()) {
            return self::ERR_MISSING_VALUE;
        }
        if ($this->getMappingType() == self::TYPE_BY_TYPE && $this->getFieldName() != 'type') {
            return self::ERR_MISSING_BY_TYPE;
        }
        if ($this->getMappingType() != self::TYPE_BY_TYPE && $this->getFieldName() == 'type') {
            return self::ERR_MISSING_VALUE;
        }
        return 0;
    }
    
    /**
     * condition to string
     * @return
     */
    public function conditionToString()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        switch ($this->getMappingType()) {
            case self::TYPE_FIXED:

                if ($this->getFieldName() == 'part_id') {
                    return $lng->txt('ecs_field_' . $this->getFieldName()) . ': ' . $this->participantsToString();
                }
                return $lng->txt('ecs_field_' . $this->getFieldName()) . ': ' . $this->getMappingValue();
                
            case self::TYPE_DURATION:
                return $lng->txt('ecs_field_' . $this->getFieldName()) . ': ' . ilDatePresentation::formatPeriod(
                    $this->getDateRangeStart(),
                    $this->getDateRangeEnd()
                );
                
            case self::TYPE_BY_TYPE:
                return $lng->txt('type') . ': ' . $lng->txt('obj_' . $this->getByType());
        }
    }
    
    /**
     * get strong presentation of participants
     * @param
     * @return
     */
    public function participantsToString()
    {
        include_once './Services/WebServices/ECS/classes/class.ilECSUtils.php';
        
        $part_string = "";
        $part = explode(',', $this->getMappingValue());
        $counter = 0;
        foreach ($part as $part_id) {
            if ($counter++) {
                $part_string .= ', ';
            }
            $part_string .= '"';
            
            $part_id_arr = explode('_', $part_id);
            if ($name = ilECSUtils::lookupParticipantName($part_id_arr[1], $part_id_arr[0])) {
                $part_string .= $name;
            } else {
                $part_string .= $part_id;
            }
            $part_string .= '"';
        }
        return $part_string;
    }

    /**
     * Check if rule matches a specific econtent
     * @param array	 $a_matchable_content
     * @return bool
     */
    public function matches(array $a_matchable_content)
    {
        if (isset($a_matchable_content[$this->getFieldName()])) {
            $value = $a_matchable_content[$this->getFieldName()];
            return $this->matchesValue($value[0], $value[1]);
        }
        return false;
    }
    
    /**
     * Check if value matches
     * @param	mixed	$a_value	Econtent value
     * @param	int		$a_type		Parameter type
     * @return
     */
    protected function matchesValue($a_value, $a_type)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];
        
        
        switch ($a_type) {
            case self::ATTR_ARRAY:
                $values = explode(',', $a_value);
                $ilLog->write(__METHOD__ . ': Checking for value: ' . $a_value);
                $ilLog->write(__METHOD__ . ': Checking against attribute values: ' . $this->getMappingValue());
                break;
                
            case self::ATTR_INT:
                $ilLog->write(__METHOD__ . ': Checking for value: ' . $a_value);
                $ilLog->write(__METHOD__ . ': Checking against attribute values: ' . $this->getMappingValue());
                $values = array($a_value);
                break;
                
            case self::ATTR_STRING:
                $values = array($a_value);
                break;
        }
        $values = explode(',', $a_value);
        
        foreach ($values as $value) {
            $value = trim($value);
            switch ($this->getMappingType()) {
                case self::TYPE_FIXED:
                    
                    foreach ($this->getMappingAsArray() as $attribute_value) {
                        $attribute_value = trim($attribute_value);
                        if (strcasecmp($attribute_value, $value) == 0) {
                            return true;
                        }
                    }
                    break;
                    
                case self::TYPE_DURATION:
                    include_once './Services/Calendar/classes/class.ilDateTime.php';
                    $tmp_date = new ilDate($a_value, IL_CAL_UNIX);
                    return ilDateTime::_after($tmp_date, $this->getDateRangeStart()) and
                        ilDateTime::_before($tmp_date, $this->getDateRangeEnd());
            }
        }
        return false;
    }
    
    /**
     * Read entries
     * @return
     */
    protected function read()
    {
        if (!$this->getMappingId()) {
            return false;
        }
        $res = $this->db->queryF(
            'SELECT * FROM ecs_container_mapping WHERE mapping_id = %s',
            array('integer'),
            array($this->getMappingId())
        );
        while ($row = $this->db->fetchObject($res)) {
            $this->setMappingId($row->mapping_id);
            $this->setDateRangeStart($row->date_range_start ? new ilDate($row->date_range_start, IL_CAL_UNIX) : null);
            $this->setDateRangeEnd($row->date_range_end ? new ilDate($row->date_range_end, IL_CAL_UNIX) : null);
            $this->setMappingType($row->mapping_type);
            $this->setFieldName($row->field_name);
            $this->setContainerId($row->container_id);
            
            if ($this->getMappingType() == self::TYPE_BY_TYPE) {
                $this->setByType($row->mapping_value);
            } else {
                $this->setMappingValue($row->mapping_value);
            }
        }
        return true;
    }
}
