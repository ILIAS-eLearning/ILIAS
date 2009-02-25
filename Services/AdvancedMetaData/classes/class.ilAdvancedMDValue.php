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
* @ilCtrl_Calls 
* @ingroup ServicesAdvancedMetaData
*/
class ilAdvancedMDValue
{
	private static $instances = array();
	
	protected $db;
	
	private $obj_id;
	private $field_id;
	private $value;
	private $disabled = false;

	/**
	 * Singleton constructor
	 *
	 * @access private
	 * @param int field_id
	 * @param int obj_id
	 * 
	 */
	public function __construct($a_field_id,$a_obj_id = 0)
	{
	 	global $ilDB;
	 	
	 	$this->db = $ilDB;
	 	
	 	$this->obj_id = $a_obj_id;
	 	$this->field_id = $a_field_id;
	 	
	 	$this->read();
	}
	
	/**
	 * Get instance 
	 *
	 * @access public
	 * @static
	 *
	 * @param
	 */
	public static function _getInstance($a_obj_id,$a_field_id)
	{
		if(isset(self::$instances[$a_obj_id][$a_field_id]))
		{
			return self::$instances[$a_obj_id][$a_field_id];
		}
		return self::$instances[$a_obj_id][$a_field_id] = new ilAdvancedMDValue($a_field_id,$a_obj_id);
	}
	
	/**
	 * To string method
	 *
	 * @access public
	 * 
	 */
	public function __toString()
	{
	 	return $this->value;
	}
	
	/**
	 * set object id
	 *
	 * @access public
	 * @param int obj_id
	 * 
	 */
	public function setObjId($a_obj_id)
	{
	 	$this->obj_id = $a_obj_id;
	}
	
	/**
	 * append value xml
	 *
	 * @access public
	 * @param object instance of ilXmlWriter
	 * 
	 */
	public function appendXML($xml_writer)
	{
	 	include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
	 	
	 	$xml_writer->xmlElement('Value',
	 		array('id' => ilAdvancedMDFieldDefinition::_lookupImportId($this->field_id)),
	 		$this->getValue());
	}
	
	
	/**
	 * Set value
	 *
	 * @access public
	 * @param string value
	 * 
	 */
	public function setValue($a_value)
	{
	 	$this->value = $a_value;
	}
	
	/**
	 * get value
	 *
	 * @access public
	 */
	public function getValue()
	{
	 	return $this->value;
	}
	
	/**
	 * Check if value is imported and therefore disabled.
	 * This is the case for imported course links.
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function isDisabled()
	{
	 	return (bool) $this->disabled;
	}
	
	/**
	 * Toggle disabled status
	 *
	 * @access public
	 * @param bool disabled status
	 * 
	 */
	public function toggleDisabledStatus($a_status)
	{
	 	$this->disabled = (bool) $a_status;
	}
	
	/**
	 * Delete value
	 *
	 * @access public
	 * 
	 */
	public function delete()
	{
	 	global $ilDB;
	 	
	 	$query = "DELETE FROM adv_md_values ".
	 		"WHERE obj_id = ".$this->db->quote($this->obj_id ,'integer')." ".
	 		"AND field_id = ".$this->db->quote($this->field_id ,'integer');
		$res = $ilDB->manipulate($query);
	}
	
	/**
	 * Save data
	 *
	 * @access public
	 * 
	 */
	public function save()
	{
	 	global $ilDB;
	 	
	 	$this->delete();
	 	
	 	$query = "INSERT INTO adv_md_values (obj_id,field_id,value,disabled) ".
	 		"VALUES( ".
	 		$this->db->quote($this->obj_id ,'integer').", ".
	 		$this->db->quote($this->field_id ,'integer').", ".
	 		$this->db->quote($this->getValue() ,'text').", ".
	 		$ilDB->quote($this->isDisabled(),'integer')." ".
	 		")";
		$res = $ilDB->manipulate($query);
	}
	
	/**
	 * Read data
	 *
	 * @access private
	 */
	private function read()
	{
	 	global $ilDB;
	 	
	 	if(!$this->obj_id or !$this->field_id)
	 	{
	 		return;
	 	}
	 	
	 	$query = "SELECT * FROM adv_md_values ".
	 		"WHERE obj_id = ".$this->db->quote($this->obj_id ,'integer')." ".
	 		"AND field_id = ".$this->db->quote($this->field_id ,'integer')." ";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setValue($row->value);
			$this->toggleDisabledStatus((bool) $row->disabled);
		}
		return true;	
	}
}
?>