<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

define ("TYPE_XLS_PC", "latin1");
define ("TYPE_XLS_MAC", "macos");
define ("TYPE_SPSS", "csv");

/**
* Output class for assessment test evaluation
*
* The ilTestEvaluationGUI class creates the output for the ilObjTestGUI
* class when authors evaluate a test. This saves some heap space because 
* the ilObjTestGUI class will be much smaller then
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @module   class.ilTestEvaluationGUI.php
* @modulegroup   assessment
*/
class ilTestEvaluationGUI
{
	var $object;
	var $lng;
	var $tpl;
	var $ctrl;
	var $ilias;
	var $tree;
	
/**
* ilTestEvaluationGUI constructor
*
* The constructor takes possible arguments an creates an instance of the 
* ilTestEvaluationGUI object.
*
* @param object $a_object Associated ilObjTest class
* @access public
*/
  function ilTestEvaluationGUI($a_object)
  {
		global $lng, $tpl, $ilCtrl, $ilias, $tree;

    $this->lng =& $lng;
    $this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->ilias =& $ilias;
		$this->object =& $a_object;
		$this->tree =& $tree;
	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		$this->ctrl->saveParameter($this, "etype");
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);

		$cmd = $this->getCommand($cmd);
		switch($next_class)
		{
			default:
				$ret =& $this->$cmd();
				break;
		}
		return $ret;
	}

/**
* Retrieves the ilCtrl command
*
* Retrieves the ilCtrl command
*
* @access public
*/
	function getCommand($cmd)
	{
		return $cmd;
	}

	/**
	* Creates the output for the search results when trying to add users/groups to a test evaluation
	*
	* Creates the output for the search results when trying to add users/groups to a test evaluation
	*
	* @access public
	*/
	function outStatSelectedSearchResults()
	{
		include_once ("./classes/class.ilSearch.php");
		global $ilUser;
		
		if (is_array($_POST["search_for"]))
		{
			if (in_array("usr", $_POST["search_for"]) or in_array("grp", $_POST["search_for"]))
			{
				$search =& new ilSearch($ilUser->id);
				$search->setSearchString($_POST["search_term"]);
				$search->setCombination($_POST["concatenation"]);
				$search->setSearchFor($_POST["search_for"]);
				$search->setSearchType("new");
				if($search->validate($message))
				{
					$search->performSearch();
				}
				if ($message)
				{
					//sendInfo($message);
				}
				if(!$search->getNumberOfResults() && $search->getSearchFor())
				{
					sendInfo($this->lng->txt("search_no_match"));
					return;
				}
				$buttons = array("add");
				$participants =& $this->object->evalTotalPersonsArray();
				$eval_users = $this->object->getEvaluationUsers($ilUser->id);
				if ($searchresult = $search->getResultByType("usr"))
				{
					$users = array();
					foreach ($searchresult as $result_array)
					{
						if (!array_key_exists($result_array["id"], $eval_users))
						{
							if (array_key_exists($result_array["id"], $participants))
							{
								$users[$result_array["id"]] = $eval_users[$result_array["id"]];
							}
						}
					}
					$this->outEvalSearchResultTable("usr", $users, "user_result", "user_row", $this->lng->txt("search_found_users"), $buttons);
				}
				$searchresult = array();
				$eval_groups = $this->object->getEvaluationGroups($ilUser->id);
				if ($searchresult = $search->getResultByType("grp"))
				{
					$groups = array();
					foreach ($searchresult as $result_array)
					{
						if (!in_array($result_array["id"], $eval_groups))
						{
							include_once("./classes/class.ilObjGroup.php");
							$grp = new ilObjGroup($result_array["id"], true);
							$members = $grp->getGroupMemberIds();
							$found_member = 0;
							foreach ($members as $member_id)
							{
								if (array_key_exists($member_id, $participants))
								{
									$found_member = 1;
								}
							}
							if ($found_member)
							{
								array_push($groups, $result_array["id"]);
							}
						}
					}
					$this->outEvalSearchResultTable("grp", $groups, "group_result", "group_row", $this->lng->txt("search_found_groups"), $buttons);
				}
			}
		}
		else
		{
			sendInfo($this->lng->txt("no_user_or_group_selected"));
		}
	}
	
	/**
	* Adds found users to the selected users table
	*
	* Adds found users to the selected users table
	*
	* @access public
	*/
	function addFoundUsersToEval()
	{
		global $ilUser;
		if (is_array($_POST["user_select"]))
		{
			foreach ($_POST["user_select"] as $user_id)
			{
				$this->object->addSelectedUser($user_id, $ilUser->id);
			}
		}
		$this->evalStatSelected();
	}
	
	/**
	* Removes selected users from the selected users table
	*
	* Removes selected users from the selected users table
	*
	* @access public
	*/
	function removeSelectedUser()
	{
		global $ilUser;
		if (is_array($_POST["selected_users"]))
		{
			foreach ($_POST["selected_users"] as $user_id)
			{
				$this->object->removeSelectedUser($user_id, $ilUser->id);
			}
		}
		$this->evalStatSelected();
	}
	
	/**
	* Removes selected users from the selected users table
	*
	* Removes selected users from the selected users table
	*
	* @access public
	*/
	function removeSelectedGroup()
	{
		global $ilUser;
		if (is_array($_POST["selected_groups"]))
		{
			foreach ($_POST["selected_groups"] as $group_id)
			{
				$this->object->removeSelectedGroup($group_id, $ilUser->id);
			}
		}
		$this->evalStatSelected();
	}
	
	/**
	* Removes selected groups from the selected groups table
	*
	* Removes selected groups from the selected groups table
	*
	* @access public
	*/
	function addFoundGroupsToEval()
	{
		global $ilUser;
		if (is_array($_POST["group_select"]))
		{
			foreach ($_POST["group_select"] as $group_id)
			{
				$this->object->addSelectedGroup($group_id, $ilUser->id);
			}
		}
		$this->evalStatSelected();
	}
	
	/**
	* Called when the search button is pressed in the evaluation user selection
	*
	* Called when the search button is pressed in the evaluation user selection
	*
	* @access public
	*/
	function searchForEvaluation()
	{
		$this->evalStatSelected(1);
	}
	
	/**
	* Creates the ouput of the selected users/groups for the test evaluation
	*
	* Creates the ouput of the selected users/groups for the test evaluation
	*
	* @access public
	*/
	function evalStatSelected($search = 0)
	{
		global $ilUser;
		
		$this->ctrl->setCmd("evalStatSelected");
		$this->setResultsTabs();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_eval_statistical_evaluation_selection.html", true);
		if ($search)
		{
			$this->outStatSelectedSearchResults();
		}
		$this->tpl->setCurrentBlock("userselection");
		$this->tpl->setVariable("SEARCH_USERSELECTION", $this->lng->txt("eval_search_userselection"));
		$this->tpl->setVariable("SEARCH_TERM", $this->lng->txt("eval_search_term"));
		$this->tpl->setVariable("SEARCH_FOR", $this->lng->txt("search_for"));
		$this->tpl->setVariable("SEARCH_USERS", $this->lng->txt("eval_search_users"));
		$this->tpl->setVariable("SEARCH_GROUPS", $this->lng->txt("eval_search_groups"));
		$this->tpl->setVariable("TEXT_CONCATENATION", $this->lng->txt("eval_concatenation"));
		$this->tpl->setVariable("TEXT_AND", $this->lng->txt("and"));
		$this->tpl->setVariable("TEXT_OR", $this->lng->txt("or"));
		$this->tpl->setVariable("VALUE_SEARCH_TERM", $_POST["search_term"]);
		if (is_array($_POST["search_for"]))
		{
			if (in_array("usr", $_POST["search_for"]))
			{
				$this->tpl->setVariable("CHECKED_USERS", " checked=\"checked\"");
			}
			if (in_array("grp", $_POST["search_for"]))
			{
				$this->tpl->setVariable("CHECKED_GROUPS", " checked=\"checked\"");
			}
		}
		if (strcmp($_POST["concatenation"], "and") == 0)
		{
			$this->tpl->setVariable("CHECKED_AND", " checked=\"checked\"");
		}
		else if (strcmp($_POST["concatenation"], "or") == 0)
		{
			$this->tpl->setVariable("CHECKED_OR", " checked=\"checked\"");
		}
		$this->tpl->setVariable("SEARCH", $this->lng->txt("search"));
		$this->tpl->parseCurrentBlock();

		// output of alread found users and groups
		$eval_users = $this->object->getEvaluationUsers($ilUser->id);
		$eval_groups = $this->object->getEvaluationGroups($ilUser->id);
		$buttons = array("remove");
		if (count($eval_users))
		{
			$this->outEvalSearchResultTable("usr", $eval_users, "selected_user_result", "selected_user_row", $this->lng->txt("eval_found_selected_users"), $buttons);
		}
		if (count($eval_groups))
		{
			$this->outEvalSearchResultTable("grp", $eval_groups, "selected_group_result", "selected_group_row", $this->lng->txt("eval_found_selected_groups"), $buttons);
		}

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("CMD_EVAL", "evalSelectedUsers");
		$this->tpl->setVariable("TXT_STAT_USERS_INTRO", $this->lng->txt("tst_stat_users_intro"));
		$this->tpl->setVariable("TXT_STAT_ALL_USERS", $this->lng->txt("tst_stat_selected_users"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_QWORKEDTHROUGH", $this->lng->txt("tst_stat_result_qworkedthrough"));
		$this->tpl->setVariable("TXT_PWORKEDTHROUGH", $this->lng->txt("tst_stat_result_pworkedthrough"));
		$this->tpl->setVariable("TXT_TIMEOFWORK", $this->lng->txt("tst_stat_result_timeofwork"));
		$this->tpl->setVariable("TXT_ATIMEOFWORK", $this->lng->txt("tst_stat_result_atimeofwork"));
		$this->tpl->setVariable("TXT_FIRSTVISIT", $this->lng->txt("tst_stat_result_firstvisit"));
		$this->tpl->setVariable("TXT_LASTVISIT", $this->lng->txt("tst_stat_result_lastvisit"));
		$this->tpl->setVariable("TXT_RESULTSPOINTS", $this->lng->txt("tst_stat_result_resultspoints"));
		$this->tpl->setVariable("TXT_RESULTSMARKS", $this->lng->txt("tst_stat_result_resultsmarks"));
		$this->tpl->setVariable("TXT_DISTANCEMEDIAN", $this->lng->txt("tst_stat_result_distancemedian"));
		$this->tpl->setVariable("TXT_SPECIFICATION", $this->lng->txt("tst_stat_result_specification"));
		$user_settings = $this->object->evalLoadStatisticalSettings($ilUser->id);
		foreach ($user_settings as $key => $value) {
			if ($value == 1) {
				$user_settings[$key] = " checked=\"checked\"";
			} else {
				$user_settings[$key] = "";
			}
		}
		$this->tpl->setVariable("CHECKED_QWORKEDTHROUGH", $user_settings["qworkedthrough"]);
		$this->tpl->setVariable("CHECKED_PWORKEDTHROUGH", $user_settings["pworkedthrough"]);
		$this->tpl->setVariable("CHECKED_TIMEOFWORK", $user_settings["timeofwork"]);
		$this->tpl->setVariable("CHECKED_ATIMEOFWORK", $user_settings["atimeofwork"]);
		$this->tpl->setVariable("CHECKED_FIRSTVISIT", $user_settings["firstvisit"]);
		$this->tpl->setVariable("CHECKED_LASTVISIT", $user_settings["lastvisit"]);
		$this->tpl->setVariable("CHECKED_RESULTSPOINTS", $user_settings["resultspoints"]);
		$this->tpl->setVariable("CHECKED_RESULTSMARKS", $user_settings["resultsmarks"]);
		$this->tpl->setVariable("CHECKED_DISTANCEMEDIAN", $user_settings["distancemedian"]);
		$this->tpl->setVariable("TXT_STATISTICAL_EVALUATION", $this->lng->txt("tst_statistical_evaluation"));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Creates the search output for the user/group search form
	*
	* Creates the search output for the user/group search form
	*
	* @access	public
	*/
	function outEvalSearchResultTable($a_type, $id_array, $block_result, $block_row, $title_text, $buttons)
	{
		global $rbacsystem;
		
		$rowclass = array("tblrow1", "tblrow2");
		switch($a_type)
		{
			case "usr":
				include_once "./classes/class.ilObjUser.php";
				foreach ($id_array as $user_id => $username)
				{
					$counter = 0;
					$user = new ilObjUser($user_id);
					$this->tpl->setCurrentBlock($block_row);
					$this->tpl->setVariable("COLOR_CLASS", $rowclass[$counter % 2]);
					$this->tpl->setVariable("COUNTER", $user->getId());
					$this->tpl->setVariable("VALUE_LOGIN", $user->getLogin());
					$this->tpl->setVariable("VALUE_FIRSTNAME", $user->getFirstname());
					$this->tpl->setVariable("VALUE_LASTNAME", $user->getLastname());
					$counter++;
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock($block_result);
				$this->tpl->setVariable("TEXT_USER_TITLE", "<img src=\"" . ilUtil::getImagePath("icon_usr_b.gif") . "\" alt=\"".$this->lng->txt("objs_".$a_type)."\" /> " . $title_text);
				$this->tpl->setVariable("TEXT_LOGIN", $this->lng->txt("login"));
				$this->tpl->setVariable("TEXT_FIRSTNAME", $this->lng->txt("firstname"));
				$this->tpl->setVariable("TEXT_LASTNAME", $this->lng->txt("lastname"));
				if ($rbacsystem->checkAccess("write", $this->object->getRefId()))
				{
					foreach ($buttons as $cat)
					{
						$this->tpl->setVariable("VALUE_" . strtoupper($cat), $this->lng->txt($cat));
					}
					$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"".$this->lng->txt("arrow_downright")."\"/>");
				}
				$this->tpl->parseCurrentBlock();
				break;
			case "grp":
				include_once "./classes/class.ilObjGroup.php";
				foreach ($id_array as $group_id)
				{
					$counter = 0;
					$group = new ilObjGroup($group_id);
					$this->tpl->setCurrentBlock($block_row);
					$this->tpl->setVariable("COLOR_CLASS", $rowclass[$counter % 2]);
					$this->tpl->setVariable("COUNTER", $group->getRefId());
					$this->tpl->setVariable("VALUE_TITLE", $group->getTitle());
					$this->tpl->setVariable("VALUE_DESCRIPTION", $group->getDescription());
					$counter++;
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock($block_result);
				$this->tpl->setVariable("TEXT_GROUP_TITLE", "<img src=\"" . ilUtil::getImagePath("icon_grp_b.gif") . "\" alt=\"".$this->lng->txt("objs_".$a_type)."\" /> " . $title_text);
				$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
				$this->tpl->setVariable("TEXT_DESCRIPTION", $this->lng->txt("description"));
				if ($rbacsystem->checkAccess("write", $this->object->getRefId()))
				{
					foreach ($buttons as $cat)
					{
						$this->tpl->setVariable("VALUE_" . strtoupper($cat), $this->lng->txt($cat));
					}
					$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"".$this->lng->txt("arrow_downright")."\"/>");
				}
				$this->tpl->parseCurrentBlock();
				break;
		}
	}

	/**
	* Creates the output of a users text answer
	*
	* Creates the output of a users text answer
	*
	* @access	public
	*/
	function evaluationDetail()
	{
		include_once "./classes/class.ilObjUser.php";
		$answertext = $this->object->getTextAnswer($_GET["userdetail"], $_GET["answer"]);
		$questiontext = $this->object->getQuestiontext($_GET["answer"]);
		include_once "./classes/class.ilTemplate.php";
		$this->tpl = new ilTemplate("./assessment/templates/default/tpl.il_as_tst_eval_user_answer.html", true, true);
		$this->tpl->setVariable("TITLE_USER_ANSWER", $this->lng->txt("tst_eval_user_answer"));
		$this->tpl->setVariable("TEXT_USER", $this->lng->txt("user"));
		include_once "./classes/class.ilObjUser.php";
		$user = new ilObjUser($_GET["userdetail"]);
		$this->tpl->setVariable("TEXT_USERNAME", trim($user->getFirstname() . " " . $user->getLastname()));
		$this->tpl->setVariable("TEXT_QUESTION", $this->lng->txt("question"));
		$this->tpl->setVariable("TEXT_QUESTIONTEXT", $questiontext);
		$this->tpl->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
		$this->tpl->setVariable("TEXT_USER_ANSWER", str_replace("\n", "<br />", ilUtil::prepareFormOutput($answertext)));
	}
	
	function eval_stat()
	{
		$this->ctrl->setCmdClass(get_class($this));
		$this->ctrl->setCmd("eval_stat");
		$this->setResultsTabs();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_eval_statistical_evaluation_selection.html", true);
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("CMD_EVAL", "evalAllUsers");
		$this->tpl->setVariable("TXT_STAT_USERS_INTRO", $this->lng->txt("tst_stat_users_intro"));
		$this->tpl->setVariable("TXT_STAT_ALL_USERS", $this->lng->txt("tst_stat_all_users"));
		$this->tpl->setVariable("TXT_QWORKEDTHROUGH", $this->lng->txt("tst_stat_result_qworkedthrough"));
		$this->tpl->setVariable("TXT_PWORKEDTHROUGH", $this->lng->txt("tst_stat_result_pworkedthrough"));
		$this->tpl->setVariable("TXT_TIMEOFWORK", $this->lng->txt("tst_stat_result_timeofwork"));
		$this->tpl->setVariable("TXT_ATIMEOFWORK", $this->lng->txt("tst_stat_result_atimeofwork"));
		$this->tpl->setVariable("TXT_FIRSTVISIT", $this->lng->txt("tst_stat_result_firstvisit"));
		$this->tpl->setVariable("TXT_LASTVISIT", $this->lng->txt("tst_stat_result_lastvisit"));
		$this->tpl->setVariable("TXT_RESULTSPOINTS", $this->lng->txt("tst_stat_result_resultspoints"));
		$this->tpl->setVariable("TXT_RESULTSMARKS", $this->lng->txt("tst_stat_result_resultsmarks"));
		$this->tpl->setVariable("TXT_DISTANCEMEDIAN", $this->lng->txt("tst_stat_result_distancemedian"));
		$this->tpl->setVariable("TXT_SPECIFICATION", $this->lng->txt("tst_stat_result_specification"));
		$user_settings = $this->object->evalLoadStatisticalSettings($ilUser->id);
		foreach ($user_settings as $key => $value) {
			if ($value == 1) {
				$user_settings[$key] = " checked=\"checked\"";
			} else {
				$user_settings[$key] = "";
			}
		}
		$this->tpl->setVariable("CHECKED_QWORKEDTHROUGH", $user_settings["qworkedthrough"]);
		$this->tpl->setVariable("CHECKED_PWORKEDTHROUGH", $user_settings["pworkedthrough"]);
		$this->tpl->setVariable("CHECKED_TIMEOFWORK", $user_settings["timeofwork"]);
		$this->tpl->setVariable("CHECKED_ATIMEOFWORK", $user_settings["atimeofwork"]);
		$this->tpl->setVariable("CHECKED_FIRSTVISIT", $user_settings["firstvisit"]);
		$this->tpl->setVariable("CHECKED_LASTVISIT", $user_settings["lastvisit"]);
		$this->tpl->setVariable("CHECKED_RESULTSPOINTS", $user_settings["resultspoints"]);
		$this->tpl->setVariable("CHECKED_RESULTSMARKS", $user_settings["resultsmarks"]);
		$this->tpl->setVariable("CHECKED_DISTANCEMEDIAN", $user_settings["distancemedian"]);
		$this->tpl->setVariable("TXT_STATISTICAL_EVALUATION", $this->lng->txt("tst_statistical_evaluation"));
		$this->tpl->parseCurrentBlock();
	}

	function saveEvaluationSettings()
	{
		$eval_statistical_settings = array(
			"qworkedthrough" => $_POST["chb_result_qworkedthrough"],
			"pworkedthrough" => $_POST["chb_result_pworkedthrough"],
			"timeofwork" => $_POST["chb_result_timeofwork"],
			"atimeofwork" => $_POST["chb_result_atimeofwork"],
			"firstvisit" => $_POST["chb_result_firstvisit"],
			"lastvisit" => $_POST["chb_result_lastvisit"],
			"resultspoints" => $_POST["chb_result_resultspoints"],
			"resultsmarks" => $_POST["chb_result_resultsmarks"],
			"distancemedian" => $_POST["chb_result_distancemedian"]
		);
		$this->object->evalSaveStatisticalSettings($eval_statistical_settings, $ilUser->id);
		return $eval_statistical_settings;
	}
	
	function evalSelectedUsers($all_users = 0)
	{
		global $ilUser;

		if ($all_users)
		{
			$this->ctrl->setParameter($this, "etype", "all");
		}
		else
		{
			$this->ctrl->setParameter($this, "etype", "selected");
		}
		$this->tpl->setCurrentBlock("generic_css");
		$this->tpl->setVariable("LOCATION_GENERIC_STYLESHEET", "./assessment/templates/default/test_print.css");
		$this->tpl->setVariable("MEDIA_GENERIC_STYLESHEET", "print");
		$this->tpl->parseCurrentBlock();
		$savetextanswers = 0;
		$textanswers = 0;
		$export = 0;
		if (strcmp($_POST["cmd"][$this->ctrl->getCmd()], $this->lng->txt("export")) == 0)
		{
			$export = 1;
		}
		if (strcmp($_POST["cmd"][$this->ctrl->getCmd()], $this->lng->txt("save_text_answer_points")) == 0)
		{

			$savetextanswers = 1;
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/(\d+)_(\d+)_(\d+)/", $key, $matches))
				{
					include_once "./assessment/classes/class.assTextQuestion.php";
					ASS_TextQuestion::_setReachedPoints($matches[1], $this->object->getTestId(), $matches[2], $value, $matches[3]);
				}
			}
			sendInfo($this->lng->txt("text_answers_saved"));
		}
		if ((count($_POST) == 0) || ($export) || ($savetextanswers) || is_numeric($_GET["uid"]))
		{
			$user_settings = $this->object->evalLoadStatisticalSettings($ilUser->id);
			$eval_statistical_settings = array(
				"qworkedthrough" => $user_settings["qworkedthrough"],
				"pworkedthrough" => $user_settings["pworkedthrough"],
				"timeofwork" => $user_settings["timeofwork"],
				"atimeofwork" => $user_settings["atimeofwork"],
				"firstvisit" => $user_settings["firstvisit"],
				"lastvisit" => $user_settings["lastvisit"],
				"resultspoints" => $user_settings["resultspoints"],
				"resultsmarks" => $user_settings["resultsmarks"],
				"distancemedian" => $user_settings["distancemedian"]
			);
		}
		else
		{
			$eval_statistical_settings = $this->saveEvaluationSettings();
		}
//		$this->ctrl->setCmd("evalSelectedUsers");
		$this->setResultsTabs();
		$legend = array();
		$legendquestions = array();
		$titlerow = array();
		// build title columns
		$name_column = $this->lng->txt("name");
		if ($this->object->getTestType() == TYPE_SELF_ASSESSMENT)
		{
			$name_column = $this->lng->txt("counter");
		}
		array_push($titlerow, $name_column);
		
		$char = "A";
		if ($eval_statistical_settings["qworkedthrough"]) {
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_qworkedthrough");
			$char++;
		}
		if ($eval_statistical_settings["pworkedthrough"]) {
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_pworkedthrough");
			$char++;
		}
		if ($eval_statistical_settings["timeofwork"]) {
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_timeofwork");
			$char++;
		}
		if ($eval_statistical_settings["atimeofwork"]) {
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_atimeofwork");
			$char++;
		}
		if ($eval_statistical_settings["firstvisit"]) {
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_firstvisit");
			$char++;
		}
		if ($eval_statistical_settings["lastvisit"]) {
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_lastvisit");
			$char++;
		}
		if ($eval_statistical_settings["resultspoints"]) {
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_resultspoints");
			$char++;
		}
		if ($eval_statistical_settings["resultsmarks"]) {
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_resultsmarks");
			$char++;
			
			if ($this->object->ects_output)
			{
				array_push($titlerow, $char);
				$legend[$char] = $this->lng->txt("ects_grade");
				$char++;
			}
		}
		if ($eval_statistical_settings["distancemedian"]) {
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_mark_median");
			$char++;
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_rank_participant");
			$char++;
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_rank_median");
			$char++;
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_total_participants");
			$char++;
			array_push($titlerow, $char);
			$legend[$char] = $this->lng->txt("tst_stat_result_median");
			$char++;
		}
		
		$titlerow_without_questions = $titlerow;
		if (!$this->object->isRandomTest())
		{
			for ($i = 1; $i <= $this->object->getQuestionCount(); $i++)
			{
				array_push($titlerow, $this->lng->txt("question_short") . " " . $i);
//				$legend[$this->lng->txt("question_short") . " " . $i] = $this->object->getQuestionTitle($i);
				$legendquestions[$i] = $this->object->getQuestionTitle($i);
				$legend[$this->lng->txt("question_short") . " " . $i] = $i; 
				
			}
		}
		else
		{
			for ($i = 1; $i <= $this->object->getQuestionCount(); $i++)
			{
				array_push($titlerow, "&nbsp;");
			}
		}
		$total_users =& $this->object->evalTotalPersonsArray();
		$selected_users = array();
		if ($all_users == 1) 
		{
			$selected_users = $total_users;
		} 
		else 
		{
			$selected_users =& $this->object->getEvaluationUsers($ilUser->id);
			$selected_groups =& $this->object->getEvaluationGroups($ilUser->id);
			include_once("./classes/class.ilObjGroup.php");
			foreach ($selected_groups as $group_id)
			{
				$grp = new ilObjGroup($group_id, true);
				$members = $grp->getGroupMemberIds();
				foreach ($members as $member_id)
				{
					if (array_key_exists($member_id, $total_users))
					{
						include_once "./classes/class.ilObjUser.php";
						$usr = new ilObjUser($member_id); 
						$selected_users[$member_id] = trim($usr->firstname . " " . $usr->lastname);
					}
				}
			}
		}
//			$ilBench->stop("Test_Statistical_evaluation", "getAllParticipants");
		$row = 0;
		$question_legend = false;
		$question_stat = array();
		$evaluation_array = array();
		foreach ($total_users as $key => $value) 
		{
			// receive array with statistical information on the test for a specific user
//				$ilBench->start("Test_Statistical_evaluation", "this->object->evalStatistical($key)");
			$stat_eval =& $this->object->evalStatistical($key);
			foreach ($stat_eval as $sindex => $sarray)
			{
				if (preg_match("/\d+/", $sindex))
				{
					$qt = $sarray["title"];
					$qt = preg_replace("/<.*?>/", "", $qt);
					if (!array_key_exists($sarray["qid"], $question_stat))
					{
						$question_stat[$sarray["qid"]] = array("max" => 0, "reached" => 0, "title" => $qt);
					}
					$question_stat[$sarray["qid"]]["single_max"] = $sarray["max"];
					$question_stat[$sarray["qid"]]["max"] += $sarray["max"];
					$question_stat[$sarray["qid"]]["reached"] += $sarray["reached"];
				}
			}
//				$ilBench->stop("Test_Statistical_evaluation", "this->object->evalStatistical($key)");
			$evaluation_array[$key] = $stat_eval;
		}

		include_once "./classes/class.ilStatistics.php";
		// calculate the median
		$median_array = array();
		foreach ($evaluation_array as $key => $value)
		{
			array_push($median_array, $value["resultspoints"]);
		}
		include_once "./classes/class.ilStatistics.php";
		$statistics = new ilStatistics();
		$statistics->setData($median_array);
		$median = $statistics->median();
//			$ilBench->stop("Test_Statistical_evaluation", "calculate all statistical data");
//			$ilBench->save();
		$evalcounter = 1;
		$question_titles = array();
		$question_title_counter = 1;
		$eval_complete = array();
		foreach ($selected_users as $key => $name)
		{
			$stat_eval = $evaluation_array[$key];
			
			$titlerow_user = array();
			if ($this->object->isRandomTest())
			{
				include_once "./assessment/classes/class.ilObjTest.php";
				$counted_pass = ilObjTest::_getResultPass($key, $this->object->getTestId());
				$this->object->loadQuestions($key, $counted_pass);
				$titlerow_user = $titlerow_without_questions;
				$i = 1;
				foreach ($stat_eval as $key1 => $value1)
				{
					if (preg_match("/\d+/", $key1))
					{
						$qt = $value1["title"];
						$qt = preg_replace("/<.*?>/", "", $qt);
						if (!array_key_exists($value1["qid"], $legendquestions))
						{
							array_push($titlerow_user, $this->lng->txt("question_short") . " " . $question_title_counter);
							$legend[$this->lng->txt("question_short") . " " . $question_title_counter] = $value1["qid"];
							$legendquestions[$value1["qid"]] = $qt;
							$question_title_counter++;
						}
						else
						{
							$arraykey = array_search($value1["qid"], $legend);
							array_push($titlerow_user, $arraykey);
						}
					}
				}
			}

			$evalrow = array();
			$username = $evalcounter++; 
			if ($this->object->getTestType() != TYPE_SELF_ASSESSMENT)
			{
				$username = $selected_users[$key];
			}
			array_push($evalrow, array(
				"html" => "<a href=\"".$this->ctrl->getLinkTargetByClass(get_class($this), "evalUserDetail")."&uid=$key\">$username</a>",
				"xls"  => $username,
				"csv"  => $username
			));
			if ($eval_statistical_settings["qworkedthrough"]) {
				array_push($evalrow, array(
					"html" => $stat_eval["qworkedthrough"],
					"xls"  => $stat_eval["qworkedthrough"],
					"csv"  => $stat_eval["qworkedthrough"]
				));
			}
			if ($eval_statistical_settings["pworkedthrough"]) {
				array_push($evalrow, array(
					"html" => sprintf("%2.2f", $stat_eval["pworkedthrough"] * 100.0) . " %",
					"xls"  => $stat_eval["pworkedthrough"],
					"csv"  => $stat_eval["pworkedthrough"],
					"format" => "%"
				));
			}
			if ($eval_statistical_settings["timeofwork"]) {
				$time = $stat_eval["timeofwork"];
				$time_seconds = $time;
				$time_hours    = floor($time_seconds/3600);
				$time_seconds -= $time_hours   * 3600;
				$time_minutes  = floor($time_seconds/60);
				$time_seconds -= $time_minutes * 60;
				array_push($evalrow, array(
					"html" => sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds),
					"xls"  => $stat_eval["timeofwork"],
					"csv"  => $stat_eval["timeofwork"]
				));
			}
			if ($eval_statistical_settings["atimeofwork"]) {
				$time = $stat_eval["atimeofwork"];
				$time_seconds = $time;
				$time_hours    = floor($time_seconds/3600);
				$time_seconds -= $time_hours   * 3600;
				$time_minutes  = floor($time_seconds/60);
				$time_seconds -= $time_minutes * 60;
				array_push($evalrow, array(
					"html" => sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds),
					"xls"  => $stat_eval["atimeofwork"],
					"csv"  => $stat_eval["atimeofwork"]
				));
			}
			if ($eval_statistical_settings["firstvisit"]) {
				array_push($evalrow, array(
					"html" => date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], mktime($stat_eval["firstvisit"]["hours"], $stat_eval["firstvisit"]["minutes"], $stat_eval["firstvisit"]["seconds"], $stat_eval["firstvisit"]["mon"], $stat_eval["firstvisit"]["mday"], $stat_eval["firstvisit"]["year"])),
					"xls"  => ilUtil::excelTime($stat_eval["firstvisit"]["year"],$stat_eval["firstvisit"]["mon"],$stat_eval["firstvisit"]["mday"],$stat_eval["firstvisit"]["hours"],$stat_eval["firstvisit"]["minutes"],$stat_eval["firstvisit"]["seconds"]),
					"csv"  => date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], mktime($stat_eval["firstvisit"]["hours"], $stat_eval["firstvisit"]["minutes"], $stat_eval["firstvisit"]["seconds"], $stat_eval["firstvisit"]["mon"], $stat_eval["firstvisit"]["mday"], $stat_eval["firstvisit"]["year"])),
					"format" => "t"
				));
			}
			if ($eval_statistical_settings["lastvisit"]) {
				array_push($evalrow, array(
					"html" => date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], mktime($stat_eval["lastvisit"]["hours"], $stat_eval["lastvisit"]["minutes"], $stat_eval["lastvisit"]["seconds"], $stat_eval["lastvisit"]["mon"], $stat_eval["lastvisit"]["mday"], $stat_eval["lastvisit"]["year"])),
					"xls"  => ilUtil::excelTime($stat_eval["lastvisit"]["year"],$stat_eval["lastvisit"]["mon"],$stat_eval["lastvisit"]["mday"],$stat_eval["lastvisit"]["hours"],$stat_eval["lastvisit"]["minutes"],$stat_eval["lastvisit"]["seconds"]),
					"csv"  => date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], mktime($stat_eval["lastvisit"]["hours"], $stat_eval["lastvisit"]["minutes"], $stat_eval["lastvisit"]["seconds"], $stat_eval["lastvisit"]["mon"], $stat_eval["lastvisit"]["mday"], $stat_eval["lastvisit"]["year"])),
					"format" => "t"
				));
			}
			if ($eval_statistical_settings["resultspoints"]) {
				array_push($evalrow, array(
					"html" => $stat_eval["resultspoints"]." ".strtolower($this->lng->txt("of"))." ". $stat_eval["maxpoints"],
					"xls"  => $stat_eval["resultspoints"],
					"csv"  => $stat_eval["resultspoints"]
				));
			}
			if ($eval_statistical_settings["resultsmarks"]) {
				array_push($evalrow, array(
					"html" => $stat_eval["resultsmarks"],
					"xls"  => $stat_eval["resultsmarks"],
					"csv"  => $stat_eval["resultsmarks"]
				));

				if ($this->object->ects_output)
				{
					$mark_ects = $this->object->getECTSGrade($stat_eval["resultspoints"],$stat_eval["maxpoints"]);
					array_push($evalrow, array(
						"html" => $mark_ects,
						"xls"  => $mark_ects,
						"csv"  => $mark_ects
					));
				}
			}
			
			if ($eval_statistical_settings["distancemedian"]) {
				if ($stat_eval["maxpoints"] == 0)
				{
					$pct = 0;
				}
				else
				{
					$pct = ($median / $stat_eval["maxpoints"]) * 100.0;
				}
				$mark = $this->object->mark_schema->get_matching_mark($pct);
				$mark_short_name = "";
				if ($mark)
				{
					$mark_short_name = $mark->get_short_name();
				}
				array_push($evalrow, array(
					"html" => $mark_short_name,
					"xls"  => $mark_short_name,
					"csv"  => $mark_short_name
				));
				$rank_participant = $statistics->rank($stat_eval["resultspoints"]);
				array_push($evalrow, array(
					"html" => $rank_participant,
					"xls"  => $rank_participant,
					"csv"  => $rank_participant
				));
				$rank_median = $statistics->rank_median();
				array_push($evalrow, array(
					"html" => $rank_median,
					"xls"  => $rank_median,
					"csv"  => $rank_median
				));
				$total_participants = count($median_array);
				array_push($evalrow, array(
					"html" => $total_participants,
					"xls"  => $total_participants,
					"csv"  => $total_participants
				));
				array_push($evalrow, array(
					"html" => $median,
					"xls"  => $median,
					"csv"  => $median
				));
			}
			
			for ($i = 1; $i <= $this->object->getQuestionCount(); $i++)
			{
				$qshort = "";
				$qt = "";
				if ($this->object->isRandomTest())
				{
					$qt = $stat_eval[$i-1]["title"];
					$qt = preg_replace("/<.*?>/", "", $qt);
					$arrkey = array_search($stat_eval[$i-1]["qid"], $legend);
					if ($arrkey)
					{
						$qshort = "<span title=\"" . ilUtil::prepareFormOutput($qt) . "\">" . $arrkey . "</span>: ";
					}
				}

				$htmloutput = "";
				if ($stat_eval[$i-1]["type"] == "qt_text")
				{
					// Text question
					$name = $key."_".$stat_eval[$i-1]["qid"]."_".$stat_eval[$i-1]["max"];
					$htmloutput = $qshort . "<input type=\"text\" name=\"".$name."\" size=\"3\" value=\"".$stat_eval[$i-1]["reached"]."\" />".strtolower($this->lng->txt("of"))." ". $stat_eval[$i-1]["max"];
					// Solution
					$htmloutput .= " [<a href=\"".$this->ctrl->getLinkTargetByClass(get_class($this), "evaluationDetail") . "&userdetail=$key&answer=".$stat_eval[$i-1]["qid"]."\" target=\"popup\" onclick=\"";
					$htmloutput .= "window.open('', 'popup', 'width=600, height=200, scrollbars=no, toolbar=no, status=no, resizable=yes, menubar=no, location=no, directories=no')";
					$htmloutput .= "\">".$this->lng->txt("tst_eval_show_answer")."</a>]";
					$textanswers++;
				}
					else
				{
					$htmloutput = $qshort . $stat_eval[$i-1]["reached"] . " " . strtolower($this->lng->txt("of")) . " " .  $stat_eval[$i-1]["max"];
				}

				array_push($evalrow, array(
					"html" => $htmloutput,
					"xls"  => $stat_eval[$i-1]["reached"],
					"csv"  => $stat_eval[$i-1]["reached"]
				));
			}
			array_push($eval_complete, array("title" => $titlerow_user, "data" => $evalrow));
		}

		$noqcount = count($titlerow_without_questions);
		if ($export)
		{
			$testname = preg_replace("/\s/", "_", $this->object->getTitle());
			switch ($_POST["export_type"])
			{
				case TYPE_XLS_PC:
				case TYPE_XLS_MAC:
					// Creating a workbook
					include_once './classes/Spreadsheet/Excel/Writer.php';
					$workbook = new Spreadsheet_Excel_Writer();
	
					// sending HTTP headers
					$workbook->send("$testname.xls");
	
					// Creating a worksheet
					$format_bold =& $workbook->addFormat();
					$format_bold->setBold();
					$format_percent =& $workbook->addFormat();
					$format_percent->setNumFormat("0.00%");
					$format_datetime =& $workbook->addFormat();
					$format_datetime->setNumFormat("DD/MM/YYYY hh:mm:ss");
					$format_title =& $workbook->addFormat();
					$format_title->setBold();
					$format_title->setColor('black');
					$format_title->setPattern(1);
					$format_title->setFgColor('silver');
					$worksheet =& $workbook->addWorksheet();
					$row = 0;
					$col = 0;
					include_once "./classes/class.ilExcelUtils.php";
					if (!$this->object->isRandomTest())
					{
						foreach ($titlerow as $title)
						{
							if (preg_match("/\d+/", $title))
							{
								$worksheet->write($row, $col, ilExcelUtils::_convert_text($legendquestions[$legend[$title]], $_POST["export_type"]), $format_title);
							}
							else
							{
								$worksheet->write($row, $col, ilExcelUtils::_convert_text($legend[$title], $_POST["export_type"]), $format_title);
							}
							$col++;
						}
						$row++;
					}
					foreach ($eval_complete as $evalrow)
					{
						$col = 0;
						if ($this->object->isRandomTest())
						{
							foreach ($evalrow["title"] as $key => $value)
							{
								if ($key == 0)
								{
									$worksheet->write($row, $col, ilExcelUtils::_convert_text($value, $_POST["export_type"]), $format_title);
								}
								else
								{
									if (preg_match("/\d+/", $value))
									{
										$worksheet->write($row, $col, ilExcelUtils::_convert_text($legendquestions[$legend[$value]], $_POST["export_type"]), $format_title);
									}
									else
									{
										$worksheet->write($row, $col, ilExcelUtils::_convert_text($legend[$value], $_POST["export_type"]), $format_title);
									}
								}
								$col++;
							}
							$row++;
						}
						$col = 0;
						foreach ($evalrow["data"] as $key => $value)
						{
							switch ($value["format"])
							{
								case "%":
									$worksheet->write($row, $col, $value["xls"], $format_percent);
									break;
								case "t":
									$worksheet->write($row, $col, $value["xls"], $format_datetime);
									break;
								default:
									$worksheet->write($row, $col, ilExcelUtils::_convert_text($value["xls"], $_POST["export_type"]));
									break;
							}
							$col++;
						}
						$row++;
					}
					$workbook->close();
					exit;
				case TYPE_SPSS:
					$csv = "";
					$separator = ";";
					if (!$this->object->isRandomTest())
					{
						$titlerow =& $this->object->processCSVRow($titlerow, TRUE, $separator);
						$csv .= join($titlerow, $separator) . "\n";
					}
					foreach ($eval_complete as $evalrow)
					{
						$csvrow = array();
						foreach ($evalrow["data"] as $dataarray)
						{
							array_push($csvrow, $dataarray["csv"]);
						}
						if ($this->object->isRandomTest())
						{
							$evalrow["title"] =& $this->object->processCSVRow($evalrow["title"], TRUE, $separator);
							$csv .= join($evalrow["title"], $separator) . "\n";
						}
						$csvarr = array();
						$evalrow["data"] =& $this->object->processCSVRow($csvrow, TRUE, $separator);
						$csv .= join($evalrow["data"], $separator) . "\n";
					}
					ilUtil::deliverData($csv, "$testname.csv");
					break;
			}
			exit;
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_eval_statistical_evaluation.html", true);
		$color_class = array("tblrow1", "tblrow2");
		foreach ($legend as $short => $long)
		{
			$this->tpl->setCurrentBlock("legendrow");
			$this->tpl->setVariable("TXT_SYMBOL", $short);
			if (preg_match("/\d+/", $short))
			{
				$this->tpl->setVariable("TXT_MEANING", $legendquestions[$long]);
			}
			else
			{
				$this->tpl->setVariable("TXT_MEANING", $long);
			}
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("legend");
		$this->tpl->setVariable("TXT_LEGEND", $this->lng->txt("legend"));
		$this->tpl->setVariable("TXT_LEGEND_LINK", $this->lng->txt("eval_legend_link"));
		$this->tpl->setVariable("TXT_SYMBOL", $this->lng->txt("symbol"));
		$this->tpl->setVariable("TXT_MEANING", $this->lng->txt("meaning"));
		$this->tpl->parseCurrentBlock();

		$counter = 0;
		foreach ($question_stat as $title => $values)
		{
			$this->tpl->setCurrentBlock("meanrow");
			$this->tpl->setVariable("TXT_QUESTION", ilUtil::prepareFormOutput($values["title"]));
			$percent = 0;
			if ($values["max"] > 0)
			{
				$percent = $values["reached"] / $values["max"];
			}
			$this->tpl->setVariable("TXT_MEAN", sprintf("%.2f", $values["single_max"]*$percent) . " " . strtolower($this->lng->txt("of")) . " " . sprintf("%.2f", $values["single_max"]) . " (" . sprintf("%.2f", $percent*100) . " %)");
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("question_mean_points");
		$this->tpl->setVariable("TXT_AVERAGE_POINTS", $this->lng->txt("average_reached_points"));
		$this->tpl->setVariable("TXT_QUESTION", $this->lng->txt("question_title"));
		$this->tpl->setVariable("TXT_MEAN", $this->lng->txt("average_reached_points"));
		$this->tpl->parseCurrentBlock();
		
		$noq = $noqcount;		
		foreach ($titlerow as $title)
		{
			if ($noq > 0)
			{
				$this->tpl->setCurrentBlock("titlecol");
				$this->tpl->setVariable("TXT_TITLE", "<div title=\"" . ilUtil::prepareFormOutput($legendquestions[$legend[$title]]) . "\">" . $title . "</div>");
				$this->tpl->parseCurrentBlock();
				if ($noq == $noqcount)
				{
					$this->tpl->setCurrentBlock("questions_titlecol");
					$this->tpl->setVariable("TXT_TITLE", $title);
					$this->tpl->parseCurrentBlock();
				}
				$noq--;
			}
			else
			{
				$this->tpl->setCurrentBlock("questions_titlecol");
				$this->tpl->setVariable("TXT_TITLE", "<div title=\"" . $legendquestions[$legend[$title]] . "\">" . $title . "</div>");
				$this->tpl->parseCurrentBlock();
			}
		}
		$counter = 0;
		foreach ($eval_complete as $row)
		{
			$noq = $noqcount;
			foreach ($row["data"] as $key => $value)
			{
				if ($noq > 0)
				{
					$this->tpl->setCurrentBlock("datacol");
					$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
					$this->tpl->setVariable("TXT_DATA", $value["html"]);
					$this->tpl->parseCurrentBlock();
					if ($noq == $noqcount)
					{
						$this->tpl->setCurrentBlock("questions_datacol");
						$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
						$this->tpl->setVariable("TXT_DATA", $value["html"]);
						$this->tpl->parseCurrentBlock();
					}
					$noq--;
				}
				else
				{
					$this->tpl->setCurrentBlock("questions_datacol");
					$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
					$this->tpl->setVariable("TXT_DATA", $value["html"]);
					$this->tpl->parseCurrentBlock();
				}
			}
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("questions_row");
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$this->tpl->parseCurrentBlock();
			$counter++;
		}

		if ($textanswers)
		{
			$this->tpl->setCurrentBlock("questions_output_button");
			$this->tpl->setVariable("BUTTON_SAVE", $this->lng->txt("save_text_answer_points"));
			$this->tpl->setVariable("BTN_COMMAND", $this->ctrl->getCmd());
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("questions_output");
		$this->tpl->setVariable("TXT_QUESTIONS",  $this->lng->txt("ass_questions"));
		$this->tpl->setVariable("FORM_ACTION_RESULTS", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("export_btn");
		$this->tpl->setVariable("EXPORT_DATA", $this->lng->txt("exp_eval_data"));
		$this->tpl->setVariable("TEXT_EXCEL", $this->lng->txt("exp_type_excel"));
		$this->tpl->setVariable("TEXT_EXCEL_MAC", $this->lng->txt("exp_type_excel_mac"));
		$this->tpl->setVariable("TEXT_CSV", $this->lng->txt("exp_type_spss"));
		$this->tpl->setVariable("BTN_EXPORT", $this->lng->txt("export"));
		$this->tpl->setVariable("BTN_PRINT", $this->lng->txt("print"));
		$this->tpl->setVariable("BTN_COMMAND", $this->ctrl->getCmd());
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TXT_STATISTICAL_DATA", $this->lng->txt("statistical_data"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setVariable("PAGETITLE", $this->object->getTitle());
	}
	
	function evalAllUsers()
	{
		$this->evalSelectedUsers(1);
	}
	
	function eval_a()
	{
		$this->setResultsTabs();
		$color_class = array("tblrow1", "tblrow2");
		$counter = 0;
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_eval_anonymous_aggregation.html", true);
		$total_persons = $this->object->evalTotalPersons();
		if ($total_persons) {
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("tst_eval_total_persons"));
			$this->tpl->setVariable("TXT_VALUE", $total_persons);
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("tst_eval_total_finished"));
			$total_finished = $this->object->evalTotalFinished();
			$this->tpl->setVariable("TXT_VALUE", $total_finished);
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("row");
			$average_time = $this->object->evalTotalFinishedAverageTime();
			$diff_seconds = $average_time;
			$diff_hours    = floor($diff_seconds/3600);
			$diff_seconds -= $diff_hours   * 3600;
			$diff_minutes  = floor($diff_seconds/60);
			$diff_seconds -= $diff_minutes * 60;
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("tst_eval_total_finished_average_time"));
			$this->tpl->setVariable("TXT_VALUE", sprintf("%02d:%02d:%02d", $diff_hours, $diff_minutes, $diff_seconds));
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$passed_tests = $this->object->evalTotalFinishedPassed();
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("tst_eval_total_passed"));
			$this->tpl->setVariable("TXT_VALUE", $passed_tests["total_passed"]);
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("tst_eval_total_passed_average_points"));
			$this->tpl->setVariable("TXT_VALUE", sprintf("%2.2f", $passed_tests["average_points"]) . " " . strtolower($this->lng->txt("of")) . " " . sprintf("%2.2f", $passed_tests["maximum_points"]));
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$this->tpl->parseCurrentBlock();
		} else {
			$this->tpl->setCurrentBlock("emptyrow");
			$this->tpl->setVariable("TXT_NO_ANONYMOUS_AGGREGATION", $this->lng->txt("tst_eval_no_anonymous_aggregation"));
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TXT_ANON_EVAL", $this->lng->txt("tst_anon_eval"));
		$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("result"));
		$this->tpl->setVariable("TXT_VALUE", $this->lng->txt("value"));
		$this->tpl->parseCurrentBlock();
	}

/**
* Output of the learner overview for a varying random test
*
* Output of the learner overview for a varying random test
*
* @access public
*/
	function evalUserDetail()
	{
		$user_id = $_GET["uid"];
		$this->ctrl->saveParameter($this, "uid");		
		if (!is_numeric($user_id))
		{
			$this->ctrl->redirect($this, "eval_stat");
		}
		if ($this->object->getTestType() != TYPE_VARYING_RANDOMTEST)
		{
			$this->ctrl->redirect($this, "passDetails");
		}
		include_once "./assessment/classes/class.ilObjTest.php";
		$counted_pass = ilObjTest::_getResultPass($user_id, $this->object->getTestId());
		$this->setResultsTabs();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_eval_user_detail_overview.html", true);
		$color_class = array("tblrow1", "tblrow2");
		$counter = 0;
		$reached_pass = $this->object->_getPass($user_id, $this->object->getTestId());
		for ($pass = 0; $pass <= $reached_pass; $pass++)
		{
			$finishdate = $this->object->getPassFinishDate($user_id, $this->object->getTestId(), $pass);
			if ($finishdate > 0)
			{
				$result_array =& $this->object->getTestResult($user_id, $pass);
				if (!$result_array["test"]["total_max_points"])
				{
					$percentage = 0;
				}
				else
				{
					$percentage = ($result_array["test"]["total_reached_points"]/$result_array["test"]["total_max_points"])*100;
				}
				$total_max = $result_array["test"]["total_max_points"];
				$total_reached = $result_array["test"]["total_reached_points"];
				$this->tpl->setCurrentBlock("result_row");
				if ($pass == $counted_pass)
				{
					$this->tpl->setVariable("COLOR_CLASS", "tblrowmarked");
				}
				else
				{
					$this->tpl->setVariable("COLOR_CLASS", $color_class[$pass % 2]);
				}
				$this->tpl->setVariable("VALUE_PASS", $pass + 1);
				$this->tpl->setVariable("VALUE_DATE", ilFormat::formatDate(ilFormat::ftimestamp2dateDB($finishdate), "date"));
				$this->tpl->setVariable("VALUE_ANSWERED", $this->object->getAnsweredQuestionCount($user_id, $this->object->getTestId(), $pass) . " " . strtolower($this->lng->txt("of")) . " " . (count($result_array)-1));
				$this->tpl->setVariable("VALUE_REACHED", $total_reached . " " . strtolower($this->lng->txt("of")) . " " . $total_max);
				$this->tpl->setVariable("VALUE_PERCENTAGE", sprintf("%.2f", $percentage) . "%");
				if ($this->object->canViewResults())
				{
					$this->tpl->setVariable("HREF_PASS_DETAILS", "<a href=\"".$this->ctrl->getLinkTargetByClass(get_class($this), "passDetails")."&pass=$pass\">" . $this->lng->txt("show_details") . "</a>");
				}
				$this->tpl->parseCurrentBlock();
			}
		}
		$this->tpl->setCurrentBlock("test_user_name");
		include_once "./classes/class.ilObjUser.php";
		$uname = ilObjUser::_lookupName($user_id);
		$struname = trim($uname["title"] . " " . $uname["firstname"] . " " . $uname["lastname"]);
		$this->tpl->setVariable("USER_NAME", sprintf($this->lng->txt("tst_result_user_name"), $struname));
		$this->tpl->setVariable("TEXT_RESULTS", $this->lng->txt("tst_results"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("PASS_COUNTER", $this->lng->txt("pass"));
		$this->tpl->setVariable("DATE", $this->lng->txt("date"));
		$this->tpl->setVariable("ANSWERED_QUESTIONS", $this->lng->txt("tst_answered_questions"));
		$this->tpl->setVariable("REACHED_POINTS", $this->lng->txt("tst_reached_points"));
		$this->tpl->setVariable("PERCENTAGE_CORRECT", $this->lng->txt("tst_percent_solved"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("BACK_TO_EVALUATION", $this->lng->txt("tst_results_back_evaluation"));
		$back_command = ($_GET["etype"] == "all") ? "evalAllUsers"	: "evalSelectedUsers";
		$this->tpl->setVariable("BACK_COMMAND", $back_command);
		$this->tpl->parseCurrentBlock();
	}
	
/**
* Output of the learners view of an existing test
*
* Output of the learners view of an existing test
*
* @access public
*/
	function passDetails() 
	{
		function sort_percent($a, $b) 
		{
			if (strcmp($_GET["order"], "ASC")) 
			{
				$smaller = 1;
				$greater = -1;
			} 
			else 
			{
				$smaller = -1;
				$greater = 1;
			}
			if ($a["percent"] == $b["percent"]) 
			{
				if ($a["nr"] == $b["nr"]) return 0;
		 	 	return ($a["nr"] < $b["nr"]) ? -1 : 1;
			}
			return ($a["percent"] < $b["percent"]) ? $smaller : $greater;
		}

		function sort_nr($a, $b) 
		{
			if (strcmp($_GET["order"], "ASC")) 
			{
				$smaller = 1;
				$greater = -1;
			} 
			else 
			{
				$smaller = -1;
				$greater = 1;
			}
			if ($a["nr"] == $b["nr"]) return 0;
			return ($a["nr"] < $b["nr"]) ? $smaller : $greater;
		}

		$pass = $_GET["pass"];
		$user_id = $_GET["uid"];
		$this->ctrl->saveParameter($this, "uid");		
		$this->ctrl->saveParameter($this, "pass");		
		if (!is_numeric($pass)) $pass = NULL;
		
		$this->setResultsTabs();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_eval_user_detail_detail.html", true);
		$color_class = array("tblrow1", "tblrow2");
		$counter = 0;
		$result_array =& $this->object->getTestResult($user_id, $pass);

		if (!$result_array["test"]["total_max_points"])
		{
			$percentage = 0;
		}
		else
		{
			$percentage = ($result_array["test"]["total_reached_points"]/$result_array["test"]["total_max_points"])*100;
		}
		$total_max = $result_array["test"]["total_max_points"];
		$total_reached = $result_array["test"]["total_reached_points"];
		$img_title_percent = "";
		$img_title_nr = "";
		switch ($_GET["sortres"]) 
		{
			case "percent":
				usort($result_array, "sort_percent");
				$img_title_percent = " <img src=\"" . ilUtil::getImagePath(strtolower($_GET["order"]) . "_order.png", true) . "\" alt=\"".$this->lng->txt(strtolower($_GET["order"])."ending_order")."\" />";
				if (strcmp($_GET["order"], "ASC") == 0) 
				{
					$sortpercent = "DESC";
				} 
				else 
				{
					$sortpercent = "ASC";
				}
				break;
			case "nr":
				usort($result_array, "sort_nr");
				$img_title_nr = " <img src=\"" . ilUtil::getImagePath(strtolower($_GET["order"]) . "_order.png", true) . "\" alt=\"".$this->lng->txt(strtolower($_GET["order"])."ending_order")."\" />";
				if (strcmp($_GET["order"], "ASC") == 0) 
				{
					$sortnr = "DESC";
				} 
				else 
				{
					$sortnr = "ASC";
				}
				break;
		}
		if (!$sortpercent) 
		{
			$sortpercent = "ASC";
		}
		if (!$sortnr) 
		{
			$sortnr = "ASC";
		}

		foreach ($result_array as $key => $value) 
		{
			if (preg_match("/\d+/", $key)) 
			{
				$this->tpl->setCurrentBlock("question_row");
				$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
				$this->tpl->setVariable("VALUE_QUESTION_COUNTER", $value["nr"]);
				//if ($this->object->isOnlineTest())
					$this->tpl->setVariable("VALUE_QUESTION_TITLE", $value["title"]);
				//else
				//	$this->tpl->setVariable("VALUE_QUESTION_TITLE", "<a href=\"" . $this->ctrl->getLinkTargetByClass(get_class($this), "run") . "&evaluation=" . $value["qid"] . "\">" . $value["title"] . "</a>");
				$this->tpl->setVariable("VALUE_MAX_POINTS", $value["max"]);
				$this->tpl->setVariable("VALUE_REACHED_POINTS", $value["reached"]);
				$this->tpl->setVariable("VALUE_PERCENT_SOLVED", $value["percent"]);
				$this->tpl->parseCurrentBlock();
				$counter++;
			}
		}

		$this->tpl->setCurrentBlock("question_row");
		$this->tpl->setVariable("COLOR_CLASS", "std");
		$this->tpl->setVariable("VALUE_QUESTION_COUNTER", "<strong>" . $this->lng->txt("total") . "</strong>");
		$this->tpl->setVariable("VALUE_QUESTION_TITLE", "");
		$this->tpl->setVariable("VALUE_MAX_POINTS", "<strong>" . sprintf("%d", $total_max) . "</strong>");
		$this->tpl->setVariable("VALUE_REACHED_POINTS", "<strong>" . sprintf("%d", $total_reached) . "</strong>");
		$this->tpl->setVariable("VALUE_PERCENT_SOLVED", "<strong>" . sprintf("%2.2f", $percentage) . " %" . "</strong>");
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("test_user_name");
		include_once "./classes/class.ilObjUser.php";
		$uname = ilObjUser::_lookupName($user_id);
		$struname = trim($uname["title"] . " " . $uname["firstname"] . " " . $uname["lastname"]);
		$this->tpl->setVariable("USER_NAME", sprintf($this->lng->txt("tst_result_user_name"), $struname));
		$this->tpl->parseCurrentBlock();

		if ($this->object->isRandomTest())
		{
			$this->object->loadQuestions($user_id, $pass);
		}
		$counter = 1;
		// output of questions with solutions
		$ilUser = new ilObjUser($user_id);
		foreach ($this->object->questions as $question_id)
		{
			$this->tpl->setCurrentBlock("question");
			$question_gui = $this->object->createQuestionGUI("", $question_id);

			$this->tpl->setVariable("COUNTER_QUESTION", $counter.".&nbsp;");
			$this->tpl->setVariable("QUESTION_TITLE", $question_gui->object->getTitle());
			$idx = $this->object->test_id;
			switch ($question_gui->getQuestionType()) 
			{
				case "qt_imagemap" :
					$question_gui->outWorkingForm($idx, false, $show_solutions=false, $formaction, $show_question_page=false, $show_solution_only = false, $ilUser, $pass, $mixpass = true);
					break;
				case "qt_javaapplet" :
					$question_gui->outWorkingForm($idx, $is_postponed = false, $showsolution = 0, $show_question_page=false, $show_solution_only = false, $ilUser, $pass, $mixpass = true);
					break;
				default :
					$question_gui->outWorkingForm($idx, $is_postponed = false, $showsolution = 0, $show_question_page=false, $show_solution_only = false, $ilUser, $pass, $mixpass = true);
			}
			$this->tpl->parseCurrentBlock();
			$counter ++;
		}
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEST_RESULTS_BY_PASS", $this->lng->txt("tst_eval_results_by_pass"));
		$this->tpl->setVariable("TEXT_RESULTS", $this->lng->txt("tst_results"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("QUESTION_COUNTER", "<a href=\"" . $this->ctrl->getLinkTargetByClass(get_class($this), "passDetails") . "&sortres=nr&order=$sortnr\">" . $this->lng->txt("tst_question_no") . "</a>$img_title_nr");
		$this->tpl->setVariable("QUESTION_TITLE", $this->lng->txt("tst_question_title"));
		$this->tpl->setVariable("MAX_POINTS", $this->lng->txt("tst_maximum_points"));
		$this->tpl->setVariable("REACHED_POINTS", $this->lng->txt("tst_reached_points"));
		$this->tpl->setVariable("PERCENT_SOLVED", "<a href=\"" . $this->ctrl->getLinkTargetByClass(get_class($this), "passDetails") . "&sortres=percent&order=$sortpercent\">" . $this->lng->txt("tst_percent_solved") . "</a>$img_title_percent");
		$back_command = "evalUserDetail";
		$back_text = $this->lng->txt("tst_results_back_overview");
		if (is_null($pass))
		{
			$back_command = ($_GET["etype"] == "all") ? "evalAllUsers"	: "evalSelectedUsers";
			$back_text = $this->lng->txt("tst_results_back_evaluation");
		}
		$this->tpl->setVariable("BACK_TO_OVERVIEW", $back_text);
		$this->tpl->setVariable("BACK_COMMAND", $back_command);
		$this->tpl->parseCurrentBlock();
	}

	function outShowAnswers($isForm, &$ilUser) 
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_print_answers_sheet_details.html", true);		
		$this->outShowAnswersDetails($isForm, $ilUser);
	}
	
	function outShowAnswersDetails($isForm, &$ilUser) 
	{
		$tpl = &$this->tpl;		 				
		$invited_users = array_pop($this->object->getInvitedUsers($ilUser->getId()));
		$active = $this->object->getActiveTestUser($ilUser->getId());
		$t = $active->submittimestamp;
		
		// output of submit date and signature
		if ($active->submitted)
		{
			// only display submit date when it exists (not in the summary but in the print form)
			$tpl->setCurrentBlock("freefield_bottom");
			$tpl->setVariable("TITLE", $this->object->getTitle());
			$tpl->setVariable("TXT_DATE", $this->lng->txt("date"));
			$tpl->setVariable("VALUE_DATE", strftime("%Y-%m-%d %H:%M:%S", ilUtil::date_mysql2time($t)));
			$tpl->setVariable("TXT_ANSWER_SHEET", $this->lng->txt("tst_answer_sheet"));
	
			$freefieldtypes = array ("freefield_bottom" => 	array(	array ("title" => $this->lng->txt("tst_signature"), "length" => 300)));
	/*					"freefield_top" => 		array (	array ("title" => $this->lng->txt("semester"), "length" => 300), 
															array ("title" => $this->lng->txt("career"), "length" => 300)
															 ),*/
						
			
			
			foreach ($freefieldtypes as $type => $freefields) {
				$counter = 0;
	
				while ($counter < count ($freefields)) {
					$freefield = $freefields[$counter];
					
					//$tpl->setCurrentBlock($type);
				
					$tpl->setVariable("TXT_FREE_FIELD", $freefield["title"]);
					$tpl->setVariable("VALUE_FREE_FIELD", "<img height=\"30px\" alt=\"".$this->lng->txt("spacer")."\" border=\"0\" src=\"".ilUtil :: getImagePath("spacer.gif", false)."\" width=\"".$freefield["length"]."px\" />");
				
					$counter ++;
				
					//$tpl->parseCurrentBlock($type);
				}
			}
			$tpl->parseCurrentBlock();
		}

		$counter = 1;
		
		// output of questions with solutions
		foreach ($this->object->questions as $question) 
		{
			$tpl->setCurrentBlock("question");
			$question_gui = $this->object->createQuestionGUI("", $question);

			$tpl->setVariable("COUNTER_QUESTION", $counter.". ");
			$tpl->setVariable("QUESTION_TITLE", $question_gui->object->getTitle());
			
			$idx = $this->object->test_id;
			
			switch ($question_gui->getQuestionType()) {
				case "qt_imagemap" :
					$question_gui->outWorkingForm($idx, false, $show_solutions=false, $formaction, $show_question_page=false, $show_solution_only = false, $ilUser);
					break;
				case "qt_javaapplet" :
					$question_gui->outWorkingForm("", $is_postponed = false, $showsolution = 0, $show_question_page=false, $show_solution_only = false, $ilUser);
					break;
				default :
					$question_gui->outWorkingForm($idx, $is_postponed = false, $showsolution = 0, $show_question_page=false, $show_solution_only = false, $ilUser);
			}
			$tpl->parseCurrentBlock();
			$counter ++;
		}
		// output of submit buttons
		if ($isForm && !$active->submitted) 
		{
			$tpl->setCurrentBlock("confirm");
			$tpl->setVariable("TXT_SUBMIT_ANSWERS", $this->lng->txt("tst_submit_answers_txt"));
			$tpl->setVariable("BTN_CANCEL", $this->lng->txt("back"));
			$tpl->setVariable("BTN_OK", $this->lng->txt("tst_submit_answers"));
			$tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
			$tpl->parseCurrentBlock();
		}
		
		// output of non-block elements
		$tpl->setCurrentBlock("adm_content");
		$tpl->setVariable("TXT_TEST_TITLE", $this->lng->txt("title"));
		$tpl->setVariable("VALUE_TEST_TITLE", $this->object->getTitle());
		$tpl->setVariable("TXT_USR_NAME", $this->lng->txt("name"));
		$tpl->setVariable("VALUE_USR_NAME", $ilUser->getLastname().", ".$ilUser->getFirstname());
		$tpl->setVariable("TXT_USR_MATRIC", $this->lng->txt("matriculation"));
		$tpl->setVariable("VALUE_USR_MATRIC", $ilUser->getMatriculation());
		$tpl->setVariable("TXT_CLIENT_IP", $this->lng->txt("client_ip"));
		$tpl->setVariable("VALUE_CLIENT_IP", $invited_users->clientip);
		$tpl->setVariable("TXT_TEST_PROLOG", $this->lng->txt("tst_your_answers"));
		
		$tpl->parseCurrentBlock();
		
	}
	
	/**
	* set the tabs for the results overview ("results" in the repository)
	*/
	function setResultsTabs()
	{
		include_once ("./classes/class.ilTabsGUI.php");
		$tabs_gui =& new ilTabsGUI();

		// Test results tab
		$tabs_gui->addTarget("tst_results_aggregated",
			$this->ctrl->getLinkTarget($this, "eval_a"),
			array("eval_a"),
			"", "");

		$force_active = (is_numeric($_GET["uid"]) && $_GET["etype"] == "all") ? true	: false;
		$tabs_gui->addTarget("eval_all_users", 
			$this->ctrl->getLinkTargetByClass(get_class($this), "eval_stat"), 
			array("eval_stat", "evalAllUsers", "evalUserDetail"),	
			"", "", $force_active
		);
		
		$force_active = (is_numeric($_GET["uid"]) && $_GET["etype"] == "selected") ? true	: false;
		$tabs_gui->addTarget("eval_selected_users", 
			$this->ctrl->getLinkTargetByClass(get_class($this), "evalStatSelected"), 
			array("evalStatSelected", "evalSelectedUsers", "searchForEvaluation",
			"addFoundUsersToEval", "removeSelectedUser"),	
			"", "", $force_active
		);
		$this->tpl->setVariable("TABS", $tabs_gui->getHTML());
	}	
}
?>
