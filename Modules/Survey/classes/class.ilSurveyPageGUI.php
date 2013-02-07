<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Survey per page view
*
* @author		Jörg Lützenkirchen <luetzenkirchen@leifos.com
* @version  $Id: class.ilObjSurveyGUI.php 26720 2010-11-25 17:06:26Z jluetzen $
*
* @ilCtrl_Calls ilSurveyPageGUI:
*
* @ingroup ModulesSurvey
*/
class ilSurveyPageGUI 
{
	protected $ref_id; // [int]
	protected $lng; // [object]
	protected $object; // [ilObjSurvey]
	protected $survey_gui; // [ilObjSurveyGUI]
	protected $current_page; // [int]
	protected $has_previous_page; // [bool]
	protected $has_next_page; // [bool]
	protected $has_datasets; // [bool]
	protected $use_pool; // [bool]
	
	/**
	* Constructor
	*
	* @param ilObjSurveyGUI $a_survey_gui
	*/
	function __construct(ilObjSurveyGUI $a_survey_gui)
	{
		$this->survey_gui = $a_survey_gui;
		$this->ref_id = $this->survey_gui->ref_id;
		$this->object = $this->survey_gui->object;
	}

	/**
	 * Routing
	 */
	function executeCommand()
	{
		global $lng, $ilCtrl, $rbacsystem;

		$cmd = $ilCtrl->getCmd("renderPage");
		$next_class = $ilCtrl->getNextClass($this);

		switch($next_class)
		{
			default:
				$this->determineCurrentPage();

				$has_content = false;
				
				if($rbacsystem->checkAccess("write", $this->ref_id))
				{
					// add page?
					if($_REQUEST["new_id"])
					{
						$this->insertNewQuestion($_REQUEST["new_id"]);
					}

					// subcommands
					if($_REQUEST["il_hform_subcmd"])
					{
						$subcmd = $_REQUEST["il_hform_subcmd"];

						// make sure that it is set for current and next requests
						$ilCtrl->setParameter($this->survey_gui, "pgov", $this->current_page);
						$_REQUEST["pgov"] = $this->current_page;

						$id = explode("_", $_REQUEST["il_hform_node"]);
						$id = (int)$id[1];

						// multi operation
						if(substr($_REQUEST["il_hform_subcmd"], 0, 5) == "multi")
						{
							if($_REQUEST["il_hform_multi"])
							{
								// removing types as we only allow questions anyway
								$id = array();
								foreach(explode(";", $_REQUEST["il_hform_multi"]) as $item)
								{
									$id[] = (int)array_pop(explode("_", $item));
								}

								if($subcmd == "multiDelete")
								{
									$subcmd = "deleteQuestion";
								}
							}
							else
							{
								// #9525
								if($subcmd == "multiDelete")
								{
									ilUtil::sendFailure($lng->txt("no_checkbox"), true);
									$ilCtrl->redirect($this, "renderPage");
								}	
								else
								{
									ilUtil::sendFailure($lng->txt("no_checkbox"));
								}
							}
						}

						if(substr($subcmd, 0, 11) == "addQuestion")
						{
							$type = explode("_", $subcmd);
							$type = (int)$type[1];
							$has_content = $this->addQuestion($type, $this->object->isPoolActive(), $id, $_REQUEST["il_hform_node"]);
						}
						else
						{
							$has_content = $this->$subcmd($id, $_REQUEST["il_hform_node"]);
						}
					}
				}

				if(!$has_content)
				{
					$this->$cmd();
				}
				break;
		}
	}

	/**
	 * determine current page
	 */
	public function determineCurrentPage()
	{
		$current_page = (int)$_REQUEST["jump"];
		if(!$current_page)
		{
			$current_page = (int)$_REQUEST["pgov"];
		}
		if(!$current_page)
		{
			$current_page = (int)$_REQUEST["pg"];
		}
		if(!$current_page)
		{
			$current_page = 1;
		}
		$this->current_page = $current_page;
	}

	/**
     * Add new question to survey (database part)
	 * 
	 * @param int $a_new_id
	 * @param bool $a_duplicate
	 */
	protected function appendNewQuestionToSurvey($a_new_id, $a_duplicate = true, $a_force_duplicate = false)
	{
		global $ilDB;

		// get maximum sequence index in test
		$result = $ilDB->queryF("SELECT survey_question_id FROM svy_svy_qst WHERE survey_fi = %s",
			array('integer'),
			array($this->object->getSurveyId())
		);
		$sequence = $result->numRows();

		// create duplicate if pool question (or forced for question blocks copy)
		if($a_duplicate)
		{
			$survey_question_id = $this->object->duplicateQuestionForSurvey($a_new_id, $a_force_duplicate);
		}
		// used by copy & paste
		else
		{
			$survey_question_id = $a_new_id;
		}
		
		// append to survey 
		$next_id = $ilDB->nextId('svy_svy_qst');
		$affectedRows = $ilDB->manipulateF("INSERT INTO svy_svy_qst (survey_question_id, survey_fi,".
			"question_fi, sequence, tstamp) VALUES (%s, %s, %s, %s, %s)",
			array('integer', 'integer', 'integer', 'integer', 'integer'),
			array($next_id, $this->object->getSurveyId(), $survey_question_id, $sequence, time())
		);

		return $survey_question_id;
	}

	/**
	 * Add new question to survey
	 *
	 * @param int $a_new_id
	 */
	public function insertNewQuestion($a_new_id)
	{
		global $rbacsystem, $ilDB, $lng;

		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
		if (!SurveyQuestion::_isComplete($a_new_id))
		{
			ilUtil::sendFailure($lng->txt("survey_error_insert_incomplete_question"));
		}
		else
		{
			$a_new_id = $this->appendNewQuestionToSurvey($a_new_id);
			$this->object->loadQuestionsFromDb();

			$pos = $_REQUEST["pgov_pos"];

			// a[fter]/b[efore] on same page
			if(substr($pos, -1) != "c")
			{
				// block handling
				$current = $this->object->getSurveyPages();
				$current = $current[$this->current_page-1];
				if(sizeof($current) == 1)
				{
					// as questions are moved to first block question
					// always use existing as first
					// the new question is moved later on (see below)
					$this->object->createQuestionblock($this->getAutoBlockTitle(), true, false,
									array((int)$pos, $a_new_id));
				}
				else
				{
					$block_id = array_pop($current);
					$block_id = $block_id["questionblock_id"];

					$this->object->addQuestionToBlock($a_new_id, $block_id);
				}
			}
			// c: as new page (from toolbar/pool)
			else
			{
				// after given question
				if((int)$pos)
				{
					$pos = (int)$pos."a";
					$this->current_page++;
				}
				// at the beginning
				else
				{
					$first = $this->object->getSurveyPages();
					$first = $first[0];
					$first = array_shift($first);
					$pos = $first["question_id"]."b";
					$this->current_page = 1;
				}
			}

			// move to target position
			$this->object->moveQuestions(array($a_new_id), (int)$pos,
				((substr($pos, -1) == "a") ? 1 : 0));
		}
	}
	
	/**
	 * Copy and insert questions from block 
	 * 
	 * @param int $a_block_id
	 */
	public function insertQuestionBlock($a_block_id)
	{
		$new_ids = array();
		$question_ids = $this->object->getQuestionblockQuestionIds($a_block_id);
		foreach($question_ids as $qid)
		{
			$new_ids[] = $this->appendNewQuestionToSurvey($qid, true, true);
		}
		
		if(sizeof($new_ids))
		{
			$this->object->loadQuestionsFromDb();
			
			$pos = $_REQUEST["pgov_pos"];
		
			// a[fter]/b[efore] on same page
			if(substr($pos, -1) != "c")
			{
				// block handling
				$current = $this->object->getSurveyPages();
				$current = $current[$this->current_page-1];
				if(sizeof($current) == 1)
				{										
					// as questions are moved to first block question
					// always use existing as first
					// the new question is moved later on (see below)
					$this->object->createQuestionblock($this->getAutoBlockTitle(), true, false,
									array((int)$pos)+$new_ids);
				}
				else
				{
					$block_id = array_pop($current);
					$block_id = $block_id["questionblock_id"];

					foreach($new_ids as $qid)
					{
						$this->object->addQuestionToBlock($qid, $block_id);
					}
				}
			}
			// c: as new page (from toolbar/pool)
			else
			{
				// re-create block
				$this->object->createQuestionblock($this->getAutoBlockTitle(), true, false,
								$new_ids);
				
				// after given question
				if((int)$pos)
				{
					$pos = (int)$pos."a";
				}
				// at the beginning
				else
				{
					$first = $this->object->getSurveyPages();
					$first = $first[0];
					$first = array_shift($first);
					$pos = $first["question_id"]."b";
				}
			}

			// move to target position
			$this->object->moveQuestions($new_ids, (int)$pos,
				((substr($pos, -1) == "a") ? 1 : 0));			
		}
	}

	/**
	 * Call add question to survey form
	 *
	 * @param int $a_type question type
	 * @param bool $a_use_pool add question to pool
	 * @param int $a_pos target position
	 * @param string $a_special_position special positions (toolbar | page_end)
	 */
	protected function addQuestion($a_type, $a_use_pool, $a_pos, $a_special_position)
	{
		global $ilCtrl;
		
		// get translated type
		include_once "./Modules/SurveyQuestionPool/classes/class.ilObjSurveyQuestionPool.php";
		$questiontypes = ilObjSurveyQuestionPool::_getQuestiontypes();
		foreach($questiontypes as $item)
		{
			if($item["questiontype_id"] == $a_type)
			{
				$type_trans = $item["type_tag"];
			}
		}

		$id = $a_pos;

		// new page behind current (from toolbar)
		if($a_special_position == "toolbar")
		{
			$id = $this->object->getSurveyPages();
			if($a_pos && $a_pos != "fst")
			{
				$id = $id[$a_pos-1];
				$id = array_pop($id);
				$id = $id["question_id"]."c";
			}
			else
			{
				$id = "0c";
			}
		}
		// append current page
		else if($a_special_position == "page_end")
		{
			$id = $this->object->getSurveyPages();
			$id = $id[$this->current_page-1];
			$id = array_pop($id);
			$id = $id["question_id"]."a";
		}
		else
		{
			$id .= "b";
		}

		if($a_use_pool)
		{
			$_GET["sel_question_types"] = $type_trans;
			$_REQUEST["pgov_pos"] = $id;
			$ilCtrl->setParameter($this->survey_gui, "pgov_pos", $id);
			if(!$_POST["usage"])
			{
				$this->survey_gui->createQuestionObject();
			}
			else
			{
				$this->survey_gui->executeCreateQuestionObject();
			}
			return true;
		}
		else
		{
			ilUtil::redirect("ilias.php?baseClass=ilObjSurveyQuestionPoolGUI&ref_id=".
				$this->ref_id."&cmd=createQuestionForSurvey&new_for_survey=".
				$this->ref_id."&sel_question_types=".$type_trans."&pgov=".$this->current_page.
				"&pgov_pos=".$id);
		}
	}
	
	/**
	 * Add question to be cut to clipboard
	 *
	 * @param int $a_id question id
	 */
	protected function cutQuestion($a_id)
	{
		global $lng;
		
		ilUtil::sendSuccess($lng->txt("survey_questions_to_clipboard_cut"));
		$this->suppress_clipboard_msg = true;
		
		$_SESSION["survey_page_view"][$this->ref_id]["clipboard"] = array(
						"source" => $this->current_page,
						"nodes" => array($a_id),
						"mode" => "cut");
	}
	
	/**
	 * Add question to be copied to clipboard
	 *
	 * @param int $a_id question id
	 */
	protected function copyQuestion($a_id)
	{
		global $lng;
		
		ilUtil::sendSuccess($lng->txt("survey_questions_to_clipboard_copy"));
		$this->suppress_clipboard_msg = true;
		
		$_SESSION["survey_page_view"][$this->ref_id]["clipboard"] = array(
						"source" => $this->current_page,
						"nodes" => array($a_id),
						"mode" => "copy");
	}

	/**
	 * Add questions to be cut to clipboard
	 *
	 * @param array $a_id question ids
	 */
	protected function multiCut($a_id)
	{
		global $lng;
		
		ilUtil::sendSuccess($lng->txt("survey_questions_to_clipboard_cut"));
		$this->suppress_clipboard_msg = true;
		
		$_SESSION["survey_page_view"][$this->ref_id]["clipboard"] = array(
						"source" => $this->current_page,
						"nodes" => $a_id,
						"mode" => "cut");
	}

	/**
	 * Add questions to be copied to clipboard
	 *
	 * @param array $a_id question ids
	 */
	protected function multiCopy($a_id)
	{
		global $lng;
		
		ilUtil::sendSuccess($lng->txt("survey_questions_to_clipboard_copy"));
		$this->suppress_clipboard_msg = true;
		
		$_SESSION["survey_page_view"][$this->ref_id]["clipboard"] = array(
						"source" => $this->current_page,
						"nodes" => $a_id,
						"mode" => "copy");
	}

	/**
	 * Empty clipboard
	 */
	protected function clearClipboard()
	{
		$_SESSION["survey_page_view"][$this->ref_id]["clipboard"] = null;
	}

	/**
	 * Paste from clipboard
	 *
	 * @param int $a_id target position
	 */
	protected function paste($a_id)
	{
		$data = $_SESSION["survey_page_view"][$this->ref_id]["clipboard"];
		$pages = $this->object->getSurveyPages();
		$source = $pages[$data["source"]-1];
		$target = $pages[$this->current_page-1];
		$nodes = $data["nodes"];
		
		// append to last position?
		$pos = 0;
		if($_REQUEST["il_hform_node"] == "page_end")
		{
			$a_id = $target;
			$a_id = array_pop($a_id);
			$a_id = $a_id["question_id"];
			$pos = 1;
		}
		
		// cut			
		if($data["mode"] == "cut")
		{				
			// special case: paste cut on same page (no block handling needed)
			if($data["source"] == $this->current_page)
			{
				// re-order nodes in page
				if(sizeof($nodes) <= sizeof($source))
				{
					$this->object->moveQuestions($nodes, $a_id, $pos);									
				}			
				$this->clearClipboard();
				return;
			}
			else
			{
				// only if source has block
				$source_block_id = false;
				if(sizeof($source) > 1)
				{
					$source_block_id = $source;
					$source_block_id = array_shift($source_block_id);
					$source_block_id = $source_block_id["questionblock_id"];

					// remove from block
					if(sizeof($source) > sizeof($nodes))
					{
						foreach($nodes as $qid)
						{
							$this->object->removeQuestionFromBlock($qid, $source_block_id);
						}
					}
					// remove complete block
					else
					{
						$this->object->unfoldQuestionblocks(array($source_block_id));
					}
				}

				// page will be "deleted" by operation
				if(sizeof($source) == sizeof($nodes) && $data["source"] < $this->current_page)
				{
					$this->current_page--;
				}
			}
		}		
		
		// copy
		else if($data["mode"] == "copy")
		{
			$titles = array();
			foreach($this->object->getSurveyPages() as $page)
			{
				foreach($page as $question)
				{
					$titles[] = $question["title"];
				}
			}

			// copy questions
			$question_pointer = array();
			foreach($nodes as $qid)
			{										
				// create new questions
				$question = ilObjSurvey::_instanciateQuestion($qid);

				// handle exisiting copies
				$title = $question->getTitle();
				$max = 0;
				foreach($titles as $existing_title)
				{
					if(preg_match("/".preg_quote($title)." \(([0-9]+)\)$/", $existing_title, $match))
					{
						$max = max($match[1], $max);						
					}
				}					
				if($max)
				{
					$title .= " (".($max+1).")";
				}
				else
				{
					$title .= " (2)";
				}					
				$titles[] = $title;
				$question->setTitle($title);					

				$question->id = -1;
				$question->saveToDb();

				$question_pointer[$qid] = $question->getId();
				$this->appendNewQuestionToSurvey($question->getId(), false);
			}

			// copy textblocks
			$this->object->cloneTextblocks($question_pointer);

			$this->object->loadQuestionsFromDb();

			$nodes = array_values($question_pointer);
		}

			
		// paste

		// create new block
		if(sizeof($target) == 1)
		{
			$nodes = array_merge(array($a_id), $nodes);

			// moveQuestions() is called within
			$this->object->createQuestionblock($this->getAutoBlockTitle(), true, false,
				$nodes);
		}
		// add to existing block
		else
		{
			$target_block_id = $target;
			$target_block_id = array_shift($target_block_id);
			$target_block_id = $target_block_id["questionblock_id"];

			foreach($nodes as $qid)
			{
				$this->object->addQuestionToBlock($qid, $target_block_id);
			}

			// move to new position
			$this->object->moveQuestions($nodes, $a_id, $pos);
		}

		$this->clearClipboard();
	}

	/**
	 * Move questions in page
	 */
	protected function dnd()
	{
		$source_id = (int)array_pop(explode("_", $_REQUEST["il_hform_source"]));
		if($_REQUEST["il_hform_target"] != "page_end")
		{
			$target_id = (int)array_pop(explode("_", $_REQUEST["il_hform_target"]));
			$pos = 0;
		}
		else
		{
			$page = $this->object->getSurveyPages();
			$page = $page[$this->current_page-1];
			$last = array_pop($page);
			$target_id = (int)$last["question_id"];
			$pos = 1;
		}
		if($source_id != $target_id)
		{
			$this->object->moveQuestions(array($source_id), $target_id, $pos);
		}
	}

	/**
	 * Confirm removing question block
	 * @param int $a_id
	 */
	protected function deleteBlock()
	{
		global $lng, $ilCtrl;

		$this->survey_gui->questionsSubtabs('questions_per_page');

		$ilCtrl->setParameter($this->survey_gui, "pgov", $this->current_page);
		ilUtil::sendQuestion($lng->txt("remove_questions"));

		$page = $this->object->getSurveyPages();
		$page = $page[$this->current_page-1];
		
		// #10567
		if($_REQUEST["csum"] != md5(print_r($page, true)))
		{
			$ilCtrl->redirect($this, "renderPage");
		}
		
		$page = array_shift($page);
		$block_id = $page["questionblock_id"];
		if($block_id)
		{
			$this->survey_gui->removeQuestionsForm(array($block_id), array(), array());
		}
		else
		{
			$this->survey_gui->removeQuestionsForm(array(), array($page["question_id"]), array());
		}
	}

	/**
	 * Confirm removing question(s) from survey
	 *
	 * @param int|array $a_id
	 */
    protected function deleteQuestion($a_id)
	{
		if(!is_array($a_id))
		{
			$a_id = array($a_id);
		}
		$this->survey_gui->removeQuestionsForm(array(), $a_id, array());
		return true;
	}

	/**
	 * Remove question(s) from survey
	 */
	protected function confirmRemoveQuestions()
	{
		global $ilCtrl;
		
		// gather ids
		$ids = array();
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/id_(\d+)/", $key, $matches))
			{
				array_push($ids, $matches[1]);
			}
		}


		$pages = $this->object->getSurveyPages();
		$source = $pages[$this->current_page-1];

		$block_id = $source;
		$block_id = array_shift($block_id);
		$block_id = $block_id["questionblock_id"];

		if(sizeof($ids) && sizeof($source) > sizeof($ids))
		{
			// block is obsolete
			if(sizeof($source)-sizeof($ids) == 1)
			{
				$this->object->unfoldQuestionblocks(array($block_id));
			}
			// block will remain, remove question(s) from block
			else
			{
				foreach($ids as $qid)
				{
					$this->object->removeQuestionFromBlock($qid, $block_id);
				}
			}

			$this->object->removeQuestions($ids, array());
		}
		// all items on page 
		else 
		{
			// remove complete block
			if($block_id)
			{
				$this->object->removeQuestions(array(), array($block_id));
			}
			// remove single question
			else
			{				
				$this->object->removeQuestions($ids, array());
			}

			// render previous page
			if($this->current_page > 1)
			{
				$this->current_page--;
			}
		}

		$this->object->saveCompletionStatus();
		
		// #10567
		$ilCtrl->setParameter($this, "pgov", $this->current_page);
		$ilCtrl->redirect($this, "renderPage");
	}

	/**
	 * Edit question block
	 *
	 * @param int $a_id
	 */
	protected function editBlock($a_id)
	{
		$this->survey_gui->defineQuestionblock($a_id);
		return true;
	}
	
	/**
	 * Add heading to question
	 *
	 * @param int $a_id
	 */
	protected function addHeading($a_id)
	{
		$this->survey_gui->addHeadingObject(false, $a_id);
		return true;
	}

	/**
	 * Edit question heading
	 *
	 * @param int $a_id
	 */
	protected function editHeading($a_id)
	{
		$this->survey_gui->addHeadingObject(false, $a_id);
		return true;
	}

	/**
	 * Delete question heading
	 *
	 * @param int $a_id
	 */
	protected function deleteHeading($a_id)
	{
		$_GET["removeheading"] = $a_id;
		$this->survey_gui->confirmRemoveHeadingForm();
		return true;
	}

	/**
	 * Split current page in 2 pages
	 *
	 * @param int $a_id
	 */
	protected function splitPage($a_id)
	{
		$pages = $this->object->getSurveyPages();
		$source = $pages[$this->current_page-1];

		$block_questions = array();
		$add = $block_id = false;
		foreach($source as $idx => $item)
		{
			if($item["question_id"] == $a_id)
			{
				$block_id = $item["questionblock_id"];
				$add = $idx;
			}
			if($add)
			{
				$block_questions[] = $item["question_id"];
			}
		}

		// just 1 question left: block is obsolete
		if($add == 1)
		{
			$this->object->unfoldQuestionblocks(array($block_id));
		}
		// remove questions from block
		else
		{
			foreach($block_questions as $qid)
			{
				$this->object->removeQuestionFromBlock($qid, $block_id);
			}
		}

		// more than 1 moved?
		if(sizeof($block_questions) > 1)
		{
			// create new block and move target questions
			$this->object->createQuestionblock($this->getAutoBlockTitle(), true, false,
				$block_questions);
		}
		
		$this->current_page++;
	}

	/**
	 * Move question to next page
	 *
	 * @param int $a_id
	 */
	protected function moveNext($a_id)
	{
		$pages = $this->object->getSurveyPages();
		$source = $pages[$this->current_page-1];
		$target = $pages[$this->current_page];
		if(sizeof($target))
		{
			$target_id = $target;
			$target_id = array_shift($target_id);
			$target_block_id = $target_id["questionblock_id"];
			$target_id = $target_id["question_id"];

			// nothing to do if no block
			if(sizeof($source) > 1)
			{
				$block_id = $source;
				$block_id = array_shift($block_id);
				$block_id = $block_id["questionblock_id"];

				// source pages block is obsolete
				if(sizeof($source) == 2)
				{
					// delete block
					$this->object->unfoldQuestionblocks(array($block_id));
				}
				else
				{
					// remove question from block
					$this->object->removeQuestionFromBlock($a_id, $block_id);
				}
			}

			// move source question to target
			$this->object->moveQuestions(array($a_id), $target_id, 0);

			// new page has no block yet
			if(sizeof($target) < 2)
			{
				// create block and  move target question and source into block
				$this->object->createQuestionblock($this->getAutoBlockTitle(), true, false,
					array($a_id, $target_id));
			}
			else
			{
				// add source question to block
				$this->object->addQuestionToBlock($a_id, $target_block_id);
			}

			// only if current page is not "deleted"
			if(sizeof($source) > 1)
			{
				$this->current_page++;
			}
		}
	}

	/**
	 * Move question to previous page
	 *
	 * @param int $a_id
	 */
	protected function movePrevious($a_id)
	{
		$pages = $this->object->getSurveyPages();
		$source = $pages[$this->current_page-1];
		$target = $pages[$this->current_page-2];
		if(sizeof($target))
		{
			$target_id = $target;
			$target_id = array_pop($target_id);
			$target_block_id = $target_id["questionblock_id"];
			$target_id = $target_id["question_id"];

			// nothing to do if no block
			if(sizeof($source) > 1)
			{
				$block_id = $source;
				$block_id = array_shift($block_id);
				$block_id = $block_id["questionblock_id"];

				// source pages block is obsolete
				if(sizeof($source) == 2)
				{
					// delete block
					$this->object->unfoldQuestionblocks(array($block_id));
				}
				else
				{
					// remove question from block
					$this->object->removeQuestionFromBlock($a_id, $block_id);
				}
			}

			// move source question to target
			$this->object->moveQuestions(array($a_id), $target_id, 1);

			// new page has no block yet
			if(sizeof($target) < 2)
			{
				// create block and  move target question and source into block
				$this->object->createQuestionblock($this->getAutoBlockTitle(), true, false,
					array($target_id, $a_id));
			}
			else
			{
				// add source question to block
				$this->object->addQuestionToBlock($a_id, $target_block_id);
			}

			$this->current_page--;
		}
	}

	/**
	 * Edit question
	 *
	 * @param int $a_id
	 */
	protected function editQuestion($a_id)
	{
		$data = $this->object->getQuestions(array($a_id));
		$pool_id = current(ilObject::_getAllReferences($data[0]["obj_fi"]));
		
		ilUtil::redirect("ilias.php?baseClass=ilObjSurveyQuestionPoolGUI&ref_id=".
				$pool_id."&cmd=editQuestionForSurvey&calling_survey=".
				$this->ref_id."&q_id=".$a_id."&pgov=".$this->current_page);
	}

	/**
	 * Add question to survey form (used in toolbar)
	 */
	protected function addQuestionToolbarForm()
	{
		global $lng, $ilCtrl, $tpl;

		$this->survey_gui->questionsSubtabs('questions_per_page');

		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this, "addQuestionToolbar"));
		$form->setTitle($lng->txt("survey_add_new_question"));

		// question types
		include_once "./Modules/SurveyQuestionPool/classes/class.ilObjSurveyQuestionPool.php";
		$questiontypes = ilObjSurveyQuestionPool::_getQuestiontypes();
		$type_map = array();
		foreach($questiontypes as $trans => $item)
		{
			$type_map[$item["questiontype_id"]] = $trans;
		}
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$si = new ilSelectInputGUI($lng->txt("question_type"), "qtype");
		$si->setOptions($type_map);
		$form->addItem($si);

		$pages = $this->object->getSurveyPages();
		if($pages)
		{
			$pages_drop = array("fst"=>$lng->txt("survey_at_beginning"));
			foreach($pages as $idx => $questions)
			{
				$question = array_shift($questions);
				if($question["questionblock_id"])
				{
					$pages_drop[$idx+1] = $lng->txt("survey_behind_page")." ".$question["questionblock_title"];
				}
				else
				{
					$pages_drop[$idx+1] = $lng->txt("survey_behind_page")." ".strip_tags($question["title"]);
				}
			}
			$pos = new ilSelectInputGUI($lng->txt("position"), "pgov");
			$pos->setOptions($pages_drop);
			$form->addItem($pos);

			$pos->setValue($this->current_page);
		}
		else
		{
			// #9089: 1st page 
			$pos = new ilHiddenInputGUI("pgov");
			$pos->setValue("fst");
			$form->addItem($pos);
		}

		if($this->object->isPoolActive())
		{
			$this->survey_gui->createQuestionObject($form);
		}

		$form->addCommandButton("addQuestionToolbar", $lng->txt("submit"));
		$form->addCommandButton("renderPage", $lng->txt("cancel"));

		return $tpl->setContent($form->getHTML());
	}
		
	/**
	 * Add question to survey action (used in toolbar)
	 */
	protected function addQuestionToolbar()
	{
		global $ilCtrl, $lng;

		$pool_active = $this->object->isPoolActive();

		if(!$_POST["usage"] && $pool_active)
		{
			ilUtil::sendFailure($lng->txt("select_one"), true);
			return $this->addQuestionToolbarForm();
		}

		// make sure that it is set for current and next requests
		$ilCtrl->setParameter($this->survey_gui, "pgov", $this->current_page);

		if(!$this->addQuestion($_POST["qtype"], $pool_active, $_POST["pgov"], "toolbar"))
		{
			$this->renderPage();
		}
	}

	/**
	 * Move current page
	 */
	protected function movePageForm()
	{
		global $lng, $ilCtrl, $tpl;

		$this->survey_gui->questionsSubtabs('questions_per_page');

		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this, "movePage"));
		$form->setTitle($lng->txt("survey_move_page"));
		
		$old_pos = new ilHiddenInputGUI("old_pos");
		$old_pos->setValue($this->current_page);
		$form->addItem($old_pos);

		$pages = $this->object->getSurveyPages();
		if($pages)
		{
			$pages_drop = array();
			if($this->current_page != 1)
			{
				$pages_drop["fst"] = $lng->txt("survey_at_beginning");
			}
			foreach($pages as $idx => $questions)
			{
				if(($idx+1) != $this->current_page && ($idx+2) != $this->current_page)
				{
					$question = array_shift($questions);
					if($question["questionblock_id"])
					{
						$pages_drop[$idx+1] = $lng->txt("survey_behind_page")." ".$question["questionblock_title"];
					}
					else
					{
						$pages_drop[$idx+1] = $lng->txt("survey_behind_page")." ".strip_tags($question["title"]);
					}
				}
			}
			$pos = new ilSelectInputGUI($lng->txt("position"), "pgov");
			$pos->setOptions($pages_drop);
			$form->addItem($pos);
		}

		$form->addCommandButton("movePage", $lng->txt("submit"));
		$form->addCommandButton("renderPage", $lng->txt("cancel"));

		return $tpl->setContent($form->getHTML());
	}

	/**
	 * Move current page to new position
	 */
	protected function movePage()
	{
		global $lng, $ilCtrl;

		// current_page is already set to new position
		$target_page = $this->current_page-1;
		$source_page = $_REQUEST["old_pos"]-1;

		$pages = $this->object->getSurveyPages();
		foreach($pages[$source_page] as $question)
		{
			$questions[] = $question["question_id"];
		}

		// move to first position
		$position = 0;
		if($_REQUEST["pgov"] != "fst")
		{
			$position = 1;
		}

		$target = $pages[$target_page];
		$target = array_shift($target);
		$this->object->moveQuestions($questions, $target["question_id"], $position);

		if($target_page < $source_page && $position)
		{
		   $this->current_page++;
		}

		ilUtil::sendSuccess($lng->txt("survey_page_moved"), true);
		$ilCtrl->setParameter($this, "pgov", $this->current_page);
		$ilCtrl->redirect($this, "renderPage");
	}

	/**
	 * Render toolbar form
	 *
	 * @param array $a_pages
	 */
	protected function renderToolbar($a_pages)
	{
		global $ilToolbar, $ilCtrl, $lng, $ilUser;

		if(!$this->has_datasets)
		{
			$ilToolbar->addButton($lng->txt("survey_add_new_question"),	$ilCtrl->getLinkTarget($this, "addQuestionToolbarForm"));
			
			if($this->object->isPoolActive())
			{
				$ilToolbar->addSeparator();

				$last_on_page = 0;
				if($a_pages)
				{
					$last_on_page = $a_pages[$this->current_page-1];
					$last_on_page = array_pop($last_on_page);
					$last_on_page = $last_on_page["question_id"];
				}

				$ilCtrl->setParameter($this->survey_gui, "pgov", $this->current_page);
				$ilCtrl->setParameter($this->survey_gui, "pgov_pos", $last_on_page."c");

				$cmd = ($ilUser->getPref('svy_insert_type') == 1 || strlen($ilUser->getPref('svy_insert_type')) == 0) ? 'browseForQuestions' : 'browseForQuestionblocks';
				$ilToolbar->addButton($lng->txt("browse_for_questions"),
					$ilCtrl->getLinkTarget($this->survey_gui, $cmd));		
				
				$ilCtrl->setParameter($this->survey_gui, "pgov", "");
				$ilCtrl->setParameter($this->survey_gui, "pgov_pos", "");
			}
			
			if($a_pages)
			{
				$ilToolbar->addSeparator();
			}
		}
		
		// parse data for pages drop-down
		if($a_pages)
		{
			// previous/next
			$ilCtrl->setParameter($this, "pg", $this->current_page-1);
			$ilToolbar->addLink($lng->txt("survey_prev_question"),
				$ilCtrl->getLinkTarget($this, "renderPage"), !$this->has_previous_page);
			$ilCtrl->setParameter($this, "pg", $this->current_page+1);
			$ilToolbar->addLink($lng->txt("survey_next_question"),
				$ilCtrl->getLinkTarget($this, "renderPage"), !$this->has_next_page);
			$ilCtrl->setParameter($this, "pg", $this->current_page);

			foreach($a_pages as $idx => $questions)
			{
				$page = $questions;
				$page = array_shift($page);
				if($page["questionblock_id"])
				{
					$pages_drop[$idx+1] = $page["questionblock_title"];

					if(sizeof($questions) > 1)
					{
						foreach($questions as $question)
						{
							$pages_drop[($idx+1)."__".$question["question_id"]] = "- ".$question["title"];
						}
					}
				}
				else
				{
					$pages_drop[$idx+1] = strip_tags($page["title"]);
				}
			}
		}

		// jump to page
		if(sizeof($pages_drop) > 1)
		{
			$ilToolbar->addSeparator();

			$ilToolbar->setFormAction($ilCtrl->getFormAction($this));

			include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
			$si = new ilSelectInputGUI($lng->txt("survey_jump_to"), "jump");
			$si->addCustomAttribute("onChange=\"forms['ilToolbar'].submit();\"");
			$si->setOptions($pages_drop);
			$si->setValue($this->current_page);
			$ilToolbar->addInputItem($si, true);

			// we need this to have to right cmd
			$cmd = new ilHiddenInputGUI("cmd[renderPage]");
			$cmd->setValue("1");
			$ilToolbar->addInputItem($cmd);
		
			if(!$this->has_datasets)
			{
				$ilToolbar->addSeparator();
				$ilCtrl->setParameter($this, "csum", md5(print_r($a_pages[$this->current_page-1], true)));
				$ilToolbar->addButton($lng->txt("survey_delete_page"), $ilCtrl->getLinkTarget($this, "deleteBlock"));
				$ilCtrl->setParameter($this, "csum", "");		
				
				$ilToolbar->addSeparator();
				$ilToolbar->addButton($lng->txt("survey_move_page"), $ilCtrl->getLinkTarget($this, "movePageForm"));
			}
		}
	}

	/**
	 * render questions per page
	 */
	protected function renderPage()
	{
		global $ilCtrl, $lng, $tpl, $rbacsystem;

		$this->survey_gui->questionsSubtabs('questions_per_page');

		$pages = $this->object->getSurveyPages();
		$this->has_next_page = ($this->current_page < sizeof($pages));
		$this->has_previous_page = ($this->current_page > 1);
		$this->has_datasets = $this->object->_hasDatasets($this->object->getSurveyId());

		if($this->has_datasets)
		{
			$link = $ilCtrl->getLinkTarget($this->survey_gui, "maintenance");
			$link = "<a href=\"".$link."\">".$lng->txt("survey_has_datasets_warning_page_view_link")."</a>";
			ilUtil::sendInfo($lng->txt("survey_has_datasets_warning_page_view")." ".$link);
		}

		$ilCtrl->setParameter($this, "pg", $this->current_page);
		$ilCtrl->setParameter($this, "pgov", "");

		$this->renderToolbar($pages);

		if($pages)
		{
			$ttpl = new ilTemplate("tpl.il_svy_svy_page_view.html", true, true, "Modules/Survey");
			$ttpl->setVariable("FORM_ACTION", $ilCtrl->getFormAction($this));
			$ttpl->setVariable("WYSIWYG_ACTION", $ilCtrl->getFormAction($this));
			$lng->loadLanguageModule("form");

			$read_only = ($this->has_datasets || !$rbacsystem->checkAccess("write", $this->ref_id));

			$commands = $multi_commands = array();

			if(!$read_only)
			{
				// clipboard is empty
				if(!$_SESSION["survey_page_view"][$this->ref_id]["clipboard"])
				{
					$multi_commands[] = array("cmd"=>"multiDelete", "text"=>$lng->txt("delete"));
					$multi_commands[] = array("cmd"=>"multiCut", "text"=>$lng->txt("cut"));
					$multi_commands[] = array("cmd"=>"multiCopy", "text"=>$lng->txt("copy"));
				}
				else
				{
					if(!$this->suppress_clipboard_msg)
					{
						ilUtil::sendInfo($lng->txt("survey_clipboard_notice"));
					}
					$multi_commands[] = array("cmd"=>"clearClipboard", "text"=>$lng->txt("survey_dnd_clear_clipboard"));
				}

				// help
				$ttpl->setCurrentBlock("help_img");
				$ttpl->setVariable("IMG_HELP", ilUtil::getImagePath("streaked_area.png"));
				$ttpl->parseCurrentBlock();
				$ttpl->setCurrentBlock("help_section");
				$ttpl->setVariable("TXT_HELP",	$lng->txt("form_hierarchy_add_elements"));
				$ttpl->parseCurrentBlock();

				$ttpl->setCurrentBlock("help_img");
				$ttpl->setVariable("IMG_HELP", ilUtil::getImagePath("icon_cont_el_s.png"));
				$ttpl->parseCurrentBlock();
				$ttpl->setVariable("IMG_HELP", ilUtil::getImagePath("drop_streaked_area.png"));
				$ttpl->parseCurrentBlock();
				$ttpl->setCurrentBlock("help_section");
				$ttpl->setVariable("TXT_HELP",	$lng->txt("form_hierarchy_drag_drop_help"));
				$ttpl->parseCurrentBlock();

				$ttpl->setCurrentBlock("help_img");
				$ttpl->setVariable("IMG_HELP", ilUtil::getImagePath("icon_cont_el_s.png"));
				$ttpl->parseCurrentBlock();
				$ttpl->setCurrentBlock("help_section");
				$ttpl->setVariable("TXT_HELP",	$lng->txt("survey_dnd_double_click_to_delete"));
				$ttpl->parseCurrentBlock();

				$ttpl->setVariable("DND_INIT_JS", "initDragElements();");


				// tiny mce
				
				include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
				$tags = ilObjAdvancedEditing::_getUsedHTMLTags("survey");

				include_once "./Services/RTE/classes/class.ilTinyMCE.php";
				$tiny = new ilTinyMCE("3.3.9.2");				
				$ttpl->setVariable("WYSIWYG_BLOCKFORMATS", $tiny->_buildAdvancedBlockformatsFromHTMLTags($tags));
				$ttpl->setVariable("WYSIWYG_VALID_ELEMENTS", $tiny->_getValidElementsFromHTMLTags($tags));

				$buttons_1 = $tiny->_buildAdvancedButtonsFromHTMLTags(1, $tags);
				$buttons_2 = $tiny->_buildAdvancedButtonsFromHTMLTags(2, $tags).','.
							$tiny->_buildAdvancedTableButtonsFromHTMLTags($tags).
							($tiny->getStyleSelect() ? ',styleselect' : '');
				$buttons_3 = $tiny->_buildAdvancedButtonsFromHTMLTags(3, $tags);
				$ttpl->setVariable('WYSIWYG_BUTTONS_1', $tiny->_removeRedundantSeparators($buttons_1));
				$ttpl->setVariable('WYSIWYG_BUTTONS_2', $tiny->_removeRedundantSeparators($buttons_2));
				$ttpl->setVariable('WYSIWYG_BUTTONS_3', $tiny->_removeRedundantSeparators($buttons_3));
			}

			// commands
			if (count($multi_commands) > 0 || count($commands) > 0)
			{
				$single = false;
				foreach($commands as $cmd)
				{
					$ttpl->setCurrentBlock("cmd");
					$ttpl->setVariable("ORG_CMD", "renderPage");
					$ttpl->setVariable("CMD", $cmd["cmd"]);
					$ttpl->setVariable("CMD_TXT", $cmd["text"]);
					$ttpl->parseCurrentBlock();
					$single = true;
				}

				$multi = false;
				foreach($multi_commands as $cmd)
				{
					$ttpl->setCurrentBlock("multi_cmd");
					$ttpl->setVariable("ORG_CMD_MULTI", "renderPage");
					$ttpl->setVariable("MULTI_CMD", $cmd["cmd"]);
					$ttpl->setVariable("MULTI_CMD_TXT", $cmd["text"]);
					$ttpl->parseCurrentBlock();
					$multi = true;
				}
				if ($multi)
				{
					$ttpl->setCurrentBlock("multi_cmds");
					$ttpl->setVariable("MCMD_ALT", $lng->txt("commands"));
					$ttpl->setVariable("MCMD_IMG", ilUtil::getImagePath("arrow_downright.png"));
					$ttpl->parseCurrentBlock();
				}

				if ($single || $multi)
				{
					$ttpl->setCurrentBlock("commands");
					$ttpl->parseCurrentBlock();
				}
			}

			// nodes
			$ttpl->setVariable("NODES", $this->getPageNodes($pages[$this->current_page-1],
				$this->has_previous_page, $this->has_next_page, $read_only));

			$tpl->setContent($ttpl->get());

			// add js to template
			include_once("./Services/YUI/classes/class.ilYuiUtil.php");
			ilYuiUtil::initDragDrop();
			$tpl->addJavascript("./Modules/Survey/js/SurveyPageView.js");
			$tpl->addJavascript("./Services/RTE/tiny_mce_3_3_9_2/tiny_mce_src.js");
		}
	}

	/**
	 * Get Form HTML
	 *
	 * @param array $questions
	 * @param bool $a_has_previous_page
	 * @param bool $a_has_next_page
	 * @param bool $a_readonly
	 * @return string
	 */
	function getPageNodes(array $a_questions, $a_has_previous_page = false, $a_has_next_page = false, $a_readonly = false)
	{
		global $ilCtrl, $lng;

		$ttpl = new ilTemplate("tpl.il_svy_svy_page_view_nodes.html", true, true, "Modules/Survey");

		$has_clipboard = (bool)$_SESSION["survey_page_view"][$this->ref_id]["clipboard"];

		// question block ?

		$first_question = $a_questions;
		$first_question = array_shift($first_question);

		if($first_question["questionblock_id"])
		{
			$menu = array();

			if(!$a_readonly && !$has_clipboard)
			{
				$menu[] = array("cmd" => "editBlock", "text" => $lng->txt("edit"));
			}

			if($first_question["questionblock_show_blocktitle"])
			{
				$block_status = $lng->txt("survey_block_visible");
			}
			else
			{
				$block_status = $lng->txt("survey_block_hidden");
			}

			$this->renderPageNode($ttpl, "block", $first_question["questionblock_id"],
				$first_question["questionblock_title"]." (".$block_status.")", $menu, false, false, $block_status);
		}


		// questions/headings

		include_once "./Modules/SurveyQuestionPool/classes/class.ilObjSurveyQuestionPool.php";
		$questiontypes = ilObjSurveyQuestionPool::_getQuestiontypes();

		$counter = $question_count;
		$block_done = null;
		foreach($a_questions as $idx => $question)
		{
			// drop area

			$menu = array();

			if(!$a_readonly)
			{
				if(!$has_clipboard)
				{
					foreach($questiontypes as $trans => $item)
					{
						$menu[] = array("cmd"=> "addQuestion_".$item["questiontype_id"],
							"text"=> sprintf($lng->txt("svy_page_add_question"), $trans));
					}
					
					if($this->object->isPoolActive())
					{
						$menu[] = array("cmd"=> "addPoolQuestion",
							"text"=> $lng->txt("browse_for_questions"));
					}
				}
				else 
				{
					$menu[] = array("cmd" => "paste", "text" => $lng->txt("survey_dnd_paste"));
				}
			}

			$this->renderPageNode($ttpl, "droparea", $question["question_id"], null, $menu, true);

			// question
			$question_gui = $this->object->getQuestionGUI($question["type_tag"], $question["question_id"]);
			$question_gui = $question_gui->getWorkingForm(array(), $this->object->getShowQuestionTitles(),
					$question["questionblock_show_questiontext"], null, $this->object->getSurveyId());

			$menu = array();

			if(!$a_readonly && !$has_clipboard)
			{
				$menu[] = array("cmd" => "editQuestion", "text" => $lng->txt("edit"));				
				$menu[] = array("cmd" => "cutQuestion", "text" => $lng->txt("cut"));
				$menu[] = array("cmd" => "copyQuestion", "text" => $lng->txt("copy"));

				if(sizeof($a_questions) > 1 && $idx > 0)
				{
					$menu[] = array("cmd" => "splitPage", "text" => $lng->txt("survey_dnd_split_page"));
				}
				if($a_has_next_page)
				{
					$menu[] = array("cmd" => "moveNext", "text" => $lng->txt("survey_dnd_move_next"));
				}
				if($a_has_previous_page)
				{
					$menu[] = array("cmd" => "movePrevious", "text" => $lng->txt("survey_dnd_move_previous"));
				}
				
				$menu[] = array("cmd" => "deleteQuestion", "text" => $lng->txt("delete"));
				
				// heading
				if($question["heading"])
				{
					$menu[] = array("cmd" => "editHeading", "text" => $lng->txt("survey_edit_heading"));
					$menu[] = array("cmd" => "deleteHeading", "text" => $lng->txt("survey_delete_heading"));
				}
				else
				{
					$menu[] = array("cmd" => "addHeading", "text" => $lng->txt("add_heading"));
				}
			}

			if($first_question["questionblock_show_questiontext"])
			{
				$question_title_status = $lng->txt("survey_question_text_visible");
			}
			else
			{
				$question_title_status = $lng->txt("survey_question_text_hidden");
			}

			$this->renderPageNode($ttpl, "question", $question["question_id"], $question_gui, $menu,
				false, $question["title"], $question_title_status, $question["heading"]);

			$ilCtrl->setParameter($this, "eqid", "");
		}


		// last position (no question id)

		$menu = array();

		if(!$a_readonly)
		{
			if(!$has_clipboard)
			{
				foreach($questiontypes as $trans => $item)
				{
					$menu[] = array("cmd"=> "addQuestion_".$item["questiontype_id"],
						"text"=> sprintf($lng->txt("svy_page_add_question"), $trans));
				}
				
				if($this->object->isPoolActive())
				{
					$menu[] = array("cmd"=> "addPoolQuestion",
						"text"=> $lng->txt("browse_for_questions"));
				}
			}
			else 
			{
				$menu[] = array("cmd" => "paste", "text" => $lng->txt("survey_dnd_paste"));
			}
		}

		$this->renderPageNode($ttpl, "page", "end", null, $menu, true);

		return $ttpl->get();
	}

	/**
	 * Render single of dnd page view
	 *
	 * @param ilTemplate $a_tpl
	 * @param string $a_type
	 * @param int $a_id
	 * @param string $a_content
	 * @param array $a_menu
	 * @param bool $a_spacer
	 * @param string $a_subtitle
	 * @param string $a_heading
	 */
	function renderPageNode(ilTemplate $a_tpl, $a_type, $a_id, $a_content = null, array $a_menu = null, $a_spacer = false, $a_subtitle = false, $a_status = false, $a_heading = false)
	{
		global $ilCtrl, $lng;

		$node_id = $a_type."_".$a_id;

		if($a_content !== null)
		{
			$drag = "";
			$double = false;
			switch($a_type)
			{
				case "block":
					$caption = $lng->txt("questionblock");
					break;

				case "question":
					if($a_heading)
					{
						$a_content = "<div class=\"questionheading\">".$a_heading."</div>".
							$a_content;
					}
					$caption = $lng->txt("question").": ".$a_subtitle;
					$drag = "_drag";
					$double = true;
					break;

				case "heading":
					$caption = $lng->txt("heading");
					break;

				default:
					return;
			}

			if($a_status)
			{
				$caption .= " (".$a_status.")";
			}

			$a_tpl->setCurrentBlock("list_item");
			$a_tpl->setVariable("NODE_ID", $node_id);
			$a_tpl->setVariable("NODE_DRAG", $drag);
			$a_tpl->setVariable("TXT_NODE_TYPE", $caption);
			$a_tpl->setVariable("TXT_NODE_CONTENT", $a_content);
			if($double)
			{
				$a_tpl->setVariable("VAL_DBLCLICK", "onDblClick=\"doMouseDblClick(event,this.id);\"");
			}
			$a_tpl->parseCurrentBlock();
		}

		// drop area menu
		if($a_menu)
		{
			foreach($a_menu as $mcnt => $menu_item)
			{
				$a_tpl->setCurrentBlock("menu_cmd");
				$a_tpl->setVariable("TXT_MENU_CMD", $menu_item["text"]);
				$a_tpl->setVariable("MENU_CMD", "renderPage");

				$a_tpl->setVariable("FC", $menu_item["cmd"]);
				$a_tpl->setVariable("MCNT", $mcnt);

				$a_tpl->setVariable("CMD_NODE", $node_id);
				$a_tpl->parseCurrentBlock();
			}

			$a_tpl->setCurrentBlock("drop_area_menu");
			$a_tpl->setVariable("MNODE_ID", $node_id);
			$a_tpl->parseCurrentBlock();
		}

		if($a_spacer)
		{
			$a_tpl->setCurrentBlock("drop_area");
			$a_tpl->setVariable("DNODE_ID", $node_id);
			$a_tpl->setVariable("IMG_BLANK", ilUtil::getImagePath("spacer.png"));
			$a_tpl->parseCurrentBlock();
		}

		$a_tpl->setCurrentBlock("element");
		$a_tpl->parseCurrentBlock();
	}

	/**
	 * Deletes all user data of the survey after confirmation
	 */
	public function confirmDeleteAllUserData()
	{
		global $lng, $ilCtrl;
		
		$this->object->deleteAllUserData();
		ilUtil::sendSuccess($lng->txt("svy_all_user_data_deleted"), true);
		
		$this->renderPage();
	}
	
	/**
	 * Deletes heading after confirmation
	 */
	public function confirmRemoveHeading()
	{
		global $ilCtrl;

		$this->object->saveHeading("", $_POST["removeheading"]);
		$this->renderPage();
	}

	/**
	 * Edit content (ajax, js)
	 *
	 * @return string
	 */
	public function editJS()
	{
		$node = $_POST["ajaxform_node"];
		if($node)
		{
			$node = explode("_", $node);
			if($node[0] == "heading")
			{
				$id = (int)$node[1];

				include_once "Modules/Survey/classes/class.ilObjSurvey.php";
				echo ilObjSurvey::getTextblock($id);
				exit();
			}
		}
	}

	/**
	 * Save content (ajax, js)
	 */
	public function saveJS()
	{
		$node = $_POST["ajaxform_node"];
		if($node)
		{
			$node = explode("_", $node);
			if($node[0] == "heading")
			{
				$id = (int)$node[1];
				$content = trim($_POST["ajaxform_content"]);

				$this->object->saveHeading($content, $id);
				exit();
			}
		}
	}

	/**
	 * Get name for newly created blocks
	 *
	 * @return string
	 */
	public function getAutoBlockTitle()
	{
		global $lng;

		return $lng->txt("survey_auto_block_title");
	}
	
	public function addPoolQuestion($pos, $node)
	{	
		global $ilCtrl, $ilUser;
		
		if($node == "page_end")
		{
			$pos = $this->object->getSurveyPages();
			$pos = array_pop($pos[$this->current_page-1]);
			$pos = $pos["question_id"]."a";
		}
		else
		{
			$pos = $pos."b";
		}		
		
		$ilCtrl->setParameter($this->survey_gui, "pgov", $this->current_page);
		$ilCtrl->setParameter($this->survey_gui, "pgov_pos", $pos);
		
		$cmd = ($ilUser->getPref('svy_insert_type') == 1 || strlen($ilUser->getPref('svy_insert_type')) == 0) ? 'browseForQuestions' : 'browseForQuestionblocks';
		$ilCtrl->redirect($this->survey_gui, $cmd);
	}
}

?>