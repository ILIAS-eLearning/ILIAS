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

require_once("content/classes/class.ilSCORMObject");

/**
* SCORM Item
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @extends ilSCORMObject
* @package content
*/
class ilSCORMItem extends ilSCORMObject
{
	var $import_id;
	var $identifierref;
	var $isvisible;
	var $parameters;
	var $prereq_type;
	var $prerequisites;
	var $maxtimeallowed;
	var $timelimitaction;
	var $datafromlms;
	var $masteryscore;

	/**
	* Constructor
	*
	* @param	int		$a_id		Object ID
	* @access	public
	*/
	function ilSCORMItem($a_id = 0)
	{
		parent::ilSCORMOject($a_id);
	}

	function getImportId()
	{
		return $this->import_id;
	}

	function setImportId($a_import_id)
	{
		$this->import_id = $a_import_id;
	}

	function getIdentifierRef()
	{
		return $this->identifierref;
	}

	function setIdentifierRef($a_id_ref)
	{
		$this->identifierref = $a_id_ref;
	}

	function getVisible()
	{
		return $this->isvisible;
	}

	function setVisible($a_visible)
	{
		$this->isvisible = $a_visible;
	}

	function getParameters()
	{
		return $this->parameters;
	}

	function setParameters($a_par)
	{
		$this->parameters = $a_par;
	}

	function getPrereqType()
	{
		return $this->prereq_type;
	}

	function setPrereqType($a_p_type)
	{
		$this->prereq_type = $a_p_type;
	}

	function getPrerequisites()
	{
		return $this->prerequisites;
	}

	function setPrerequisites($a_pre)
	{
		$this->prerequisites = $a_pre;
	}

	function getMaxTimeAllowed()
	{
		return $this->maxtimeallowed;
	}

	function setMaxTimeAllowed($a_max)
	{
		$this->maxtimeallowed = $a_max;
	}

	function getTimeLimitAction()
	{
		return $this->timelimitaction;
	}

	function setTimeLimitAction($a_lim_act)
	{
		$this->timelimitaction = $a_lim_act;
	}

	function getDataFromLms()
	{
		return $this->datafromlms;
	}

	function setDataFromLms($a_data)
	{
		$this->datafromlms = $a_data;
	}

	function getMasteryScore()
	{
		return $this->masteryscore;
	}

	function setMasteryScore($a_score)
	{
		$this->masteryscore = $a_score;
	}


	function read()
	{
		parent::read();

		$q = "SELECT * FROM sc_item WHERE id = '".$this->getId()."'";

		$obj_set = $this->ilias->db->query($q);
		$obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC);
		$this->setImportId($obj_rec["import_id"]);
		$this->setIdentifierRef($obj_rec["identifierref"]);
		if (strtolower($obj_rec["isvisible"]) == "false")
		{
			$this->setVisible(false);
		}
		else
		{
			$this->setVisible(true);
		}
		$this->setParameters($obj_rec["parameters"]);
		$this->setPrereqType($obj_rec["prereq_type"]);
		$this->setPrerequisites($obj_rec["prerequisites"]);
		$this->setMaxTimeAllowed($obj_rec["maxtimeallowed"]);
		$this->setTimeLimitAction($obj_rec["timelimitaction"]);
		$this->setDataFromLms($obj_rec["datafromlms"]);
		$this->setMasteryScore($obj_rec["masteryscore"]);
	}

	function create()
	{
		parent::create();

		$str_visible = ($this->getVisible())
			? "true"
			: "false";

		$q = "INSERT INTO sc_organization (obj_id, import_id, identifierref,".
			"isvisible, parameters, prereq_type, prerequisites, maxtimeallowed,".
			"timelimitaction, datafromlms, masteryscore) VALUES ".
			"('".$this->getId()."', '".$this->getImportId()."','".$this->getIdentifierRef().
			"','$str_visible','".$this->getParameters()."','".$this->getPrereqType().
			"','".$this->getPrerequisites()."','".$this->getMaxTimeAllowed()."','".
			$this->getTimeLimitAction()."','".$this->getDataFromLms()."','".
			$this->getMasteryScore()."')";
		$this->ilias->db->query($q);
	}

}
?>
