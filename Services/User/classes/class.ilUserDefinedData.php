<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* Class ilUserDefinedData
*
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
*
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

	function update()
	{
		include_once './Services/User/classes/class.ilUserDefinedFields.php';
		$udf_obj =& ilUserDefinedFields::_getInstance();

		$sql = '';

		foreach($udf_obj->getDefinitions() as $definition)
		{
			$sql .= ("`".(int) $definition['field_id']."` = ".$this->db->quote($this->get($definition['field_id'])).", ");
		}

		$query = "REPLACE INTO usr_defined_data ".
			"SET ".$sql." ".
			"usr_id = '".$this->getUserId()."'";
		$this->db->query($query);
		return true;
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
		$query = "SELECT * FROM usr_defined_data ".
			"WHERE usr_id = ".$this->db->quote($this->usr_id)."";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			foreach($row as $field => $data)
			{
				if($field != 'usr_id')
				{
					$this->user_data[$field] = $data;
				}
			}
		}
	}

}
?>