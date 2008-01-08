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
* Parent object for all AICC objects, that are stored in table aicc_object
*
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilAICCObject
{
	var $id;
	var $title;
	var $objType;
	var $alm_id;
	var $description;
	var $developer_id;
	var $system_id;


	/**
	* Constructor
	*
	* @param	int		$a_id		Object ID
	* @access	public
	*/
	function ilAICCObject($a_id = 0)
	{
		global $ilias;

		$this->ilias =& $ilias;
		$this->id = $a_id;
		if ($a_id > 0)
		{
			$this->read();
		}
	}

	function getId()
	{
		return $this->id;
	}

	function setId($a_id)
	{
		$this->id = $a_id;
	}

	function getType()
	{
		return $this->objType;
	}

	function setType($a_objType)
	{
		$this->objType = $a_objType;
	}

	function getTitle()
	{
		return $this->title;
	}

	function setTitle($a_title)
	{
		$this->title = $a_title;
	}
	
	function getDescription()
	{
		return $this->description;
	}

	function setDescription($a_description)
	{
		$this->description = $a_description;
	}
	
	function getDeveloperId()
	{
		return $this->developer_id;
	}

	function setDeveloperId($a_developer_id)
	{
		$this->developer_id = $a_developer_id;
	}	
	
	function getSystemId()
	{
		return $this->system_id;
	}

	function setSystemId($a_system_id)
	{
		$this->system_id = $a_system_id;
	}		

	function getALMId()
	{
		return $this->alm_id;
	}

	function setALMId($a_alm_id)
	{
		$this->alm_id = $a_alm_id;
	}
	
	function prepForStore($string) {
		if (!get_magic_quotes_runtime()) {
    	$string = addslashes($string);
    }
    return $string;
	}

	function read()
	{
		global $ilDB;
		
		$q = "SELECT * FROM aicc_object WHERE obj_id = ".$ilDB->quote($this->getId());

		$obj_set = $this->ilias->db->query($q);
		$obj_rec = $obj_set->fetchRow(MDB2_FETCHMODE_ASSOC);
		$this->setTitle($obj_rec["title"]);
		$this->setType($obj_rec["type"]);
		$this->setALMId($obj_rec["alm_id"]);
		$this->setDescription($obj_rec["description"]);
		$this->setDeveloperId($obj_rec["developer_id"]);
		$this->setSystemId($obj_rec["system_id"]);
	}

	function create()
	{
		global $ilDB;
		
		$q = "INSERT INTO aicc_object (title, type, slm_id, description, developer_id, system_id) VALUES (";
		$q.=$ilDB->quote($this->getTitle()).", ";
		$q.=$ilDB->quote($this->getType()).", ";
		$q.=$ilDB->quote($this->getALMId()).", ";
		$q.=$ilDB->quote($this->getDescription()).", ";
		$q.=$ilDB->quote($this->getDeveloperId()).", ";
		$q.=$ilDB->quote($this->getSystemId()).") ";
		$this->ilias->db->query($q);
		$this->setId($this->ilias->db->getLastInsertId());
	}

	function update()
	{
		global $ilDB;
		
		$q = "UPDATE aicc_object SET ";
		$q.="title = ".$ilDB->quote($this->getTitle()).", ";
		$q.="type = ".$ilDB->quote($this->getType()).", ";
		$q.="slm_id = ".$ilDB->quote($this->getALMId()).", ";
		$q.="description = ".$ilDB->quote($this->getDescription()).", ";
		$q.="developer_id = ".$ilDB->quote($this->getDeveloperId()).", ";
		$q.="system_id = ".$ilDB->quote($this->getSystemId())." ";
		$q.="WHERE obj_id = ".$ilDB->quote($this->getId());
		
		$this->ilias->db->query($q);
	}

	function delete()
	{
		global $ilDB;

		$q = "DELETE FROM aicc_object WHERE obj_id =".$ilDB->quote($this->getId());
		$ilDB->query($q);
	}

	/**
	* get instance of specialized GUI class
	*
	* static
	*/
	function &_getInstance($a_id, $a_slm_id)
	{
		global $ilDB;

		$sc_set = $ilDB->query("SELECT type FROM aicc_object WHERE obj_id =".$ilDB->quote($a_id).
			" AND slm_id = ".$ilDB->quote($a_slm_id));
		$sc_rec = $sc_set->fetchRow(MDB2_FETCHMODE_ASSOC);

		switch($sc_rec["type"])
		{
			case "sbl":					// Block
				include_once("./Modules/ScormAicc/classes/AICC/class.ilAICCBlock.php");
				$block =& new ilAICCBlock($a_id);
				return $block;
				break;

			case "sau":					// assignable unit
				include_once("./Modules/ScormAicc/classes/AICC/class.ilAICCUnit.php");
				$sau =& new ilAICCUnit($a_id);
				return $sau;
				break;
				
			case "shd":					// course
				include_once("./Modules/ScormAicc/classes/AICC/class.ilAICCCourse.php");
				$shd =& new ilAICCCourse($a_id);
				return $shd;
				break;
		}
		
	}

}
?>