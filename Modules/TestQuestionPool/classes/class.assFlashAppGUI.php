<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/

include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Flash Application question GUI representation
*
* The assFlashAppGUI class encapsulates the GUI representation
* for Flash Application questions.
*
* @ilCtrl_IsCalledBy assFlashAppGUI: ilObjQuestionPoolGUI
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @author		Christian Müller <christian.mueller@uni-passau.de>
* @version	$Id$
* @module   class.assFlashAppGUI.php
* @modulegroup   Assessment
*/
class assFlashAppGUI extends assQuestionGUI
{
	/**
	* assFlashAppGUI constructor
	*
	* The constructor takes possible arguments an creates an instance of the assFlashAppGUI object.
	*
	* @param integer $id The database id of a image map question object
	* @access public
	*/
	function assFlashAppGUI(
		$id = -1
	)
	{
		$this->assQuestionGUI();
		include_once "./Modules/TestQuestionPool/classes/class.assFlashApp.php";
		$this->object = new assFlashApp();
		if ($id >= 0)
		{
			$this->object->loadFromDb($id);
		}
	}

	/**
	* Returns the question type string
	*
	* Returns the question type string
	*
	* @result string The question type string
	* @access public
	*/
	function getQuestionType()
	{
		return "assFlashApp";
	}

	function getCommand($cmd)
	{
		if (substr($cmd, 0, 6) == "delete")
		{
			$cmd = "delete";
		}
		return $cmd;
	}

	/**
	* Creates an output of the edit form for the question
	*
	* Creates an output of the edit form for the question
	*
	* @access public
	*/
	function editQuestion()
	{
		$this->getQuestionTemplate();
		$this->tpl->addBlockFile("QUESTION_DATA", "question_data", "tpl.il_as_qpl_flashapp_question.html", "Modules/TestQuestionPool");
		if ($this->error)
		{
			sendInfo($this->error);
		}
		// call to other question data i.e. estimated working time block
		$this->outOtherQuestionData();
		// image block
		$this->tpl->setCurrentBlock("post_save");

		$internallinks = array(
			"lm" => $this->lng->txt("obj_lm"),
			"st" => $this->lng->txt("obj_st"),
			"pg" => $this->lng->txt("obj_pg"),
			"glo" => $this->lng->txt("glossary_term")
		);
		foreach ($internallinks as $key => $value)
		{
			$this->tpl->setCurrentBlock("internallink");
			$this->tpl->setVariable("TYPE_INTERNAL_LINK", $key);
			$this->tpl->setVariable("TEXT_INTERNAL_LINK", $value);
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setVariable("TEXT_SOLUTION_HINT", $this->lng->txt("solution_hint"));
		if (count($this->object->suggested_solutions))
		{
			$solution_array = $this->object->getSuggestedSolution(0);
			include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
			$href = assQuestion::_getInternalLinkHref($solution_array["internal_link"]);
			$this->tpl->setVariable("TEXT_VALUE_SOLUTION_HINT", " <a href=\"$href\" target=\"content\">" . $this->lng->txt("solution_hint"). "</a> ");
			$this->tpl->setVariable("BUTTON_REMOVE_SOLUTION", $this->lng->txt("remove"));
			$this->tpl->setVariable("BUTTON_ADD_SOLUTION", $this->lng->txt("change"));
			$this->tpl->setVariable("VALUE_SOLUTION_HINT", $solution_array["internal_link"]);
		}
		else
		{
			$this->tpl->setVariable("BUTTON_ADD_SOLUTION", $this->lng->txt("add"));
		}
		
		// java applet block
		$javaapplet = $this->object->getFlashFilename();
		$this->tpl->setVariable("TEXT_FLASHFILE", $this->lng->txt("flashfile"));
		if (!empty($javaapplet))
		{
			$this->tpl->setVariable("FLASHFILE_FILENAME", $javaapplet);
			$this->tpl->setVariable("VALUE_FLASHFILE_UPLOAD", $this->lng->txt("change"));
			$this->tpl->setCurrentBlock("flashfileupload");
			$this->tpl->setVariable("UPLOADED_FLASHFILE", $javaapplet);
			$this->tpl->parse("javaappletupload");
			$this->tpl->setCurrentBlock("delete_flash");
			$this->tpl->setVariable("VALUE_FLASHFILE_DELETE", $this->lng->txt("delete"));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setVariable("VALUE_FLASHFILE_UPLOAD", $this->lng->txt("upload"));
		}
		$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("available_points"));
		$this->tpl->setVariable("VALUE_FLASH_POINTS", $this->object->getPoints());
		$this->tpl->parseCurrentBlock();

		
		if ((strlen($this->object->getTitle()) > 0) && (strlen($this->object->getAuthor()) > 0) && (strlen($this->object->getQuestion()) > 0) && ($this->object->getPoints() > 0))
		{
			$emptyname = 0;
			for ($i = 0; $i < $this->object->getParameterCount(); $i++)
			{
				// create template for existing applet parameters
				$this->tpl->setCurrentBlock("delete_parameter");
				$this->tpl->setVariable("VALUE_DELETE_PARAMETER", $this->lng->txt("delete"));
				$this->tpl->setVariable("DELETE_PARAMETER_COUNT", $i);
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("flash_parameter");
				$this->tpl->setVariable("PARAM_PARAM", $this->lng->txt("applet_parameter") . " " . ($i+1));
				$this->tpl->setVariable("PARAM_NAME", $this->lng->txt("name"));
				$this->tpl->setVariable("PARAM_VALUE", $this->lng->txt("value"));
				$param = $this->object->getParameter($i);
				$this->tpl->setVariable("PARAM_NAME_VALUE", $param["name"]);
				$this->tpl->setVariable("PARAM_VALUE_VALUE", $param["value"]);
				$this->tpl->setVariable("PARAM_COUNTER", $i);
				$this->tpl->parseCurrentBlock();
				if (!$param["name"])
				{
					$emptyname = 1;
				}
			}
			if ($this->ctrl->getCmd() == "addParameter")
			{
				if ($emptyname == 0)
				{
					// create template for new applet parameter
					$this->tpl->setCurrentBlock("flash_parameter");
					$this->tpl->setVariable("PARAM_PARAM", $this->lng->txt("applet_new_parameter"));
					$this->tpl->setVariable("PARAM_NAME", $this->lng->txt("name"));
					$this->tpl->setVariable("PARAM_VALUE", $this->lng->txt("value"));
					$this->tpl->setVariable("PARAM_COUNTER", $this->object->getParameterCount());
					$this->tpl->parseCurrentBlock();
				}
				else
				{
					sendInfo($this->lng->txt("too_many_empty_parameters"));
				}
			}
			if (!strlen($javaapplet))
			{
				
				$this->tpl->setVariable("VALUE_ARCHIVE", $this->object->getFlashFilename());
				
			}

			$this->tpl->setCurrentBlock("flashcode");
			$this->tpl->setVariable("FLASH_ATTRIBUTES", $this->lng->txt("flash_attributes"));
			$this->tpl->setVariable("TEXT_CODE", $this->lng->txt("code"));
			$this->tpl->setVariable("TEXT_WIDTH", $this->lng->txt("width"));
			$this->tpl->setVariable("TEXT_HEIGHT", $this->lng->txt("height"));
			
			$this->tpl->setVariable("VALUE_WIDTH", $this->object->getFlashWidth());
			$this->tpl->setVariable("VALUE_HEIGHT", $this->object->getFlashHeight());
			$this->tpl->setVariable("FLASH_PARAMETERS", $this->lng->txt("flash_parameters"));
			$this->tpl->setVariable("VALUE_ADD_PARAMETER", $this->lng->txt("add_flash_parameter"));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("HeadContent");
		$javascript = "<script type=\"text/javascript\">function initialSelect() {\n%s\n}</script>";
		$this->tpl->setVariable("CONTENT_BLOCK", sprintf($javascript, "document.frm_javaapplet.title.focus();"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("question_data");
		$this->tpl->setVariable("FLASHFILE_ID", $this->object->getId());
		$this->tpl->setVariable("VALUE_FLASHFILE_TITLE", ilUtil::prepareFormOutput($this->object->getTitle()));
		$this->tpl->setVariable("VALUE_FLASHFILE_COMMENT", ilUtil::prepareFormOutput($this->object->getComment()));
		$this->tpl->setVariable("VALUE_FLASHFILE_AUTHOR", ilUtil::prepareFormOutput($this->object->getAuthor()));
		$questiontext = $this->object->getQuestion();
		$this->tpl->setVariable("VALUE_QUESTION", $this->object->prepareTextareaOutput($questiontext));
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("TEXT_COMMENT", $this->lng->txt("description"));
		$this->tpl->setVariable("TEXT_QUESTION", $this->lng->txt("question"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));

		$this->tpl->setVariable("SAVE",$this->lng->txt("save"));
		$this->tpl->setVariable("SAVE_EDIT", $this->lng->txt("save_edit"));
		$this->tpl->setVariable("CANCEL",$this->lng->txt("cancel"));
		$this->ctrl->setParameter($this, "sel_question_types", "assFlashApp");
		$this->tpl->setVariable("TEXT_QUESTION_TYPE", $this->lng->txt("assFlashApp"));
		$formaction = $this->ctrl->getFormaction($this);
		if ($this->object->getId() > 0)
		{
			if (!preg_match("/q_id\=\d+/", $formaction))
			{
				$formaction = str_replace("q_id=", "q_id=" . $this->object->getId(), $formaction);
			}
		}
		$this->tpl->setVariable("ACTION_FLASH_QUESTION", $formaction);
		$this->tpl->parseCurrentBlock();
		include_once "./Services/RTE/classes/class.ilRTE.php";
		$rtestring = ilRTE::_getRTEClassname();
		include_once "./Services/RTE/classes/class.$rtestring.php";
		$rte = new $rtestring();
		$rte->addPlugin("latex");
		$rte->addButton("latex");
		include_once "./classes/class.ilObject.php";
		$obj_id = $_GET["q_id"];
		$obj_type = ilObject::_lookupType($_GET["ref_id"], TRUE);
		$rte->addRTESupport($obj_id, $obj_type, "assessment");

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("BODY_ATTRIBUTES", " onload=\"initialSelect();\""); 
		$this->tpl->parseCurrentBlock();

	}


	/**
	* save question to db and return to question pool
	*/
	function uploadingFlash()
	{
		$result = $this->writePostData();
		if ($result == 0)
		{
			$this->object->saveToDb();
		}
		$this->editQuestion();
	}


	/**
	* save question to db and return to question pool
	*/
	function removeFlash()
	{
		$this->object->deleteFlashFilename();
		$this->object->saveToDb();
		$this->editQuestion();
	}

	/**
	* save question to db and return to question pool
	*/
	function addParameter()
	{
		$this->writePostData();
		$this->editQuestion();
	}

	/**
	* delete a parameter
	*/
	function delete()
	{
		$this->writePostData();
		$this->editQuestion();
	}

	/**
	* Evaluates a posted edit form and writes the form data in the question object
	*
	* Evaluates a posted edit form and writes the form data in the question object
	*
	* @return integer A positive value, if one of the required fields wasn't set, else 0
	* @access private
	*/
	function writePostData()
	{
		$result = 0;
		$saved = false;
		if (!$this->checkInput())
		{
			$result = 1;
		}
		$this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
		$this->object->setAuthor(ilUtil::stripSlashes($_POST["author"]));
		$this->object->setComment(ilUtil::stripSlashes($_POST["comment"]));
		include_once "./classes/class.ilObjAdvancedEditing.php";
		$questiontext = ilUtil::stripSlashes($_POST["question"], true, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment"));
		$this->object->setQuestion($questiontext);
		$this->object->setSuggestedSolution($_POST["solution_hint"], 0);
		$this->object->setShuffle($_POST["shuffle"]);
		$this->object->setPoints($_POST["flash_points"]);
		if ($_POST["flash_points"] < 0)
		{
			$result = 1;
			$this->setErrorMessage($this->lng->txt("negative_points_not_allowed"));
		}
		// adding estimated working time
		$saved = $saved | $this->writeOtherPostData($result);

		if ($result == 0)
		{
			//setting java applet
			if (empty($_FILES['flashfileName']['tmp_name']))
			{
				$this->object->setFlashFilename(ilUtil::stripSlashes($_POST['uploaded_flashfile']));
			}
			else
			{
				if ($this->object->getId() < 1)
				{
					$saved = 1;
					$this->object->saveToDb();
				}
				$this->object->setFlashFilename($_FILES['flashfileName']['name'], $_FILES['flashfileName']['tmp_name']);
			}
			if ((strlen($this->object->getTitle()) > 0) && (strlen($this->object->getAuthor()) > 0) && (strlen($this->object->getQuestion()) > 0) && ($this->object->getPoints() > 0) && array_key_exists("flash_height", $_POST))
			{

				//$this->object->setJavaArchive($_POST["java_archive"]);
				$this->object->setFlashWidth($_POST["flash_width"]);
				$this->object->setFlashHeight($_POST["flash_height"]);
				if ((!$_POST["flash_width"]) or (!$_POST["flash_height"])) $result = 1;
				$this->object->flushParams();
				foreach ($_POST as $key => $value)
				{
					if (preg_match("/param_name_(\d+)/", $key, $matches))
					{
						$this->object->addParameterAtIndex($matches[1], $value, $_POST["param_value_$matches[1]"]);
					}
				}
				if (preg_match("/delete_(\d+)/", $this->ctrl->getCmd(), $matches))
				{
					$this->object->removeParameter($_POST["param_name_$matches[1]"]);
				}
			}
		}
		if ($saved)
		{
			$this->object->saveToDb();
			$this->error .= $this->lng->txt("question_saved_for_upload");
		}
		return $result;
	}

	function outQuestionForTest($formaction, $active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE)
	{
		$test_output = $this->getTestOutput($active_id, $pass, $is_postponed, $use_post_solutions); 
		$this->tpl->setVariable("QUESTION_OUTPUT", $test_output);
		$this->tpl->setVariable("FORMACTION", $formaction);
	}

	function getSolutionOutput($active_id, $pass = NULL, $graphicalOutput = FALSE, $result_output = FALSE, $show_question_only = TRUE, $show_feedback = FALSE)
	{
		$userdata = $this->object->getActiveUserData($active_id);

		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		include_once "./Modules/Test/classes/class.ilObjTest.php";
		$template = new ilTemplate("tpl.il_as_qpl_flashapp_question_output_solution.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html",TRUE, TRUE, "Modules/TestQuestionPool");

		if ($active_id)
		{
			$this->createTmpData($active_id);
				
			// Variablen, die mittels GET an die Flashanwendung übergeben werden
			// Diese dienen dazu das temporäre File zu lokalisieren TMP_PATH/SESSION
			$template->setVariable("SESSION", $_COOKIE["PHPSESSID"]);
			$template->setVariable("TMP_PATH", $this->object->getFlashPathWeb());
		}
		
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$template->setVariable("VALUE_WIDTH", $this->object->getFlashWidth());
		$template->setVariable("VALUE_HEIGHT", $this->object->getFlashHeight());
		

		if (strpos($this->object->getFlashFilename(), ".swf") !== FALSE)
		{
			$template->setVariable("FLASH_SRC", $this->object->getFlashPathWeb().$this->object->getFlashFilename());
		}
	

		if ($active_id)
		{
			if ($graphicalOutput)
			{
				// output of ok/not ok icons for user entered solutions
				$reached_points = $this->object->getReachedPoints($active_id, $pass);
				if ($reached_points == $this->object->getMaximumPoints())
				{
					$template->setCurrentBlock("icon_ok");
					$template->setVariable("ICON_OK", ilUtil::getImagePath("icon_ok.gif"));
					$template->setVariable("TEXT_OK", $this->lng->txt("answer_is_right"));
					$template->parseCurrentBlock();
				}
				else
				{
					$template->setCurrentBlock("icon_ok");
					if ($reached_points > 0)
					{
						$template->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_mostly_ok.gif"));
						$template->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_not_correct_but_positive"));
					}
					else
					{
						$template->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_not_ok.gif"));
						$template->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_wrong"));
					}
					$template->parseCurrentBlock();
				}
			}
		}
		$questionoutput = $template->get();
		$feedback = ($show_feedback) ? $this->getAnswerFeedbackOutput($active_id) : "";
		$questionoutput .= $feedback;
		$solutiontemplate->setVariable("SOLUTION_OUTPUT", $questionoutput);

		$solutionoutput = $solutiontemplate->get(); 
		if (!$show_question_only)
		{
			// get page object output
			$pageoutput = $this->getILIASPage();
			$solutionoutput = preg_replace("/(\<div( xmlns:xhtml\=\"http:\/\/www.w3.org\/1999\/xhtml\"){0,1} class\=\"ilc_Question\">\<\/div>)/ims", $solutionoutput, $pageoutput);
		}
		return $solutionoutput;
	}
	
	function getQuestion()
	{
		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_flashapp_question_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		

		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$template->setVariable("VALUE_WIDTH", $this->object->getFlashWidth());
		$template->setVariable("VALUE_HEIGHT", $this->object->getFlashHeight());
		
		
			if (strpos($this->object->getFlashFilename(), ".swf") !== FALSE)
			{
				$template->setVariable("FLASH_SRC", $this->object->getFlashPathWeb().$this->object->getFlashFilename());
				
			}
		
		$questionoutput = $template->get();
		$questionoutput = preg_replace("/\<div[^>]*?>(.*)\<\/div>/is", "\\1", $questionoutput);
		
		//echo "flash_src".$this->object->getFlashPathWeb().$this->object->getFlashFilename();
		return $questionoutput;
	}
	
	function getActiveUserData($active_id)
	{
		global $ilDB;
		$query = sprintf("SELECT * FROM tst_active WHERE active_id = %s",
			$ilDB->quote($active_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			return array("user_id" => $row["user_fi"], "test_id" => $row["test_fi"]);
		}
		else
		{
			return array();
		}
	}
	
	function getTestOutput($active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE)
	{
		$this->createTmpData($active_id);
		
		// get page object output
		$pageoutput = $this->outQuestionPage("", $is_postponed, $active_id);
		$userdata = $this->getActiveUserData($active_id);
		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_flashapp_question_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		
		
		// Variablen, die mittels GET an die Flashanwendung übergeben werden
		// Diese dienen dazu das temporäre File zu lokalisieren TMP_PATH/SESSION
		$template->setVariable("SESSION", $_COOKIE["PHPSESSID"]);
		$template->setVariable("TMP_PATH", $this->object->getFlashPathWeb());


		
		
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$template->setVariable("VALUE_WIDTH", $this->object->getFlashWidth());
		$template->setVariable("VALUE_HEIGHT", $this->object->getFlashHeight());
		
		
		
			if (strpos($this->object->getFlashFilename(), ".swf") !== FALSE)
			{
				//echo "via Flash... ";
				$template->setVariable("FLASH_SRC", $this->object->getFlashPathWeb().$this->object->getFlashFilename());
			}
		
		$questionoutput = $template->get();
		$questionoutput = str_replace("<div xmlns:xhtml=\"http://www.w3.org/1999/xhtml\" class=\"ilc_Question\"></div>", $questionoutput, $pageoutput);
		return $questionoutput;
	}
	
	function getPreview()
	{
		$this->createTmpData($active_id);
		
		// get page object output
		$pageoutput = $this->outQuestionPage("", $is_postponed, $active_id);
		$userdata = $this->getActiveUserData($active_id);
		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_flashapp_question_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		
		
		// Variablen, die mittels GET an die Flashanwendung übergeben werden
		// Diese dienen dazu das temporäre File zu lokalisieren TMP_PATH/SESSION
		$template->setVariable("SESSION", $_COOKIE["PHPSESSID"]);
		$template->setVariable("TMP_PATH", $this->object->getFlashPathWeb());


		
		
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$template->setVariable("VALUE_WIDTH", $this->object->getFlashWidth());
		$template->setVariable("VALUE_HEIGHT", $this->object->getFlashHeight());
		
		
		
			if (strpos($this->object->getFlashFilename(), ".swf") !== FALSE)
			{
				//echo "via Flash... ";
				$template->setVariable("FLASH_SRC", $this->object->getFlashPathWeb().$this->object->getFlashFilename());
			}
		
		$questionoutput = $template->get();
		$questionoutput = preg_replace("/\<div[^>]*?>(.*)\<\/div>/is", "\\1", $questionoutput);
		return $questionoutput;
	}

	/**
	 * 
	 * 
	 */
	 function createTmpData($active_id)
	 {
		$userdata = $this->getActiveUserData($active_id);
	 	
		include_once "./Modules/Test/classes/class.ilObjTest.php";
	 	//$tmp_data =  "testtype=". ilObjTest::_getTestType($active_id);
		$tmp_data .= "test_id=". $userdata["test_id"];
		$tmp_data .= "&id=". $this->object->getId();
		$tmp_data .= "&user_id=". $userdata["user_id"];
		$tmp_data .= "&points=". $this->object->getPoints();
		$tmp_data .= "&sessionid=". $_COOKIE["PHPSESSID"];
		$tmp_data .= "&client_id=". CLIENT_ID;
		$tmp_data .= "&pass=". ilObjTest::_getPass($active_id);
		$tmp_data .= "&post_url=". ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) . "/Modules/TestQuestionPool/save_flash_question_result.php";
		if ($active_id)
		{
			$solutions = NULL;
			if (!ilObjTest::_getUsePreviousAnswers($active_id, true))
			{
				if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
			}
			$info = $this->object->getReachedInformation($active_id, $pass);
			//echo sizeof($this->object->getReachedInformation(11, 3));
			foreach ($info as $kk => $infodata)
			{
				$tmp_data .= "&value_" . $infodata["order"] . "_1=";
				$tmp_data .= $infodata["value1"];
			}
		}
		
		$questiontext = $this->object->getQuestion();
		$tmp_data .= "&width=". $this->object->getFlashWidth();
		$tmp_data .= "&height=". $this->object->getFlashHeight();
		
		
			if (strpos($this->object->getFlashFilename(), ".swf") !== FALSE)
			{
				$tmp_data .= "&flash_src=". $this->object->getFlashPathWeb().$this->object->getFlashFilename();
			}
		
		
	// temporäres File wird geschrieben
	//echo $tmp_data;
			if (!$this->object->writeTmpFile($tmp_data))
			die ("Not able to create tmp data file!");	
	 }
	 
	/**
	* check input fields
	*/
	function checkInput()
	{
		
		if ((strlen($_POST["title"]) == 0) or (strlen($_POST["author"]) == 0) or (strlen($_POST["question"]) == 0) or (strlen($_POST["flash_points"]) == 0))
		{
			$this->error .= $this->lng->txt("fill_out_all_required_fields");
			return false;
		}
		return true;
	}


	function addSuggestedSolution()
	{
		$_SESSION["subquestion_index"] = 0;
		if ($_POST["cmd"]["addSuggestedSolution"])
		{
			if ($this->writePostData())
			{
				sendInfo($this->getErrorMessage());
				$this->editQuestion();
				return;
			}
			if ($result != 0)
			{
				$this->editQuestion();
				return;
			}
		}
		$this->object->saveToDb();
		$_GET["q_id"] = $this->object->getId();
		$this->tpl->setVariable("HEADER", $this->object->getTitle());
		$this->getQuestionTemplate();
		parent::addSuggestedSolution();
	}

	/**
	* Sets the ILIAS tabs for this question type
	*
	* Sets the ILIAS tabs for this question type
	*
	* @access public
	*/
	function setQuestionTabs()
	{
		global $rbacsystem, $ilTabs;
		
		$this->ctrl->setParameterByClass("ilpageobjectgui", "q_id", $_GET["q_id"]);
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$q_type = $this->object->getQuestionType();

		if (strlen($q_type))
		{
			$classname = $q_type . "GUI";
			$this->ctrl->setParameterByClass(strtolower($classname), "sel_question_types", $q_type);
			$this->ctrl->setParameterByClass(strtolower($classname), "q_id", $_GET["q_id"]);
		}

		if ($_GET["q_id"])
		{
			if ($rbacsystem->checkAccess('write', $_GET["ref_id"]))
			{
				// edit page
				$ilTabs->addTarget("edit_content",
					$this->ctrl->getLinkTargetByClass("ilPageObjectGUI", "view"),
					array("view", "insert", "exec_pg"),
					"", "", $force_active);
			}
	
			// edit page
			$ilTabs->addTarget("preview",
				$this->ctrl->getLinkTargetByClass("ilPageObjectGUI", "preview"),
				array("preview"),
				"ilPageObjectGUI", "", $force_active);
		}

		$force_active = false;
		$commands = $_POST["cmd"];
		if (is_array($commands))
		{
			foreach ($commands as $key => $value)
			{
				if (preg_match("/^delete_.*/", $key, $matches) || 
					preg_match("/^addSelectGapText_.*/", $key, $matches) ||
					preg_match("/^addGapText_.*/", $key, $matches) ||
					preg_match("/^upload_.*/", $key, $matches) ||
					preg_match("/^addSuggestedSolution_.*/", $key, $matches) ||
					preg_match("/^removeSuggestedSolution_.*/", $key, $matches)
					)
				{
					$force_active = true;
				}
			}
		}
		if ($rbacsystem->checkAccess('write', $_GET["ref_id"]))
		{
			$url = "";
			if ($classname) $url = $this->ctrl->getLinkTargetByClass($classname, "editQuestion");
			// edit question properties
			$ilTabs->addTarget("edit_properties",
				$url,
				array("editQuestion", "save", "cancel", "addSuggestedSolution",
					 "cancelExplorer", "linkChilds", "removeSuggestedSolution",
					 "createGaps", "saveEdit", "changeGapType"),
				$classname, "", $force_active);
		}

		if ($_GET["q_id"])
		{
			$ilTabs->addTarget("feedback",
				$this->ctrl->getLinkTargetByClass($classname, "feedback"),
				array("feedback", "saveFeedback"),
				$classname, "");
		}
		
		// Assessment of questions sub menu entry
		if ($_GET["q_id"])
		{
			$ilTabs->addTarget("statistics",
				$this->ctrl->getLinkTargetByClass($classname, "assessment"),
				array("assessment"),
				$classname, "");
		}
		
		if (($_GET["calling_test"] > 0) || ($_GET["test_ref_id"] > 0))
		{
			$ref_id = $_GET["calling_test"];
			if (strlen($ref_id) == 0) $ref_id = $_GET["test_ref_id"];
			$ilTabs->setBackTarget($this->lng->txt("backtocallingtest"), "ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=$ref_id");
		}
		else
		{
			$ilTabs->setBackTarget($this->lng->txt("qpl"), $this->ctrl->getLinkTargetByClass("ilobjquestionpoolgui", "questions"));
		}
	}

	/**
	* Saves the feedback for a question type
	*
	* Saves the feedback for a question type
	*
	* @access public
	*/
	function saveFeedback()
	{
		include_once "./classes/class.ilObjAdvancedEditing.php";
		$this->object->saveFeedbackGeneric(0, ilUtil::stripSlashes($_POST["feedback_incomplete"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")));
		$this->object->saveFeedbackGeneric(1, ilUtil::stripSlashes($_POST["feedback_complete"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")));
		$this->object->cleanupMediaObjectUsage();

		// additional feedback processing
		$this->feedback();
	}

	/**
	* Creates the output of the feedback page for a single choice question
	*
	* Creates the output of the feedback page for a single choice question
	*
	* @access public
	*/
	function feedback()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "feedback", "tpl.il_as_qpl_mytype_feedback.html", "Modules/TestQuestionPool");
		$this->tpl->setVariable("FEEDBACK_TEXT", $this->lng->txt("feedback"));
		$this->tpl->setVariable("FEEDBACK_COMPLETE", $this->lng->txt("feedback_complete_solution"));
		$this->tpl->setVariable("VALUE_FEEDBACK_COMPLETE", $this->object->prepareTextareaOutput($this->object->getFeedbackGeneric(1)), FALSE);
		$this->tpl->setVariable("FEEDBACK_INCOMPLETE", $this->lng->txt("feedback_incomplete_solution"));
		$this->tpl->setVariable("VALUE_FEEDBACK_INCOMPLETE", $this->object->prepareTextareaOutput($this->object->getFeedbackGeneric(0)), FALSE);
		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		
		// additional feedback if needed

		include_once "./Services/RTE/classes/class.ilRTE.php";
		$rtestring = ilRTE::_getRTEClassname();
		include_once "./Services/RTE/classes/class.$rtestring.php";
		$rte = new $rtestring();
		$rte->addPlugin("latex");
		$rte->addButton("latex");
		include_once "./classes/class.ilObject.php";
		$obj_id = $_GET["q_id"];
		$obj_type = ilObject::_lookupType($_GET["ref_id"], TRUE);
		$rte->addRTESupport($obj_id, $obj_type, "assessment");
	}

}
?>
