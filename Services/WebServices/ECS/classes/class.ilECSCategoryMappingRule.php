<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
* Defines a rule for the assignment of ECS remote courses to categories.
*
* @author Stefan Meyer <meyer@leifos.com>
*/
class ilECSCategoryMappingRule
{
    public const ATTR_STRING = 1;
    public const ATTR_INT = 2;
    public const ATTR_ARRAY = 3;
    
    public const TYPE_FIXED = 0;
    public const TYPE_DURATION = 1;
    public const TYPE_BY_TYPE = 2;
    
    public const ERR_MISSING_VALUE = 'ecs_err_missing_value';
    public const ERR_INVALID_DATES = 'ecs_err_invalid_dates';
    public const ERR_INVALID_TYPE = 'ecs_err_invalid_type';
    public const ERR_MISSING_BY_TYPE = 'ecs_err_invalid_by_type';
    
    private ilDBInterface $db;
    private ilLanguage $language;
    private ilLogger $logger;
    
    private int $mapping_id;
    private ?int $container_id = null;
    private ?string $field_name = null;
    private int $mapping_type = ilECSCategoryMappingRule::TYPE_FIXED;
    private ?string $mapping_value = null;
    private ?ilDate $range_dt_start = null;
    private ?ilDate $range_dt_end = null;
    private ?string $by_type = null;
    
    /**
     * Constructor
     * @param int mapping id
     */
    public function __construct(int $a_mapping_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->language = $DIC->language();
        $this->logger = $DIC->logger()->wsrv();
        
        $this->mapping_id = $a_mapping_id;

        $this->read();
    }
    
    /**
     * set mapping id
     * @param	int	$a_mapping_id	mapping id
     * @return void
     */
    protected function setMappingId(int $a_id) : void
    {
        $this->mapping_id = $a_id;
    }
    
    /**
     * get mapping id
     * @return
     */
    public function getMappingId() : int
    {
        return $this->mapping_id;
    }
    
    /**
     * set container id
     * @param int	$a_id	$a_container_id
     * @return
     */
    public function setContainerId(int $a_id) : void
    {
        $this->container_id = $a_id;
    }
    
    /**
     * get container id
     */
    public function getContainerId() : ?int
    {
        return $this->container_id;
    }
    
    /**
     * set date range start
     * @param  object $start ilDate
     */
    public function setDateRangeStart(ilDate $start) : void
    {
        $this->range_dt_start = $start;
    }
    
    /**
     * get date range start
     */
    public function getDateRangeStart() : ilDate
    {
        return $this->range_dt_start ?: new ilDate(time(), IL_CAL_UNIX);
    }
    
    /**
     * set date range end
     * @param  object $start ilDate
     * @return
     */
    public function setDateRangeEnd(ilDate $end) : void
    {
        $this->range_dt_end = $end;
    }
    
    /**
     * get date range end
     * @return
     */
    public function getDateRangeEnd() : ilDate
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
    public function setFieldName(string $a_field) : void
    {
        $this->field_name = $a_field;
    }
    
    /**
     * get field name
     * @return
     */
    public function getFieldName() : ?string
    {
        return $this->field_name;
    }
    
    /**
     * set mapping type
     * @param int $a_type	Mapping type
     */
    public function setMappingType(int $a_type) : void
    {
        $this->mapping_type = $a_type;
    }
    
    /**
     * get mapping type
     */
    public function getMappingType() : int
    {
        return $this->mapping_type;
    }
    
    /**
     * set mapping value
     * @param string	$val	Mapping value
     */
    public function setMappingValue(string $a_value) : void
    {
        $this->mapping_value = $a_value;
    }
    
    /**
     * get mapping value
     * @return
     */
    public function getMappingValue() : ?string
    {
        return $this->mapping_value;
    }
    
    /**
     * get mapping values as array
     * @return
     */
    public function getMappingAsArray() : array
    {
        return explode(',', $this->getMappingValue());
    }
    
    /**
     * set mapping by type
     * @param string	$type	Mapping type
     * @return
     */
    public function setByType(string $a_type) : void
    {
        $this->by_type = $a_type;
    }
    
    /**
     * get mapping by type
     * @return string
     */
    public function getByType() : ?string
    {
        return $this->by_type;
    }
    
    /**
     * delete rule
     * @todo move to repository class
     * @return
     */
    public function delete() : void
    {
        $this->db->manipulateF(
            'DELETE FROM ecs_container_mapping WHERE mapping_id = %s ',
            array('integer'),
            array($this->getMappingId())
        );
    }
    
    /**
     * update
     * @return
     */
    public function update() : void
    {
        if ($this->getMappingType() === self::TYPE_BY_TYPE) {
            $mapping_value = $this->getByType();
        } else {
            $mapping_value = $this->getMappingValue();
        }
        
        $this->db->manipulateF(
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
    public function save() : void
    {
        if ($this->getMappingType() === self::TYPE_BY_TYPE) {
            $mapping_value = $this->getByType();
        } else {
            $mapping_value = $this->getMappingValue();
        }
        
        $mapping_id = $this->db->nextId('ecs_container_mapping');
        $this->db->manipulateF(
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
     */
    public function validate() : string
    {
        if (ilObject::_lookupType(ilObject::_lookupObjId($this->getContainerId())) !== 'cat') {
            return self::ERR_INVALID_TYPE;
        }
        if (!ilDateTime::_after($this->getDateRangeEnd(), $this->getDateRangeStart(), IL_CAL_DAY)) {
            return self::ERR_INVALID_DATES;
        }
        if ($this->getMappingType() === self::TYPE_DURATION && !in_array($this->getFieldName(), array('begin', 'end'))) {
            return self::ERR_MISSING_VALUE;
        }
        // handled by form gui?
        if ($this->getMappingType() === self::TYPE_FIXED || !$this->getMappingValue()) {
            return self::ERR_MISSING_VALUE;
        }
        if ($this->getMappingType() === self::TYPE_BY_TYPE && $this->getFieldName() !== 'type') {
            return self::ERR_MISSING_BY_TYPE;
        }
        if ($this->getMappingType() !== self::TYPE_BY_TYPE && $this->getFieldName() === 'type') {
            return self::ERR_MISSING_VALUE;
        }
        return '';
    }
    
    /**
     * condition to string
     * @return
     */
    public function conditionToString() : string
    {
        switch ($this->getMappingType()) {
            case self::TYPE_FIXED:

                if ($this->getFieldName() === 'part_id') {
                    return $this->language->txt('ecs_field_' . $this->getFieldName()) . ': ' . $this->participantsToString();
                }
                return $this->language->txt('ecs_field_' . $this->getFieldName()) . ': ' . $this->getMappingValue();
                
            case self::TYPE_DURATION:
                return $this->language->txt('ecs_field_' . $this->getFieldName()) . ': ' . ilDatePresentation::formatPeriod(
                    $this->getDateRangeStart(),
                    $this->getDateRangeEnd()
                );
                
            case self::TYPE_BY_TYPE:
                return $this->language->txt('type') . ': ' . $this->language->txt('obj_' . $this->getByType());
        }
        return "";
    }
    
    /**
     * get strong presentation of participants
     * @param
     * @return
     */
    public function participantsToString() : string
    {
        $part_string = "";
        $part = explode(',', $this->getMappingValue());
        $counter = 0;
        foreach ($part as $part_id) {
            if ($counter++) {
                $part_string .= ', ';
            }
            $part_string .= '"';
            
            $part_id_arr = explode('_', $part_id);
            $name = ilECSCommunityReader::getInstanceByServerId($part_id_arr[0])
                ->getParticipantNameByMid($part_id_arr[1]);
            if ($name) {
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
     */
    public function matches(array $a_matchable_content) : bool
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
    protected function matchesValue($a_value, int $a_type) : bool
    {
        switch ($a_type) {
            case self::ATTR_ARRAY:
                $values = explode(',', $a_value);
                $this->logger->info(__METHOD__ . ': Checking for value: ' . $a_value);
                $this->logger->info(__METHOD__ . ': Checking against attribute values: ' . $this->getMappingValue());
                break;
                
            case self::ATTR_INT:
                $this->logger->info(__METHOD__ . ': Checking for value: ' . $a_value);
                $this->logger->info(__METHOD__ . ': Checking against attribute values: ' . $this->getMappingValue());
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
                        if (strcasecmp($attribute_value, $value) === 0) {
                            return true;
                        }
                    }
                    break;
                    
                case self::TYPE_DURATION:
                    $tmp_date = new ilDate($a_value, IL_CAL_UNIX);
                    return ilDateTime::_after($tmp_date, $this->getDateRangeStart()) and
                        ilDateTime::_before($tmp_date, $this->getDateRangeEnd());
            }
        }
        return false;
    }
    
    /**
     * Read entries
     */
    protected function read() : bool
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
            $this->setMappingId((int) $row->mapping_id);
            $this->setDateRangeStart($row->date_range_start ? new ilDate($row->date_range_start, IL_CAL_UNIX) : null);
            $this->setDateRangeEnd($row->date_range_end ? new ilDate($row->date_range_end, IL_CAL_UNIX) : null);
            $this->setMappingType((int) $row->mapping_type);
            $this->setFieldName($row->field_name);
            $this->setContainerId((int) $row->container_id);
            
            if ($this->getMappingType() === self::TYPE_BY_TYPE) {
                $this->setByType($row->mapping_value);
            } else {
                $this->setMappingValue($row->mapping_value);
            }
        }
        return true;
    }
}
