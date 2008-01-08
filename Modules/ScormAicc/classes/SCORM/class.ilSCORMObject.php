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
	function ilSCORMObject($a_id = 0)
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
		
		$q = "SELECT * FROM scorm_object WHERE obj_id = ".$ilDB->quote($this->getId());

		$obj_set = $this->ilias->db->query($q);
		$obj_rec = $obj_set->fetchRow(MDB2_FETCHMODE_ASSOC);
		$this->setTitle($obj_rec["title"]);
		$this->setType($obj_rec["type"]);
		$this->setSLMId($obj_rec["slm_id"]);
	}
	
	/**
	* Count number of presentable SCOs/Assets of SCORM learning module. 
	*/
	function _lookupPresentableItems($a_slm_id)
	{
		global $ilDB;
		
		$q = "SELECT sit.obj_id as id FROM scorm_object as sob, sc_item as sit".
			" WHERE sob.slm_id = ".$ilDB->quote($a_slm_id).
			" AND sob.obj_id = sit.obj_id ".
			" AND sit.identifierref <> ''";
		
		$set = $ilDB->query($q);
		$items = array();
		while ($rec = $set->fetchRow(MDB2_FETCHMODE_ASSOC))
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

        $q = "INSERT INTO scorm_object (title, type, slm_id) VALUES "
            . "(" . $ilDB->quote($this->getTitle()) . ", " . $ilDB->quote($this->getType()) . ","
            .$ilDB->quote($this->getSLMId()).")";
        $this->ilias->db->query($q);
        $this->setId($this->ilias->db->getLastInsertId());
    }

    /**
     * Updates database record for SCORM object.
     *
     */
    function update()
    {
        global $ilDB;

        $q = "UPDATE scorm_object SET " . 
            "title = " . $ilDB->quote($this->getTitle()) . ", "
            . "type = " . $ilDB->quote($this->getType()) . ", "
            . "slm_id = ".$ilDB->quote($this->getSLMId())." "
            . "WHERE obj_id = ".$ilDB->quote($this->getId());
        $this->ilias->db->query($q);
    } 

	function delete()
	{
		global $ilDB;

		$q = "DELETE FROM scorm_object WHERE obj_id =" . $ilDB->quote($this->getId());
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

		$sc_set = $ilDB->query("SELECT type FROM scorm_object WHERE obj_id =" . $ilDB->quote($a_id).
			" AND slm_id = ".$ilDB->quote($a_slm_id));
		$sc_rec = $sc_set->fetchRow(MDB2_FETCHMODE_ASSOC);

		switch($sc_rec["type"])
		{
			case "sit":					// item
				include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMItem.php");
				$item =& new ilSCORMItem($a_id);
				return $item;
				break;

			case "sos":					// organizations
				include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMOrganizations.php");
				$sos =& new ilSCORMOrganizations($a_id);
				return $sos;
				break;

			case "sor":					// organization
				include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMOrganization.php");
				$sor =& new ilSCORMOrganization($a_id);
				return $sor;
				break;

			case "sma":					// manifest
				include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMManifest.php");
				$sma =& new ilSCORMManifest($a_id);
				return $sma;
				break;

			case "srs":					// resources
				include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMResources.php");
				$srs =& new ilSCORMResources($a_id);
				return $srs;
				break;

			case "sre":					// resource
				include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMResource.php");
				$sre =& new ilSCORMResource($a_id);
				return $sre;
				break;
		}
	}

}
?>
