<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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


require_once "classes/class.ilObject.php";
require_once "./Modules/ScormAicc/classes/class.ilObjSCORMValidator.php";
//require_once "Services/MetaData/classes/class.ilMDLanguageItem.php";

/** @defgroup ModulesScormAicc Modules/ScormAicc
 */

/**
* Class ilObjSCORMLearningModule
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ingroup ModulesScormAicc
*/
class ilObjSAHSLearningModule extends ilObject
{
	var $validator;
//	var $meta_data;

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjSAHSLearningModule($a_id = 0, $a_call_by_reference = true)
	{
		$this->type = "sahs";
		parent::ilObject($a_id,$a_call_by_reference);
	}

	/**
	* create file based lm
	*/
	function create()
	{
		global $ilDB;

		parent::create();
		$this->createMetaData();

		$this->createDataDirectory();

		$ilDB->manipulateF('
			INSERT INTO sahs_lm (id, c_online, api_adapter, c_type, editable) 
			VALUES (%s,%s,%s,%s,%s)', 
			array('integer', 'text', 'text', 'text', 'integer'), 
			array($this->getId(),'n','API', $this->getSubType(),(int)$this->getEditable()));
	}

	/**
	* read object
	*/
	function read()
	{
		global $ilDB;
		
		parent::read();

		$lm_set = $ilDB->queryF('SELECT * FROM sahs_lm WHERE id = %s', 
			array('integer'),array($this->getId()));	
		
		while($lm_rec = $ilDB->fetchAssoc($lm_set))
		{
			$this->setOnline(ilUtil::yn2tf($lm_rec["c_online"]));
			$this->setAutoReview(ilUtil::yn2tf($lm_rec["auto_review"]));
			$this->setAPIAdapterName($lm_rec["api_adapter"]);
			$this->setDefaultLessonMode($lm_rec["default_lesson_mode"]);
			$this->setAPIFunctionsPrefix($lm_rec["api_func_prefix"]);
			$this->setCreditMode($lm_rec["credit"]);
			$this->setSubType($lm_rec["c_type"]);
			$this->setEditable($lm_rec["editable"]);
			$this->setStyleSheetId($lm_rec["stylesheet"]);
			$this->setMaxAttempt($lm_rec["max_attempt"]);
			$this->setModuleVersion($lm_rec["module_version"]);
			$this->setAssignedGlossary($lm_rec["glossary"]);
			$this->setTries($lm_rec["question_tries"]);
		}
	}

	/**
	* check wether scorm module is online
	*/
	function _lookupOnline($a_id)
	{
		global $ilDB;
		
		$lm_set = $ilDB->queryF('SELECT c_online FROM sahs_lm WHERE id = %s', 
		array('integer'), array($a_id));
		$lm_rec = $ilDB->fetchAssoc($lm_set);
		
		return ilUtil::yn2tf($lm_rec["c_online"]);
	}

	/**
	* lookup subtype id (scorm, aicc, hacp)
	*
	* @param	int		$a_id		object id
	*/
	function _lookupSubType($a_obj_id)
	{
		global $ilDB;

		$obj_set = $ilDB->queryF('SELECT c_type FROM sahs_lm WHERE id = %s', 
		array('integer'), array($a_obj_id));
		$obj_rec = $ilDB->fetchAssoc($obj_set);
		
		return $obj_rec["c_type"];
	}

	/**
	* Set Editable.
	*
	* @param	boolean	$a_editable	Editable
	*/
	function setEditable($a_editable)
	{
		$this->editable = $a_editable;
	}

	/**
	* Get Editable.
	*
	* @return	boolean	Editable
	*/
	function getEditable()
	{
		return $this->editable;
	}


	/**
	* Set default tries for questions
	*
	* @param	boolean	$a_tres	tries
	*/
	function setTries($a_tries)
	{
		$this->tries = $a_tries;
	}

	/**
	* Get Tries.
	*
	* @return	boolean	tries
	*/
	function getTries()
	{
		return $this->tries;
	}
	
	static function _getTries($a_id)
	{
		global $ilDB;

		$lm_set = $ilDB->queryF('SELECT question_tries FROM sahs_lm WHERE id = %s', 
		array('integer'), array($a_id));
		$lm_rec = $ilDB->fetchAssoc($lm_set);
			
		return $lm_rec['question_tries'];
	}
	/**
	* Gets the disk usage of the object in bytes.
    *
	* @access	public
	* @return	integer		the disk usage in bytes
	*/
	function getDiskUsage()
	{
	    require_once("./Modules/ScormAicc/classes/class.ilObjSAHSLearningModuleAccess.php");
		return ilObjSAHSLearningModuleAccess::_lookupDiskUsage($this->id);
	}


	/**
	* creates data directory for package files
	* ("./data/lm_data/lm_<id>")
	*/
	function createDataDirectory()
	{
		$lm_data_dir = ilUtil::getWebspaceDir()."/lm_data";
		ilUtil::makeDir($lm_data_dir);
		ilUtil::makeDir($this->getDataDirectory());
	}

	/**
	* get data directory of lm
	*/
	function getDataDirectory($mode = "filesystem")
	{
		$lm_data_dir = ilUtil::getWebspaceDir($mode)."/lm_data";
		$lm_dir = $lm_data_dir."/lm_".$this->getId();

		return $lm_dir;
	}

	/**
	* get api adapter name
	*/
	function getAPIAdapterName()
	{
		return $this->api_adapter;
	}

	/**
	* set api adapter name
	*/
	function setAPIAdapterName($a_api)
	{
		$this->api_adapter = $a_api;
	}

	/**
	* get api functions prefix
	*/
	function getAPIFunctionsPrefix()
	{
		return $this->api_func_prefix;
	}

	/**
	* set api functions prefix
	*/
	function setAPIFunctionsPrefix($a_prefix)
	{
		$this->api_func_prefix = $a_prefix;
	}

	/**
	* get credit mode
	*/
	function getCreditMode()
	{
		return $this->credit_mode;
	}

	/**
	* set credit mode
	*/
	function setCreditMode($a_credit_mode)
	{
		$this->credit_mode = $a_credit_mode;
	}

	/**
	* set default lesson mode
	*/
	function setDefaultLessonMode($a_lesson_mode)
	{
		$this->lesson_mode = $a_lesson_mode;
	}

	/**
	* get default lesson mode
	*/
	function getDefaultLessonMode()
	{
		return $this->lesson_mode;
	}
	/**
	* get ID of assigned style sheet object
	*/
	function getStyleSheetId()
	{
		return $this->style_id;
	}

	/**
	* set ID of assigned style sheet object
	*/
	function setStyleSheetId($a_style_id)
	{
		$this->style_id = $a_style_id;
	}


	/**
	* get auto review
	*/
	function setAutoReview($a_auto_review)
	{
		$this->auto_review = $a_auto_review;
	}
	/**
	* set auto review
	*/
	function getAutoReview()
	{
		return $this->auto_review;
	}
	
	
	/**
	* get max attempt
	*/
	function getMaxAttempt()
	{
		return $this->max_attempt;
	}


	/**
	* set max attempt
	*/
	function setMaxAttempt($a_max_attempt)
	{
		$this->max_attempt = $a_max_attempt;
	}
	
	/**
	* get max attempt
	*/
	function getModuleVersion()
	{
		return $this->module_version;
	}

	/**
	* get assigned glossary
	*/	
	function getAssignedGlossary()
	{
		return $this->assigned_glossary;
	}
	
	/**
	* set assigned glossary
	*/	
	function setAssignedGlossary($a_assigned_glossary)
	{
		$this->assigned_glossary = $a_assigned_glossary;
	}
	/**
	* set max attempt
	*/
	function setModuleVersion($a_module_version)
	{
		$this->module_version = $a_module_version;
	}
	
	/**
	* update meta data only
	*/
/*
	function updateMetaData()
	{
		$this->meta_data->update();
		if ($this->meta_data->section != "General")
		{
			$meta = $this->meta_data->getElement("Title", "General");
			$this->meta_data->setTitle($meta[0]["value"]);
			$meta = $this->meta_data->getElement("Description", "General");
			$this->meta_data->setDescription($meta[0]["value"]);
		}
		else
		{
			$this->setTitle($this->meta_data->getTitle());
			$this->setDescription($this->meta_data->getDescription());
		}
		parent::update();

	}
*/


	/**
	* update object data
	*
	* @access	public
	* @return	boolean
	*/
	function update()
	{
		global $ilDB;

		$this->updateMetaData();
		parent::update();

		$statement = $ilDB->manipulateF('
			UPDATE sahs_lm  
			SET c_online = %s, 
				api_adapter = %s, 
				api_func_prefix = %s,
				auto_review = %s,
				default_lesson_mode = %s,
				c_type = %s,
				stylesheet = %s, 
				editable = %s, 
				max_attempt = %s, 
				module_version = %s, 
				credit = %s, 
				glossary = %s, 
				question_tries = %s
			WHERE id = %s', 
		array(	'text',
				'text',
				'text',
				'text',
				'text',
				'text',
				'integer',
				'integer',
				'integer',
				'integer',
				'text',
				'integer',
				'integer',
				'integer'), 
		array(	ilUtil::tf2yn($this->getOnline()),
				$this->getAPIAdapterName(),
				$this->getAPIFunctionsPrefix(),
				ilUtil::tf2yn($this->getAutoReview()),
				$this->getDefaultLessonMode(),
				$this->getSubType(),
				$this->getStyleSheetId(),
				$this->getEditable(),
				$this->getMaxAttempt(),
				$this->getModuleVersion(),
				$this->getCreditMode(),
				$this->getAssignedGlossary(),
				$this->getTries(),
				$this->getId())
		);
		
		return true;
	}

	/**
	* get online
	*/
	function setOnline($a_online)
	{
		$this->online = $a_online;
	}

	/**
	* set online
	*/
	function getOnline()
	{
		return $this->online;
	}

	/**
	* get sub type
	*/
	function setSubType($a_sub_type)
	{
		$this->sub_type = $a_sub_type;
	}

	/**
	* set sub type
	*/
	function getSubType()
	{
		return $this->sub_type;
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
		
		$ilLog->write("SAHS Delete(SAHSLM), Subtype: ".$this->getSubType());
		
		if ($this->getSubType() == "scorm")
		{
			// remove all scorm objects and scorm tree
			include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMTree.php");
			include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMObject.php");
			$sc_tree = new ilSCORMTree($this->getId());
			$r_id = $sc_tree->readRootId();
			if ($r_id > 0)
			{
				$items = $sc_tree->getSubTree($sc_tree->getNodeData($r_id));
				foreach($items as $item)
				{
					$sc_object =& ilSCORMObject::_getInstance($item["obj_id"], $this->getId());
					if (is_object($sc_object))
					{
						$sc_object->delete();
					}
				}
				$sc_tree->removeTree($sc_tree->getTreeId());
			}
		}

		if ($this->getSubType() != "scorm")
		{
			// delete aicc data
			// this is highly dependent on the database
			$ilDB->manipulateF('
				DELETE FROM aicc_units 
				USING aicc_object, aicc_units 
				WHERE aicc_object.obj_id = aicc_units.obj_id 
				AND aicc_object.slm_id = %s', 
				array('integer'), array($this->getId()));
				
			$ilDB->manipulateF('
				DELETE FROM aicc_course 
				USING aicc_object, aicc_course 
				WHERE aicc_object.obj_id = aicc_course.obj_id 
				AND aicc_object.slm_id = %s',
				array('integer'), array($this->getId()));

			$ilDB->manipulateF('
				DELETE FROM aicc_object WHERE slm_id = %s',
				array('integer'), array($this->getId()));	
		}

		$q_log = "DELETE FROM scorm_tracking WHERE obj_id = ".$ilDB->quote($this->getId());
		$ilLog->write("SAHS Delete(SAHSLM): ".$q_log);

		$ilDB->manipulateF('DELETE FROM scorm_tracking WHERE obj_id = %s',
			array('integer'), array($this->getId()));

		// always call parent delete function at the end!!
		return true;
	}

	/**
	* notifys an object about an event occured
	* Based on the event happend, each object may decide how it reacts.
	*
	* @access	public
	* @param	string	event
	* @param	integer	reference id of object where the event occured
	* @param	array	passes optional paramters if required
	* @return	boolean
	*/
	function notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params = 0)
	{
		global $tree;

		switch ($a_event)
		{
			case "link":

				//var_dump("<pre>",$a_params,"</pre>");
				//echo "SCORMLearningModule ".$this->getRefId()." triggered by link event. Objects linked into target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "cut":

				//echo "SCORMLearningModule ".$this->getRefId()." triggered by cut event. Objects are removed from target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "copy":

				//var_dump("<pre>",$a_params,"</pre>");
				//echo "SCORMLearningModule ".$this->getRefId()." triggered by copy event. Objects are copied into target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "paste":

				//echo "SCORMLearningModule ".$this->getRefId()." triggered by paste (cut) event. Objects are pasted into target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "new":

				//echo "SCORMLearningModule ".$this->getRefId()." triggered by paste (new) event. Objects are applied to target object ref_id: ".$a_ref_id;
				//exit;
				break;
		}

		// At the beginning of the recursive process it avoids second call of the notify function with the same parameter
		if ($a_node_id==$_GET["ref_id"])
		{
			$parent_obj =& $this->ilias->obj_factory->getInstanceByRefId($a_node_id);
			$parent_type = $parent_obj->getType();
			if($parent_type == $this->getType())
			{
				$a_node_id = (int) $tree->getParentId($a_node_id);
			}
		}

		parent::notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params);
	}

	/**
	* Returns the points in percent for the learning module
	* This is called by the certificate generator if [SCORM_POINTS] is
	* inserted.
	*/
	public function getPointsInPercent()
	{
		global $ilUser;
		if (strcmp($this->getSubType(), "scorm2004") == 0)
		{
			$res = ilObjSCORM2004LearningModule::_getUniqueScaledScoreForUser($this->getId(), $ilUser->getId());
			if (!is_null($res)) 
			{
				return $res * 100.0;
			}
			else
			{
				return $res;
			}
		}
		else
		{
			return null;
		}
	}

} // END class.ilObjSCORMLearningModule
?>
