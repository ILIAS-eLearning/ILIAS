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
* @version $Id$
*
* @ingroup ModulesScormAicc
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
				if($a_max_fields_cst == NULL) $a_max_fields_cst=0;
		$this->max_fields_cst = $a_max_fields_cst;
	}
	
	function getMaxFieldsOrt()
	{
		return $this->max_fields_ort;
	}
	
	function setMaxFieldsOrt($a_max_fields_ort)
	{
		if($a_max_fields_ort == NULL) $a_max_fields_ort=0;
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
		if($a_total_complex_obj == NULL) $a_total_complex_obj=0;	
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
		global $ilDB;
		
		parent::read();

		$obj_set = $ilDB->queryF('SELECT * FROM aicc_course WHERE obj_id = %s',
					array('integer'), array($this->id));
					
		while($obj_rec = $ilDB->fetchAssoc($obj_set))
		{
			$this->setCourseCreator($obj_rec["course_creator"]);
			$this->setCourseId($obj_rec["course_id"]);
			$this->setCourseSystem($obj_rec["course_system"]);
			$this->setCourseTitle($obj_rec["course_title"]);
			$this->setLevel($obj_rec["c_level"]);
			$this->setMaxFieldsCst($obj_rec["max_fields_cst"]);
			$this->setMaxFieldsOrt($obj_rec["max_fields_ort"]);
			$this->setTotalAUs($obj_rec["total_aus"]);
			$this->setTotalBlocks($obj_rec["total_blocks"]);
			$this->setTotalComplexObj($obj_rec["total_complex_obj"]);
			$this->setTotalObjectives($obj_rec["total_objectives"]);
			$this->setVersion($obj_rec["version"]);
			$this->setMaxNormal($obj_rec["max_normal"]);
			$this->setDescription($obj_rec["description"]);
		}
	}

	function create()
	{
		global $ilDB;
		
		parent::create();
		
		$ilDB->insert('aicc_course', array(
			'obj_id'			=> array('integer', $this->getId()),
			'course_creator'	=> array('text', $this->getCourseCreator()),
			'course_id'			=> array('text', $this->getCourseId()),
			'course_system'		=> array('text', $this->getCourseSystem()),
			'course_title'		=> array('text', $this->getCourseTitle()),
			'c_level'			=> array('text', $this->getLevel()),
			'max_fields_cst'	=> array('integer', $this->getMaxFieldsCst()),
			'max_fields_ort'	=> array('integer', $this->getMaxFieldsOrt()),
			'total_aus'			=> array('integer', $this->getTotalAUs()),
			'total_blocks'		=> array('integer', $this->getTotalBlocks()),
			'total_complex_obj'	=> array('integer', $this->getTotalComplexObj()),
			'total_objectives'	=> array('integer', $this->getTotalObjectives()),
			'version'			=> array('text', $this->getVersion()),
			'max_normal'		=> array('integer', $this->getMaxNormal()),
			'description'		=> array('clob', $this->getDescription())
		));
	}

	function update()
	{
		global $ilDB;
		
		parent::update();
		
		$ilDB->update('aicc_course',
			array(
				'course_creator'	=> array('text', $this->getCourseCreator()),
				'course_id'			=> array('text', $this->getCourseId()),
				'course_system'		=> array('text', $this->getCourseSystem()),
				'course_title'		=> array('text', $this->getCourseTitle()),
				'c_level'			=> array('text', $this->getLevel()),
				'max_fields_cst'	=> array('integer', $this->getMaxFieldsCst()),
				'max_fields_ort'	=> array('integer', $this->getMaxFieldsOrt()),
				'total_aus'			=> array('integer', $this->getTotalAUs()),
				'total_blocks'		=> array('integer', $this->getTotalBlocks()),
				'total_complex_obj'	=> array('integer', $this->getTotalComplexObj()),
				'total_objectives'	=> array('integer', $this->getTotalObjectives()),
				'version'			=> array('text', $this->getVersion()),
				'max_normal'		=> array('integer', $this->getMaxNormal()),
				'description'		=> array('clob', $this->getDescription())
			),
			array(
				'obj_id'			=> array('integer', $this->getId())
			)
		);
	}

	function delete()
	{
		global $ilDB, $ilLog;

		parent::delete();

		$statement = $ilDB->manipulateF(
			'DELETE FROM aicc_course WHERE obj_id = %s',
			array('integer'),
			array($this->getId())
		);
		
		$statement = $ilDB->manipulateF('
			DELETE FROM scorm_tracking 
			WHERE sco_id = %s
			AND obj_id = %s',
			array('integer', 'integer'),
 			array($this->getId(),$this->getALMId())
 		);
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

		$track_set = $ilDB->queryF('
			SELECT lvalue, rvalue FROM scorm_tracking 
			WHERE sco_id = %s
			AND user_id = %s
			AND obj_id = %s',
			array('integer', 'integer', 'integer'),
			array($this->getId(), $a_user_id, $this->getALMId())
		);	
		$trdata = array();
		while ($track_rec = $ilDB->fetchAssoc($track_set))
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