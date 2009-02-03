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
	const TYPE_FIXED = 0;
	const TYPE_DURATION = 1;
	
	const ERR_MISSING_VALUE = 'ecs_err_missing_value';
	const ERR_INVALID_DATES = 'ecs_err_invalid_dates';
	const ERR_INVALID_TYPE = 'ecs_err_invalid_type';
	
	protected $db;
	
	private $mapping_id;
	private $container_id;
	private $field_name;
	private $mapping_type;
	private $mapping_value;
	private $range_dt_start;
	private $range_dt_end;
	
	/**
	 * Constructor 
	 * @param int mapping id
	 */
	public function __construct($a_mapping_id = 0)
	{
		global $ilDB;
		
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
		return $this->range_dt_start ? $this->range_dt_start : new ilDate(time(),IL_CAL_UNIX);
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
		if($this->range_dt_end)
		{
			return $this->range_dt_end;
		}
		$this->range_dt_end = $this->getDateRangeStart();
		$this->range_dt_end->increment(IL_CAL_MONTH,6);
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
	 * delete rule
	 * @return
	 */
	public function delete()
	{
		$sta = $this->db->prepareManip('DELETE FROM ecs_container_mapping WHERE mapping_id = ?',array('integer'));
		$par = array($this->getMappingId());
		$res = $this->db->execute($sta,$par);
	}
	
	/**
	 * update
	 * @return
	 */
	public function update()
	{
		$sta = $this->db->prepareManip(
			'UPDATE ecs_container_mapping SET '.
			'container_id = ?, '.
			'field_name = ?, '.
			'mapping_type = ?, '.
			'mapping_value = ?, '.
			'date_range_start = ?,'.
			'date_range_end = ? '.
			'WHERE mapping_id = ?',
			array('integer','clob','integer','clob','integer','integer','integer'));
		$par = array(
			$this->getContainerId(),
			$this->getFieldName(),
			$this->getMappingType(),
			$this->getMappingValue(),
			$this->getDateRangeStart()->get(IL_CAL_UNIX),
			$this->getDateRangeEnd()->get(IL_CAL_UNIX),
			$this->getMappingId());
		$this->db->execute($sta,$par);
	}
	
	/**
	 * save 
	 * @return
	 */
	public function save()
	{
		$sta = $this->db->prepareManip(
			'INSERT INTO ecs_container_mapping  '.
			'(container_id,field_name,mapping_type,mapping_value,date_range_start,date_range_end) '.
			'VALUES(?,?,?,?,?,?) ',
			array('integer','clob','integer','clob','integer','integer'));
		$par = array(
			$this->getContainerId(),
			$this->getFieldName(),
			$this->getMappingType(),
			$this->getMappingValue(),
			$this->getDateRangeStart()->get(IL_CAL_UNIX),
			$this->getDateRangeEnd()->get(IL_CAL_UNIX));
		$this->db->execute($sta,$par);
		 
	}
	
	/**
	 * validate rule 
	 * @return
	 */
	public function validate()
	{
		if(ilObject::_lookupType(ilObject::_lookupObjId($this->getContainerId())) != 'cat')
		{
			return self::ERR_INVALID_TYPE;
		}
		if(!ilDateTime::_after($this->getDateRangeEnd(),$this->getDateRangeStart(),IL_CAL_DAY))
		{
			return self::ERR_INVALID_DATES;
		}
		if($this->getMappingType() == self::TYPE_FIXED and !$this->getMappingValue())
		{
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
		global $lng;
		
		switch($this->getMappingType())
		{
			case self::TYPE_FIXED:
				return $lng->txt('ecs_field_'.$this->getFieldName()).': '.$this->getMappingValue();
				
			case self::TYPE_DURATION:
				include_once './Services/Calendar/classes/class.ilDatePresentation.php';
				return $lng->txt('ecs_field_'.$this->getFieldName()).': '.ilDatePresentation::formatPeriod(
					$this->getDateRangeStart(),
					$this->getDateRangeEnd());
		}	 
	}
	
	/**
	 * Read entries 
	 * @return
	 */
	protected function read()
	{
		if(!$this->getMappingId())
		{
			return false;
		}
		$sta = $this->db->prepare('SELECT * FROM ecs_container_mapping WHERE mapping_id = ?',array('integer'));
		$res = $this->db->execute($sta,array($this->getMappingId()));
		while($row = $this->db->fetchObject($res))
		{
			$this->setMappingId($row->mapping_id);
			$this->setDateRangeStart($row->date_range_start ? new ilDate($row->date_range_start,IL_CAL_UNIX) : null);
			$this->setDateRangeEnd($row->date_range_end ? new ilDate($row->date_range_end,IL_CAL_UNIX) : null);
			$this->setMappingType($row->mapping_type);
			$this->setMappingValue($row->mapping_value);
			$this->setFieldName($row->field_name);
			$this->setContainerId($row->container_id);
		}
		return true;
	}
}
?>
