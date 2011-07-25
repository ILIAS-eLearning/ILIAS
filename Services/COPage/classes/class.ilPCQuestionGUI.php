<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/COPage/classes/class.ilPageContentGUI.php";
include_once "./Services/COPage/classes/class.ilPCQuestion.php";

/**
* Class ilPCQuestionGUI
*
* Adapter User Interface class for assessment questions
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilPCQuestionGUI: ilQuestionEditGUI
*
* @ingroup ServicesCOPage
*/
class ilPCQuestionGUI extends ilPageContentGUI
{
	var $page_config = null;

	/**
	* Constructor
	* @access	public
	*/
	function ilPCQuestionGUI(&$a_pg_obj, &$a_content_obj, $a_hier_id, $a_pc_id = "")
	{
		global $ilCtrl;
		$this->scormlmid = $a_pg_obj->parent_id;
		parent::ilPageContentGUI($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
		$ilCtrl->saveParameter($this, array("qpool_ref_id"));
	}

	/**
	 * Set Page Config
	 *
	 * @param	object	Page Config
	 */
	function setPageConfig($a_val)
	{
		$this->page_config = $a_val;
	}

	/**
	 * Get Page Config
	 *
	 * @return	object	Page Config
	 */
	function getPageConfig()
	{
		return $this->page_config;
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl;
		
		// get current command
		$cmd = $ilCtrl->getCmd();
		$next_class = $ilCtrl->getNextClass($this);

		$q_type = ($_POST["q_type"] != "")
			? $_POST["q_type"]
			: $_GET["q_type"];
			
		
		
		switch($next_class)
		{
			
			case "ilquestioneditgui":
				include_once("./Modules/TestQuestionPool/classes/class.ilQuestionEditGUI.php");

				$edit_gui = new ilQuestionEditGUI();
				if ($q_type != "")
				{
					$edit_gui->setQuestionType($q_type);
				}
				$edit_gui->setPageConfig($this->getPageConfig());
				//$edit_gui->setPoolRefId($qpool_ref_id);		
				$this->setTabs();
				$edit_gui->addNewIdListener($this, "setNewQuestionId");
				$edit_gui->setSelfAssessmentEditingMode(true);
				$ret = $ilCtrl->forwardCommand($edit_gui);
				$this->tpl->setContent($ret);
				break;
			
			default:
				//set tabs
				if ($cmd != "insert")
				{
					$this->setTabs();
				}
				else if ($_GET["subCmd"] != "")
				{
					$cmd = $_GET["subCmd"];
				}
				
				$ret = $this->$cmd();
		}
		
		
		
		return $ret;
	}

	/**
	* Set Self Assessment Mode.
	*
	* @param	boolean	$a_selfassessmentmode	Self Assessment Mode
	*/
	function setSelfAssessmentMode($a_selfassessmentmode)
	{
		$this->selfassessmentmode = $a_selfassessmentmode;
	}

	/**
	* Get Self Assessment Mode.
	*
	* @return	boolean	Self Assessment Mode
	*/
	function getSelfAssessmentMode()
	{
		return $this->selfassessmentmode;
	}

	/**
	 * Set insert tabs
	 *
	 * @param string $a_active active tab id
	 */
	function setInsertTabs($a_active)
	{
		global $ilTabs, $ilCtrl, $lng;
		
		// new question
		$ilTabs->addSubTab("new_question",
			$lng->txt("cont_new_question"),
			$ilCtrl->getLinkTarget($this, "insert"));
		
		// copy from pool
		$ilCtrl->setParameter($this, "subCmd", "insertFromPool");
		$ilTabs->addSubTab("copy_question",
			$lng->txt("cont_copy_question_from_pool"),
			$ilCtrl->getLinkTarget($this, "insert"));
		
		$ilTabs->activateSubTab($a_active);
		
		$ilCtrl->setParameter($this, "subCmd", "");
	}
	
	/**
	 * Insert new question form
	 */
	function insert($a_mode = "create")
	{
		global $ilUser, $lng, $ilCtrl;
		
		$this->setInsertTabs("new_question");

		$this->displayValidationError();
		
		// get all question types (@todo: we have to check, whether they are
		// suitable for self assessment or not)
		include_once("./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php");
		$all_types = ilObjQuestionPool::_getSelfAssessmentQuestionTypes();
		$options = array();
		$all_types = ilUtil::sortArray($all_types, "order", "asc", true, true);

		foreach ($all_types as $k => $v)
		{
			$options[$v["type_tag"]] = $k;
		}
		
		// new table form (input of rows and columns)
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form_gui = new ilPropertyFormGUI();
		$this->form_gui->setFormAction($ilCtrl->getFormAction($this));
		$this->form_gui->setTitle($lng->txt("cont_ed_insert_pcqst"));
		
		// Select Question Type
		$qtype_input = new ilSelectInputGUI($lng->txt("cont_question_type"), "q_type");
		$qtype_input->setOptions($options);
		$qtype_input->setRequired(true);
		$this->form_gui->addItem($qtype_input);
		
		// Select Question Pool
/*
		include_once("./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php");
		$qpools = ilObjQuestionPool::_getAvailableQuestionpools(false, false, false, true, false, "write");

		if (count($qpools) > 0)
		{
			$pool_options = array();
			foreach ($qpools as $key => $value)
			{
				$pool_options[$key] = $value["title"];
			}
			$pool_input = new ilSelectInputGUI($lng->txt("cont_question_pool"), "qpool_ref_id");
			$pool_input->setOptions($pool_options);
			$pool_input->setRequired(true);
			$this->form_gui->addItem($pool_input);
		}
		else
		{
			$pool_input = new ilTextInputGUI($lng->txt("cont_question_pool"), "qpool_title");
			$pool_input->setRequired(true);
			$this->form_gui->addItem($pool_input);
		}
*/
		if ($a_mode == "edit_empty")
		{
			$this->form_gui->addCommandButton("edit", $lng->txt("save"));
		}
		else
		{
			$this->form_gui->addCommandButton("create_pcqst", $lng->txt("save"));
			$this->form_gui->addCommandButton("cancelCreate", $lng->txt("cancel"));
		}

		$this->tpl->setContent($this->form_gui->getHTML());
	}

	
	/**
	* Create new question
	*/
	function create()
	{
		global	$lng, $ilCtrl, $ilTabs;

		$ilTabs->setTabActive('question');
		
		$this->content_obj = new ilPCQuestion($this->dom);
		$this->content_obj->create($this->pg_obj, $this->hier_id);
		
		$this->updated = $this->pg_obj->update();

		if ($this->updated)
		{
			// create question pool, if necessary
/*			if ($_POST["qpool_ref_id"] <= 0)
			{
				$pool_ref_id = $this->createQuestionPool($_POST["qpool_title"]);
			}
			else
			{
				$pool_ref_id = $_POST["qpool_ref_id"];
			}*/
			
			$this->pg_obj->stripHierIDs();
			$this->pg_obj->addHierIDs();
			$hier_id = $this->content_obj->lookupHierId();
			$ilCtrl->setParameter($this, "q_type", $_POST["q_type"]);
//			$ilCtrl->setParameter($this, "qpool_ref_id", $pool_ref_id);
			//$ilCtrl->setParameter($this, "hier_id", $hier_id);
			$ilCtrl->setParameter($this, "hier_id", $this->content_obj->readHierId());
			$ilCtrl->setParameter($this, "pc_id", $this->content_obj->readPCId());

			$ilCtrl->redirect($this, "edit");
		}

		$this->insert();
	}
	
	/**
	* Set new question id
	*/
	function setNewQuestionId($a_par)
	{
		if ($a_par["new_id"] > 0)
		{
			$this->content_obj->setQuestionReference("il__qst_".$a_par["new_id"]);
			$this->pg_obj->update();
		}
	}
	
	/**
	* edit question
	*/
	function edit()
	{
		global $ilCtrl, $ilTabs;
		
		$ilTabs->setTabActive('question');
		
		
		if ($this->getSelfAssessmentMode())		// behaviour in content pages, e.g. scorm
		{
			$q_ref = $this->content_obj->getQuestionReference();
			
			if ($q_ref != "")
			{
				$inst_id = ilInternalLink::_extractInstOfTarget($q_ref);
				if (!($inst_id > 0))
				{
					$q_id = ilInternalLink::_extractObjIdOfTarget($q_ref);
				}
			}
			
			$q_type = ($_POST["q_type"] != "")
				? $_POST["q_type"]
				: $_GET["q_type"];
			$ilCtrl->setParameter($this, "q_type", $q_type);

// @todo: check access stuff

			include_once("./Modules/TestQuestionPool/classes/class.ilQuestionEditGUI.php");
			include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");
			include_once("./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php");
			
			$ilCtrl->setCmdClass("ilquestioneditgui");
			$ilCtrl->setCmd("editQuestion");
			$edit_gui = new ilQuestionEditGUI();

			if ($q_id > 0)
			{
				$edit_gui->setQuestionId($q_id);
//				$edit_gui->setPoolObjId(assQuestion::_lookupQPoolId($q_id));
$edit_gui->setPoolObjId(0);
			}
			else
			{
				if ($_GET["qpool_ref_id"] > 0)
				{
					$edit_gui->setPoolRefId($_GET["qpool_ref_id"]);
$edit_gui->setPoolRefId(0);
				}
				//set default tries
				$edit_gui->setDefaultNrOfTries(ilObjSAHSLearningModule::_getTries($this->scormlmid));
			}

			if ($q_id == "" && $q_type == "")
			{
				return $this->insert("edit_empty");
			}

			$edit_gui->setQuestionType($q_type);
			$edit_gui->setSelfAssessmentEditingMode(true);
			$edit_gui->setPageConfig($this->getPageConfig());
			$ret = $ilCtrl->forwardCommand($edit_gui);
			$this->tpl->setContent($ret);
			return $ret;
		}
		else	// behaviour in question pool
		{
			require_once("./Modules/TestQuestionPool/classes/class.assQuestionGUI.php");
			$q_gui =& assQuestionGUI::_getQuestionGUI("", $_GET["q_id"]);
			$this->ctrl->redirectByClass(array("ilobjquestionpoolgui", get_class($q_gui)), "editQuestion");
		}
	}

	function feedback() 
	{
		global $ilCtrl, $ilTabs;
		
		
		
		include_once("./Modules/TestQuestionPool/classes/class.ilQuestionEditGUI.php");
		include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");
		
		$ilTabs->setTabActive('feedback');
        
		$q_ref = $this->content_obj->getQuestionReference();
		
		if ($q_ref != "")
		{
			$inst_id = ilInternalLink::_extractInstOfTarget($q_ref);
			if (!($inst_id > 0))
			{
				$q_id = ilInternalLink::_extractObjIdOfTarget($q_ref);
			}
		}
		
		$ilCtrl->setCmdClass("ilquestioneditgui");
		$ilCtrl->setCmd("feedback");
		$edit_gui = new ilQuestionEditGUI();
		if ($q_id > 0)
		{
			$edit_gui->setQuestionId($q_id);
		}	
//		$edit_gui->setQuestionType("assSingleChoice");
		$edit_gui->setSelfAssessmentEditingMode(true);
		$edit_gui->setPageConfig($this->getPageConfig());
		$ret = $ilCtrl->forwardCommand($edit_gui);
		$this->tpl->setContent($ret);
		return $ret;
		
	}
	/**
	* Creates a new questionpool and returns the reference id
	*
	* Creates a new questionpool and returns the reference id
	*
	* @return integer Reference id of the newly created questionpool
	* @access	public
	*/
	function createQuestionPool($name = "Dummy")
	{
		global $tree;
		$parent_ref = $tree->getParentId($_GET["ref_id"]);
		include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
		$qpl = new ilObjQuestionPool();
		$qpl->setType("qpl");
		$qpl->setTitle($name);
		$qpl->setDescription("");
		$qpl->create();
		$qpl->createReference();
		$qpl->putInTree($parent_ref);
		$qpl->setPermissions($parent_ref);
		$qpl->setOnline(1); // must be online to be available
		$qpl->saveToDb();
		return $qpl->getRefId();
	}
	
	/**
	* Set tabs
	*/
	function setTabs()
	{
		global $ilTabs, $ilCtrl, $lng;
		include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");
		
		if ($this->content_obj!="") {
			$q_ref = $this->content_obj->getQuestionReference();
		}
		
		if ($q_ref != "")
		{
			$inst_id = ilInternalLink::_extractInstOfTarget($q_ref);
			if (!($inst_id > 0))
			{
				$q_id = ilInternalLink::_extractObjIdOfTarget($q_ref);
			}
		}
			
		$ilTabs->addTarget("question",
			$ilCtrl->getLinkTarget($this, "edit"), array("editQuestion", "save", "cancel", "addSuggestedSolution",
				"cancelExplorer", "linkChilds", "removeSuggestedSolution",
				"addPair", "addTerm", "delete", "deleteTerms", "editMode", "upload",
				"saveEdit","uploadingImage", "uploadingImagemap", "addArea",
				"deletearea", "saveShape", "back", "saveEdit", "changeGapType","createGaps","addItem","addYesNo", "addTrueFalse",
				"toggleGraphicalAnswers", "setMediaMode"),
			"");
		
		if ($q_id > 0) {
			$q_obj = new assQuestion();
			
			if ($q_obj->_getQuestionType($q_id)!= "assTextQuestion") 
			{
				$ilTabs->addTarget("feedback",
					$ilCtrl->getLinkTarget($this, "feedback"), array("feedback","saveFeedback"),
				"");
			}	
		}
	}

	////
	//// Get question from pool
	////
	
	/**
	 * Insert question from ppol
	 */
	function insertFromPool()
	{
		global $ilCtrl, $ilAccess, $ilTabs, $tpl, $lng, $ilToolbar;
//var_dump($_SESSION["cont_qst_pool"]);
		if ($_SESSION["cont_qst_pool"] != "" &&
			$ilAccess->checkAccess("write", "", $_SESSION["cont_qst_pool"])
			&& ilObject::_lookupType(ilObject::_lookupObjId($_SESSION["cont_qst_pool"])) == "qpl")
		{
			$this->listPoolQuestions();
		}
		else
		{
			$this->poolSelection();
		}
	}

	/**
	 * Pool selection
	 *
	 * @param
	 * @return
	 */
	function poolSelection()
	{
		global $ilCtrl, $tree, $tpl, $ilTabs;
		
		$this->setInsertTabs("copy_question");

		include_once "./Services/COPage/classes/class.ilPoolSelectorGUI.php";

		$exp = new ilPoolSelectorGUI($ilCtrl->getLinkTarget($this, "insert"));

		if ($_GET["expand"] == "")
		{
			$expanded = $tree->readRootId();
		}
		else
		{
			$expanded = $_GET["expand"];
		}
		$exp->setExpand($expanded);

		$exp->setTargetGet("sel_id");
		$ilCtrl->setParameter($this, "target_type", $a_type);
		$ilCtrl->setParameter($this, "subCmd", "poolSelection");
		$exp->setParamsGet($this->ctrl->getParameterArray($this, "insert"));
		
		// filter
		$exp->setFiltered(true);
		$exp->setFilterMode(IL_FM_POSITIVE);
		$exp->addFilter("root");
		$exp->addFilter("cat");
		$exp->addFilter("grp");
		$exp->addFilter("fold");
		$exp->addFilter("crs");
		$exp->addFilter("qpl");
		$exp->setContentGUIClass("ilpcquestiongui");
		$exp->setSelectableTypes(array('qpl'));

		$exp->setOutput(0);

		$tpl->setContent($exp->getOutput());
	}
	
	/**
	 * Select concrete question pool
	 */
	function selectPool()
	{
		global $ilCtrl;
		
		$_SESSION["cont_qst_pool"] = $_GET["pool_ref_id"];
		$ilCtrl->setParameter($this, "subCmd", "insertFromPool");
		$ilCtrl->redirect($this, "insert");
	}

	/**
	 * List questions of pool
	 *
	 * @param
	 * @return
	 */
	function listPoolQuestions()
	{
		global $ilToolbar, $tpl, $ilCtrl, $lng;
		
		$ilCtrl->setParameter($this, "subCmd", "poolSelection");
		$ilToolbar->addButton(
			$lng->txt("cont_select_other_qpool"),
			$ilCtrl->getLinkTarget($this, "insert"));
		$ilCtrl->setParameter($this, "subCmd", "");

		$this->setInsertTabs("copy_question");

		include_once "./Services/COPage/classes/class.ilCopySelfAssQuestionTableGUI.php";
		
		$ilCtrl->setParameter($this, "subCmd", "listPoolQuestions");
		$table_gui = new ilCopySelfAssQuestionTableGUI($this, 'insert',
			$_SESSION["cont_qst_pool"]);
/*
		$arrFilter = array();
		foreach ($table_gui->getFilterItems() as $item)
		{
			if ($item->getValue() !== false)
			{
				$arrFilter[$item->getPostVar()] = $item->getValue();
			}
		}
		$data = $this->object->getAvailableQuestions($arrFilter, 1);
		$table_gui->setData($data);
*/
		$tpl->setContent($table_gui->getHTML());
	}
	
}
?>
