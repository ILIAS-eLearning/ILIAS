<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject.php";
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
	function create($upload=false)
	{
		global $ilDB;

		parent::create();
		if(!$upload)
		$this->createMetaData();

		$this->createDataDirectory();

		$ilDB->manipulateF('
			INSERT INTO sahs_lm (id, c_online, api_adapter, c_type, editable, seq_exp_mode,localization) 
			VALUES (%s,%s,%s,%s,%s,%s,%s)', 
			array('integer', 'text', 'text', 'text', 'integer','integer','text'), 
			array($this->getId(),'n','API', $this->getSubType(),(int)$this->getEditable(),
				(int)$this->getSequencingExpertMode(), $this->getLocalization()
				));
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
			$this->setLocalization($lm_rec["localization"]);
			$this->setSession(ilUtil::yn2tf($lm_rec["unlimited_session"]));
			$this->setNoMenu(ilUtil::yn2tf($lm_rec["no_menu"]));
			$this->setHideNavig(ilUtil::yn2tf($lm_rec["hide_navig"]));
			$this->setSequencing(ilUtil::yn2tf($lm_rec["sequencing"]));
			$this->setInteractions(ilUtil::yn2tf($lm_rec["interactions"]));
			$this->setObjectives(ilUtil::yn2tf($lm_rec["objectives"]));
			$this->setComments(ilUtil::yn2tf($lm_rec["comments"]));
			$this->setTime_from_lms(ilUtil::yn2tf($lm_rec["time_from_lms"]));
			$this->setDebug(ilUtil::yn2tf($lm_rec["debug"]));
			$this->setDebugPw($lm_rec["debugpw"]);
			$this->setSequencingExpertMode($lm_rec["seq_exp_mode"]);
			$this->setOpenMode($lm_rec["open_mode"]);
			$this->setWidth($lm_rec["width"]);
			$this->setHeight($lm_rec["height"]);
			$this->setAutoContinue(ilUtil::yn2tf($lm_rec["auto_continue"]));
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
	 * Get affective localization
	 * 
	 * @param int $a_id scorm lm id
	 */
	function getAffectiveLocalization($a_id)
	{
		global $ilDB, $lng;
		
		$lm_set = $ilDB->queryF('SELECT localization FROM sahs_lm WHERE id = %s', 
			array('integer'), array($a_id));
		$lm_rec = $ilDB->fetchAssoc($lm_set);
		$inst_lang = $lng->getInstalledLanguages();
		if ($lm_rec["localization"] != "" && in_array($lm_rec["localization"], $inst_lang))
		{
			return $lm_rec["localization"];
		}
		return $lng->getLangKey();
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
	
	/**
	 * Set localization
	 *
	 * @param string $a_val localization	
	 */
	function setLocalization($a_val)
	{
		$this->localization = $a_val;
	}
	
	/**
	 * Get localization
	 *
	 * @return string localization
	 */
	function getLocalization()
	{
		return $this->localization;
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
	* get session setting
	*/
	function getSession()
	{
		return $this->session;
	}
	
	/**
	* set session setting
	*/
	function setSession($a_session)
	{
		$this->session = $a_session;
	}
	
	/**
	* disable menu
	*/
	function getNoMenu()
	{
		return $this->no_menu;
	}
	
	/**
	* disable menu
	*/
	function setNoMenu($a_no_menu)
	{
		$this->no_menu = $a_no_menu;
	}
	
	/**
	* hide navigation tree
	*/
	function getHideNavig()
	{
		return $this->hide_navig;
	}
	
	/**
	* disable menu
	*/
	function setHideNavig($a_hide_navig)
	{
		$this->hide_navig = $a_hide_navig;
	}

	/**
	* BrowserCacheDisabled for SCORM 2004 / ENABLE_JS_DEBUG
	*/
	function getCacheDeactivated()
	{
		global $ilSetting;
		$lm_set = new ilSetting("lm");
		if ($lm_set->get("scormdebug_disable_cache") == "1") return true;
		return false;
	}

	/**
	* debugActivated
	*/
	function getDebugActivated()
	{
		global $ilSetting;
		$lm_set = new ilSetting("lm");
		if ($lm_set->get("scormdebug_global_activate") == "1") return true;
		return false;
	}

	/**
	* sequencing
	*/
	function getSequencing()
	{
		return $this->sequencing;
	}

	function setSequencing($a_sequencing)
	{
		$this->sequencing = $a_sequencing;
	}

	/**
	* interactions
	*/
	function getInteractions()
	{
		return $this->interactions;
	}

	function setInteractions($a_interactions)
	{
		$this->interactions = $a_interactions;
	}

	/**
	* objectives
	*/
	function getObjectives()
	{
		return $this->objectives;
	}

	function setObjectives($a_objectives)
	{
		$this->objectives = $a_objectives;
	}

	/**
	* comments
	*/
	function getComments()
	{
		return $this->comments;
	}

	function setComments($a_comments)
	{
		$this->comments = $a_comments;
	}

	/**
	* time_from_lms
	*/
	function getTime_from_lms()
	{
		return $this->time_from_lms;
	}

	function setTime_from_lms($a_time_from_lms)
	{
		$this->time_from_lms = $a_time_from_lms;
	}


	/**
	* debug
	*/
	function getDebug()
	{
		return $this->debug;
	}
	
	/**
	* debug
	*/
	function setDebug($a_debug)
	{
		$this->debug = $a_debug;
	}
	
	/**
	* debug pw
	*/
	function getDebugPw()
	{
		return $this->debug_pw;
	}
	
	/**
	* debug pw
	*/
	function setDebugPw($a_debug_pw)
	{
		$this->debug_pw = $a_debug_pw;
	}
	
	/**
	* get auto continue
	*/
	function setAutoContinue($a_auto_continue)
	{
		$this->auto_continue = $a_auto_continue;
	}
	/**
	* set auto continue
	*/
	function getAutoContinue()
	{
		return $this->auto_continue;
	}

	
	/**
	 * Set sequencing expert mode
	 *
	 * @param boolean $a_val sequencing expert mode	
	 */
	function setSequencingExpertMode($a_val)
	{
		$this->seq_exp_mode = $a_val;
	}
	
	/**
	 * Get sequencing expert mode
	 *
	 * @return boolean sequencing expert mode
	 */
	function getSequencingExpertMode()
	{
		return $this->seq_exp_mode;
	}

	
	/**
	* open_mode
	* 0: in Tab/new Window like in previous versions
	* 1: in iFrame with width=100% and heigth=100%
	* 2: in iFrame with specified width and height
	* 3: 
	* 4:
	* 5: in new Window without specified width and height
	* 6: in new Window with specified width and height
	*/
	function getOpenMode()
	{
		return $this->open_mode;
	}
	function setOpenMode($a_open_mode)
	{
		$this->open_mode = $a_open_mode;
	}
	
	/**
	* width
	*/
	function getWidth()
	{
		return $this->width;
	}
	function setWidth($a_width)
	{
		$this->width = $a_width;
	}

	/**
	* height
	*/
	function getHeight()
	{
		return $this->height;
	}
	function setHeight($a_height)
	{
		$this->height = $a_height;
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
				question_tries = %s,
				unlimited_session = %s,
				no_menu = %s,
				hide_navig = %s,
				sequencing = %s,
				interactions = %s,
				objectives = %s,
				comments = %s,
				time_from_lms = %s,
				debug = %s,
				localization = %s,
				seq_exp_mode = %s,
				debugpw = %s,
				open_mode = %s,
				width = %s,
				height = %s,
				auto_continue = %s
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
				'text',
				'text',
				'text',
				'text',
				'text',
				'text',
				'text',
				'text',
				'text',
				'text',
				'integer',
				'text',
				'integer',
				'integer',
				'integer',
				'text',
				'integer'
				), 
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
				ilUtil::tf2yn($this->getSession()),
				ilUtil::tf2yn($this->getNoMenu()),
				ilUtil::tf2yn($this->getHideNavig()),
				ilUtil::tf2yn($this->getSequencing()),
				ilUtil::tf2yn($this->getInteractions()),
				ilUtil::tf2yn($this->getObjectives()),
				ilUtil::tf2yn($this->getComments()),
				ilUtil::tf2yn($this->getTime_from_lms()),
				ilUtil::tf2yn($this->getDebug()),
				$this->getLocalization(),
				$this->getSequencingExpertMode(),
				$this->getDebugPw(),
				$this->getOpenMode(),
				$this->getWidth(),
				$this->getHeight(),
				ilUtil::tf2yn($this->getAutoContinue()),
				$this->getId())
		);

		return true;
	}
	
	/**
	 * Get SCORM modules that assign a certain glossary
	 *
	 * @param
	 * @return
	 */
	static function getScormModulesForGlossary($a_glo_id)
	{
		global $ilDB;
	
		$set = $ilDB->query("SELECT DISTINCT id FROM sahs_lm WHERE ".
			" glossary = ".$ilDB->quote($a_glo_id, "integer"));
		$sms = array();
		while ($rec  = $ilDB->fetchAssoc($set))
		{
			if (ilObject::_hasUntrashedReference($rec["id"]))
			{
				$sms[] = $rec["id"];
			}
		}
		return $sms;
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
 			$res = $ilDB->queryF('
				SELECT aicc_object.obj_id FROM aicc_object, aicc_units
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
				SELECT aicc_object.obj_id FROM aicc_object, aicc_course
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
	
	/**
	 * 
	 * Returns score.max for the learning module, refered to the last sco where score.max is set.
	 * This is called by the certificate generator if [SCORM_POINTS_MAX] is
	 * inserted.
	 * 
	 * @access	public
	 * @return	float
	 * 
	 */
	public function getMaxPoints()
	{
		global $ilUser;
		
		if(strcmp($this->getSubType(), 'scorm2004') == 0)
		{
			$res = ilObjSCORM2004LearningModule::_getMaxScoreForUser($this->getId(), $ilUser->getId());
			return $res;
		}
		else
		{
			return null;
		}
	}
	
	/**
	 * Populate by directory. Add a filename to do a special check for
	 * ILIAS SCORM export files. If the corresponding directory is found
	 * within the passed directory path (i.e. "htlm_<id>") this
	 * subdirectory is used instead.
	 *
	 * @param
	 * @return
	 */
	function populateByDirectoy($a_dir, $a_filename = "")
	{
		/*preg_match("/.*sahs_([0-9]*)\.zip/", $a_filename, $match);
		if (is_dir($a_dir."/sahs_".$match[1]))
		{
			$a_dir = $a_dir."/sahs_".$match[1];
		}*/
		ilUtil::rCopy($a_dir, $this->getDataDirectory());
		ilUtil::renameExecutables($this->getDataDirectory());
	}

	
	/**
	 * Clone scorm object
	 *
	 * @param int target ref_id
	 * @param int copy id
	 */
	public function cloneObject($a_target_id,$a_copy_id = 0)
	{
		global $ilDB, $ilUser, $ilias;

		$new_obj = parent::cloneObject($a_target_id,$a_copy_id);
		$this->cloneMetaData($new_obj);

		// copy properties
		$new_obj->setTitle($this->getTitle());
		$new_obj->setDescription($this->getDescription());
		$new_obj->setSubType($this->getSubType());
		$new_obj->setAPIAdapterName($this->getAPIAdapterName());
		$new_obj->setAPIFunctionsPrefix($this->getAPIFunctionsPrefix());
		$new_obj->setAutoReview($this->getAutoReview());
		$new_obj->setDefaultLessonMode($this->getDefaultLessonMode());
		$new_obj->setEditable($this->getEditable());
		$new_obj->setMaxAttempt($this->getMaxAttempt());
//		$new_obj->getModuleVersion($this->getModuleVersion());	??
		$new_obj->setModuleVersion(1);
		$new_obj->setCreditMode($this->getCreditMode());
		$new_obj->setAssignedGlossary($this->getAssignedGlossary());
		$new_obj->setTries($this->getTries());
		$new_obj->setSession($this->getSession());
		$new_obj->setNoMenu($this->getNoMenu());
		$new_obj->setHideNavig($this->getHideNavig());
		$new_obj->setSequencing($this->getSequencing());
		$new_obj->setInteractions($this->getInteractions());
		$new_obj->setObjectives($this->getObjectives());
		$new_obj->setComments($this->getComments());
		$new_obj->setTime_from_lms($this->getTime_from_lms());
		$new_obj->setDebug($this->getDebug());
		$new_obj->setLocalization($this->getLocalization());
		$new_obj->setSequencingExpertMode($this->getSequencingExpertMode());
		$new_obj->setDebugPw($this->getDebugPw());
		$new_obj->setOpenMode($this->getOpenMode());
		$new_obj->setWidth($this->getWidth());
		$new_obj->setHeight($this->getHeight());
		$new_obj->setAutoContinue($this->getAutoContinue());
		$new_obj->update();

		// set/copy stylesheet
/*		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$style_id = $this->getStyleSheetId();
		if ($style_id > 0 && !ilObjStyleSheet::_lookupStandard($style_id))
		{
			$style_obj = $ilias->obj_factory->getInstanceByObjId($style_id);
			$new_id = $style_obj->ilClone();
			$new_obj->setStyleSheetId($new_id);
			$new_obj->update();
		}*/
		
		// up to this point $new_obj is of type ilobjsahslearning module
		
		// create instance of correct subtype and call forward it to
		// cloneIntoNewObject method
		switch ($this->getSubType())
		{
			case "scorm":
				include_once("./Modules/ScormAicc/classes/class.ilObjSCORMLearningModule.php");
				$source_obj = new ilObjSCORMLearningModule($this->getRefId());
				$new_obj = new ilObjSCORMLearningModule($new_obj->getRefId());
				$source_obj->cloneIntoNewObject($new_obj,$a_target_id,$a_copy_id);
				break;
				
			case "scorm2004":
				include_once("./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php");
				$source_obj = new ilObjSCORM2004LearningModule($this->getRefId());
				$new_obj = new ilObjSCORM2004LearningModule($new_obj->getRefId());
				break;

			case "aicc":
				include_once("./Modules/ScormAicc/classes/class.ilObjAICCLearningModule.php");
				$source_obj = new ilObjAICCLearningModule($this->getRefId());
				$new_obj = new ilObjAICCLearningModule($new_obj->getRefId());
				break;

			case "hacp":
				include_once("./Modules/ScormAicc/classes/class.ilObjHACPLearningModule.php");
				$source_obj = new ilObjHACPLearningModule($this->getRefId());
				$new_obj = new ilObjHACPLearningModule($new_obj->getRefId());
				break;

		}

		// copy data directory
		$new_obj->populateByDirectoy($source_obj->getDataDirectory());

		// read manifest file
		$new_obj->readObject();

		return $new_obj;
	}

}
?>
