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

/** 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ServicesAdvancedMetaData 
*/
class ilAdvancedMDSubstitution
{
	private static $instances = null; 
	private static $mappings = null;

	protected $db;
	
	protected $type;
	protected $substitutions;
	protected $bold = array();
	protected $newline = array();
	
	protected $enabled_desc = true;
	protected $enabled_field_names = true;
	protected $active = false;
	protected $date_fields = array();
	protected $datetime_fields = array();
	protected $active_fields = array();
	
	
	/*
	 * Singleton class
	 * Use _getInstance
	 * @access private
	 * @param
	 */
	private function __construct($a_type)
	{
		global $ilDB;
		
		$this->db = $ilDB;
		$this->type = $a_type;
		
		$this->initECSMappings();
		$this->read();
	}
	
	/**
	 * Singleton: use this method to get an instance
	 * 
	 * @param string ilias object type (3 or 4 characters)
	 * @access public
	 * @static
	 *
	 */
	public static function _getInstanceByObjectType($a_type)
	{
		if(isset(self::$instances[$a_type]))
		{
			return self::$instances[$a_type];
		}
		return self::$instances[$a_type] = new ilAdvancedMDSubstitution($a_type);
	}
	
	/**
	 * Sort definitions
	 *
	 * @access public
	 * @param array int field_id
	 * 
	 */
	public function sortDefinitions($a_definitions)
	{
	 	$sorted = array();
	 	foreach($this->substitutions as $field_id)
	 	{
			if(isset($a_definitions[$field_id]))
			{
				$sorted[$field_id] = $a_definitions[$field_id];	 		
				unset($a_definitions[$field_id]);
			}
	 	}
	 	return array_merge($sorted,$a_definitions);
	}
	
	/**
	 * Is substitution active
	 *
	 * @access public
	 * 
	 */
	public function isActive()
	{
	 	return $this->active;
	}
	
	/**
	 * Is description enabled
	 *
	 * @access public
	 * 
	 */
	public function isDescriptionEnabled()
	{
	 	return (bool) $this->enabled_desc;
	}
	
	/**
	 * Enable description presentation
	 *
	 * @access public
	 * @param bool status description enabled
	 * 
	 */
	public function enableDescription($a_status)
	{
	 	$this->enabled_desc = $a_status;
	}
	
	/**
	 * is field name enabled
	 *
	 * @access public
	 * 
	 */
	public function enabledFieldNames()
	{
	 	return (bool) $this->enabled_field_names;
	}
	
	/**
	 * enable field names
	 *
	 * @access public
	 * @param bool enable/disable status
	 * 
	 */
	public function enableFieldNames($a_status)
	{
	 	$this->enabled_field_names = $a_status;
	}
	
	/**
	 * Substitute
	 *
	 * @access public
	 * @param int ref_id
	 * @param int obj_id
	 * @param string description
	 * 
	 */
	public function getParsedSubstitutions($a_ref_id, $a_obj_id)
	{		
  		if(!count($this->getSubstitutions()))
  		{
  			return array();
  		}
  		
  		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php');
  		$values_records = ilAdvancedMDValues::preloadedRead($this->type, $a_obj_id);
		 		
  		$counter = 0;
  		foreach($this->getSubstitutions() as $field_id)
  		{
			if(!isset($this->active_fields[$field_id]))
			{
				continue;
			}
			
			$value = $this->parseValue($field_id, $values_records);		
			
			if($value === null)
			{
				if($this->hasNewline($field_id) and $counter)
				{
					$substituted[$counter-1]['newline'] = true;
				}
				continue;
			}
							
			$substituted[$counter]['name'] = $this->active_fields[$field_id];
			$substituted[$counter]['value'] = $value;
			$substituted[$counter]['bold'] = $this->isBold($field_id);
			if($this->hasNewline($field_id))
			{
				$substituted[$counter]['newline'] = true;
			}
			else
			{
				$substituted[$counter]['newline'] = false;
			}
			$substituted[$counter]['show_field'] = $this->enabledFieldNames();
			$counter++;
  		}
  		
  		return $substituted ? $substituted : array();
	}
	
	/**
	 * special handling for date(time) values 
	 * and ECS dates
	 * 
	 * @param int $a_field_id field ID
	 * @param array $a_values_records values
	 * @access public
	 * @return string parsed value
	 * 
	 */
	private function parseValue($a_field_id,$a_values_records)
	{
		global $ilUser;
		
		if($this->type == 'crs' or $this->type == 'rcrs')
		{
			// Special handling for ECS fields
			// @FIXME
			/*
			if($a_field_id == self::$mappings->getMappingByECSName('begin') and
				$end = self::$mappings->getMappingByECSName('end'))
			{
				// Parse a duration
				$start = in_array($a_field_id,$this->date_fields) ?
					new ilDate($a_values[$a_field_id],IL_CAL_UNIX) :
					new ilDateTime($a_values[$a_field_id],IL_CAL_UNIX);
				$end = in_array($end,$this->date_fields) ?
					new ilDate($a_values[$end],IL_CAL_UNIX) :
					new ilDateTime($a_values[$end],IL_CAL_UNIX);
				
				include_once('./Services/Calendar/classes/class.ilCalendarUtil.php');
				$weekday = ilCalendarUtil::_numericDayToString($start->get(IL_CAL_FKT_DATE,'w',$ilUser->getTimeZone()),false);
				
				ilDatePresentation::setUseRelativeDates(false);
				$value = ilDatePresentation::formatPeriod($start,$end);
				ilDatePresentation::setUseRelativeDates(true);
				return $weekday.', '.$value;
			}
			*/
		}
		
		foreach($a_values_records as $a_values)
		{
			if($a_values->getADTGroup()->hasElement($a_field_id))				
			{				
				$element = $a_values->getADTGroup()->getElement($a_field_id);
				if(!$element->isNull())
				{				
					return ilADTFactory::getInstance()->getPresentationBridgeForInstance($element)->getList();
				}
			}
		}		
	}
	
	
	/**
	 * set substitutions
	 *
	 * @access public
	 * @param array array of field definitions
	 * 
	 */
	public function resetSubstitutions()
	{
	 	$this->substitutions = array();
	 	$this->bold = array();
	 	$this->newline = array();
	}
	
	/**
	 * append field to substitutions
	 *
	 * @access public
	 * @param int field id
	 * 
	 */
	public function appendSubstitution($a_field_id,$a_bold = false,$a_newline = false)
	{
	 	$this->substitutions[] = $a_field_id;
	 	if($a_bold)
	 	{
	 		$this->bold[] = $a_field_id;
	 	}
	 	if($a_newline)
	 	{
		 	$this->newline[] = $a_field_id;
	 	} 
	}
	
	/**
	 * get substitution string
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getSubstitutions()
	{
	 	return $this->substitutions ? $this->substitutions : array();
	}
	
	/**
	 * is substituted
	 *
	 * @access public
	 * @param int field_id
	 * 
	 */
	public function isSubstituted($a_field_id)
	{
	 	return in_array($a_field_id,$this->getSubstitutions());
	}
	
	/**
	 * is bold
	 *
	 * @access public
	 * @param int field_id
	 * 
	 */
	public function isBold($a_field_id)
	{
	 	#var_dump("<pre>",$this->bold,$a_field_id,"</pre>");
		
	 	return in_array($a_field_id,$this->bold);
	}
	
	/**
	 * has newline
	 *
	 * @access public
	 * @param int field_id
	 * 
	 */
	public function hasNewline($a_field_id)
	{
	 	return in_array($a_field_id,$this->newline);
	}
	/**
	 * update
	 *
	 * @access public
	 * 
	 */
	public function update()
	{
	 	global $ilDB;
	 	
	 	$counter = 0;
	 	$substitutions = array();
	
	 	foreach($this->substitutions as $field_id)
	 	{
	 		$substitutions[$counter]['field_id'] = $field_id;
	 		$substitutions[$counter]['bold'] = $this->isBold($field_id);
	 		$substitutions[$counter]['newline'] = $this->hasNewline($field_id);
	 		$counter++;
	 	}
	 	
	 	$query = "DELETE FROM adv_md_substitutions WHERE obj_type = ".$ilDB->quote($this->type,'text');
	 	$res = $ilDB->manipulate($query);
			
	 	
	 	$values = array(
	 		'obj_type'			=> array('text',$this->type),
	 		'substitution'		=> array('clob',serialize($substitutions)),
	 		'hide_description'	=> array('integer',!$this->isDescriptionEnabled()),
	 		'hide_field_names'	=> array('integer',!$this->enabledFieldNames())
	 		);
	 	$ilDB->insert('adv_md_substitutions',$values);
	}
	
	/**
	 * Read db entries
	 *
	 * @access private
	 * 
	 */
	private function read()
	{
	 	global $ilDB;
	 		 
	 	// Check active status
	 	$query = "SELECT active,field_id,amfd.title FROM adv_md_record amr ".
	 		"JOIN adv_md_record_objs amro ON amr.record_id = amro.record_id ".
	 		"JOIN adv_mdf_definition amfd ON amr.record_id = amfd.record_id ".
	 		"WHERE active = 1 ".
	 		"AND obj_type = ".$this->db->quote($this->type ,'text')." ";
	 	$res = $this->db->query($query);
	 	$this->active = $res->numRows() ? true : false;
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$this->active_fields[$row->field_id] = $row->title;
	 	}
			
	 	$query = "SELECT * FROM adv_md_substitutions ".
	 		"WHERE obj_type = ".$this->db->quote($this->type ,'text')." ";
	 	$res = $this->db->query($query);
	 	$this->substitutions = array();
	 	$this->bold = array();
	 	$this->newline = array();
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$tmp_substitutions = unserialize($row->substitution);
	 		if(is_array($tmp_substitutions))
	 		{
	 			foreach($tmp_substitutions as $substitution)
	 			{
	 				if($substitution['field_id'])
	 				{
	 					$this->substitutions[] = $substitution['field_id'];
	 				}
	 				if($substitution['bold'])
	 				{
	 					$this->bold[] = $substitution['field_id'];
	 				}
	 				if($substitution['newline'])
	 				{
	 					$this->newline[] = $substitution['field_id'];
	 				}
	 				
	 			}
	 		}
	 		$this->enabled_desc = !$row->hide_description;
	 		$this->enabled_field_names = !$row->hide_field_names;
	 	}

	 	if($this->type == 'crs' or $this->type == 'rcrs')
	 	{
	 		// Handle ECS substitutions
			/*
	 		if($begin = self::$mappings->getMappingByECSName('begin') and
	 			$end = self::$mappings->getMappingByECSName('end'))
	 		{
	 			// Show something like 'Monday, 30.12.2008 9:00 - 12:00'
	 			unset($this->active_fields[$end]);
	 		}
			*/
	 	}
	}
	
	/**
	 * init ECS mappings
	 *
	 * @access private
	 * 
	 */
	private function initECSMappings()
	{
		return true;

		include_once('./Services/WebServices/ECS/classes/class.ilECSDataMappingSettings.php');
		
		if(isset(self::$mappings) and is_object(self::$mappings))
		{
			return true;
		}
		self::$mappings = ilECSDataMappingSettings::_getInstance();
		return true;
	}
}
?>