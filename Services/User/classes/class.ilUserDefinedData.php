<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilUserDefinedData
*
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
* @ingroup ServicesUser
*/
class ilUserDefinedData
{
	var $db = null;
	var $user_data = array();
	var $usr_id = null;

	function ilUserDefinedData($a_usr_id)
	{
		global $ilDB;

		$this->db =& $ilDB;
		$this->usr_id = $a_usr_id;

		$this->__read();
	}

	function getUserId()
	{
		return $this->usr_id;
	}

	function set($a_field,$a_value)
	{
		$this->user_data[$a_field] = $a_value;
	}
	function get($a_field)
	{
		return isset($this->user_data[$a_field]) ? $this->user_data[$a_field] : '';
	}

	/**
	 * Get all fields
	 */
	function getAll()
	{
		return $this->user_data;
	}
	
	/**
	 * Update data 
	 */
	function update()
	{
		global $ilDB;
		
		include_once './Services/User/classes/class.ilUserDefinedFields.php';
		$udf_obj =& ilUserDefinedFields::_getInstance();

		$sql = '';
		$field_def = array();
		foreach($udf_obj->getDefinitions() as $definition)
		{
//			$field_def['f_'.$definition['field_id']] = array('text',$this->get($definition['field_id']));
			
//			$sql .= ("`".(int) $definition['field_id']."` = ".$this->db->quote($this->get($definition['field_id'])).", ");

			if ($definition["field_type"] == UDF_TYPE_WYSIWYG)
			{
				$ilDB->replace("udf_clob", array(
						"usr_id" => array("integer", $this->getUserId()),
						"field_id" => array("integer", $definition['field_id'])),
						array(
						"value" => array("text", $this->get("f_".$definition['field_id']))
						));
			}
			else
			{
				$ilDB->replace("udf_text", array(
						"usr_id" => array("integer", $this->getUserId()),
						"field_id" => array("integer", $definition['field_id'])),
						array(
						"value" => array("text", $this->get("f_".$definition['field_id']))
						));
			}
		}
/*		if(!$field_def)
		{
			return true;
		}

		$query = "SELECT usr_id FROM udf_data WHERE usr_id = ".$ilDB->quote($this->getUserId(),'integer');
		$res = $ilDB->query($query);
		
		if($res->numRows())
		{
			$ilDB->update('udf_data',$field_def,array('usr_id' => array('integer',$this->getUserId())));
		}
		else
		{
			$field_def['usr_id'] = array('integer',$this->getUserId());
			$ilDB->insert('udf_data',$field_def);
		}*/
		return true;
	}
	
	/**
	 * Delete data of user
	 *
	 * @param	int		user id
	 */
	static function deleteEntriesOfUser($a_user_id)
	{
		global $ilDB;

		$ilDB->manipulate("DELETE FROM udf_text WHERE "
			." usr_id = ".$ilDB->quote($a_user_id, "integer")
			);
		$ilDB->manipulate("DELETE FROM udf_clob WHERE "
			." usr_id = ".$ilDB->quote($a_user_id, "integer")
			);
	}

	/**
	 * Delete data of particular field
	 *
	 * @param	int		field id
	 */
	static function deleteEntriesOfField($a_field_id)
	{
		global $ilDB;

		$ilDB->manipulate("DELETE FROM udf_text WHERE "
			." field_id = ".$ilDB->quote($a_field_id, "integer")
			);
		$ilDB->manipulate("DELETE FROM udf_clob WHERE "
			." field_id = ".$ilDB->quote($a_field_id, "integer")
			);
	}

	/**
	 * Delete data of particular value of a (selection) field
	 *
	 * @param	int			field id
	 * * @param	string		value
	 */
	static function deleteFieldValue($a_field_id, $a_value)
	{
		global $ilDB;

		$ilDB->manipulate("UPDATE udf_text SET value = ".$ilDB->quote("", "text")." WHERE "
			." field_id = ".$ilDB->quote($a_field_id, "integer")
			." AND value = ".$ilDB->quote($a_value, "text")
			);
	}

	function toXML()
	{
		include_once 'classes/class.ilXmlWriter.php';
		$xml_writer = new ilXmlWriter();

		$this->addToXML ($xml_writer);

		return $xml_writer->xmlDumpMem(false);
	}

	/**
	*	add user defined field data to xml (using usr dtd)
	*	@param*XmlWriter $xml_writer
	*/
	function addToXML($xml_writer)
	{
		include_once './Services/User/classes/class.ilUserDefinedFields.php';
		$udf_obj =& ilUserDefinedFields::_getInstance();

		foreach($udf_obj->getDefinitions() as $definition)
		{
			if ($definition["export"] != FALSE)
				$xml_writer->xmlElement('UserDefinedField',
									array('Id' => $definition['il_id'],
										  'Name' => $definition['field_name']),
									$this->user_data["$definition[field_id]"]);
		}
	}

	// Private
	function __read()
	{
		$this->user_data = array();
		$query = "SELECT * FROM udf_text ".
			"WHERE usr_id = ".$this->db->quote($this->usr_id,'integer')."";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->user_data["f_".$row["field_id"]] = $row["value"];
		}
		$query = "SELECT * FROM udf_clob ".
			"WHERE usr_id = ".$this->db->quote($this->usr_id,'integer')."";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->user_data["f_".$row["field_id"]] = $row["value"];
		}
	}

}
?>