<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Survey question table GUI class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id$
 *
 * @ingroup ModulesSurvey
 */
class ilSurveyQuestionTableGUI extends ilTable2GUI
{
	protected $object;
	protected $read_only;
	
	/**
	 * Constructor
	 *
	 * @param object $a_parent_obj parent gui object
	 * @param string $a_parent_cmd parent default command
	 */
	function __construct($a_parent_obj, $a_parent_cmd, ilObjSurvey $a_survey_obj, $a_read_only = false)
	{
		global $ilCtrl, $lng;

		$this->object = $a_survey_obj;
		$this->read_only = (bool)$a_read_only;

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setId("il_svy_qst");
		$this->setLimit(9999);

		// $this->setTitle($lng->txt("survey_questions"));

		if(!$this->read_only)
		{
			// command dropdown
			if(!array_key_exists("move_questions", $_SESSION))
			{
				$this->addMultiCommand("createQuestionblock", $lng->txt("define_questionblock"));
				$this->addMultiCommand("unfoldQuestionblock", $lng->txt("unfold"));
				$this->addMultiCommand("removeQuestions", $lng->txt("remove_question"));
				$this->addMultiCommand("moveQuestions", $lng->txt("move"));
				$this->addMultiCommand("copyQuestionsToPool", $lng->txt("survey_copy_questions_to_pool"));
			}
			else
			{
				$this->addMultiCommand("insertQuestionsBefore", $lng->txt("insert_before"));
				$this->addMultiCommand("insertQuestionsAfter", $lng->txt("insert_after"));
			}

			// right side
			$this->addCommandButton("saveObligatory", $lng->txt("save_obligatory_state"));

			$this->setSelectAllCheckbox("id[]");
			$this->addColumn("", "");
			$this->addColumn($lng->txt("survey_order"), "");
		}
		
		$this->addColumn($lng->txt("title"), "");
		$this->addColumn($lng->txt("obligatory"), "");
		$this->addColumn($lng->txt("description"), "");
		$this->addColumn($lng->txt("type"), "");
		$this->addColumn($lng->txt("author"), "");
		$this->addColumn($lng->txt("survey_question_pool"), "");
		
		if(!$this->read_only)
		{
			$this->addColumn("", "");
		}
	
		$this->setDefaultOrderField("order");
		$this->setDefaultOrderDirection("asc");

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.il_svy_svy_question_table.html", "Modules/Survey");
		
		$this->setShowRowsSelector(true);

		$this->importData();
	}

	/**
	 * Import data from DB
	 */
	protected function importData()
	{
		global $ilCtrl, $lng;
		
		$survey_questions = $this->object->getSurveyQuestions();
		if (count($survey_questions) > 0)
		{
			include_once "./Modules/SurveyQuestionPool/classes/class.ilObjSurveyQuestionPool.php";
			$questiontypes = ilObjSurveyQuestionPool::_getQuestiontypes();

			$questionpools = $this->object->getQuestionpoolTitles(true);
			
			$table_data = array();
			$last_questionblock_id = $position = $block_position = 0;
			foreach ($survey_questions as $question_id => $data)
			{								
				// question block
				if ($data["questionblock_id"] > 0 &&
					$data["questionblock_id"] != $last_questionblock_id)
				{
					$id = "qb_" . $data["questionblock_id"];

					$table_data[$id] = array("id" => $id,
						"type" => "block",
						"title" => $data["questionblock_title"]);

					if (!$this->read_only)
					{
						// order
						if(sizeof($survey_questions) > 1)
						{
							$position += 10;
							$table_data[$id]["position"] = $position;
						}
						
						$ilCtrl->setParameter($this->parent_obj, "bl_id", $data["questionblock_id"]);
						$table_data[$id]["url"] = $ilCtrl->getLinkTarget($this->parent_obj, "editQuestionblock");
						$ilCtrl->setParameter($this->parent_obj, "bl_id", "");
					}

					$block_position = 0;
				}

				// question
				
				$id = $data["question_id"];
				
				$table_data[$id] = array("id" => $id,
					"type" => "question",
					"heading" => $data["heading"],
					"title" => $data["title"],
					"description" => $data["description"],
					"author" => $data["author"],
					"block_id" => $data["questionblock_id"],
					"obligatory" => (bool)$data["obligatory"]);

				// question type
				foreach ($questiontypes as $trans => $typedata)
				{
					if (strcmp($typedata["type_tag"], $data["type_tag"]) == 0)
					{
						$table_data[$id]["question_type"] = $trans;
					}
				}
				
				// pool title
				if($data["original_id"])
				{
					if(isset($questionpools[$data["obj_fi"]]))
					{
						$table_data[$id]["pool"] = $questionpools[$data["obj_fi"]];
					}
					else
					{
						// #11186
						$table_data[$id]["pool"] = $this->lng->txt("status_no_permission");
					}
				}

				if (!$this->read_only)
				{
					if ($data["obj_fi"] > 0)
					{
						// edit url
						$q_gui = $data["type_tag"]."GUI";						
						$ilCtrl->setParameterByClass($q_gui, "q_id", $id);
						$table_data[$id]["url"] = $ilCtrl->getLinkTargetByClass($q_gui, "editQuestion") .
						$ilCtrl->setParameterByClass($q_gui, "q_id", "");
					}

					// order
					if(sizeof($survey_questions) > 1)
					{
						if (!$data["questionblock_id"])
						{
							$position += 10;
							$table_data[$id]["position"] = $position;
						}
						else
						{
							$block_position += 10;
							$table_data[$id]["position"] = $block_position;
						}						
					}
				}
			
				$last_questionblock_id = $data["questionblock_id"];
			}
		}

		$this->setData($table_data);
	}
	
	/**
	 * Fill table row
	 *
	 * @param array $a_set data array
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;

		switch($a_set["type"])
		{
			case "block":				
				if(!$this->read_only)
				{
					// checkbox
					$this->tpl->setCurrentBlock("checkable");
					$this->tpl->setVariable("QUESTION_ID", $a_set["id"]);
					$this->tpl->parseCurrentBlock();

					// order
					if($a_set["position"])
					{
						$this->tpl->setCurrentBlock("order");
						$this->tpl->setVariable("ORDER_NAME", "order[".$a_set["id"]."]");
						$this->tpl->setVariable("ORDER_VALUE", $a_set["position"]);
						$this->tpl->parseCurrentBlock();
					}								
				}
				
				$this->tpl->setVariable("TYPE", $lng->txt("questionblock"));
				break;

			case "question":				
				$this->tpl->setVariable("DESCRIPTION", $a_set["description"]);
				$this->tpl->setVariable("TYPE", $a_set["question_type"]);
				$this->tpl->setVariable("AUTHOR", $a_set["author"]);
				$this->tpl->setVariable("POOL", $a_set["pool"]);
				
				if($a_set["heading"])
				{
					$this->tpl->setCurrentBlock("heading");
					$this->tpl->setVariable("TXT_HEADING", $a_set["heading"]);
					$this->tpl->parseCurrentBlock();
				}
				
				if($a_set["block_id"])
				{
					$this->tpl->setVariable("TITLE_INDENT", " style=\"padding-left:30px\"");
				}

				if(!$this->read_only)
				{
					// checkbox
					$this->tpl->setCurrentBlock("checkable");
					$this->tpl->setVariable("QUESTION_ID", $a_set["id"]);
					$this->tpl->parseCurrentBlock();
					
					if($a_set["block_id"])
					{
						$this->tpl->setVariable("CHECKABLE_INDENT", " style=\"padding-left:30px\"");
					}

					// order
					if($a_set["position"])
					{
						$this->tpl->setCurrentBlock("order");
						if(!$a_set["block_id"])
						{
							$this->tpl->setVariable("ORDER_NAME", "order[q_".$a_set["id"]."]");
						}
						else
						{
							$this->tpl->setVariable("ORDER_NAME", "block_order[".$a_set["block_id"]."][".$a_set["id"]."]");
						}
						$this->tpl->setVariable("ORDER_VALUE", $a_set["position"]);
						$this->tpl->parseCurrentBlock();
						if($a_set["block_id"])
						{
							$this->tpl->setVariable("ORDER_INDENT", " style=\"padding-left:30px\"");
						}
					}

					// obligatory
					$checked = $a_set["obligatory"] ? " checked=\"checked\"" : "";
					$obligatory = "<input type=\"checkbox\" name=\"obligatory_".
						$a_set["id"] . "\" value=\"1\"".$checked." />";
				}
				else if($a_set["obligatory"])
				{
					$obligatory = "<img src=\"".ilUtil::getImagePath("obligatory.png", "Modules/Survey").
						"\" alt=\"".$lng->txt("question_obligatory").
						"\" title=\"".$lng->txt("question_obligatory")."\" />";
				}
				$this->tpl->setVariable("OBLIGATORY", $obligatory);
				break;

			case "heading":				
				if(!$this->read_only)
				{
					// checkbox
					$this->tpl->setCurrentBlock("checkable");
					$this->tpl->setVariable("QUESTION_ID", $a_set["id"]);
					$this->tpl->parseCurrentBlock();
					if($a_set["in_block"])
					{
						$this->tpl->setVariable("CHECKABLE_INDENT", " style=\"padding-left:30px\"");
						$this->tpl->setVariable("TITLE_INDENT", " style=\"padding-left:30px\"");
					}
				}
				
				$this->tpl->setVariable("TYPE", $lng->txt("heading"));
				break;
		}

		if(!$this->read_only)
		{
			$this->tpl->setCurrentBlock("actions");
			
			$ilCtrl->setParameter($this->parent_obj, "q_id", $a_set["id"]);
			
			include_once "Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php";
			$list = new ilAdvancedSelectionListGUI();
			$list->setId($a_set["id"]);
			$list->setListTitle($lng->txt("actions"));
			if($a_set["url"])
			{
				$list->addItem($lng->txt("edit"), "", $a_set["url"]);
			}
			
			if($a_set["heading"])
			{
				$list->addItem($lng->txt("survey_edit_heading"), "", 
					$ilCtrl->getLinkTarget($this->parent_obj, "editheading"));
				
				$list->addItem($lng->txt("survey_delete_heading"), "", 
					$ilCtrl->getLinkTarget($this->parent_obj, "removeheading"));
			}
			else if($a_set["type"] == "question")
			{
				$list->addItem($lng->txt("add_heading"), "", 
					$ilCtrl->getLinkTarget($this->parent_obj, "addHeading"));
			}
		
			$this->tpl->setVariable("ACTION", $list->getHTML());
			
			$ilCtrl->setParameter($this->parent_obj, "q_id", "");				
			
			$this->tpl->parseCurrentBlock();
			
			// #11186
			if($a_set["url"])
			{
				$this->tpl->setCurrentBlock("title_edit");
				$this->tpl->setVariable("TITLE", $a_set["title"]);
				$this->tpl->setVariable("URL_TITLE", $a_set["url"]);
				$this->tpl->parseCurrentBlock();	
			}
			else
			{
				$this->tpl->setCurrentBlock("title_static");
				$this->tpl->setVariable("TITLE", $a_set["title"]);
				$this->tpl->parseCurrentBlock();
			}
		}
		else
		{
			$this->tpl->setCurrentBlock("title_static");
			$this->tpl->setVariable("TITLE", $a_set["title"]);
			$this->tpl->parseCurrentBlock();
		}
	}
}

?>