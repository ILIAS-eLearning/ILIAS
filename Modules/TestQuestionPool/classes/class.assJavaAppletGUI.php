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
* Java applet question GUI representation
*
* The assJavaAppletGUI class encapsulates the GUI representation
* for java applet questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assJavaAppletGUI extends assQuestionGUI
{
	/**
	* assJavaAppletGUI constructor
	*
	* The constructor takes possible arguments an creates an instance of the assJavaAppletGUI object.
	*
	* @param integer $id The database id of a image map question object
	* @access public
	*/
	function __construct($id = -1)
	{
		parent::__construct();
		include_once "./Modules/TestQuestionPool/classes/class.assJavaApplet.php";
		$this->object = new assJavaApplet();
		if ($id >= 0)
		{
			$this->object->loadFromDb($id);
		}
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
	* Evaluates a posted edit form and writes the form data in the question object
	*
	* @return integer A positive value, if one of the required fields wasn't set, else 0
	* @access private
	*/
	function writePostData($always = false)
	{
		$hasErrors = (!$always) ? $this->editQuestion(true) : false;
		if (!$hasErrors)
		{
			$this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
			$this->object->setAuthor(ilUtil::stripSlashes($_POST["author"]));
			$this->object->setComment(ilUtil::stripSlashes($_POST["comment"]));
			include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
			$questiontext = ilUtil::stripSlashes($_POST["question"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment"));
			$this->object->setQuestion($questiontext);
			$this->object->setEstimatedWorkingTime(
				ilUtil::stripSlashes($_POST["Estimated"]["hh"]),
				ilUtil::stripSlashes($_POST["Estimated"]["mm"]),
				ilUtil::stripSlashes($_POST["Estimated"]["ss"])
			);
			$this->object->setPoints($_POST["points"]);
			
			if ($_POST['delete_applet'])
			{
				// delete the applet file
				$this->object->deleteJavaAppletFilename();
			}
			else
			{
				$this->object->setJavaAppletFilename(ilUtil::stripSlashes($_POST['uploaded_javaapplet']));
			}
			
			//setting java applet
			if (!empty($_FILES['javaappletName']['tmp_name']))
			{
				$this->object->setJavaAppletFilename($_FILES['javaappletName']['name'], $_FILES['javaappletName']['tmp_name']);
			}
			$this->object->setJavaCode($_POST["java_code"]);
			$this->object->setJavaCodebase($_POST["java_codebase"]);
			$this->object->setJavaArchive($_POST["java_archive"]);
			$this->object->setJavaWidth($_POST["java_width"]);
			$this->object->setJavaHeight($_POST["java_height"]);

			$this->object->flushParams();
			if (is_array($_POST['kvp']['key']))
			{
				foreach ($_POST['kvp']['key'] as $idx => $val)
				{
					if (strlen($val) && strlen($_POST['kvp']['value'][$idx]))
					{
						$this->object->addParameter(ilUtil::stripSlashes($val), ilUtil::stripSlashes($_POST['kvp']['value'][$idx]));
					}
				}
			}
			return 0;
		}
		else
		{
			return 1;
		}
	}

	/**
	* Creates an output of the edit form for the question
	*
	* @access public
	*/
	public function editQuestion($checkonly = FALSE)
	{
		$save = ((strcmp($this->ctrl->getCmd(), "save") == 0) || (strcmp($this->ctrl->getCmd(), "saveEdit") == 0)) ? TRUE : FALSE;
		$this->getQuestionTemplate();

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->outQuestionType());
		$form->setMultipart(true);
		$form->setTableWidth("100%");
		$form->setId("assjavaapplet");

		// title
		$title = new ilTextInputGUI($this->lng->txt("title"), "title");
		$title->setValue($this->object->getTitle());
		$title->setRequired(TRUE);
		$form->addItem($title);
		// author
		$author = new ilTextInputGUI($this->lng->txt("author"), "author");
		$author->setValue($this->object->getAuthor());
		$author->setRequired(TRUE);
		$form->addItem($author);
		// description
		$description = new ilTextInputGUI($this->lng->txt("description"), "comment");
		$description->setValue($this->object->getComment());
		$description->setRequired(FALSE);
		$form->addItem($description);
		// questiontext
		$question = new ilTextAreaInputGUI($this->lng->txt("question"), "question");
		$question->setValue($this->object->prepareTextareaOutput($this->object->getQuestion()));
		$question->setRequired(TRUE);
		$question->setRows(10);
		$question->setCols(80);
		$question->setUseRte(TRUE);
		$question->addPlugin("latex");
		$question->addButton("latex");
		$question->addButton("pastelatex");
		$question->setRTESupport($this->object->getId(), "qpl", "assessment");
		$form->addItem($question);

		// duration
		$duration = new ilDurationInputGUI($this->lng->txt("working_time"), "Estimated");
		$duration->setShowHours(TRUE);
		$duration->setShowMinutes(TRUE);
		$duration->setShowSeconds(TRUE);
		$ewt = $this->object->getEstimatedWorkingTime();
		$duration->setHours($ewt["h"]);
		$duration->setMinutes($ewt["m"]);
		$duration->setSeconds($ewt["s"]);
		$duration->setRequired(FALSE);
		$form->addItem($duration);

		// points
		$points = new ilNumberInputGUI($this->lng->txt("points"), "points");
		$points->setValue($this->object->getPoints());
		$points->setRequired(TRUE);
		$points->setSize(3);
		$points->setMinValue(0.0);
		$form->addItem($points);

		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->lng->txt("applet_attributes"));
		$form->addItem($header);
		
		// java applet
		$javaapplet = $this->object->getJavaAppletFilename();
		$applet = new ilFileInputGUI($this->lng->txt('javaapplet'), 'javaappletName');
		$applet->setSuffixes(array('jar','class'));
		$applet->setRequired(false);

		if (strlen($javaapplet))
		{
			$filename = new ilNonEditableValueGUI($this->lng->txt('filename'), 'uploaded_javaapplet');
			$filename->setValue($javaapplet);
			$applet->addSubItem($filename);
			
			$delete = new ilCheckboxInputGUI('', 'delete_applet');
			$delete->setOptionTitle($this->lng->txt('delete'));
			$delete->setValue(1);
			$applet->addSubItem($delete);
		}
		$form->addItem($applet);

		// Code
		$code = new ilTextInputGUI($this->lng->txt("code"), "java_code");
		$code->setValue($this->object->getJavaCode());
		$code->setRequired(TRUE);
		$form->addItem($code);

		if (!strlen($javaapplet))
		{
			// Archive
			$archive = new ilTextInputGUI($this->lng->txt("archive"), "java_archive");
			$archive->setValue($this->object->getJavaArchive());
			$archive->setRequired(false);
			$form->addItem($archive);

			// Codebase
			$codebase = new ilTextInputGUI($this->lng->txt("codebase"), "java_codebase");
			$codebase->setValue($this->object->getJavaCodebase());
			$codebase->setRequired(false);
			$form->addItem($codebase);
		}

		// Width
		$width = new ilNumberInputGUI($this->lng->txt("width"), "java_width");
		$width->setDecimals(0);
		$width->setSize(6);
		$width->setMinValue(50);
		$width->setMaxLength(6);
		$width->setValue($this->object->getJavaWidth());
		$width->setRequired(TRUE);
		$form->addItem($width);

		// Height
		$height = new ilNumberInputGUI($this->lng->txt("height"), "java_height");
		$height->setDecimals(0);
		$height->setSize(6);
		$height->setMinValue(50);
		$height->setMaxLength(6);
		$height->setValue($this->object->getJavaHeight());
		$height->setRequired(TRUE);
		$form->addItem($height);

		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->lng->txt("applet_parameters"));
		$form->addItem($header);

		include_once "./Modules/TestQuestionPool/classes/class.ilKVPWizardInputGUI.php";
		$kvp = new ilKVPWizardInputGUI($this->lng->txt("applet_parameters"), "kvp");
		$values = array();
		for ($i = 0; $i < $this->object->getParameterCount(); $i++)
		{
			$param = $this->object->getParameter($i);
			array_push($values, array($param['name'], $param['value']));
		}
		if (count($values) == 0)
		{
			array_push($values, array("", ""));
		}
		$kvp->setKeyName($this->lng->txt('name'));
		$kvp->setValueName($this->lng->txt('value'));
		$kvp->setValues($values);
		$form->addItem($kvp);
		
		$form->addCommandButton("save", $this->lng->txt("save"));
		$form->addCommandButton("saveEdit", $this->lng->txt("save_edit"));

		$errors = false;
	
		if ($save)
		{
			$form->setValuesByPost();
			$errors = !$form->checkInput();
			if ($errors) $checkonly = false;
		}

		if (!$checkonly) $this->tpl->setVariable("QUESTION_DATA", $form->getHTML());
		return $errors;
	}
	
	/**
	* Add a new answer
	*/
	public function addkvp()
	{
		$this->writePostData(true);
		$position = key($_POST['cmd']['addkvp']);
		$this->object->addParameterAtIndex($position+1, "", "");
		$this->editQuestion();
	}

	/**
	* Remove an answer
	*/
	public function removekvp()
	{
		$this->writePostData(true);
		$position = key($_POST['cmd']['removekvp']);
		$this->object->removeParameter($position);
		$this->editQuestion();
	}

	function outQuestionForTest($formaction, $active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE)
	{
		$test_output = $this->getTestOutput($active_id, $pass, $is_postponed, $use_post_solutions); 
		$this->tpl->setVariable("QUESTION_OUTPUT", $test_output);
		$this->tpl->setVariable("FORMACTION", $formaction);
	}

	/**
	* Get the question solution output
	*
	* @param integer $active_id The active user id
	* @param integer $pass The test pass
	* @param boolean $graphicalOutput Show visual feedback for right/wrong answers
	* @param boolean $result_output Show the reached points for parts of the question
	* @param boolean $show_question_only Show the question without the ILIAS content around
	* @param boolean $show_feedback Show the question feedback
	* @param boolean $show_correct_solution Show the correct solution instead of the user solution
	* @param boolean $show_manual_scoring Show specific information for the manual scoring output
	* @return The solution output of the question as HTML code
	*/
	function getSolutionOutput(
		$active_id,
		$pass = NULL,
		$graphicalOutput = FALSE,
		$result_output = FALSE,
		$show_question_only = TRUE,
		$show_feedback = FALSE,
		$show_correct_solution = FALSE,
		$show_manual_scoring = FALSE
	)
	{
		$userdata = $this->object->getActiveUserData($active_id);

		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		include_once "./Modules/Test/classes/class.ilObjTest.php";
		$template = new ilTemplate("tpl.il_as_qpl_javaapplet_question_output_solution.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html",TRUE, TRUE, "Modules/TestQuestionPool");
		if (strlen($userdata["test_id"]))
		{
			$template->setCurrentBlock("appletparam");
			$template->setVariable("PARAM_NAME", "test_type");
			if (ilObjTest::_lookupAnonymity(ilObjTest::_getObjectIDFromTestID($userdata["test_id"])))
			{
				$template->setVariable("PARAM_VALUE", "0");
			}
			else
			{
				$template->setVariable("PARAM_VALUE", "1");
			}
			$template->parseCurrentBlock();
		}
		if (strlen($userdata["test_id"]))
		{
			$template->setCurrentBlock("appletparam");
			$template->setVariable("PARAM_NAME", "test_id");
			$template->setVariable("PARAM_VALUE", $userdata["test_id"]);
			$template->parseCurrentBlock();
		}
		$template->setCurrentBlock("appletparam");
		$template->setVariable("PARAM_NAME", "active_id");
		$template->setVariable("PARAM_VALUE", $active_id);
		$template->parseCurrentBlock();
		$template->setCurrentBlock("appletparam");
		$template->setVariable("PARAM_NAME", "question_id");
		$template->setVariable("PARAM_VALUE", $this->object->getId());
		$template->parseCurrentBlock();
		if (strlen($userdata["user_id"]))
		{
			$template->setCurrentBlock("appletparam");
			$template->setVariable("PARAM_NAME", "user_id");
			$template->setVariable("PARAM_VALUE", $userdata["user_id"]);
			$template->parseCurrentBlock();
		}
		$template->setCurrentBlock("appletparam");
		$template->setVariable("PARAM_NAME", "points_max");
		$template->setVariable("PARAM_VALUE", $this->object->getPoints());
		$template->parseCurrentBlock();
		$template->setCurrentBlock("appletparam");
		$template->setVariable("PARAM_NAME", "session_id");
		$template->setVariable("PARAM_VALUE", $_COOKIE["PHPSESSID"]);
		$template->parseCurrentBlock();
		$template->setCurrentBlock("appletparam");
		$template->setVariable("PARAM_NAME", "client");
		$template->setVariable("PARAM_VALUE", CLIENT_ID);
		$template->parseCurrentBlock();
		$template->setCurrentBlock("appletparam");
		$template->setVariable("PARAM_NAME", "pass");
		$actualpass = ilObjTest::_getPass($active_id);
		$template->setVariable("PARAM_VALUE", $actualpass);
		$template->parseCurrentBlock();
		// additional parameters
		for ($i = 0; $i < $this->object->getParameterCount(); $i++)
		{
			$parameter = $this->object->getParameter($i);
			$template->setCurrentBlock("appletparam");
			$template->setVariable("PARAM_NAME", $parameter["name"]);
			$template->setVariable("PARAM_VALUE", $parameter["value"]);
			$template->parseCurrentBlock();
		}

		if (($active_id > 0) && (!$show_correct_solution))
		{
			$solutions = NULL;
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			$info = $this->object->getReachedInformation($active_id, $pass);
			foreach ($info as $kk => $infodata)
			{
				$template->setCurrentBlock("appletparam");
				$template->setVariable("PARAM_NAME", "value_" . $infodata["order"] . "_1");
				$template->setVariable("PARAM_VALUE", $infodata["value1"]);
				$template->parseCurrentBlock();
				$template->setCurrentBlock("appletparam");
				$template->setVariable("PARAM_NAME", "value_" . $infodata["order"] . "_2");
				$template->setVariable("PARAM_VALUE", $infodata["value2"]);
				$template->parseCurrentBlock();
			}
		}
		
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$template->setVariable("APPLET_WIDTH", $this->object->getJavaWidth());
		$template->setVariable("APPLET_HEIGHT", $this->object->getJavaHeight());
		$template->setVariable("APPLET_CODE", $this->object->getJavaCode());
		if (strlen($this->object->getJavaArchive()) > 0)
		{
			$template->setVariable("APPLET_ARCHIVE", " archive=\"".$this->object->getJavaArchive()."\"");
		}
		else
		{
			if (strpos($this->object->getJavaAppletFilename(), ".jar") !== FALSE)
			{
				$template->setVariable("APPLET_ARCHIVE", " archive=\"".$this->object->getJavaPathWeb().$this->object->getJavaAppletFilename()."\"");
			}
		}
		if (strlen($this->object->getJavaCodebase()) > 0)
		{
			$template->setVariable("APPLET_CODEBASE", " codebase=\"".$this->object->getJavaCodebase()."\"");
		}
		else
		{
			if (strpos($this->object->getJavaAppletFilename(), ".class") !== FALSE)
			{
				$template->setVariable("APPLET_CODEBASE", " codebase=\"".$this->object->getJavaPathWeb()."\"");
			}
		}
		if (($active_id > 0) && (!$show_correct_solution))
		{
			if ($graphicalOutput)
			{
				// output of ok/not ok icons for user entered solutions
				$reached_points = $this->object->getReachedPoints($active_id, $pass);
				if ($reached_points == $this->object->getPoints())
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
		$feedback = ($show_feedback) ? $this->getAnswerFeedbackOutput($active_id, $pass) : "";
		if (strlen($feedback)) $solutiontemplate->setVariable("FEEDBACK", $feedback);
		$solutiontemplate->setVariable("SOLUTION_OUTPUT", $questionoutput);

		$solutionoutput = $solutiontemplate->get(); 
		if (!$show_question_only)
		{
			// get page object output
			$solutionoutput = $this->getILIASPage($solutionoutput);
		}
		return $solutionoutput;
	}
	
	function getPreview($show_question_only = FALSE)
	{
		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_javaapplet_question_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$template->setCurrentBlock("appletparam");
		$template->setVariable("PARAM_NAME", "question_id");
		$template->setVariable("PARAM_VALUE", $this->object->getId());
		$template->parseCurrentBlock();
		$template->setCurrentBlock("appletparam");
		$template->setVariable("PARAM_NAME", "points_max");
		$template->setVariable("PARAM_VALUE", $this->object->getPoints());
		$template->parseCurrentBlock();
		$template->setCurrentBlock("appletparam");
		$template->setVariable("PARAM_NAME", "session_id");
		$template->setVariable("PARAM_VALUE", $_COOKIE["PHPSESSID"]);
		$template->parseCurrentBlock();
		$template->setCurrentBlock("appletparam");
		$template->setVariable("PARAM_NAME", "client");
		$template->setVariable("PARAM_VALUE", CLIENT_ID);
		$template->parseCurrentBlock();
		// additional parameters
		for ($i = 0; $i < $this->object->getParameterCount(); $i++)
		{
			$parameter = $this->object->getParameter($i);
			$template->setCurrentBlock("appletparam");
			$template->setVariable("PARAM_NAME", $parameter["name"]);
			$template->setVariable("PARAM_VALUE", $parameter["value"]);
			$template->parseCurrentBlock();
		}

		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$template->setVariable("APPLET_WIDTH", $this->object->getJavaWidth());
		$template->setVariable("APPLET_HEIGHT", $this->object->getJavaHeight());
		$template->setVariable("APPLET_CODE", $this->object->getJavaCode());
		if (strlen($this->object->getJavaArchive()) > 0)
		{
			$template->setVariable("APPLET_ARCHIVE", " archive=\"".$this->object->getJavaArchive()."\"");
		}
		else
		{
			if (strpos($this->object->getJavaAppletFilename(), ".jar") !== FALSE)
			{
				$template->setVariable("APPLET_ARCHIVE", " archive=\"".$this->object->getJavaPathWeb().$this->object->getJavaAppletFilename()."\"");
			}
		}
		if (strlen($this->object->getJavaCodebase()) > 0)
		{
			$template->setVariable("APPLET_CODEBASE", " codebase=\"".$this->object->getJavaCodebase()."\"");
		}
		else
		{
			if (strpos($this->object->getJavaAppletFilename(), ".class") !== FALSE)
			{
				$template->setVariable("APPLET_CODEBASE", " codebase=\"".$this->object->getJavaPathWeb()."\"");
			}
		}
		$questionoutput = $template->get();
		if (!$show_question_only)
		{
			// get page object output
			$questionoutput = $this->getILIASPage($questionoutput);
		}
		return $questionoutput;
	}
	
	function getTestOutput($active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE)
	{
		$userdata = $this->object->getActiveUserData($active_id);
		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_javaapplet_question_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$template->setCurrentBlock("appletparam");
		$template->setVariable("PARAM_NAME", "test_type");
		include_once "./Modules/Test/classes/class.ilObjTest.php";
		if (ilObjTest::_lookupAnonymity(ilObjTest::_getObjectIDFromTestID($userdata["test_id"])))
		{
			$template->setVariable("PARAM_VALUE", "0");
		}
		else
		{
			$template->setVariable("PARAM_VALUE", "1");
		}
		$template->parseCurrentBlock();
		$template->setCurrentBlock("appletparam");
		$template->setVariable("PARAM_NAME", "active_id");
		$template->setVariable("PARAM_VALUE", $active_id);
		$template->parseCurrentBlock();
		$template->setCurrentBlock("appletparam");
		$template->setVariable("PARAM_NAME", "test_id");
		$template->setVariable("PARAM_VALUE", $userdata["test_id"]);
		$template->parseCurrentBlock();
		$template->setCurrentBlock("appletparam");
		$template->setVariable("PARAM_NAME", "question_id");
		$template->setVariable("PARAM_VALUE", $this->object->getId());
		$template->parseCurrentBlock();
		$template->setCurrentBlock("appletparam");
		$template->setVariable("PARAM_NAME", "user_id");
		$template->setVariable("PARAM_VALUE", $userdata["user_id"]);
		$template->parseCurrentBlock();
		$template->setCurrentBlock("appletparam");
		$template->setVariable("PARAM_NAME", "points_max");
		$template->setVariable("PARAM_VALUE", $this->object->getPoints());
		$template->parseCurrentBlock();
		$template->setCurrentBlock("appletparam");
		$template->setVariable("PARAM_NAME", "session_id");
		$template->setVariable("PARAM_VALUE", $_COOKIE["PHPSESSID"]);
		$template->parseCurrentBlock();
		$template->setCurrentBlock("appletparam");
		$template->setVariable("PARAM_NAME", "client");
		$template->setVariable("PARAM_VALUE", CLIENT_ID);
		$template->parseCurrentBlock();
		$template->setCurrentBlock("appletparam");
		$template->setVariable("PARAM_NAME", "pass");
		$actualpass = ilObjTest::_getPass($active_id);
		$template->setVariable("PARAM_VALUE", $actualpass);
		$template->parseCurrentBlock();
		$template->setCurrentBlock("appletparam");
		$template->setVariable("PARAM_NAME", "post_url");
		$template->setVariable("PARAM_VALUE", ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) . "/Modules/TestQuestionPool/save_question_post_data.php");
		$template->parseCurrentBlock();
		// additional parameters
		for ($i = 0; $i < $this->object->getParameterCount(); $i++)
		{
			$parameter = $this->object->getParameter($i);
			$template->setCurrentBlock("appletparam");
			$template->setVariable("PARAM_NAME", $parameter["name"]);
			$template->setVariable("PARAM_VALUE", $parameter["value"]);
			$template->parseCurrentBlock();
		}

		if ($active_id)
		{
			$solutions = NULL;
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			if (!ilObjTest::_getUsePreviousAnswers($active_id, true))
			{
				if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
			}
			$info = $this->object->getReachedInformation($active_id, $pass);
			foreach ($info as $kk => $infodata)
			{
				$template->setCurrentBlock("appletparam");
				$template->setVariable("PARAM_NAME", "value_" . $infodata["order"] . "_1");
				$template->setVariable("PARAM_VALUE", $infodata["value1"]);
				$template->parseCurrentBlock();
				$template->setCurrentBlock("appletparam");
				$template->setVariable("PARAM_NAME", "value_" . $infodata["order"] . "_2");
				$template->setVariable("PARAM_VALUE", $infodata["value2"]);
				$template->parseCurrentBlock();
			}
			include_once './Services/Administration/classes/class.ilSetting.php';
			$soapSetting = new ilSetting();
			if ($soapSetting->get("soap_user_administration") == 1)
			{
				$template->setCurrentBlock("appletparam");
				$template->setVariable("PARAM_NAME", "server");
				$template->setVariable("PARAM_VALUE", ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) . "/webservice/soap/server.php");
			}
		}
		
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$template->setVariable("APPLET_WIDTH", $this->object->getJavaWidth());
		$template->setVariable("APPLET_HEIGHT", $this->object->getJavaHeight());
		$template->setVariable("APPLET_CODE", $this->object->getJavaCode());
		if (strlen($this->object->getJavaArchive()) > 0)
		{
			$template->setVariable("APPLET_ARCHIVE", " archive=\"".$this->object->getJavaArchive()."\"");
		}
		else
		{
			if (strpos($this->object->getJavaAppletFilename(), ".jar") !== FALSE)
			{
				$template->setVariable("APPLET_ARCHIVE", " archive=\"".$this->object->getJavaPathWeb().$this->object->getJavaAppletFilename()."\"");
			}
		}
		if (strlen($this->object->getJavaCodebase()) > 0)
		{
			$template->setVariable("APPLET_CODEBASE", " codebase=\"".$this->object->getJavaCodebase()."\"");
		}
		else
		{
			if (strpos($this->object->getJavaAppletFilename(), ".class") !== FALSE)
			{
				$template->setVariable("APPLET_CODEBASE", " codebase=\"".$this->object->getJavaPathWeb()."\"");
			}
		}
		$questionoutput = $template->get();
		$pageoutput = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput);
		return $pageoutput;
	}
	
	/**
	* Saves the feedback for a java applet question
	*
	* @access public
	*/
	function saveFeedback()
	{
		include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
		$this->object->saveFeedbackGeneric(0, ilUtil::stripSlashes($_POST["feedback_incomplete"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")));
		$this->object->saveFeedbackGeneric(1, ilUtil::stripSlashes($_POST["feedback_complete"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")));
		$this->object->cleanupMediaObjectUsage();
		parent::saveFeedback();
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
					$this->ctrl->getLinkTargetByClass("ilPageObjectGUI", "edit"),
					array("edit", "insert", "exec_pg"),
					"", "", $force_active);
			}
	
			// edit page
			$ilTabs->addTarget("preview",
				$this->ctrl->getLinkTargetByClass("ilPageObjectGUI", "preview"),
				array("preview"),
				"ilPageObjectGUI", "", $force_active);
		}

		$force_active = false;
		if ($rbacsystem->checkAccess('write', $_GET["ref_id"]))
		{
			$url = "";
			if ($classname) $url = $this->ctrl->getLinkTargetByClass($classname, "editQuestion");
			$commands = $_POST["cmd"];
			if (is_array($commands))
			{
				foreach ($commands as $key => $value)
				{
					if (preg_match("/^delete_.*/", $key, $matches))
					{
						$force_active = true;
					}
				}
			}
			// edit question properties
			$ilTabs->addTarget("edit_properties",
				$url,
				array("editQuestion", "save", "saveEdit", "addkvp", "removekvp"),
				$classname, "", $force_active);
		}

		if ($_GET["q_id"])
		{
			$ilTabs->addTarget("feedback",
				$this->ctrl->getLinkTargetByClass($classname, "feedback"),
				array("feedback", "saveFeedback"),
				$classname, "");
		}
		
		if ($_GET["q_id"])
		{
			$ilTabs->addTarget("solution_hint",
				$this->ctrl->getLinkTargetByClass($classname, "suggestedsolution"),
				array("suggestedsolution", "saveSuggestedSolution", "outSolutionExplorer", "cancel", 
				"addSuggestedSolution","cancelExplorer", "linkChilds", "removeSuggestedSolution"
				),
				$classname, 
				""
			);
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
}
?>
