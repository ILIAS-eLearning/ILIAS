<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject.php";
//require_once "Services/MetaData/classes/class.ilMDLanguageItem.php";
require_once "./Modules/ScormAicc/classes/class.ilObjSCORMLearningModule.php";

/**
* Class ilObjAICCLearningModule
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ingroup ModulesScormAicc
*/
class ilObjAICCLearningModule extends ilObjSCORMLearningModule
{
	//var $meta_data;

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjAICCLearningModule($a_id = 0, $a_call_by_reference = true)
	{
		$this->type = "sahs";
		parent::ilObject($a_id,$a_call_by_reference);
	}

	/**
	* @access	public
	*/
	function readObject()
	{
		require_once("./Modules/ScormAicc/classes/class.ilObjAICCCourseInterchangeFiles.php");
		$cifModule = new ilObjAICCCourseInterchangeFiles();
		$cifModule->findFiles($this->getDataDirectory());

		$cifModule->readFiles();
		if (!empty($cifModule->errorText))
		{
			$this->ilias->raiseError("<b>Error reading LM-File(s):</b><br>".implode("<br>", $cifModule->errorText), $this->ilias->error_obj->WARNING);
		}

		if ($_POST["validate"] == "y")
		{

			$cifModule->validate();
			if (!empty($cifModule->errorText))
			{
				$this->ilias->raiseError("<b>Validation Error(s):</b><br>".implode("<br>", $cifModule->errorText), $this->ilias->error_obj->WARNING);
			}
		}

		$cifModule->writeToDatabase($this->getId());
	}


	/**
	* delete SCORM learning module and all related data	
	*
	* this method has been tested on may 9th 2004
	* meta data, scorm lm data, scorm tree, scorm objects (organization(s),
	* manifest, resources and items), tracking data and data directory
	* have been deleted correctly as desired
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{
		global $ilDB, $ilLog;

		// always call parent delete function first!!
		if (!parent::delete())
		{
			return false;
		}

		// delete meta data of scorm content object
/*
		$nested = new ilNestedSetXML();
		$nested->init($this->getId(), $this->getType());
		$nested->deleteAllDBData();
*/
		$this->deleteMetaData();

		// delete data directory
		ilUtil::delDir($this->getDataDirectory());

		// delete scorm learning module record
		$ilDB->manipulateF('DELETE FROM sahs_lm WHERE id = %s',
		array('integer'), array($this->getId()));

		// delete aicc data
 		$res = $ilDB->queryF('
			SELECT * FROM aicc_object, aicc_units
			WHERE aicc_object.obj_id = aicc_units.obj_id
			AND aicc_object.slm_id = %s',
			array('integer'), array($this->getId()));
		
			while($row = $ilDB->fetchAssoc($res))
			{
				$obj_id = $row['obj_id'];
				$ilDB->manipulateF('
					DELETE FROM aicc_units WHERE obj_id = %s', 
					array('integer'), array($obj_id));				
			}
		
		$res = $ilDB->queryF('
			SELECT * FROM aicc_object, aicc_course
			WHERE aicc_object.obj_id = aicc_course.obj_id
			AND aicc_object.slm_id = %s',
			array('integer'), array($this->getId()));
		
			while($row = $ilDB->fetchAssoc($res))
			{
				$obj_id = $row['obj_id'];
				$ilDB->manipulateF('
				DELETE FROM aicc_course WHERE obj_id = %s',
				array('integer'), array($obj_id));		
			}

		$ilDB->manipulateF('DELETE FROM scorm_tree WHERE slm_id = %s',
		array('integer'), array($this->getId()));

		$ilDB->manipulateF('DELETE FROM aicc_object WHERE slm_id = %s',
		array('integer'), array($this->getId()));

		$q_log = "DELETE FROM scorm_tracking WHERE obj_id = ".$ilDB->quote($this->getId());
		$ilLog->write("SAHS Delete (AICC LM): ".$q_log);
		$ilDB->manipulateF('DELETE FROM scorm_tracking WHERE obj_id = %s',
		array('integer'), array($this->getId()));

		$q_log = "DELETE FROM sahs_user WHERE obj_id = ".$ilDB->quote($this->getId());
		$ilLog->write("SAHS Delete (AICC LM): ".$q_log);
		$ilDB->manipulateF('DELETE FROM sahs_user WHERE obj_id = %s',
		array('integer'), array($this->getId()));

		// update learning progress status
		include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
		ilLPStatusWrapper::_refreshStatus($this->getId());

		// always call parent delete function at the end!!
		return true;
	}


	/**
	* get all tracking items of scorm object
	*/
	function getTrackingItems()
	{
		return ilObjAICCLearningModule::_getTrackingItems($this->getId());
	}


	/**
	* get all tracking items of scorm object
	*/
	function _getTrackingItems($a_obj_id)
	{
		global $ilDB;

		include_once("./Modules/ScormAicc/classes/AICC/class.ilAICCUnit.php");

		$statement = $ilDB->queryF('
			SELECT obj_id,title FROM aicc_object 
			WHERE slm_id = %s
			AND c_type = %s ',
			array('integer', 'text'),
			array($a_obj_id,'sau')
		);
		
		while($row = $ilDB->fetchObject($statement))
		{
			$items[$row->obj_id]['obj_id'] = $row->obj_id;
			$items[$row->obj_id]['title'] = $row->title;

		}		
		
		return $items ? $items : array();

		/*
		include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMTree.php");
		$tree = new ilSCORMTree($a_obj_id);
		$root_id = $tree->readRootId();

		$items = array();
		$childs = $tree->getSubTree($tree->getNodeData($root_id));
		foreach($childs as $child)
		{
			if($child["c_type"] == "sau")
			{
				include_once("./Modules/ScormAicc/classes/AICC/class.ilAICCUnit.php");
				$ac_item =& new ilAICCUnit($child["obj_id"]);
				$items[count($items)] =& $ac_item;
			}
		}

		return $items;
		*/
	}

	/**
	* get all tracked items of current user
	*/
	function getTrackedItems()
	{
		global $ilUser, $ilDB, $ilUser;
	
		$sco_set = $ilDB->queryF('
			SELECT DISTINCT sco_id FROM scorm_tracking WHERE obj_id = %s',
			array('integer'), array($this->getId()));	
			
		$items = array();	
		while($sco_rec = $ilDB->fetchAssoc($sco_set))
		{
			include_once("./Modules/ScormAicc/classes/AICC/class.ilAICCUnit.php");	
			$ac_item =& new ilAICCUnit($sco_rec["sco_id"]);
			$items[count($items)] =& $ac_item;

		}		
		
		return $items;
	}

	function getTrackingData($a_sco_id)
	{
		global $ilDB;

		$data_set = $ilDB->queryF('
		SELECT * FROM scorm_tracking 
		WHERE obj_id = %s 
		AND sco_id = %s
		ORDER BY user_id, lvalue',
		array('integer', 'integer'),
		array($this->getId(), $a_sco_id));
		
		$data = array();
		while($data_rec = $ilDB->fetchAssoc($data_set))
		{
			$data[] = $data_rec;
		}	
		return $data;
	}

} // END class.ilObjAICCLearningModule

?>
