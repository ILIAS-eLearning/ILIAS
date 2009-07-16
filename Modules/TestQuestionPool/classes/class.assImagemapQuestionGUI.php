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
* Image map question GUI representation
*
* The assImagemapQuestionGUI class encapsulates the GUI representation
* for image map questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assImagemapQuestionGUI extends assQuestionGUI
{
	private $linecolor;
	
	/**
	* assImagemapQuestionGUI constructor
	*
	* The constructor takes possible arguments an creates an instance of the assImagemapQuestionGUI object.
	*
	* @param integer $id The database id of a image map question object
	* @access public
	*/
	function __construct($id = -1)
	{
		parent::__construct();
		include_once "./Modules/TestQuestionPool/classes/class.assImagemapQuestion.php";
		$this->object = new assImagemapQuestion();
		if ($id >= 0)
		{
			$this->object->loadFromDb($id);
		}
		$assessmentSetting = new ilSetting("assessment");
		$this->linecolor = (strlen($assessmentSetting->get("imap_line_color"))) ? "\"#" . $assessmentSetting->get("imap_line_color") . "\"" : "\"#FF0000\"";
		
	}

	function getCommand($cmd)
	{
		if (isset($_POST["imagemap"]) ||
		isset($_POST["imagemap_x"]) ||
		isset($_POST["imagemap_y"]))
		{
			$this->ctrl->setCmd("getCoords");
			$cmd = "getCoords";
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

			if ($_POST['image_delete'])
			{
				$this->object->deleteImage();
			}
			else
			{
				if (strlen($_FILES['image']['tmp_name']) == 0)
				{
					$this->object->setImageFilename(ilUtil::stripSlashes($_POST["image_name"]));
				}
			}
			if (strlen($_FILES['image']['tmp_name']))
			{
				$this->object->setImageFilename($_FILES['image']['name'], $_FILES['image']['tmp_name']);
			}

			if (!$_POST['image_delete'])
			{
				$this->object->flushAnswers();
				if (is_array($_POST['image']['coords']['name']))
				{
					foreach ($_POST['image']['coords']['name'] as $idx => $name)
					{
						$this->object->addAnswer($name, $_POST['image']['coords']['points'][$idx], $idx, $_POST['image']['coords']['coords'][$idx], $_POST['image']['coords']['shape'][$idx]);
					}
				}
				if (strlen($_FILES['imagemapfile']['tmp_name']))
				{
					$this->object->uploadImagemap($_FILES['imagemapfile']['tmp_name']);
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
		$form->setMultipart(TRUE);
		$form->setTableWidth("100%");
		$form->setId("assimagemap");

		// title, author, description, question, working time (assessment mode)
		$this->addBasicQuestionFormProperties($form);
	
		// image
		include_once "./Modules/TestQuestionPool/classes/class.ilImagemapFileInputGUI.php";
		$image = new ilImagemapFileInputGUI($this->lng->txt('image'), 'image');
		$image->setRequired(true);

		if (strlen($this->object->getImageFilename()))
		{
			$image->setImage($this->object->getImagePathWeb() . $this->object->getImageFilename());
			$image->setValue($this->object->getImageFilename());
			$image->setAreas($this->object->getAnswers());
			$assessmentSetting = new ilSetting("assessment");
			$linecolor = (strlen($assessmentSetting->get("imap_line_color"))) ? "\"#" . $assessmentSetting->get("imap_line_color") . "\"" : "\"#FF0000\"";
			$image->setLineColor($linecolor);
			$image->setImagePath($this->object->getImagePath());
			$image->setImagePathWeb($this->object->getImagePathWeb());
		}
		$form->addItem($image);

		// imagemapfile
		$imagemapfile = new ilFileInputGUI($this->lng->txt('add_imagemap'), 'imagemapfile');
		$imagemapfile->setRequired(false);
		$form->addItem($imagemapfile);

		$form->addCommandButton("save", $this->lng->txt("save"));
		if (!$this->getSelfAssessmentEditingMode())
		{
			$form->addCommandButton("saveEdit", $this->lng->txt("save_edit"));
		}
	
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
	
	function addRect()
	{
		$this->areaEditor('rect');
	}
	
	function addCircle()
	{
		$this->areaEditor('circle');
	}
	
	function addPoly()
	{
		$this->areaEditor('poly');
	}

	/**
	* Saves a shape of the area editor
	*/
	public function saveShape()
	{
		$coords = "";
		switch ($_POST["shape"])
		{
			case "rect":
				$coords = join($_POST['image']['mapcoords'], ",");
				ilUtil::sendSuccess($this->lng->txt('msg_rect_added'), true);
				break;
			case "circle":
				if (preg_match("/(\d+)\s*,\s*(\d+)\s+(\d+)\s*,\s*(\d+)/", $_POST['image']['mapcoords'][0] . " " . $_POST['image']['mapcoords'][1], $matches))
				{
					$coords = "$matches[1],$matches[2]," . (int)sqrt((($matches[3]-$matches[1])*($matches[3]-$matches[1]))+(($matches[4]-$matches[2])*($matches[4]-$matches[2])));
				}
				ilUtil::sendSuccess($this->lng->txt('msg_circle_added'), true);
				break;
			case "poly":
				$coords = join($_POST['image']['mapcoords'], ",");
				ilUtil::sendSuccess($this->lng->txt('msg_poly_added'), true);
				break;
		}
		$this->object->addAnswer($_POST["shapetitle"], 0, count($this->object->getAnswers()), $coords, $_POST["shape"]);
		$this->object->saveToDb();
		$this->ctrl->redirect($this, 'editQuestion');
	}

	public function areaEditor($shape = '')
	{
		$shape = (strlen($shape)) ? $shape : $_POST['shape'];
		include_once "./Modules/TestQuestionPool/classes/class.ilImagemapPreview.php";
		$this->getQuestionTemplate();
		$this->tpl->addBlockFile("QUESTION_DATA", "question_data", "tpl.il_as_qpl_imagemap_question.html", "Modules/TestQuestionPool");
		$coords = array();
		if (is_array($_POST['image']['mapcoords']))
		{
			foreach ($_POST['image']['mapcoords'] as $value)
			{
				array_push($coords, $value);
			}
		}
		if (is_array($_POST['cmd']['areaEditor']['image']))
		{
			array_push($coords, $_POST['cmd']['areaEditor']['image'][0] . "," . $_POST['cmd']['areaEditor']['image'][1]);
		}
		foreach ($coords as $value)
		{
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("HIDDEN_NAME", 'image[mapcoords][]');
			$this->tpl->setVariable("HIDDEN_VALUE", $value);
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("hidden");
		$this->tpl->setVariable("HIDDEN_NAME", 'shape');
		$this->tpl->setVariable("HIDDEN_VALUE", $shape);
		$this->tpl->parseCurrentBlock();

		$preview = new ilImagemapPreview($this->object->getImagePath().$this->object->getImageFilename());
		foreach ($this->object->answers as $index => $answer)
		{
			$preview->addArea($index, $answer->getArea(), $answer->getCoords(), $answer->getAnswertext(), "", "", true, $this->linecolor);
		}
		$hidearea = false;
		$disabled_save = " disabled=\"disabled\"";
		$c = "";
		switch ($shape)
		{
			case "rect":
				if (count($coords) == 0)
				{
					ilUtil::sendInfo($this->lng->txt("rectangle_click_tl_corner"));
				}
				else if (count($coords) == 1)
				{
					ilUtil::sendInfo($this->lng->txt("rectangle_click_br_corner"));
					$preview->addPoint($preview->getAreaCount(), join($coords, ","), TRUE, "blue");
				}
				else if (count($coords) == 2)
				{
					$c = join($coords, ",");
					$hidearea = true;
					$disabled_save = "";
				}
				break;
			case "circle":
				if (count($coords) == 0)
				{
					ilUtil::sendInfo($this->lng->txt("circle_click_center"));
				}
				else if (count($coords) == 1)
				{
					ilUtil::sendInfo($this->lng->txt("circle_click_circle"));
					$preview->addPoint($preview->getAreaCount(), join($coords, ","), TRUE, "blue");
				}
				else if (count($coords) == 2)
				{
					if (preg_match("/(\d+)\s*,\s*(\d+)\s+(\d+)\s*,\s*(\d+)/", $coords[0] . " " . $coords[1], $matches))
					{
						$c = "$matches[1],$matches[2]," . (int)sqrt((($matches[3]-$matches[1])*($matches[3]-$matches[1]))+(($matches[4]-$matches[2])*($matches[4]-$matches[2])));
					}
					$hidearea = true;
					$disabled_save = "";
				}
				break;
			case "poly":
				if (count($coords) == 0)
				{
					ilUtil::sendInfo($this->lng->txt("polygon_click_starting_point"));
				}
				else if (count($coords) == 1)
				{
					ilUtil::sendInfo($this->lng->txt("polygon_click_next_point"));
					$preview->addPoint($preview->getAreaCount(), join($coords, ","), TRUE, "blue");
				}
				else if (count($coords) > 1)
				{
					ilUtil::sendInfo($this->lng->txt("polygon_click_next_or_save"));
					$disabled_save = "";
					$c = join($coords, ",");
				}
				break;
		}
		if (strlen($c))
		{
			$preview->addArea($preview->getAreaCount(), $shape, $c, $_POST["shapetitle"], "", "", true, "blue");
		}
		$preview->createPreview();
		$imagepath = $this->object->getImagePathWeb() . $preview->getPreviewFilename($this->object->getImagePath(), $this->object->getImageFilename()) . "?img=" . time();
		if (!$hidearea)
		{
			$this->tpl->setCurrentBlock("maparea");
			$this->tpl->setVariable("IMAGE_SOURCE", "$imagepath");
			$this->tpl->setVariable("IMAGEMAP_NAME", "image");
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setCurrentBlock("imagearea");
			$this->tpl->setVariable("IMAGE_SOURCE", "$imagepath");
			$this->tpl->setVariable("ALT_IMAGE", $this->lng->txt("imagemap"));
			$this->tpl->parseCurrentBlock();
		}

		if (strlen($_POST['shapetitle']))
		{
			$this->tpl->setCurrentBlock("shapetitle");
			$this->tpl->setVariable("VALUE_SHAPETITLE", $_POST["shapetitle"]);
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("TEXT_IMAGEMAP", $this->lng->txt("imagemap"));
		$this->tpl->setVariable("TEXT_SHAPETITLE", $this->lng->txt("name"));
		$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("DISABLED_SAVE", $disabled_save);
		switch ($shape)
		{
			case "rect":
				$this->tpl->setVariable("FORMACTION",	$this->ctrl->getFormaction($this, 'addRect'));
				break;
			case 'circle':
				$this->tpl->setVariable("FORMACTION",	$this->ctrl->getFormaction($this, 'addCircle'));
				break;
			case 'poly':
				$this->tpl->setVariable("FORMACTION",	$this->ctrl->getFormaction($this, 'addPoly'));
				break;
		}
	}

	function removeArea()
	{
		$this->writePostData(true);
		$position = key($_POST['cmd']['removeArea']['image']);
		$this->object->deleteArea($position);
		$this->editQuestion();
	}

	function back()
	{
		ilUtil::sendInfo($this->lng->txt('msg_cancel'), true);
		$this->ctrl->redirect($this, 'editQuestion');
	}

	function outQuestionForTest($formaction, $active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE, $show_feedback = FALSE)
	{
		$test_output = $this->getTestOutput($active_id, $pass, $is_postponed, $use_post_solutions, $show_feedback); 
		$this->tpl->setVariable("QUESTION_OUTPUT", $test_output);

		$this->ctrl->setParameterByClass("ilTestOutputGUI", "formtimestamp", time());
		$formaction = $this->ctrl->getLinkTargetByClass("ilTestOutputGUI", "selectImagemapRegion");
		include_once "./Modules/Test/classes/class.ilObjTest.php";
		if (!ilObjTest::_getUsePreviousAnswers($active_id, true))
		{
			$pass = ilObjTest::_getPass($active_id);
			$info =& $this->object->getSolutionValues($active_id, $pass);
		}
		else
		{
			$info =& $this->object->getSolutionValues($active_id, NULL);
		}
		if (count($info))
		{
			if (strcmp($info[0]["value1"], "") != 0)
			{
				$formaction .= "&selImage=" . $info[0]["value1"];
			}
		}
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
		$imagepath = $this->object->getImagePathWeb() . $this->object->getImageFilename();
		$solutions = array();
		if (($active_id > 0) && (!$show_correct_solution))
		{
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			if ((!$showsolution) && !ilObjTest::_getUsePreviousAnswers($active_id, true))
			{
				if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
			}
			$solutions =& $this->object->getSolutionValues($active_id, $pass);
		}
		else
		{
			$found_index = -1;
			$max_points = 0;
			foreach ($this->object->answers as $index => $answer)
			{
				if ($answer->getPoints() > $max_points)
				{
					$max_points = $answer->getPoints();
					$found_index = $index;
				}
			}
			array_push($solutions, array("value1" => $found_index));
		}
		$solution_id = -1;
		if (is_array($solutions))
		{
			include_once "./Modules/TestQuestionPool/classes/class.ilImagemapPreview.php";
			$preview = new ilImagemapPreview($this->object->getImagePath().$this->object->getImageFilename());
			foreach ($solutions as $idx => $solution_value)
			{
				if (strcmp($solution_value["value1"], "") != 0)
				{
					$preview->addArea($solution_value["value1"], $this->object->answers[$solution_value["value1"]]->getArea(), $this->object->answers[$solution_value["value1"]]->getCoords(), $this->object->answers[$solution_value["value1"]]->getAnswertext(), "", "", true, $this->linecolor);
					$solution_id = $solution_value["value1"];
				}
			}
			$preview->createPreview();
			$imagepath = $this->object->getImagePathWeb() . $preview->getPreviewFilename($this->object->getImagePath(), $this->object->getImageFilename());
		}
		
		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_imagemap_question_output_solution.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html",TRUE, TRUE, "Modules/TestQuestionPool");
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$template->setVariable("IMG_SRC", "$imagepath");
		$template->setVariable("IMG_ALT", $this->lng->txt("imagemap"));
		$template->setVariable("IMG_TITLE", $this->lng->txt("imagemap"));
		if (($active_id > 0) && (!$show_correct_solution))
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
			
		if ($show_feedback)
		{
			$fb = $this->object->getFeedbackSingleAnswer($solution_id);
			if (strlen($fb))
			{
				$template->setCurrentBlock("feedback");
				$template->setVariable("FEEDBACK", $fb);
				$template->parseCurrentBlock();
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
		$imagepath = $this->object->getImagePathWeb() . $this->object->getImageFilename();
		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_imagemap_question_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$formaction = "#";
		foreach ($this->object->answers as $answer_id => $answer)
		{
			$template->setCurrentBlock("imagemap_area");
			$template->setVariable("HREF_AREA", $formaction);
			$template->setVariable("SHAPE", $answer->getArea());
			$template->setVariable("COORDS", $answer->getCoords());
			$template->setVariable("ALT", ilUtil::prepareFormOutput($answer->getAnswertext()));
			$template->setVariable("TITLE", ilUtil::prepareFormOutput($answer->getAnswertext()));
			$template->parseCurrentBlock();
		}
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$template->setVariable("IMG_SRC", "$imagepath");
		$template->setVariable("IMG_ALT", $this->lng->txt("imagemap"));
		$template->setVariable("IMG_TITLE", $this->lng->txt("imagemap"));
		$questionoutput = $template->get();
		if (!$show_question_only)
		{
			// get page object output
			$questionoutput = $this->getILIASPage($questionoutput);
		}
		return $questionoutput;
	}

	function getTestOutput($active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE, $show_feedback = FALSE)
	{
		// get the solution of the user for the active pass or from the last pass if allowed
		$user_solution = "";
		if ($active_id)
		{
			$solutions = NULL;
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			if (!ilObjTest::_getUsePreviousAnswers($active_id, true))
			{
				if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
			}
			$solutions =& $this->object->getSolutionValues($active_id, $pass);
			foreach ($solutions as $idx => $solution_value)
			{
				$user_solution = $solution_value["value1"];
			}
		}

		$imagepath = $this->object->getImagePathWeb() . $this->object->getImageFilename();
		if ($active_id)
		{
			$solutions = NULL;
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			if ((!$showsolution) && !ilObjTest::_getUsePreviousAnswers($active_id, true))
			{
				if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
			}
			$solutions =& $this->object->getSolutionValues($active_id, $pass);
			include_once "./Modules/TestQuestionPool/classes/class.ilImagemapPreview.php";
			$preview = new ilImagemapPreview($this->object->getImagePath().$this->object->getImageFilename());
			foreach ($solutions as $idx => $solution_value)
			{
				if (strcmp($solution_value["value1"], "") != 0)
				{
					$preview->addArea($solution_value["value1"], $this->object->answers[$solution_value["value1"]]->getArea(), $this->object->answers[$solution_value["value1"]]->getCoords(), $this->object->answers[$solution_value["value1"]]->getAnswertext(), "", "", true, $this->linecolor);
				}
			}
			$preview->createPreview();
			$imagepath = $this->object->getImagePathWeb() . $preview->getPreviewFilename($this->object->getImagePath(), $this->object->getImageFilename());
		}
		
		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_imagemap_question_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$this->ctrl->setParameterByClass("ilTestOutputGUI", "formtimestamp", time());
		$formaction = $this->ctrl->getLinkTargetByClass("ilTestOutputGUI", "selectImagemapRegion");
		foreach ($this->object->answers as $answer_id => $answer)
		{
			$template->setCurrentBlock("imagemap_area");
			$template->setVariable("HREF_AREA", $formaction . "&amp;selImage=$answer_id");
			$template->setVariable("SHAPE", $answer->getArea());
			$template->setVariable("COORDS", $answer->getCoords());
			$template->setVariable("ALT", ilUtil::prepareFormOutput($answer->getAnswertext()));
			$template->setVariable("TITLE", ilUtil::prepareFormOutput($answer->getAnswertext()));
			$template->parseCurrentBlock();
			if ($show_feedback)
			{
				if (strlen($user_solution) && $user_solution == $answer_id)
				{
					$feedback = $this->object->getFeedbackSingleAnswer($user_solution);
					if (strlen($feedback))
					{
						$template->setCurrentBlock("feedback");
						$template->setVariable("FEEDBACK", $feedback);
						$template->parseCurrentBlock();
					}
				}
			}
		}
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$template->setVariable("IMG_SRC", "$imagepath");
		$template->setVariable("IMG_ALT", $this->lng->txt("imagemap"));
		$template->setVariable("IMG_TITLE", $this->lng->txt("imagemap"));
		$questionoutput = $template->get();
		$pageoutput = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput);
		return $pageoutput;
	}

	/**
	* Saves the feedback for a single choice question
	*/
	function saveFeedback()
	{
		include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
		$this->object->saveFeedbackGeneric(0, ilUtil::stripSlashes($_POST["feedback_incomplete"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")));
		$this->object->saveFeedbackGeneric(1, ilUtil::stripSlashes($_POST["feedback_complete"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")));
		foreach ($this->object->answers as $index => $answer)
		{
			$this->object->saveFeedbackSingleAnswer($index, ilUtil::stripSlashes($_POST["feedback_answer_$index"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")));
		}
		$this->object->cleanupMediaObjectUsage();
		parent::saveFeedback();
	}

	/**
	* Creates the output of the feedback page for a single choice question
	*/
	function feedback()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "feedback", "tpl.il_as_qpl_imagemap_feedback.html", "Modules/TestQuestionPool");
		foreach ($this->object->answers as $index => $answer)
		{
			$this->tpl->setCurrentBlock("feedback_answer");
			$this->tpl->setVariable("FEEDBACK_TEXT_ANSWER", $this->lng->txt("feedback"));
			$text = strtoupper($answer->getArea() . " (" . $answer->getCoords() . ")");
			if (strlen($answer->getAnswertext()))
			{
				$text = $answer->getAnswertext() . ": " . $text;
			}
			$this->tpl->setVariable("ANSWER_TEXT", $this->object->prepareTextareaOutput($text, TRUE));
			$this->tpl->setVariable("ANSWER_ID", $index);
			$this->tpl->setVariable("VALUE_FEEDBACK_ANSWER", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($this->object->getFeedbackSingleAnswer($index)), FALSE));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("FEEDBACK_TEXT", $this->lng->txt("feedback"));
		$this->tpl->setVariable("FEEDBACK_COMPLETE", $this->lng->txt("feedback_complete_solution"));
		$this->tpl->setVariable("VALUE_FEEDBACK_COMPLETE", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($this->object->getFeedbackGeneric(1)), FALSE));
		$this->tpl->setVariable("FEEDBACK_INCOMPLETE", $this->lng->txt("feedback_incomplete_solution"));
		$this->tpl->setVariable("VALUE_FEEDBACK_INCOMPLETE", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($this->object->getFeedbackGeneric(0)), FALSE));
		$this->tpl->setVariable("FEEDBACK_ANSWERS", $this->lng->txt("feedback_answers"));
		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		include_once "./Services/RTE/classes/class.ilRTE.php";
		$rtestring = ilRTE::_getRTEClassname();
		include_once "./Services/RTE/classes/class.$rtestring.php";
		$rte = new $rtestring();
		$rte->addPlugin("latex");
		$rte->addButton("latex"); $rte->addButton("pastelatex");
		include_once "./classes/class.ilObject.php";
		$obj_id = $_GET["q_id"];
		$obj_type = ilObject::_lookupType($_GET["ref_id"], TRUE);
		$rte->addRTESupport($obj_id, $obj_type, "assessment");
	}

	/**
	* Sets the ILIAS tabs for this question type
	*/
	public function setQuestionTabs()
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
			if (array_key_exists("imagemap_x", $_POST))
			{
				$force_active = true;
			}
			// edit question properties
			$ilTabs->addTarget("edit_properties",
				$url,
				array("editQuestion", "save", "addArea", "addRect", "addCircle", "addPoly", 
					 "uploadingImage", "uploadingImagemap", "areaEditor",
					"removeArea", "saveShape", "saveEdit"),
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
