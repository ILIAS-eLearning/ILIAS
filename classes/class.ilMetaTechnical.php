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
* Class ilMetaTechnical
*
* Handles Technical Section (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package application
*/
class ilMetaTechnical
{
	var $ilias;

	var $obj_id;
	var $obj_type;
	var $tech_id;

	var $format;
	var $size;
	var $locations;
	var $requirements;
	var $install_lang;
	var $install_remarks;
	var $other_req;
	var $other_req_lang;
	var $duration;


	/**
	* Constructor
	*
	* @access	public
	*/
	function ilMetaTechnical()
	{
		global $ilias;

		$this->ilias =& $ilias;
	}

	function setId($a_id)
	{
		$this->obj_id = $a_id;
	}

	function setType($a_type)
	{
		$this->obj_type = $a_type;
	}

	/**
	* delete all technical meta data of given object
	* static
	*
	* TODO: requirements
	*/
	function delete($a_id, $a_type)
	{
		// get locations and delete them
		$q = "SELECT * FROM meta_technical WHERE ".
			" obj_id='$a_id' AND obj_type='$a_type'";
		$tech_set = $this->ilias->db->query($q);
		while ($tech_rec = $tech_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$q = "DELETE FROM meta_tech_loc WHERE tech_id = '".$tech_rec["tech_id"]."'";
			$this->ilias->db->query($q);
		}

		// delete technical db records
		$q = "DELETE FROM meta_technical WHERE ".
			" obj_id='$a_id' AND obj_type='$a_type'";
		$this->ilias->db->query($q);
	}

	/**
	* create
	*/
	function create()
	{
		$q = "INSERT INTO meta_technical ".
			"(obj_id, obj_type, format, size, install_remarks, install_remarks_lang,".
			"other_requirements, other_requirements_lang, duration) ".
			" VALUES ('".$this->getId()."','".$this->getType()."',".
			"'".$this->getFormat()."',".
			"'".$this->getSize()."',".
			"'".$this->getInstallationRemarks()."',".
			"'".$this->getInstallationRemarksLanguage()."',".
			"'".$this->getOtherRequirements()."',".
			"'".$this->getOtherRequirementsLanguage()."',".
			"'".$this->getDuration()."')";
		$this->ilias-db->query($q);

		$q = "SELECT LAST_INSERT_ID() AS tech_id FROM meta_technical";
		$row = $this->ilias->db->getRow($q, DB_FETCHMODE_ASSOC);

		$this->tech_id =  $row["tech_id"];

		foreach ($this->locations as $location)
		{
			$q = "INSERT INTO meta_techn_loc (tech_id, location) VALUES
				('".$this->tech_id."','".$location."')";
		}

	}

	/**
	* Format
	*
	* @param	string		$a_format		mime type
	*/
	function setFormat($a_format)
	{
		$this->format = $a_format;
	}

	function getFormat($a_format)
	{
		return $this->format;
	}

	/**
	* Size
	*/
	function setSize($a_size)
	{
		$this->size = $a_size;
	}

	function getSize()
	{
		return $this->size;
	}


	/**
	* Location
	*/
	function addLocation($a_loc)
	{
		$this->locations[] = $a_loc;
	}

	function getLocations()
	{
		return $this->locations;
	}


	/**
	* Requirements
	*
	* @param	array	$a_requirement		array(
	*/
	function addRequirementSet($a_requirement_set)
	{
		$this->requirement_sets[] = $a_requirement_set;
	}

	function getRequirementSets()
	{
		return $this->requirement_sets;
	}


	/**
	* Installation Remarks
	*
	* @param	string	$a_lang		language code
	* @param	string	$a_remarks	installation remarks
	*/
	function setInstallationRemarks($a_remarks)
	{
		$this->install_remarks = $a_remarks;
	}

	function setInstallationRemarksLanguage($a_lang)
	{
		$this->install_lang = $a_lang;
	}

	function getInstallationRemarksLanguage()
	{
		return $this->install_lang;
	}

	function getInstallationRemarks()
	{
		return $this->install_remarks;
	}


	/**
	* Other Platform Requirements
	*
	* @param	string	$a_remarks	requirements
	*/
	function setOtherRequirements($a_requirements)
	{
		$this->other_req = $a_requirements;
	}

	function setOtherRequirementsLanguage($a_lang)
	{
		$this->other_req_lang = $a_lang;
	}

	function getOtherRequirementsLanguage()
	{
		return $this->other_req_lang;
	}

	function getOtherRequirements()
	{
		return $this->other_req;
	}


	/**
	* Duration
	*
	* @param	string	$a_duration		duration
	*/
	function setDuration($a_duration)
	{
		$this->duration = $a_duration;
	}

	function getDuration()
	{
		return $this->duration;
	}


}
?>
