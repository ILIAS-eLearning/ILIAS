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
* Parent object for all SCORM objects, that are stored in table scorm_object
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORMObject
{
	var $id;
	var $title;
	var $type;
	var $slm_id;


	/**
	* Constructor
	*
	* @param	int		$a_id		Object ID
	* @access	public
	*/
	function __construct($a_id = 0)
	{
		global $ilias;

		$this->ilias = $ilias;
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
		return $this->type;
	}

	function setType($a_type)
	{
		$this->type = $a_type;
	}

	function getTitle()
	{
		return $this->title;
	}

	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	function getSLMId()
	{
		return $this->slm_id;
	}

	function setSLMId($a_slm_id)
	{
		$this->slm_id = $a_slm_id;
	}

	function read()
	{
		global $ilDB;
		
		$obj_set = $ilDB->queryF('SELECT * FROM scorm_object WHERE obj_id = %s', 
			array('integer'),array($this->getId()));
		$obj_rec = $ilDB->fetchAssoc($obj_set);
		$this->setTitle($obj_rec["title"]);
		$this->setType($obj_rec["c_type"]);
		$this->setSLMId($obj_rec["slm_id"]);
	}
	
	/**
	* Count number of presentable SCOs/Assets of SCORM learning module. 
	*/
	static function _lookupPresentableItems($a_slm_id)
	{
		global $ilDB;
		
		$set = $ilDB->queryF("
			SELECT sit.obj_id id 
			FROM scorm_object sob, sc_item sit
			WHERE sob.slm_id = %s
			AND sob.obj_id = sit.obj_id
			AND sit.identifierref IS NOT NULL",
			array('integer'),
			array($a_slm_id)
		);
		$items = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$items[] = $rec["id"];
		}
		
		return $items;
	}

    /**
     * Create database record for SCORM object.
     *
     */
    function create()
    {
        global $ilDB;
      
       $nextId = $ilDB->nextId('scorm_object');
       $this->setId($nextId);
         
        $ilDB->manipulateF('
        INSERT INTO scorm_object (obj_id,title, c_type, slm_id) 
        VALUES (%s,%s,%s,%s) ',
        array('integer','text','text','integer'),
        array($nextId, $this->getTitle(),$this->getType(), $this->getSLMId()));       
        
        
    }

    /**
     * Updates database record for SCORM object.
     *
     */
    function update()
    {
        global $ilDB;
        
        $ilDB->manipulateF('
        UPDATE scorm_object 
        SET title = %s,
        	c_type = %s,
        	slm_id = %s
        WHERE obj_id = %s',
        array('text','text','integer','integer'),
        array($this->getTitle(),$this->getType(), $this->getSLMId(),$this->getId())
        );      
        
    } 

	function delete()
	{
		global $ilDB;
		$ilDB->manipulateF('DELETE FROM scorm_object WHERE obj_id = %s',
		array('integer'),array($this->getId()) );
 
	}

	/**
	* get instance of specialized GUI class
	*
	* static
	*/
	static function &_getInstance($a_id, $a_slm_id)
	{
		global $ilDB;

		$sc_set = $ilDB->queryF('
			SELECT c_type FROM scorm_object 
			WHERE obj_id = %s
			AND slm_id = %s',
			array('integer','integer'),
			array($a_id, $a_slm_id)
		);
		$sc_rec = $ilDB->fetchAssoc($sc_set);
			
		switch($sc_rec["c_type"])
		{
			case "sit":					// item
				include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMItem.php");
				$item = new ilSCORMItem($a_id);
				return $item;
				break;

			case "sos":					// organizations
				include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMOrganizations.php");
				$sos = new ilSCORMOrganizations($a_id);
				return $sos;
				break;

			case "sor":					// organization
				include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMOrganization.php");
				$sor = new ilSCORMOrganization($a_id);
				return $sor;
				break;

			case "sma":					// manifest
				include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMManifest.php");
				$sma = new ilSCORMManifest($a_id);
				return $sma;
				break;

			case "srs":					// resources
				include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMResources.php");
				$srs = new ilSCORMResources($a_id);
				return $srs;
				break;

			case "sre":					// resource
				include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMResource.php");
				$sre = new ilSCORMResource($a_id);
				return $sre;
				break;
		}
	}

}
?>
