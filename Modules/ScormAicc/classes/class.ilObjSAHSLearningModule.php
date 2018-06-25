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
	function __construct($a_id = 0, $a_call_by_reference = true)
	{
		$this->type = "sahs";
		parent::__construct($a_id,$a_call_by_reference);
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
			$this->setAutoReviewChar($lm_rec["auto_review"]);
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
			$this->setFourth_edition(ilUtil::yn2tf($lm_rec["fourth_edition"]));
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
			$this->setAuto_last_visited(ilUtil::yn2tf($lm_rec["auto_last_visited"]));
			$this->setCheck_values(ilUtil::yn2tf($lm_rec["check_values"]));
			$this->setOfflineMode(ilUtil::yn2tf($lm_rec["offline_mode"]));
			$this->setAutoSuspend(ilUtil::yn2tf($lm_rec["auto_suspend"]));
			$this->setIe_force_render(ilUtil::yn2tf($lm_rec["ie_force_render"]));
			$this->setMasteryScore($lm_rec["mastery_score"]);
			$this->setIdSetting($lm_rec["id_setting"]);
			$this->setNameSetting($lm_rec["name_setting"]);
			
			include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
			if (ilObject::_lookupType($this->getStyleSheetId()) != "sty")
			{
				$this->setStyleSheetId(0);
			}
		}
	}

	/**
	* check wether scorm module is online
	*/
	static function _lookupOnline($a_id)
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
	static function getAffectiveLocalization($a_id)
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
	* lookup subtype id (scorm, )
	*
	* @param	int		$a_id		object id
	*/
	static function _lookupSubType($a_obj_id)
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
	* set auto review as true/false for SCORM 1.2
	*/
	function setAutoReview($a_auto_review)
	{
		$this->auto_review = ilUtil::tf2yn($a_auto_review);
	}
	/**
	* get auto review as true/false for SCORM 1.2
	*/
	function getAutoReview()
	{
		return ilUtil::yn2tf($this->auto_review);
	}
	
	/**
	* set auto review as Char for SCORM 2004
	*/
	function setAutoReviewChar($a_auto_review)
	{
		$this->auto_review = $a_auto_review;
	}
	/**
	* get auto review as Char for SCORM 2004
	*/
	function getAutoReviewChar()
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
	* get module version
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
	* sessionDisabled for SCORM 2004
	*/
	function getSessionDeactivated()
	{
		global $ilSetting;
		$lm_set = new ilSetting("lm");
		if ($lm_set->get("scorm_without_session") == "1") return true;
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
	* force Internet Explorer to render again after some Milliseconds - useful for learning Modules with a lot of iframesor frames and IE >=10
	*/
	function getIe_force_render()
	{
		return $this->ie_force_render;
	}

	function setIe_force_render($a_ie_force_render)
	{
		$this->ie_force_render = $a_ie_force_render;
	}

	/**
	* SCORM 2004 4th edition features
	*/
	function getFourth_Edition()
	{
		return $this->fourth_edition;
	}

	function setFourth_edition($a_fourth_edition)
	{
		$this->fourth_edition = $a_fourth_edition;
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
	* check_values
	*/
	function getCheck_values()
	{
		return $this->check_values;
	}

	function setCheck_values($a_check_values)
	{
		$this->check_values = $a_check_values;
	}

	/**
	* offlineMode
	*/
	function getOfflineMode()
	{
		return $this->offline_mode;
	}

	function setOfflineMode($a_offline_mode)
	{
		$this->offline_mode = $a_offline_mode;
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
	* auto_last_visited
	*/
	function getAuto_last_visited()
	{
		return $this->auto_last_visited;
	}

	function setAuto_last_visited($a_auto_last_visited)
	{
		$this->auto_last_visited = $a_auto_last_visited;
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
	* get auto continue
	*/
	function setAutoSuspend($a_auto_suspend)
	{
		$this->auto_suspend = $a_auto_suspend;
	}
	/**
	* set auto continue
	*/
	function getAutoSuspend()
	{
		return $this->auto_suspend;
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
	* get mastery_score
	*/
	function getMasteryScore()
	{
		return $this->mastery_score;
	}

	/**
	* set mastery_score 
	*/
	function setMasteryScore($a_mastery_score)
	{
		$this->mastery_score = $a_mastery_score;
	}
	
	/**
	* check mastery_score / min_normalized_measure of SCOs (SCORM 1.2) / objectives (SCORM 2004)
	*/
	function checkMasteryScoreValues()
	{
		global $ilDB;
		$s_result = "";
		$a_result = array();
		$type = $this->_lookupSubType( $this->getID() );

		if ($type == "scorm2004") {
			$set = $ilDB->query("SELECT minnormalmeasure FROM cp_objective, cp_node".
				" WHERE satisfiedbymeasure=1 AND minnormalmeasure is not null AND cp_objective.cp_node_id=cp_node.cp_node_id AND".
				" slm_id = ".$ilDB->quote($this->getID(), "integer"));
			while ($rec  = $ilDB->fetchAssoc($set)) {
				$tmpval = $rec["minnormalmeasure"]*100;
				if (!in_array($tmpval,$a_result)) $a_result[] = $tmpval;
			}
		} else {
			$set = $ilDB->query("SELECT masteryscore FROM sc_item,scorm_object".
				" WHERE sc_item.masteryscore is not null AND sc_item.obj_id=scorm_object.obj_id AND".
				" slm_id = ".$ilDB->quote($this->getID(), "integer"));
			while ($rec  = $ilDB->fetchAssoc($set)) {
				if (!in_array($rec["masteryscore"],$a_result)) $a_result[] = $rec["masteryscore"];
			}
		}
		$s_result = implode(", ",$a_result);
		$this->mastery_score_values = $s_result;
	}

	/**
	* get mastery_score_values
	*/
	function getMasteryScoreValues()
	{
		return $this->mastery_score_values;
	}
	
	/**
	* update values for mastery_score / min_normalized_measure in database - not requested
	*/
	/*
	function updateMasteryScoreValues()
	{
		global $ilDB;
		$s_mastery_score = $this->getMasteryScore();
		if ($s_mastery_score != "" && is_numeric($s_mastery_score)) {
			$i_mastery_score = round(intval($s_mastery_score,10));
			$type = $this->_lookupSubType( $this->getID() );

			if ($type == "scorm2004") {
				if ($i_mastery_score > 100) $i_mastery_score = 100;
				$i_mastery_score = $i_mastery_score/100;
				$statement = $ilDB->manipulateF(
					'UPDATE cp_objective,cp_node SET minnormalmeasure = %s 
					WHERE satisfiedbymeasure=1 AND minnormalmeasure is not null AND cp_objective.cp_node_id=cp_node.cp_node_id AND slm_id = %s',
					array('text','integer'),
					array($i_mastery_score,$this->getID())
				);
			} else {
				if ($i_mastery_score > 127) $i_mastery_score = 127;
				$statement = $ilDB->manipulateF(
					'UPDATE sc_item,scorm_object SET sc_item.masteryscore = %s 
					WHERE sc_item.masteryscore is not null AND sc_item.obj_id=scorm_object.obj_id AND slm_id = %s',
					array('integer','integer'),
					array($i_mastery_score,$this->getID())
				);
			}
		}
	}
	*/
	
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
	* get id_setting
	*/
	function getIdSetting()
	{
		return $this->id_setting;
	}

	/**
	* set id_setting 
	*/
	function setIdSetting($a_id_setting)
	{
		$this->id_setting = $a_id_setting;
	}

	/**
	* get name_setting
	*/
	function getNameSetting()
	{
		return $this->name_setting;
	}

	/**
	* set name_setting 
	*/
	function setNameSetting($a_name_setting)
	{
		$this->name_setting = $a_name_setting;
	}

	


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
		
		$s_mastery_score = $this->getMasteryScore();
		if ($s_mastery_score == "") $s_mastery_score = null;

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
				fourth_edition =%s,
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
				auto_continue = %s,
				auto_last_visited = %s,
				check_values = %s,
				offline_mode = %s,
				auto_suspend = %s,
				ie_force_render = %s,
				mastery_score = %s,
				id_setting = %s,
				name_setting = %s
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
				'text',
				'integer',
				'text',
				'integer',
				'integer',
				'integer',
				'text',
				'text',
				'text',
				'text',
				'text',
				'text',
				'integer',
				'integer',
				'integer',
				'integer'
				), 
		array(	ilUtil::tf2yn($this->getOnline()),
				$this->getAPIAdapterName(),
				$this->getAPIFunctionsPrefix(),
				$this->getAutoReviewChar(),
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
				ilUtil::tf2yn($this->getFourth_edition()),
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
				ilUtil::tf2yn($this->getAuto_last_visited()),
				ilUtil::tf2yn($this->getCheck_values()),
				ilUtil::tf2yn($this->getOfflineMode()),
				ilUtil::tf2yn($this->getAutoSuspend()),
				ilUtil::tf2yn($this->getIe_force_render()),
				$s_mastery_score,
				$this->getIdSetting(),
				$this->getNameSetting(),
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
	 * Get SCORM modules that assign a certain glossary
	 *
	 * @param
	 * @return
	 */
	static function lookupAssignedGlossary($a_slm_id)
	{
		global $ilDB;
	
		$set = $ilDB->query("SELECT DISTINCT glossary FROM sahs_lm WHERE ".
			" id = ".$ilDB->quote($a_slm_id, "integer"));
		$rec  = $ilDB->fetchAssoc($set);
		$glo_id = $rec["glossary"];
		if (ilObject::_lookupType($glo_id) == "glo")
		{
			return $glo_id;
		}
		return 0;
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
					$sc_object = ilSCORMObject::_getInstance($item["obj_id"], $this->getId());
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

		$q_log = "DELETE FROM sahs_user WHERE obj_id = ".$ilDB->quote($this->getId());
		$ilLog->write("SAHS Delete(SAHSLM): ".$q_log);

		$ilDB->manipulateF('DELETE FROM sahs_user WHERE obj_id = %s',
			array('integer'), array($this->getId()));

		// always call parent delete function at the end!!
		return true;
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
	public function cloneObject($a_target_id,$a_copy_id = 0, $a_omit_tree = false)
	{
		global $ilDB, $ilUser, $ilias, $lng;

		$new_obj = parent::cloneObject($a_target_id,$a_copy_id, $a_omit_tree);
		$this->cloneMetaData($new_obj);

		//copy online status if object is not the root copy object
		$cp_options = ilCopyWizardOptions::_getInstance($a_copy_id);

		if(!$cp_options->isRootNode($this->getRefId()))
		{
			$new_obj->setOnline($this->getOnline());
		}

		// copy properties
		$new_obj->setTitle($this->getTitle() . ' ' . $lng->txt('copy_of_suffix'));
		$new_obj->setDescription($this->getDescription());
		$new_obj->setSubType($this->getSubType());
		$new_obj->setAPIAdapterName($this->getAPIAdapterName());
		$new_obj->setAPIFunctionsPrefix($this->getAPIFunctionsPrefix());
		$new_obj->setAutoReviewChar($this->getAutoReviewChar());
		$new_obj->setDefaultLessonMode($this->getDefaultLessonMode());
		$new_obj->setEditable($this->getEditable());
		$new_obj->setMaxAttempt($this->getMaxAttempt());
		$new_obj->setModuleVersion($this->getModuleVersion());
		$new_obj->setModuleVersion(1);
		$new_obj->setCreditMode($this->getCreditMode());
		$new_obj->setAssignedGlossary($this->getAssignedGlossary());
		$new_obj->setTries($this->getTries());
		$new_obj->setSession($this->getSession());
		$new_obj->setNoMenu($this->getNoMenu());
		$new_obj->setHideNavig($this->getHideNavig());
		$new_obj->setFourth_edition($this->getFourth_edition());
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
		$new_obj->setAuto_last_visited($this->getAuto_last_visited());
		$new_obj->setCheck_values($this->getCheck_values());
		$new_obj->setOfflineMode($this->getOfflineMode());
		$new_obj->setAutoSuspend($this->getAutoSuspend());
		$new_obj->setIe_force_render($this->getIe_force_render());
		$new_obj->setStyleSheetId($this->getStyleSheetId());
		$new_obj->update();


		// set/copy stylesheet
/*		include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
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
				break;
				
			case "scorm2004":
				include_once("./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php");
				$source_obj = new ilObjSCORM2004LearningModule($this->getRefId());
				$new_obj = new ilObjSCORM2004LearningModule($new_obj->getRefId());
				break;
		}

		// copy data directory
		$new_obj->populateByDirectoy($source_obj->getDataDirectory());

		// copy authored content ...
		if ($new_obj->getEditable())
		{
			$source_obj->copyAuthoredContent($new_obj);
		}
		else 
		{
			// ... or read manifest file
			$new_obj->readObject();
		}
		
		return $new_obj;
	}
	
	public function zipLmForOfflineMode()
	{
		$lmDir=ilUtil::getWebspaceDir("filesystem")."/lm_data/lm_".$this->getId();
		$zipFile=ilUtil::getDataDir()."/lm_data/lm_".$this->getId();
		return ilUtil::zip($lmDir, $zipFile, true);
	}

	/**
	* Get cmi.core.student_id / cmi.learner_id for API
	*/
	public function getApiStudentId() {
		global $ilias;
		$idSetting = $this->getIdSetting();
		$studentId = $ilias->account->getId();
		if ($idSetting%2 == 1) $studentId = $ilias->account->getLogin();
		if ($idSetting > 3) $studentId .= '_o_'.$this->getId();
		else if ($idSetting > 1) $studentId .= '_r_'.$_GET["ref_id"];
		return $studentId;
	}

	/**
	* Get cmi.core.student_name / cmi.learner_name for API
	* note: 'lastname, firstname' is required for SCORM 1.2; 9 = no name to hide student_name for external content
	*/
	public function getApiStudentName() {
		global $ilias, $lng;
		$studentName = " ";
		switch ($this->getNameSetting())
		{
			case 0:
				$studentName = $ilias->account->getLastname().', '.$ilias->account->getFirstname();
				break;
			case 1:
				$studentName = $ilias->account->getFirstname().' '.$ilias->account->getLastname();
				break;
			case 2:
				$studentName = $ilias->account->getFullname();
				break;
			case 3:
				switch($ilias->account->getGender()) {
					case 'f':
						$studentName = $lng->txt('salutation_f');
						break;

					case 'm':
						$studentName = $lng->txt('salutation_m');
						break;

					default:
						$studentName = $lng->txt('salutation');
				}
				$studentName .= ' '.$ilias->account->getLastname();
				break;
			case 4:
				$studentName = $ilias->account->getFirstname();
				break;
		}
		return $studentName;
	}

	/**
	* get button for view
	*/
	public function getViewButton() {
		$setUrl = "ilias.php?baseClass=ilSAHSPresentationGUI&amp;ref_id=".$this->getRefID();
		// $setUrl = $this->getLinkTargetByClass("ilsahspresentationgui", "")."&amp;ref_id=".$this->getRefID();
		$setTarget = "ilContObj".$this->getId();
		$om = $this->getOpenMode();
		$width = $this->getWidth();
		$height = $this->getHeight();
		if ( ($om == 5 || $om == 1) && $width > 0 && $height > 0) $om++;
		if ($om != 0) {
			$setUrl = "javascript:void(0); onclick=startSAHS('".$setUrl."','ilContObj".$this->getId()."',".$om.",".$width.",".$height.");";
			$setTarget = "";
		}
		include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
		$button = ilLinkButton::getInstance();
		$button->setCaption("view");
		$button->setPrimary(true);
		$button->setUrl($setUrl);
		$button->setTarget($setTarget);
		return $button;
	}
}
?>
