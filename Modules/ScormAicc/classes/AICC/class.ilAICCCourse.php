<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

require_once("./Modules/ScormAicc/classes/AICC/class.ilAICCObject.php");

class ilAICCCourse extends ilAICCObject
{

/**
* AICC Item
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @extends ilAICCObject
* @package content
*/

	var $course_creator;
	var $course_id;
	var $course_system;
	var $course_title;
	var $level;
	var $max_fields_cst;
	var $max_fields_ort;
	var $total_aus;
	var $total_blocks;
	var $total_complex_obj;
	var $total_objectives;
	var $version;
	var $max_normal;
	var $description;

	/**
	* Constructor
	*
	* @param	int		$a_id		Object ID
	* @access	public
	*/
	function ilAICCCourse($a_id = 0)
	{
		parent::ilAICCObject($a_id);
		$this->setType("shd");
	}
	
	function getCourseCreator()
	{
		return $this->course_creator;
	}
	
	function setCourseCreator($a_course_creator)
	{
		$this->course_creator = $a_course_creator;
	}
	
	function getCourseId()
	{
		return $this->course_id;
	}
	
	function setCourseId($a_course_id)
	{
		$this->course_id = $a_course_id;
	}
	
	function getCourseSystem()
	{
		return $this->course_system;
	}
	
	function setCourseSystem($a_course_system)
	{
		$this->course_system = $a_course_system;
	}
	
	function getCourseTitle()
	{
		return $this->course_title;
	}
	
	function setCourseTitle($a_course_title)
	{
		$this->course_title = $a_course_title;
	}
	
	function getLevel()
	{
		return $this->level;
	}
	
	function setLevel($a_level)
	{
		$this->level = $a_level;
	}
	
	function getMaxFieldsCst()
	{
		return $this->max_fields_cst;
	}
	
	function setMaxFieldsCst($a_max_fields_cst)
	{
		$this->max_fields_cst = $a_max_fields_cst;
	}
	
	function getMaxFieldsOrt()
	{
		return $this->max_fields_ort;
	}
	
	function setMaxFieldsOrt($a_max_fields_ort)
	{
		$this->max_fields_ort = $a_max_fields_ort;
	}
	
	function getTotalAUs()
	{
		return $this->total_aus;
	}
	
	function setTotalAUs($a_total_aus)
	{
		$this->total_aus = $a_total_aus;
	}
	
	function getTotalBlocks()
	{
		return $this->total_blocks;
	}
	
	function setTotalBlocks($a_total_blocks)
	{
		$this->total_blocks = $a_total_blocks;
	}
	
	function getTotalComplexObj()
	{
		return $this->total_complex_obj;
	}
	
	function setTotalComplexObj($a_total_complex_obj)
	{
		$this->total_complex_obj = $a_total_complex_obj;
	}
	
	function getTotalObjectives()
	{
		return $this->total_objectives;
	}
	
	function setTotalObjectives($a_total_objectives)
	{
		$this->total_objectives = $a_total_objectives;
	}
	
	function getVersion()
	{
		return $this->version;
	}
	
	function setVersion($a_version)
	{
		$this->version = $a_version;
	}
	
	function getMaxNormal()
	{
		return $this->max_normal;
	}
	
	function setMaxNormal($a_max_normal)
	{
		$this->max_normal = $a_max_normal;
	}
	
	function getDescription()
	{
		return $this->description;
	}
	
	function setDescription($a_description)
	{
		$this->description = $a_description;
	}

	function read()
	{
		parent::read();

		$q = "SELECT * FROM aicc_course WHERE obj_id = '".$this->getId()."'";

		$obj_set = $this->ilias->db->query($q);
		$obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC);
		$this->setCourseCreator($obj_rec["course_creator"]);
		$this->setCourseId($obj_rec["course_id"]);
		$this->setCourseSystem($obj_rec["course_system"]);
		$this->setCourseTitle($obj_rec["course_title"]);
		$this->setLevel($obj_rec["level"]);
		$this->setMaxFieldsCst($obj_rec["max_fields_cst"]);
		$this->setMaxFieldsOrt($obj_rec["max_fields_ort"]);
		$this->setTotalAUs($obj_rec["total_aus"]);
		$this->setTotalBlocks($obj_rec["total_blocks"]);
		$this->setTotalComplexObj($obj_rec["total_complex_obj"]);
		$this->setTotalObjectives($obj_rec["total_objectives"]);
		$this->setVersion($obj_rec["_version"]);
		$this->setMaxNormal($obj_rec["max_normal"]);
		$this->setDescription($obj_rec["description"]);
	}

	function create()
	{
		parent::create();

		$q = "INSERT INTO aicc_course (obj_id, course_creator, course_id, course_system, course_title,
																	level, max_fields_cst, max_fields_ort, total_aus, total_blocks,
																	total_complex_obj, total_objectives, version, max_normal,
																	description) VALUES (";
		$q.="'".$this->getId()."', ";
		$q.="'".$this->prepForStore($this->getCourseCreator())."', ";
		$q.="'".$this->prepForStore($this->getCourseId())."', ";
		$q.="'".$this->prepForStore($this->getCourseSystem())."', ";
		$q.="'".$this->prepForStore($this->getCourseTitle())."', ";
		$q.="'".$this->prepForStore($this->getLevel())."', ";
		$q.="'".$this->prepForStore($this->getMaxFieldsCst())."', ";
		$q.="'".$this->prepForStore($this->getMaxFieldsOrt())."', ";
		$q.="'".$this->prepForStore($this->getTotalAUs())."', ";
		$q.="'".$this->prepForStore($this->getTotalBlocks())."', ";
		$q.="'".$this->prepForStore($this->getTotalComplexObj())."', ";
		$q.="'".$this->prepForStore($this->getTotalObjectives())."', ";
		$q.="'".$this->prepForStore($this->getVersion())."', ";
		$q.="'".$this->prepForStore($this->getMaxNormal())."', ";
		$q.="'".$this->prepForStore($this->getDescription())."')";
		$this->ilias->db->query($q);
	}

	function update()
	{
		parent::update();
		
		$q = "UPDATE aicc_course SET ";
		$q.="course_creator='".$this->prepForStore($this->getCourseCreator())."', ";
		$q.="course_id='".$this->prepForStore($this->getCourseId())."', ";
		$q.="course_system='".$this->prepForStore($this->getCourseSystem())."', ";
		$q.="course_title='".$this->prepForStore($this->getCourseTitle())."', ";
		$q.="level='".$this->prepForStore($this->getLevel())."', ";
		$q.="max_fields_cst='".$this->prepForStore($this->getMaxFieldsCst())."', ";
		$q.="max_fields_ort='".$this->prepForStore($this->getMaxFieldsOrt())."', ";
		$q.="total_aus='".$this->prepForStore($this->getTotalAUs())."', ";
		$q.="total_blocks='".$this->prepForStore($this->getTotalBlocks())."', ";
		$q.="total_complex_obj='".$this->prepForStore($this->getTotalComplexObj())."', ";
		$q.="total_objectives='".$this->prepForStore($this->getTotalObjectives())."', ";
		$q.="version='".$this->prepForStore($this->getVersion())."', ";
		$q.="max_normal='".$this->prepForStore($this->getMaxNormal())."', ";
		$q.="description='".$this->prepForStore($this->getDescription())."' ";		
		$q.="WHERE obj_id = '".$this->getId()."'";
		$this->ilias->db->query($q);
	}

	function delete()
	{
		global $ilDB;

		parent::delete();

		$q = "DELETE FROM aicc_course WHERE obj_id =".$ilDB->quote($this->getId());
		$ilDB->query($q);

		$q = "DELETE FROM scorm_tracking WHERE ".
			"sco_id = ".$ilDB->quote($this->getId());
		$ilDB->query($q);

	}

	/**
	* get tracking data of specified or current user
	*
	*
	*/
	function getTrackingDataOfUser($a_user_id = 0)
	{
		global $ilDB, $ilUser;

		if ($a_user_id == 0)
		{
			$a_user_id = $ilUser->getId();
		}

		$q = "SELECT * FROM scorm_tracking WHERE ".
			"sco_id = '".$this->getId()."' AND ".
			"user_id = '".$a_user_id."'";

		$track_set = $ilDB->query($q);
		$trdata = array();
		while ($track_rec = $track_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$trdata[$track_rec["lvalue"]] = $track_rec["rvalue"];
		}

		return $trdata;
	}
	
	function insertTrackData($a_lval, $a_rval, $a_obj_id)
	{
		require_once("./Modules/ScormAicc/classes/SCORM/class.ilObjSCORMTracking.php");
		ilObjSCORMTracking::_insertTrackData($this->getId(), $a_lval, $a_rval, $a_obj_id);
	}

}
?>