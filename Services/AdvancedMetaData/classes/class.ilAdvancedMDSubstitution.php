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
	protected $substitution;
	protected $enabled_desc = true;
	protected $active = false;
	protected $date_fields = array();
	
	
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
	 * Substitute
	 *
	 * @access public
	 * @param int ref_id
	 * @param int obj_id
	 * @param string description
	 * 
	 */
	public function substitute($a_ref_id,$a_obj_id)
	{
  		$string = $this->getSubstitutionString();
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php');
		foreach(ilAdvancedMDValues::_getValuesByObjId($a_obj_id) as $field_id => $value)
		{
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
	}
	
	/**
	 * get substitution string
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getSubstitutionString()
	{
	 	return $this->substitution;
	}
	
	/**
	 * Set substitution
	 *
	 * @access public
	 * @param string substitution
	 * 
	 */
	public function setSubstitutionString($a_substitution)
	{
	 	$this->substitution = $a_substitution;
	}
	
	/**
	 * update
	 *
	 * @access public
	 * 
	 */
	public function update()
	{
	 	$query = "REPLACE INTO adv_md_substitutions ".
	 		"SET obj_type = ".$this->db->quote($this->type).", ".
	 		"substitution = ".$this->db->quote($this->getSubstitutionString()).", ".
	 		"hide_description = ".$this->db->quote(!$this->isDescriptionEnabled());
			
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
	 	
	 	// Check active status
	 	$query = "SELECT active FROM adv_md_record AS amr ".
	 		"JOIN adv_md_record_objs AS amro ON amr.record_id = amro.record_id ".
	 		"WHERE active = 1 ".
	 		"AND obj_type = ".$this->db->quote($this->type)." ";
	 	$res = $this->db->query($query);
	 	$this->active = $res->numRows() ? true : false;
			
	 	$query = "SELECT * FROM adv_md_substitutions ".
	 		"WHERE obj_type = ".$this->db->quote($this->type)." ";
	 	$res = $this->db->query($query);
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$this->substitution = $row->substitution;
	 		$this->enabled_desc = !$row->hide_description;
	 	}
	}
}
?>