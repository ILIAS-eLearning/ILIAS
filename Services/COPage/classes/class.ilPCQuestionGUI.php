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
				//$edit_gui->setPoolRefId($qpool_ref_id);		
				$this->setTabs();
				$edit_gui->addNewIdListener($this, "setNewQuestionId");
				$edit_gui->setSelfAssessmentEditingMode(true);
				$ret = $ilCtrl->forwardCommand($edit_gui);
				$this->tpl->setContent($ret);
				break;
			
			default:
				//set tabs
				if ($cmd != "insert") {
					$this->setTabs();
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
	* Insert new question form
	*/
	function insert($a_mode = "create")
	{
		global $ilUser, $lng, $ilCtrl;

		$this->displayValidationError();
		
		// get all question types (@todo: we have to check, whether they are
		// suitable for self assessment or not)
		include_once("./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php");
		$all_types = ilObjQuestionPool::_getSelfAssessmentQuestionTypes();
		$options = array();
		$all_types = ilUtil::sortArray($all_types, "question_type_id", "asc", true, true);

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

}
?>
