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
* @package common
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
	
	function toXML()
	{
		include_once 'classes/class.ilXmlWriter.php';
		$xml_writer = new ilXmlWriter();
		
		include_once 'classes/class.ilUserDefinedFields.php';
		$udf_obj = new ilUserDefinedFields();

		foreach($udf_obj->getDefinitions() as $definition)
		{
			$xml_writer->xmlElement('UserDefinedField',
									array('Id' => $definition['il_id'],
										  'Name' => $definition['field_name']),
									$this->user_data["$definition[field_id]"]);
		}
		return $xml_writer->xmlDumpMem(false);
	}
									
			
	// Private
	function __read()
	{
		$this->user_data = array();
		$query = "SELECT * FROM usr_defined_data ".
			"WHERE usr_id = '".$this->usr_id."'";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
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