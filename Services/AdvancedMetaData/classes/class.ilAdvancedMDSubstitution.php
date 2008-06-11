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
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesAdvancedMetaData 
*/
class ilAdvancedMDSubstitution
{
	private static $instances = null; 

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
	 		$sorted[] = $field_id;
	 		$key = array_search($field_id,$a_definitions);
 			unset($a_definitions[$key]);
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
	public function getParsedSubstitutions($a_ref_id,$a_obj_id)
	{
  		if(!count($this->getSubstitutions()))
  		{
  			return array();
  		}
  		
  		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php');
  		$values = ilAdvancedMDValues::_getValuesByObjId($a_obj_id);
		  		
  		$counter = 0;
  		foreach($this->getSubstitutions() as $field_id)
  		{
			if(!isset($this->active_fields[$field_id]))
			{
				continue;
			}
			if(!isset($values[$field_id]) or !$values[$field_id])
			{
				if($this->hasNewline($field_id) and $counter)
				{
					$substituted[$counter-1]['newline'] = true;
				}
				continue;
			}
			
			if(in_array($field_id,$this->date_fields))
			{
				$value = ilFormat::formatUnixTime((int) $values[$field_id]);
			}
			elseif(in_array($field_id,$this->datetime_fields))
			{
				$value = ilFormat::formatUnixTime((int) $values[$field_id],true);
			}
			else
			{
				$value = $values[$field_id];
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
  		/*
  		$string = $this->getSubstitutionString();
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php');
		foreach(ilAdvancedMDValues::_getValuesByObjId($a_obj_id) as $field_id => $value)
		{
			if(!in_array($field_id,$this->active_fields))
			{
				continue;
			}
			
			if(in_array($field_id,$this->date_fields) and $value)
			{
				$value = ilFormat::formatUnixTime((int) $value);
			}
			
			if($value)
			{
				// Substitute variables
				$string = str_replace('[F_'.$field_id.']',$value,$string);
				// Delete block varaibles
				$string = preg_replace('/\[\/?IF_F_'.$field_id.'\]/U','',$string);
			}
		}
		// Replace fixed variables
		$string = str_replace('[OBJ_ID]',$a_obj_id,$string);
		
		// Delete all other blocks
		$string = preg_replace('/\[IF_F_\d+\].*\[\/IF_F_\d+\]/U','',$string);
		return $string;
		*/
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
	 	$counter = 0;
	 	$substitutions = array();
	
	 	foreach($this->substitutions as $field_id)
	 	{
	 		$substitutions[$counter]['field_id'] = $field_id;
	 		$substitutions[$counter]['bold'] = $this->isBold($field_id);
	 		$substitutions[$counter]['newline'] = $this->hasNewline($field_id);
	 		$counter++;
	 	}
	 	
			
	 	$query = "REPLACE INTO adv_md_substitutions ".
	 		"SET obj_type = ".$this->db->quote($this->type).", ".
	 		"substitution = ".$this->db->quote(serialize($substitutions)).", ".
	 		"hide_description = ".$this->db->quote(!$this->isDescriptionEnabled()).', '.
	 		"hide_field_names = ".$this->db->quote(!$this->enabledFieldNames());
			
			
	 	$res = $this->db->query($query);
	}
	
	/**
	 * Read db entries
	 *
	 * @access private
	 * 
	 */
	private function read()
	{
	 	include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
	 	$this->date_fields = ilAdvancedMDFieldDefinition::_lookupDateFields();
	 	$this->datetime_fields = ilAdvancedMDFieldDefinition::_lookupDatetimeFields();
	 	
	 	// Check active status
	 	$query = "SELECT active,field_id,amfd.title FROM adv_md_record AS amr ".
	 		"JOIN adv_md_record_objs AS amro ON amr.record_id = amro.record_id ".
	 		"JOIN adv_md_field_definition AS amfd ON amr.record_id = amfd.record_id ".
	 		"WHERE active = 1 ".
	 		"AND obj_type = ".$this->db->quote($this->type)." ";
	 	$res = $this->db->query($query);
	 	$this->active = $res->numRows() ? true : false;
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$this->active_fields[$row->field_id] = $row->title;
	 	}
			
	 	$query = "SELECT * FROM adv_md_substitutions ".
	 		"WHERE obj_type = ".$this->db->quote($this->type)." ";
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
	}
}
?>