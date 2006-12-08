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
*/
class ilMetaTechnical
{
	var $ilias;

	var $meta_data;
	var $tech_id;

	var $formats;
	var $size;
	var $locations;
	var $requirement_sets;
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
	function ilMetaTechnical(&$a_meta_data)
	{
		global $ilias;

		$this->ilias =& $ilias;
		$this->meta_data =& $a_meta_data;
		$this->locations = array();
		$this->formats = array();
		$this->requirement_sets = array();
	}


	function getId()
	{
		return $this->meta_data->getId();
	}

	function getType()
	{
		return $this->meta_data->getType();
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
			$q = "DELETE FROM meta_techn_loc WHERE tech_id = '".$tech_rec["tech_id"]."'";
			$this->ilias->db->query($q);

			$q = "DELETE FROM meta_techn_format WHERE tech_id = '".$tech_rec["tech_id"]."'";
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
			"(obj_id, obj_type, size, install_remarks, install_remarks_lang,".
			"other_requirements, other_requirements_lang, duration) ".
			" VALUES ('".$this->getId()."','".$this->getType()."',".
			"'".$this->getSize()."',".
			"'".$this->getInstallationRemarks()."',".
			"'".$this->getInstallationRemarksLanguage()."',".
			"'".$this->getOtherRequirements()."',".
			"'".$this->getOtherRequirementsLanguage()."',".
			"'".$this->getDuration()."')";
//echo "MetaTechnical::create:$q<br>";
		$this->ilias->db->query($q);

		$q = "SELECT LAST_INSERT_ID() AS tech_id FROM meta_technical";
		$row = $this->ilias->db->getRow($q, DB_FETCHMODE_ASSOC);

		$this->tech_id =  $row["tech_id"];
//echo "saving...tech_id:".$row["tech_id"].":<br>";
		$i = 1;
		foreach ($this->locations as $location)
		{
//echo "INSERT INTO meta_techn_loc<br>";
			$q = "INSERT INTO meta_techn_loc (tech_id, type, location, nr) VALUES".
				" ('".$this->tech_id."','".$location["type"]."', '".
				$location["loc"]."', '".$i++."')";
			$this->ilias->db->query($q);
		}

		$i = 1;
		foreach ($this->formats as $format)
		{
//echo "INSERT INTO meta_techn_loc<br>";
			$q = "INSERT INTO meta_techn_format (tech_id, format, nr) VALUES
				('".$this->tech_id."','".$format."', '".$i++."')";
			$this->ilias->db->query($q);
		}

	}

	/**
	* reads all technical sections from db into a meta data object
	* note: there should be max. one technical section now, dtd has changed
	* static
	*/
	function readTechnicalSections(&$a_meta_obj)
	{
//echo "ilMetaTechnical_read:".$a_meta_obj->getId().":".$a_meta_obj->getType().":<br>";
		$query = "SELECT * FROM meta_technical WHERE obj_id='".$a_meta_obj->getId()."' ".
			"AND obj_type='".$a_meta_obj->getType()."'";
		$tech_set = $this->ilias->db->query($query);
		while ($tech_rec = $tech_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
//echo "---tech---";
			$tech_obj =& new ilMetaTechnical($a_meta_obj);
			$tech_obj->setSize($tech_rec["size"]);
			$tech_obj->setInstallationRemarks($tech_rec["install_remarks"]);
			$tech_obj->setInstallationRemarksLanguage($tech_rec["install_remarks_lang"]);
			$tech_obj->setOtherRequirements($tech_rec["other_requirements"]);
			$tech_obj->setOtherRequirementsLanguage($tech_rec["other_requirements_lang"]);
			$tech_obj->setDuration($tech_rec["duration"]);

			// get formats
			$query = "SELECT * FROM meta_techn_format WHERE tech_id='".$tech_rec["tech_id"]."'".
				" ORDER BY nr";
			$format_set = $this->ilias->db->query($query);
			while ($format_rec = $format_set->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$tech_obj->addFormat($format_rec["format"]);
			}

			// get locations
			$query = "SELECT * FROM meta_techn_loc WHERE tech_id='".$tech_rec["tech_id"]."'".
				" ORDER BY nr";
			$loc_set = $this->ilias->db->query($query);
			while ($loc_rec = $loc_set->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$tech_obj->addLocation($loc_rec["type"], $loc_rec["location"]);
			}

			$a_meta_obj->addTechnicalSection($tech_obj);
		}
//echo "count techs:".count($a_meta_obj->technicals).":<br>";
	}

	/**
	* Format
	*
	* @param	string		$a_format		mime type
	*/
	function addFormat($a_format)
	{
		$this->formats[] = $a_format;
	}

	function getFormats()
	{
		return $this->formats;
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
	function addLocation($a_type, $a_loc)
	{
		$this->locations[] = array("type" => $a_type, "loc" => $a_loc);
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

	// get xml string of this meta technical section
	function getXML()
	{
		$xml = "<Technical>\n";
		foreach ($this->formats as $format)
		{
			$xml.= "<Format>".$format."</Format>\n";
		}
		$xml.= "<Size>".$this->getSize()."</Size>\n";
		foreach ($this->locations as $location)
		{
			$xml.= "<Location Type=\"".$location["type"]."\">".$location["loc"]."</Location>\n";
		}
		$req_sets =& $this->getRequirementSets();
		foreach ($req_sets as $req_set)
		{
			$xml.= $req_set->getXML();
		}
		$xml.= "</Technical>";

		return $xml;
	}

}
?>
