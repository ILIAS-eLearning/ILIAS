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

		$obj_set = $ilDB->queryF('SELECT * FROM aicc_object WHERE obj_id = %s',
					array('integer'),array($this->getId()));
		while($obj_rec = $ilDB->fetchAssoc($obj_set))
		{
			$this->setTitle($obj_rec["title"]);
			$this->setType($obj_rec["c_type"]);
			$this->setALMId($obj_rec["alm_id"]);
			$this->setDescription($obj_rec["description"]);
			$this->setDeveloperId($obj_rec["developer_id"]);
			$this->setSystemId($obj_rec["system_id"]);		
		}
	}

	function create()
	{
		global $ilDB;	

		$nextId = $ilDB->nextId('aicc_object');
		
		$ilDB->insert('aicc_object', array(
			'obj_id'		=> array('integer', $nextId),
			'title'			=> array('text', $this->getTitle()),
			'c_type'		=> array('text', $this->getType()),
			'slm_id'		=> array('integer', $this->getALMId()),
			'description'	=> array('clob', $this->getDescription()),
			'developer_id'	=> array('text',$this->getDeveloperId()),
			'system_id'		=> array('integer', $this->getSystemId())
		));
	
		$this->setId($nextId);
	}

	function update()
	{
		global $ilDB;
		
		$ilDB->update('aicc_object',
			array(
				'title'			=> array('text', $this->getTitle()),
				'c_type'		=> array('text', $this->getType()),
				'slm_id'		=> array('integer', $this->getALMId()),
				'description'	=> array('clob', $this->getDescription()),
				'developer_id'	=> array('text',$this->getDeveloperId()),
				'system_id'		=> array('integer', $this->getSystemId())
			),
			array(
				'obj_id'		=> array('integer', $this->getId())
			)
		);
	}

	function delete()
	{
		global $ilDB;

		$statement = $ilDB->manipulateF('DELETE FROM aicc_object WHERE obj_id = %s',
						array('integer'),array($this->getId()));
	}

	/**
	* get instance of specialized GUI class
	*
	* static
	*/
	function &_getInstance($a_id, $a_slm_id)
	{
		global $ilDB;

		$sc_set = $ilDB->queryF('
			SELECT c_type FROM aicc_object 
			WHERE obj_id =  %s 
			AND slm_id = %s',
			array('integer', 'integer'),
			array($a_id,$a_slm_id)
		);
		
		while($sc_rec = $ilDB->fetchAssoc($sc_set))
		{
			break;
		}
		
		switch($sc_rec["c_type"])
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