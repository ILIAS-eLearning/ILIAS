<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilSurveyEditorGUI
*
* @author	Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version  $Id: class.ilObjSurveyGUI.php 43670 2013-07-26 08:41:31Z jluetzen $
*
* @ilCtrl_Calls ilSurveyEditorGUI: SurveyMultipleChoiceQuestionGUI, SurveyMetricQuestionGUI
* @ilCtrl_Calls ilSurveyEditorGUI: SurveySingleChoiceQuestionGUI, SurveyTextQuestionGUI
* @ilCtrl_Calls ilSurveyEditorGUI: SurveyMatrixQuestionGUI, ilSurveyPageGUI
*
* @ingroup ModulesSurvey
*/
class ilSurveyEditorGUI
{
	protected $parent_gui; // [ilObjSurveyGUI]
	protected $object; // [ilObjSurvey]
	
	public function __construct(ilObjSurveyGUI $a_parent_gui)
	{
		global $ilCtrl, $lng, $tpl;
		
		$this->parent_gui = $a_parent_gui;
		$this->object = $this->parent_gui->object;
		
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->tpl = $tpl;
		
		$this->ctrl->saveParameter($this, array("pgov", "pgov_pos"));
	}
	
	public function executeCommand()
	{				
		global $ilTabs;
		
		$cmd = $this->ctrl->getCmd("questions");
									
		if($_REQUEST["pgov"])
		{
			if($cmd == "questions")
			{
				$this->ctrl->setCmdClass("ilsurveypagegui");
				$this->ctrl->setCmd("renderpage");
			}
			else if($cmd == "confirmRemoveQuestions")
			{
				// #14324
				$this->ctrl->setCmdClass("ilsurveypagegui");
				$this->ctrl->setCmd("confirmRemoveQuestions");
			}
		}						
		
		$next_class = $this->ctrl->getNextClass($this);	
		switch($next_class)
		{
			case 'ilsurveypagegui':
				$this->questionsSubtabs("page");
				include_once './Modules/Survey/classes/class.ilSurveyPageGUI.php';
				$pg = new ilSurveyPageGUI($this->object, $this);
				$this->ctrl->forwardCommand($pg);
				break;
			
			default:	
				// question gui
				if(stristr($next_class, "questiongui"))
				{
					$ilTabs->clearTargets();
					$this->ctrl->saveParameter($this, array("new_for_survey"));
					
					include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestionGUI.php";
					$q_gui = SurveyQuestionGUI::_getQuestionGUI(null, $_REQUEST["q_id"]);
					if (is_object($q_gui->object))
					{
						global $ilHelp;
						$ilHelp->setScreenIdComponent("spl_qt".$q_gui->object->getQuestionTypeId());
					}
					// $q_gui->object->setObjId($this->object->getId());
					$q_gui->setBackUrl($this->ctrl->getLinkTarget($this, "questions"));
					$q_gui->setQuestionTabs();									
					$this->ctrl->forwardCommand($q_gui);
					
					if(!(int)$_REQUEST["new_for_survey"])
					{
						// not on create
						$this->tpl->setTitle($this->lng->txt("question").": ".$q_gui->object->getTitle());	
					}
				}
				else
				{
					$cmd .= "Object";
					$this->$cmd();
				}
				break;
		}			
	}

	protected function questionsSubtabs($a_cmd)
	{
		global $ilTabs;		
		
		if($a_cmd == "questions" && $_REQUEST["pgov"])
		{
			$a_cmd = "page";
		}
		
		$hidden_tabs = array();
		$template = $this->object->getTemplate();
		if($template)
		{
			include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
			$template = new ilSettingsTemplate($template);
			$hidden_tabs = $template->getHiddenTabs();
		}

		$ilTabs->addSubTab("page", 
			$this->lng->txt("survey_per_page_view"),
			$this->ctrl->getLinkTargetByClass("ilsurveypagegui", "renderPage"));

		if(!in_array("survey_question_editor", $hidden_tabs))
		{
			$this->ctrl->setParameter($this, "pgov", "");
			$ilTabs->addSubTab("questions",
				$this->lng->txt("survey_question_editor"), 
				$this->ctrl->getLinkTarget($this, "questions"));
			$this->ctrl->setParameter($this, "pgov", $_REQUEST["pgov"]);
		}

		$ilTabs->addSubTab("print",
			$this->lng->txt("print_view"), 
			$this->ctrl->getLinkTarget($this, "printView"));

		if($this->object->getSurveyPages())
		{
			if($a_cmd == "page")
			{
				$this->ctrl->setParameterByClass("ilsurveyexecutiongui", "pgov", max(1, $_REQUEST["pg"]));
			}
			$this->ctrl->setParameterByClass("ilsurveyexecutiongui", "prvw", 1);
			$ilTabs->addSubTab("preview",
				$this->lng->txt("preview"), 
				$this->ctrl->getLinkTargetByClass(array("ilobjsurveygui", "ilsurveyexecutiongui"), "preview"));
		}
		
		$ilTabs->activateSubTab($a_cmd);
	}
				
	
	// 
	// QUESTIONS BROWSER INCL. MULTI-ACTIONS	
	// 
	
	public function questionsObject() 
	{
		global $ilToolbar, $ilUser;
		
		
		// insert new questions?
		if ($_GET["new_id"] > 0)
		{
			// add a question to the survey previous created in a questionpool
			$existing = $this->object->getExistingQuestions();
			if (!in_array($_GET["new_id"], $existing))
			{
				$inserted = $this->object->insertQuestion($_GET["new_id"]);
				if (!$inserted)
				{
					ilUtil::sendFailure($this->lng->txt("survey_error_insert_incomplete_question"));
				}
			}
		}
		
		
		$this->questionsSubtabs("questions");

		$hasDatasets = $this->object->_hasDatasets($this->object->getSurveyId());
		$read_only = $hasDatasets;
		

		// toolbar

		if (!$read_only)
		{			
			$qtypes = array();
			include_once "./Modules/SurveyQuestionPool/classes/class.ilObjSurveyQuestionPool.php";
			foreach (ilObjSurveyQuestionPool::_getQuestiontypes() as $translation => $data)
			{
				$qtypes[$data["type_tag"]] = $translation;
			}

			$ilToolbar->setFormAction($this->ctrl->getFormAction($this));
			include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
			$types = new ilSelectInputGUI($this->lng->txt("create_new"), "sel_question_types");
			$types->setOptions($qtypes);
			$ilToolbar->addInputItem($types, "");
			
			
			include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
			include_once "Services/UIComponent/Button/classes/class.ilSubmitButton.php";
			
			$button = ilSubmitButton::getInstance();
			$button->setCaption("svy_create_question");								
			$button->setCommand("createQuestion");
			$ilToolbar->addButtonInstance($button);		
			
			if($this->object->isPoolActive())
			{
				$ilToolbar->addSeparator();

				$cmd = ($ilUser->getPref('svy_insert_type') == 1 || 
					strlen($ilUser->getPref('svy_insert_type')) == 0) 
					? 'browseForQuestions' 
					: 'browseForQuestionblocks';
								
				$button = ilLinkButton::getInstance();
				$button->setCaption("browse_for_questions");								
				$button->setUrl($this->ctrl->getLinkTarget($this, $cmd));										
				$ilToolbar->addButtonInstance($button);						
			}

			$ilToolbar->addSeparator();
			
			$button = ilLinkButton::getInstance();
			$button->setCaption("add_heading");								
			$button->setUrl($this->ctrl->getLinkTarget($this, "addHeading"));										
			$ilToolbar->addButtonInstance($button);		
		}
		if ($hasDatasets)
		{
			$link = $this->ctrl->getLinkTargetByClass("ilsurveyparticipantsgui", "maintenance");
			$link = "<a href=\"".$link."\">".$this->lng->txt("survey_has_datasets_warning_page_view_link")."</a>";
			ilUtil::sendInfo($this->lng->txt("survey_has_datasets_warning_page_view")." ".$link);
		}

	
		// table gui
		
		include_once "Modules/Survey/classes/class.ilSurveyQuestionTableGUI.php";
		$table = new ilSurveyQuestionTableGUI($this, "questions", $this->object,
			$read_only);
		$this->tpl->setContent($table->getHTML());
	}
		
	/**
	 * Gather (and filter) selected items from table gui
	 *
	 * @param bool $allow_blocks
	 * @param bool $allow_questions
	 * @param bool $allow_headings
	 * @param bool $allow_questions_in_blocks
	 * @return array (questions, blocks, headings)
	 */
	protected function gatherSelectedTableItems($allow_blocks = true, $allow_questions = true, $allow_headings = false, $allow_questions_in_blocks = false)
	{
		$block_map = array();
		foreach($this->object->getSurveyQuestions() as $item)
		{
			$block_map[$item["question_id"]] = $item["questionblock_id"];
		}
		
		$questions = $blocks = $headings = array();
		if($_POST["id"])
		{
			foreach ($_POST["id"] as $key)
			{
				// questions
				if ($allow_questions && preg_match("/cb_(\d+)/", $key, $matches))
				{
					if(($allow_questions_in_blocks || !$block_map[$matches[1]]) &&
						!in_array($block_map[$matches[1]], $blocks))
					{
						array_push($questions, $matches[1]);
					}
				}
				// blocks
				if ($allow_blocks && preg_match("/cb_qb_(\d+)/", $key, $matches))
				{
					array_push($blocks, $matches[1]);
				}
				// headings
				if ($allow_headings && preg_match("/cb_tb_(\d+)/", $key, $matches))
				{
					array_push($headings, $matches[1]);
				}
			}
		}
		
		return array("questions" => $questions,
			"blocks" => $blocks,
			"headings" => $headings);
	}
	
	public function saveObligatoryObject()
	{		
		if(isset($_POST["order"]))
		{
			$position = -1;
			$order = array();
			asort($_POST["order"]);
			foreach(array_keys($_POST["order"]) as $id)
			{
				// block items
				if(substr($id, 0, 3) == "qb_")
				{
					$block_id = substr($id, 3);
					$block = $_POST["block_order"][$block_id];
					asort($block);
					foreach(array_keys($block) as $question_id)
					{
						$position++;
						$order[$question_id] = $position;
					}
				}
				else
				{
					$question_id = substr($id, 2);
					$position++;
					$order[$question_id] = $position;
				}
			}
			$this->object->updateOrder($order);
		}

		$obligatory = array();
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/obligatory_(\d+)/", $key, $matches))
			{
				$obligatory[$matches[1]] = 1;
			}
		}
		$this->object->setObligatoryStates($obligatory);
		ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'), true);
		$this->ctrl->redirect($this, "questions");
	}

	public function unfoldQuestionblockObject()
	{
		$items = $this->gatherSelectedTableItems(true, false, false, false);
		if (count($items["blocks"]))
		{
			ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'), true);
			$this->object->unfoldQuestionblocks($items["blocks"]);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("qpl_unfold_select_none"), true);
		}
		$this->ctrl->redirect($this, "questions");
	}

	public function moveQuestionsObject()
	{
		$items = $this->gatherSelectedTableItems(true, true, false, false);

		$move_questions = $items["questions"];
		foreach ($items["blocks"] as $block_id)
		{
			foreach ($this->object->getQuestionblockQuestionIds($block_id) as $qid)
			{
				array_push($move_questions, $qid);
			}
		}
		if (count($move_questions) == 0)
		{
			ilUtil::sendInfo($this->lng->txt("no_question_selected_for_move"), true);
			$this->ctrl->redirect($this, "questions");
		}
		else
		{
			$_SESSION["move_questions"] = $move_questions;
			ilUtil::sendInfo($this->lng->txt("select_target_position_for_move_question"));
			$this->questionsObject();
		}
	}

	public function insertQuestionsBeforeObject()
	{
		$this->insertQuestions(0);
	}

	public function insertQuestionsAfterObject()
	{
		$this->insertQuestions(1);
	}
	
	protected function insertQuestions($insert_mode)
	{
		$insert_id = null;
		if($_POST["id"])
		{
			$items = $this->gatherSelectedTableItems(true, true, false, false);

			// we are using POST id for original order
			while(!$insert_id && sizeof($_POST["id"]))
			{
				$target = array_shift($_POST["id"]);
				if (preg_match("/^cb_(\d+)$/", $target, $matches))
				{
					// questions in blocks are not allowed
					if(in_array($matches[1], $items["questions"]))
					{
						$insert_id = $matches[1];
					}
				}
				if (!$insert_id && preg_match("/^cb_qb_(\d+)$/", $target, $matches))
				{
					$ids = $this->object->getQuestionblockQuestionIds($matches[1]);
					if (count($ids))
					{
						if ($insert_mode == 0)
						{
							$insert_id = $ids[0];
						}
						else if ($insert_mode == 1)
						{
							$insert_id = $ids[count($ids)-1];
						}
					}
				}
			}
		}

		if(!$insert_id)
		{
			ilUtil::sendInfo($this->lng->txt("no_target_selected_for_move"), true);
		}
		else
		{
			ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'), true);
			$this->object->moveQuestions($_SESSION["move_questions"], $insert_id, $insert_mode);
			unset($_SESSION["move_questions"]);
		}
	
		$this->ctrl->redirect($this, "questions");
	}
	
	public function removeQuestionsObject()
	{
		$items = $this->gatherSelectedTableItems(true, true, true, true);
		if (count($items["blocks"]) + count($items["questions"]) + count($items["headings"]) > 0)
		{
			ilUtil::sendQuestion($this->lng->txt("remove_questions"));
			$this->removeQuestionsForm($items["blocks"], $items["questions"], $items["headings"]);
			return;
		} 
		else 
		{
			ilUtil::sendInfo($this->lng->txt("no_question_selected_for_removal"), true);
			$this->ctrl->redirect($this, "questions");
		}
	}

	public function removeQuestionsForm($checked_questionblocks, $checked_questions, $checked_headings)
	{
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setHeaderText($this->lng->txt("survey_sure_delete_questions"));

		$cgui->setFormAction($this->ctrl->getFormAction($this, "confirmRemoveQuestions"));
		$cgui->setCancel($this->lng->txt("cancel"), "questions");
		$cgui->setConfirm($this->lng->txt("confirm"), "confirmRemoveQuestions");
		
		$counter = 0;
		$surveyquestions =& $this->object->getSurveyQuestions();
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
		foreach ($surveyquestions as $question_id => $data)
		{
			if (in_array($data["question_id"], $checked_questions))
			{
				$type = SurveyQuestion::_getQuestionTypeName($data["type_tag"]);
				
				$cgui->addItem("id_".$data["question_id"], $data["question_id"], 
					$type.": ".$data["title"]);
			}
			else if((in_array($data["questionblock_id"], $checked_questionblocks)))
			{
				$type = SurveyQuestion::_getQuestionTypeName($data["type_tag"]);
				
				$cgui->addItem("id_qb_".$data["questionblock_id"], $data["questionblock_id"], 
					$data["questionblock_title"]." - ".$type.": ".$data["title"]);				
			}
			else if (in_array($data["question_id"], $checked_headings))
			{
				$cgui->addItem("id_tb_".$data["question_id"], $data["question_id"], 
					$data["heading"]);				
			}
		}

		$this->tpl->setContent($cgui->getHTML());
	}
	
	public function confirmRemoveQuestionsObject()
	{		
		$checked_questions = array();
		$checked_questionblocks = array();
		$checked_headings = array();
		foreach ($_POST as $key => $value) 
		{
			if (preg_match("/id_(\d+)/", $key, $matches)) 
			{
				array_push($checked_questions, $matches[1]);
			}
			if (preg_match("/id_qb_(\d+)/", $key, $matches)) 
			{
				array_push($checked_questionblocks, $matches[1]);
			}
			if (preg_match("/id_tb_(\d+)/", $key, $matches))
			{
				array_push($checked_headings, $matches[1]);
			}
		}

		if(sizeof($checked_questions) || sizeof($checked_questionblocks))
		{
			$this->object->removeQuestions($checked_questions, $checked_questionblocks);
		}
		if($checked_headings)
		{
			foreach($checked_headings as $q_id)
			{
				$this->object->saveHeading("", $q_id);
			}
		}
		$this->object->saveCompletionStatus();
		ilUtil::sendSuccess($this->lng->txt("questions_removed"), true);
		$this->ctrl->redirect($this, "questions");
	}					
	
	public function copyQuestionsToPoolObject()
	{
		$items = $this->gatherSelectedTableItems(true, true, false, true);

		// gather questions from blocks
		$copy_questions = $items["questions"];
		foreach ($items["blocks"] as $block_id)
		{
			foreach ($this->object->getQuestionblockQuestionIds($block_id) as $qid)
			{
				array_push($copy_questions, $qid);
			}
		}
		$copy_questions = array_unique($copy_questions);

		// only if not already in pool
		if (count($copy_questions))
		{
			foreach($copy_questions as $idx => $question_id)
			{
				$question = ilObjSurvey::_instanciateQuestion($question_id);
				if($question->getOriginalId())
				{
					unset($copy_questions[$idx]);
				}
			}

		}
		if (count($copy_questions) == 0)
		{
			ilUtil::sendInfo($this->lng->txt("no_question_selected_for_copy_to_pool"), true);
			$this->ctrl->redirect($this, "questions");
		}
		else
		{
			$this->questionsSubtabs("questions");

			include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
			$form = new ilPropertyFormGUI();

			$form->setFormAction($this->ctrl->getFormAction($this, "executeCreateQuestion"));

			$ids = new ilHiddenInputGUI("question_ids");
			$ids->setValue(implode(";", $copy_questions));
			$form->addItem($ids);

			$questionpools =& $this->object->getAvailableQuestionpools(false, false, true, "write");
			$pools = new ilSelectInputGUI($this->lng->txt("survey_copy_select_questionpool"), "sel_spl");
			$pools->setOptions($questionpools);
			$form->addItem($pools);

			$form->addCommandButton("executeCopyQuestionsToPool", $this->lng->txt("submit"));
			$form->addCommandButton("questions", $this->lng->txt("cancel"));

			return $this->tpl->setContent($form->getHTML());
		}
	}
	
	public function executeCopyQuestionsToPoolObject()
	{
		$question_ids = explode(";", $_POST["question_ids"]);
		$pool_id = ilObject::_lookupObjId($_POST["sel_spl"]);

		foreach($question_ids as $qid)
		{
			// create copy (== pool "original")
			$new_question = ilObjSurvey::_instanciateQuestion($qid);
			$new_question->setId();
			$new_question->setObjId($pool_id);
			$new_question->saveToDb();

			// link "source" (survey) to copy (pool)
			SurveyQuestion::_changeOriginalId($qid, $new_question->getId(), $pool_id);
		}

		ilUtil::sendSuccess($this->lng->txt("survey_copy_to_questionpool_success"), true);
		$this->ctrl->redirect($this, "questions");
	}
		
	
	//
	// QUESTION CREATION
	// 
	
	public function createQuestionObject(ilPropertyFormGUI $a_form = null)
	{	
		if(!$this->object->isPoolActive())
		{
			$_POST["usage"] = 1;
			$_GET["sel_question_types"] = $_POST["sel_question_types"];
			return $this->executeCreateQuestionObject();
		}

		if(!$a_form)
		{			
			$this->questionsSubtabs("questions");
			
			include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
			$form = new ilPropertyFormGUI();

			$sel_question_types = (strlen($_POST["sel_question_types"])) ? $_POST["sel_question_types"] : $_GET["sel_question_types"];
			$this->ctrl->setParameter($this, "sel_question_types", $sel_question_types);
			$form->setFormAction($this->ctrl->getFormAction($this, "executeCreateQuestion"));
		}
		else
		{
			$form = $a_form;
		}

		$usage = new ilRadioGroupInputGUI($this->lng->txt("survey_pool_selection"), "usage");
		$usage->setRequired(true);
		$no_pool = new ilRadioOption($this->lng->txt("survey_no_pool"), 1);
		$usage->addOption($no_pool);
		$existing_pool = new ilRadioOption($this->lng->txt("survey_existing_pool"), 3);
		$usage->addOption($existing_pool);
		$new_pool = new ilRadioOption($this->lng->txt("survey_new_pool"), 2);
		$usage->addOption($new_pool);
		$form->addItem($usage);

		if(isset($_SESSION["svy_qpool_choice"]))
		{
			$usage->setValue($_SESSION["svy_qpool_choice"]);
		}
		else
		{
			// default: no pool
			$usage->setValue(1);
		}

		$questionpools =& $this->object->getAvailableQuestionpools(FALSE, TRUE, TRUE, "write");
		$pools = new ilSelectInputGUI($this->lng->txt("select_questionpool"), "sel_spl");
		$pools->setOptions($questionpools);
		$existing_pool->addSubItem($pools);

		$name = new ilTextInputGUI($this->lng->txt("spl_new"), "name_spl"); // #11740
		$name->setSize(50);
		$name->setMaxLength(50);
		$new_pool->addSubItem($name);

		if($a_form)
		{
			return $a_form;
		}

		$form->addCommandButton("executeCreateQuestion", $this->lng->txt("submit"));
		$form->addCommandButton("questions", $this->lng->txt("cancel"));

		return $this->tpl->setContent($form->getHTML());
	}

	function executeCreateQuestionObject()
	{		
		$_SESSION["svy_qpool_choice"] = $_POST["usage"];
		
		$q_type = $_GET["sel_question_types"];
		
		// no pool
		if ($_POST["usage"] == 1)
		{
			$obj_id = $this->object->getId();	
		}
		// existing pool
		else if ($_POST["usage"] == 3 && strlen($_POST["sel_spl"]))
		{
			$obj_id = ilObject::_lookupObjId($_POST["sel_spl"]);			
		}
		// new pool
		elseif ($_POST["usage"] == 2 && strlen($_POST["name_spl"]))
		{
			$obj_id = $this->createQuestionPool($_POST["name_spl"]);		
		}
		else
		{
			if(!$_POST["usage"])
			{
				ilUtil::sendFailure($this->lng->txt("select_one"), true);
			}
			else
			{
				ilUtil::sendFailure($this->lng->txt("err_no_pool_name"), true);
			}
			$this->ctrl->setParameter($this, "sel_question_types", $q_type);
			$this->ctrl->redirect($this, "createQuestion");
		}			
		
		
		// create question and redirect to question form
		
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestionGUI.php";
		$q_gui = SurveyQuestionGUI::_getQuestionGUI($q_type);
		$q_gui->object->setObjId($obj_id); // survey/pool!
		$q_gui->object->createNewQuestion();		
		$q_gui_class = get_class($q_gui);	
		
		if($_REQUEST["pgov"])
		{
			$this->ctrl->setParameterByClass($q_gui_class, "pgov", $_REQUEST["pgov"]);
			$this->ctrl->setParameterByClass($q_gui_class, "pgov_pos",$_REQUEST["pgov_pos"]);			
		}
				
		$this->ctrl->setParameterByClass($q_gui_class, "ref_id", $this->object->getRefId());
		$this->ctrl->setParameterByClass($q_gui_class, "new_for_survey", $this->object->getRefId());
		$this->ctrl->setParameterByClass($q_gui_class, "q_id", $q_gui->object->getId());
		$this->ctrl->setParameterByClass($q_gui_class, "sel_question_types", $q_gui->getQuestionType());		
		$this->ctrl->redirectByClass($q_gui_class, "editQuestion");					
	}
	
	protected function createQuestionPool($name = "dummy")
	{
		global $tree;
		
		$parent_ref = $tree->getParentId($this->object->getRefId());
		
		include_once "./Modules/SurveyQuestionPool/classes/class.ilObjSurveyQuestionPool.php";
		$qpl = new ilObjSurveyQuestionPool();
		$qpl->setType("spl");
		$qpl->setTitle($name);
		$qpl->setDescription("");
		$qpl->create();
		$qpl->createReference();
		$qpl->putInTree($parent_ref);
		$qpl->setPermissions($parent_ref);
		$qpl->setOnline(1); // must be online to be available
		$qpl->saveToDb();
		
		return $qpl->getId();
	}
	
	
	// 
	// ADD FROM POOL
	//
	
	protected function setBrowseForQuestionsSubtabs()
	{
		global $ilTabs, $ilToolbar, $ilUser;
				
		if(!isset($_REQUEST["pgov"]))
		{
			$link = $this->ctrl->getLinkTarget($this, "questions");
		}
		else
		{
			$link = $this->ctrl->getLinkTargetByClass("ilsurveypagegui", "renderpage");
		}
		$ilTabs->setBackTarget($this->lng->txt("menubacktosurvey"), $link);
				
		// type selector
		include_once "Services/Form/classes/class.ilSelectInputGUI.php";
		$types = new ilSelectInputGUI($this->lng->txt("display_all_available"), "datatype");
		$types->setOptions(array(
			1 =>  $this->lng->txt("questions"),
			2 =>  $this->lng->txt("questionblocks")
		));		
		$types->setValue($ilUser->getPref('svy_insert_type'));
		$ilToolbar->addInputItem($types, true);
		$ilToolbar->addFormButton($this->lng->txt("change"), "changeDatatype");
		$ilToolbar->setFormAction( $this->ctrl->getFormAction($this, "changeDatatype"));		
	}

	public function changeDatatypeObject()
	{
		global $ilUser;
		
		$ilUser->writePref('svy_insert_type', $_POST['datatype']);

		switch ($_POST["datatype"])
		{
			case 2:
				$this->ctrl->redirect($this, 'browseForQuestionblocks');
				break;
			
			case 1:
			default:
				$this->ctrl->redirect($this, 'browseForQuestions');
				break;
		}
	}

	public function browseForQuestionsObject()
	{				
		$this->setBrowseForQuestionsSubtabs();
		
		include_once "./Modules/Survey/classes/tables/class.ilSurveyQuestionbrowserTableGUI.php";
		$table_gui = new ilSurveyQuestionbrowserTableGUI($this, 'browseForQuestions', $this->object, true);
		$table_gui->setEditable(true);		
		$this->tpl->setContent($table_gui->getHTML());							
	}

	public function filterQuestionBrowserObject()
	{
		include_once "./Modules/Survey/classes/tables/class.ilSurveyQuestionbrowserTableGUI.php";
		$table_gui = new ilSurveyQuestionbrowserTableGUI($this, 'browseForQuestions', $this->object);
		$table_gui->writeFilterToSession();
		$this->ctrl->redirect($this, 'browseForQuestions');
	}

	public function resetfilterQuestionBrowserObject()
	{
		include_once "./Modules/Survey/classes/tables/class.ilSurveyQuestionbrowserTableGUI.php";
		$table_gui = new ilSurveyQuestionbrowserTableGUI($this, 'browseForQuestions', $this->object);
		$table_gui->resetFilter();
		$this->ctrl->redirect($this, 'browseForQuestions');
	}
	
	public function insertQuestionsObject()
	{
		$inserted_objects = 0;
		if (is_array($_POST['q_id']))
		{
			if($_REQUEST["pgov"])
			{
				include_once "Modules/Survey/classes/class.ilSurveyPageGUI.php";
				$page_gui = new ilSurveyPageGUI($this->object, $this);
				$page_gui->determineCurrentPage();	
				
				// as target position is predefined, insert in reverse order
				$_POST['q_id'] = array_reverse($_POST['q_id']);
			}			
			foreach ($_POST['q_id'] as $question_id)
			{
				if(!$_REQUEST["pgov"])
				{				
					$this->object->insertQuestion($question_id);
				}
				else
				{
					// target position (pgov pos) is processed there
					$page_gui->insertNewQuestion($question_id);
				}
				$inserted_objects++;
			}
		}
		if ($inserted_objects)
		{
			$this->object->saveCompletionStatus();
			ilUtil::sendSuccess($this->lng->txt("questions_inserted"), true);			
			if(!$_REQUEST["pgov"])
			{
				$this->ctrl->redirect($this, "questions");
			}
			else
			{
				$target_page = $_REQUEST["pgov"];
				if(substr($_REQUEST["pgov_pos"], -1) == "c")
				{
					// see ilSurveyPageGUI::insertNewQuestion()
					if((int)$_REQUEST["pgov_pos"])
					{
						$target_page++;
					}
					else
					{
						$target_page = 1;
					}
				}
				$this->ctrl->setParameterByClass("ilsurveypagegui", "pgov", $target_page);
				$this->ctrl->redirectByClass("ilsurveypagegui", "renderpage");			
			}
		}
		else
		{			
			ilUtil::sendInfo($this->lng->txt("insert_missing_question"), true);
			$this->ctrl->redirect($this, 'browseForQuestions');
		}
	}
	
	public function browseForQuestionblocksObject()
	{		
		$this->setBrowseForQuestionsSubtabs();
				
		include_once "./Modules/Survey/classes/tables/class.ilSurveyQuestionblockbrowserTableGUI.php";
		$table_gui = new ilSurveyQuestionblockbrowserTableGUI($this, 'browseForQuestionblocks', $this->object, true);
		$table_gui->setEditable(true);				
		$this->tpl->setContent($table_gui->getHTML());	
	}
	
	public function filterQuestionblockBrowserObject()
	{
		include_once "./Modules/Survey/classes/tables/class.ilSurveyQuestionblockbrowserTableGUI.php";
		$table_gui = new ilSurveyQuestionblockbrowserTableGUI($this, 'browseForQuestionblocks', $this->object);
		$table_gui->writeFilterToSession();
		$this->ctrl->redirect($this, 'browseForQuestionblocks');
	}

	public function resetfilterQuestionblockBrowserObject()
	{
		include_once "./Modules/Survey/classes/tables/class.ilSurveyQuestionblockbrowserTableGUI.php";
		$table_gui = new ilSurveyQuestionblockbrowserTableGUI($this, 'browseForQuestionblocks', $this->object);
		$table_gui->resetFilter();
		$this->ctrl->redirect($this, 'browseForQuestionblocks');
	}
	
	public function insertQuestionblocksObject()
	{
		$inserted_objects = 0;
		if (is_array($_POST['cb']))
		{
			if($_REQUEST["pgov"])
			{
				include_once "Modules/Survey/classes/class.ilSurveyPageGUI.php";
				$page_gui = new ilSurveyPageGUI($this->object, $this);
				$page_gui->determineCurrentPage();	
				
				// as target position is predefined, insert in reverse order
				$_POST['cb'] = array_reverse($_POST['cb']);
			}		
			foreach ($_POST['cb'] as $questionblock_id)
			{
				if(!$_REQUEST["pgov"])
				{	
					$this->object->insertQuestionblock($questionblock_id);
				}
				else
				{
					$page_gui->insertQuestionblock($questionblock_id);
				}
				$inserted_objects++;
			}
		}
		if ($inserted_objects)
		{
			$this->object->saveCompletionStatus();
			ilUtil::sendSuccess(($inserted_objects == 1) ? $this->lng->txt("questionblock_inserted") : $this->lng->txt("questionblocks_inserted"), true);			
			if(!$_REQUEST["pgov"])
			{
				$this->ctrl->redirect($this, "questions");
			}
			else
			{
				$target_page = $_REQUEST["pgov"];
				if(substr($_REQUEST["pgov_pos"], -1) == "c")
				{
					$target_page++;
				}
				$this->ctrl->setParameterByClass("ilsurveypagegui", "pgov", $target_page);
				$this->ctrl->redirectByClass("ilsurveypagegui", "renderpage");			
			}
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("insert_missing_questionblock"), true);
			$this->ctrl->redirect($this, 'browseForQuestionblocks');
		}
	}
	
	
	//
	// BLOCKS
	//
	
	public function editQuestionblockObject(ilPropertyFormGUI $a_form = null)
	{
		$block_id = (int)$_REQUEST["bl_id"];		
		$this->ctrl->setParameter($this, "bl_id", $block_id);
		
		if(!$a_form)
		{
			$a_form = $this->initQuestionblockForm($block_id);
		}
		
		$this->questionsSubtabs("questions");		
		$this->tpl->setContent($a_form->getHTML());
	}
	
	public function createQuestionblockObject(ilPropertyFormGUI $a_form = null)
	{
		if(!$a_form)
		{
			// gather questions from table selected
			$items = $this->gatherSelectedTableItems(false, true, false, false);
			if(sizeof($_POST["qids"]))
			{
				$items["questions"] = $_POST["qids"];
			}
			if (count($items["questions"]) < 2)
			{
				ilUtil::sendInfo($this->lng->txt("qpl_define_questionblock_select_missing"), true);
				$this->ctrl->redirect($this, "questions");
			}
			
			$a_form = $this->initQuestionblockForm(null, $items["questions"]);
		}
		
		$this->questionsSubtabs("questions");		
		$this->tpl->setContent($a_form->getHTML());
	}
	
	protected function initQuestionblockForm($a_block_id = null, $a_question_ids = null)
	{
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, "saveDefineQuestionblock"));
		$form->setTitle($this->lng->txt("define_questionblock"));
		
		$title = new ilTextInputGUI($this->lng->txt("title"), "title");
		$title->setRequired(true);		
		$form->addItem($title);
		
		$toggle_blocktitle = new ilCheckboxInputGUI($this->lng->txt("survey_show_blocktitle"), "show_blocktitle");
		$toggle_blocktitle->setInfo($this->lng->txt("survey_show_blocktitle_description"));		
		$form->addItem($toggle_blocktitle);
		
		$toggle_questiontitle = new ilCheckboxInputGUI($this->lng->txt("show_questiontext"), "show_questiontext");
		$toggle_questiontitle->setInfo($this->lng->txt("show_questiontext_description"));		
		$form->addItem($toggle_questiontitle);
		
		if($a_block_id)
		{
			$questionblock = $this->object->getQuestionblock($a_block_id);
			$title->setValue($questionblock["title"]);
			$toggle_blocktitle->setChecked($questionblock["show_blocktitle"]);
			$toggle_questiontitle->setChecked($questionblock["show_questiontext"]);
		}
		else
		{
			$toggle_blocktitle->setChecked(true);
			$toggle_questiontitle->setChecked(true);
		}
		
		$form->addCommandButton("saveDefineQuestionblock", $this->lng->txt("save"));
		$form->addCommandButton("questions", $this->lng->txt("cancel"));
		
		// reload?
		if(!$a_question_ids && $_POST["qids"])
		{
			$a_question_ids = $_POST["qids"];
		}
		
		if ($a_question_ids)
		{
			foreach ($a_question_ids as $q_id)
			{
				$hidden = new ilHiddenInputGUI("qids[]");
				$hidden->setValue($q_id);
				$form->addItem($hidden);
			}
		}
		
		return $form;
	}

	public function saveDefineQuestionblockObject()
	{
		$block_id = (int)$_REQUEST["bl_id"];
		$q_ids = $_POST["qids"];
				
		$this->ctrl->setParameter($this, "bl_id", $block_id);
					
		if(!$block_id && !is_array($q_ids))
		{
			$this->ctrl->redirect($this, "questions");
		}
		
		$form = $this->initQuestionblockForm($block_id);
		if($form->checkInput())
		{								
			$title = $form->getInput("title");
			$show_questiontext = $form->getInput("show_questiontext");
			$show_blocktitle = $form->getInput("show_blocktitle") ;
			if ($block_id)
			{
				
				$this->object->modifyQuestionblock($block_id, $title, 
					$show_questiontext, $show_blocktitle);
			}
			else if($q_ids)
			{
				$this->object->createQuestionblock($title, $show_questiontext, 
					$show_blocktitle, $q_ids);
			}
			
			ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'), true);
			$this->ctrl->redirect($this, "questions");
		}
		
		$form->setValuesByPost();
		$this->editQuestionblockObject($form);
	}
	
	
	//
	// HEADING
	// 
	
	protected function initHeadingForm($a_question_id = null)
	{
		$survey_questions = $this->object->getSurveyQuestions();
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, ""));

		// heading
		$heading = new ilTextAreaInputGUI($this->lng->txt("heading"), "heading");		
		$heading->setRows(10);
		$heading->setCols(80);
		$heading->setUseRte(TRUE);
		include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
		$heading->setRteTags(ilObjAdvancedEditing::_getUsedHTMLTags("survey"));
		$heading->removePlugin(ilRTE::ILIAS_IMG_MANAGER_PLUGIN);
		$heading->setRTESupport($this->object->getId(), "svy", "survey");
		$heading->setRequired(true);
		$form->addItem($heading);

		$insertbefore = new ilSelectInputGUI($this->lng->txt("insert"), "insertbefore");
		$options = array();
		foreach ($survey_questions as $key => $value)
		{
			$options[$key] = $this->lng->txt("before") . ": \"" . $value["title"] . "\"";
		}
		$insertbefore->setOptions($options);		
		$insertbefore->setRequired(true);		
		$form->addItem($insertbefore);

		$form->addCommandButton("saveHeading", $this->lng->txt("save"));
		$form->addCommandButton("questions", $this->lng->txt("cancel"));
		
		if ($a_question_id)
		{
			$form->setTitle($this->lng->txt("edit_heading"));

			$heading->setValue($this->object->prepareTextareaOutput($survey_questions[$a_question_id]["heading"]));
			$insertbefore->setValue($a_question_id);
			$insertbefore->setDisabled(true);
		}
		else
		{
			$form->setTitle($this->lng->txt("add_heading"));
		}
		
		return $form;		
	}

	public function addHeadingObject(ilPropertyFormGUI $a_form = null)
	{		
		$q_id = $_REQUEST["q_id"];
		$this->ctrl->setParameter($this, "q_id", $q_id);
		
		$this->questionsSubtabs("questions");

		if(!$a_form)
		{
			$a_form = $this->initHeadingForm($q_id);
		}
			
		$this->tpl->setContent($a_form->getHTML());
	}
	
	public function editHeadingObject(ilPropertyFormGUI $a_form = null)
	{
		$q_id = $_REQUEST["q_id"];
		$this->ctrl->setParameter($this, "q_id", $q_id);
		
		$this->questionsSubtabs("questions");

		if(!$a_form)
		{
			$a_form = $this->initHeadingForm($q_id);
		}
			
		$this->tpl->setContent($a_form->getHTML());
	}

	public function saveHeadingObject()
	{
		$q_id = (int)$_REQUEST["q_id"];
		if(!$q_id)
		{
			$this->ctrl->redirect($this, "questions");
		}
		
		$this->ctrl->setParameter($this, "q_id", $q_id);
		
		$form = $this->initHeadingForm($q_id);		
		if ($form->checkInput())
		{			
			include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
			$this->object->saveHeading(ilUtil::stripSlashes($form->getInput("heading"), 
				true, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("survey")), 
				$form->getInput("insertbefore"));
			$this->ctrl->redirect($this, "questions");
		}
		
		$form->setValuesByPost();
		$this->addHeadingObject($form);
	}
	
	public function removeHeadingObject()
	{
		$q_id = (int)$_REQUEST["q_id"];
		$this->ctrl->setParameter($this, "q_id", $q_id);
		
		if(!$q_id)
		{
			$this->ctrl->redirect($this, "questions");
		}
		
		$this->questionsSubtabs("questions");
		
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setHeaderText($this->lng->txt("confirm_remove_heading"));

		$cgui->setFormAction($this->ctrl->getFormAction($this, "confirmedRemoveHeading"));
		$cgui->setCancel($this->lng->txt("cancel"), "questions");
		$cgui->setConfirm($this->lng->txt("confirm"), "confirmedRemoveHeading");
		
		$this->tpl->setContent($cgui->getHTML());
	}
	

	public function confirmedRemoveHeadingObject()
	{
		$q_id = (int)$_REQUEST["q_id"];		
		if(!$q_id)
		{
			$this->ctrl->redirect($this, "questions");
		}
		
		$this->object->saveHeading("", $q_id);
		$this->ctrl->redirect($this, "questions");
	}
	
	
	
	
	
	
	
	
	/**
	* Creates a print view of the survey questions
	*
	* @access public
	*/
	function printViewObject()
	{	
		global $ilToolbar;
		
		$this->questionsSubtabs("print");
		
		include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
		$button = ilLinkButton::getInstance();
		$button->setCaption("print");								
		$button->setOnClick("window.print(); return false;");				
		$button->setOmitPreventDoubleSubmission(true);
		$ilToolbar->addButtonInstance($button);				
			
		include_once './Services/WebServices/RPC/classes/class.ilRPCServerSettings.php';
		if(ilRPCServerSettings::getInstance()->isEnabled())
		{
			$this->ctrl->setParameter($this, "pdf", "1");
			$pdf_url = $this->ctrl->getLinkTarget($this, "printView");
			$this->ctrl->setParameter($this, "pdf", "");
			
			$button = ilLinkButton::getInstance();
			$button->setCaption("pdf_export");								
			$button->setUrl($pdf_url);				
			$button->setOmitPreventDoubleSubmission(true);
			$ilToolbar->addButtonInstance($button);	
		}
		
		
		$template = new ilTemplate("tpl.il_svy_svy_printview.html", TRUE, TRUE, "Modules/Survey");
	
		$pages =& $this->object->getSurveyPages();
		foreach ($pages as $page)
		{
			if (count($page) > 0)
			{
				foreach ($page as $question)
				{
					$questionGUI = $this->object->getQuestionGUI($question["type_tag"], $question["question_id"]);
					if (is_object($questionGUI))
					{
						if (strlen($question["heading"]))
						{
							$template->setCurrentBlock("textblock");
							$template->setVariable("TEXTBLOCK", $question["heading"]);
							$template->parseCurrentBlock();
						}
						$template->setCurrentBlock("question");
						$template->setVariable("QUESTION_DATA", $questionGUI->getPrintView($this->object->getShowQuestionTitles(), $question["questionblock_show_questiontext"], $this->object->getSurveyId()));
						$template->parseCurrentBlock();
					}
				}
				if (count($page) > 1 && $page[0]["questionblock_show_blocktitle"])
				{
					$template->setCurrentBlock("page");
					$template->setVariable("BLOCKTITLE", $page[0]["questionblock_title"]);
					$template->parseCurrentBlock();
				}
				else
				{
					$template->setCurrentBlock("page");
					$template->parseCurrentBlock();
				}
			}
		}
		$this->tpl->addCss("./Modules/Survey/templates/default/survey_print.css", "print");
		if (array_key_exists("pdf", $_GET) && ($_GET["pdf"] == 1))
		{
			$printbody = new ilTemplate("tpl.il_as_tst_print_body.html", TRUE, TRUE, "Modules/Test");
			$printbody->setVariable("TITLE", sprintf($this->lng->txt("tst_result_user_name"), $uname));
			$printbody->setVariable("ADM_CONTENT", $template->get());
			$printoutput = $printbody->get();
			$printoutput = preg_replace("/href=\".*?\"/", "", $printoutput);
			$fo = $this->object->processPrintoutput2FO($printoutput);
			// #11436
			if(!$fo || !$this->object->deliverPDFfromFO($fo))
			{
				ilUtil::sendFailure($this->lng->txt("msg_failed"), true);
				$this->ctrl->redirect($this, "printView");
			}
		}
		else
		{
			$this->tpl->setVariable("ADM_CONTENT", $template->get());
		}
	}
	
}

?>