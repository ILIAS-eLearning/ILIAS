<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilGuiQuestionScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilGuiAnswerScoringAdjustable.php';
include_once './Modules/Test/classes/inc.AssessmentConstants.php';

/**
 * Image map question GUI representation
 *
 * The assImagemapQuestionGUI class encapsulates the GUI representation
 * for image map questions.
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Maximilian Becker <mbecker@databay.de>
 * 
 * @version	$Id$
 * 
 * @ingroup ModulesTestQuestionPool
 */
class assImagemapQuestionGUI extends assQuestionGUI implements ilGuiQuestionScoringAdjustable, ilGuiAnswerScoringAdjustable
{
	private $linecolor;
	
	/**
	 * assImagemapQuestionGUI constructor
	 *
	 * The constructor takes possible arguments an creates an instance of the assImagemapQuestionGUI object.
	 *
	 * @param integer $id The database id of a image map question object.
	 * 
	 * @return \assImagemapQuestionGUI
	 */
	public function __construct($id = -1)
	{
		parent::__construct();
		include_once './Modules/TestQuestionPool/classes/class.assImagemapQuestion.php';
		$this->object = new assImagemapQuestion();
		if ($id >= 0)
		{
			$this->object->loadFromDb($id);
		}
		$assessmentSetting = new ilSetting("assessment");
		$this->linecolor = (strlen($assessmentSetting->get("imap_line_color"))) ? "#" . $assessmentSetting->get("imap_line_color") : "#FF0000";
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
	
	protected function deleteImage()
	{
		$this->writePostData(true);
		$this->object->saveToDb();
		$this->ctrl->redirect($this, 'editQuestion');
	}

	/**
	 * Evaluates a posted edit form and writes the form data in the question object
	 *
	 * @param bool $always
	 *
	 * @return integer A positive value, if one of the required fields wasn't set, else 0
	 */
	public function writePostData($always = false)
	{
		$hasErrors = (!$always) ? $this->editQuestion(true) : false;
		if (!$hasErrors)
		{
			require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
			$this->writeQuestionGenericPostData();
			$this->writeQuestionSpecificPostData(new ilPropertyFormGUI());
			$this->writeAnswerSpecificPostData(new ilPropertyFormGUI());
			$this->saveTaxonomyAssignments();
			return 0;
		}
		return 1;
	}

	public function writeAnswerSpecificPostData(ilPropertyFormGUI $form)
	{
		if ($this->ctrl->getCmd() != 'deleteImage')
		{
			$this->object->flushAnswers();
			if (is_array( $_POST['image']['coords']['name'] ))
			{
				foreach ($_POST['image']['coords']['name'] as $idx => $name)
				{
					if( $this->object->getIsMultipleChoice() && isset($_POST['image']['coords']['points_unchecked']) )
					{
						$pointsUnchecked = $_POST['image']['coords']['points_unchecked'][$idx];
					}
					else
					{
						$pointsUnchecked = 0.0;
					}
					
					$this->object->addAnswer(
						$name,
						$_POST['image']['coords']['points'][$idx],
						$idx,
						$_POST['image']['coords']['coords'][$idx],
						$_POST['image']['coords']['shape'][$idx],
						$pointsUnchecked
					);
				}
			}
			if (strlen( $_FILES['imagemapfile']['tmp_name'] ))
			{
				if ($this->object->getSelfAssessmentEditingMode() && $this->object->getId() < 1)
					$this->object->createNewQuestion();
				$this->object->uploadImagemap( $_FILES['imagemapfile']['tmp_name'] );
			}
		}
	}

	public function writeQuestionSpecificPostData(ilPropertyFormGUI $form)
	{
		if ($this->ctrl->getCmd() == 'deleteImage')
		{
			$this->object->deleteImage();
		}
		else
		{
			if (strlen( $_FILES['image']['tmp_name'] ) == 0)
			{
				$this->object->setImageFilename( $_POST["image_name"] );
			}
		}
		if (strlen( $_FILES['image']['tmp_name'] ))
		{
			if ($this->object->getSelfAssessmentEditingMode() && $this->object->getId() < 1)
				$this->object->createNewQuestion();
			$this->object->setImageFilename( $_FILES['image']['name'], $_FILES['image']['tmp_name'] );
		}
		
		$this->object->setIsMultipleChoice($_POST['is_multiple_choice'] == assImagemapQuestion::MODE_MULTIPLE_CHOICE);
	}

	public function editQuestion($checkonly = FALSE)
	{
		$save = $this->isSaveCommand();
		$this->getQuestionTemplate();

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->outQuestionType());
		$form->setMultipart(TRUE);
		$form->setTableWidth("100%");
		$form->setId("assimagemap");

		$this->addBasicQuestionFormProperties( $form );
		$this->populateQuestionSpecificFormPart( $form );
		// $this->populateAnswerSpecificFormPart( $form ); Nothing to do here, this line FYI. See notes in method.
		
		$this->populateTaxonomyFormSection($form);
		$this->addQuestionFormCommandButtons($form);

		$errors = false;
		if ($save)
		{
			$form->setValuesByPost();
			$errors = !$form->checkInput();
			$form->setValuesByPost(); // again, because checkInput now performs the whole stripSlashes handling 
									  // and we need this if we don't want to have duplication of backslashes
			if ($errors) $checkonly = false;
		}

		if (!$checkonly) $this->tpl->setVariable("QUESTION_DATA", $form->getHTML());
		return $errors;
	}

	public function populateAnswerSpecificFormPart(\ilPropertyFormGUI $form)
	{
		return $form; // Nothing to do here since selectable areas are handled in question-specific-form part
					  // due to their immediate dependency to the image. I decide to not break up the interfaces
					  // more just to support this very rare case. tl;dr: See the issue, ignore it.
	}

	public function populateQuestionSpecificFormPart(\ilPropertyFormGUI $form)
	{
		// is MultipleChoice?
		$radioGroup = new ilRadioGroupInputGUI($this->lng->txt( 'tst_imap_qst_mode' ), 'is_multiple_choice');
		$radioGroup->setValue( $this->object->getIsMultipleChoice() );
		$modeSingleChoice = new ilRadioOption($this->lng->txt( 'tst_imap_qst_mode_sc'), 
											  assImagemapQuestion::MODE_SINGLE_CHOICE);
		$modeMultipleChoice = new ilRadioOption($this->lng->txt( 'tst_imap_qst_mode_mc'), 
												assImagemapQuestion::MODE_MULTIPLE_CHOICE);
		$radioGroup->addOption( $modeSingleChoice );
		$radioGroup->addOption( $modeMultipleChoice );
		$form->addItem( $radioGroup );

		// image
		include_once "./Modules/TestQuestionPool/classes/class.ilImagemapFileInputGUI.php";
		$image = new ilImagemapFileInputGUI($this->lng->txt( 'image' ), 'image');
		$image->setPointsUncheckedFieldEnabled( $this->object->getIsMultipleChoice() );
		$image->setRequired( true );

		if (strlen( $this->object->getImageFilename() ))
		{
			$image->setImage( $this->object->getImagePathWeb() . $this->object->getImageFilename() );
			$image->setValue( $this->object->getImageFilename() );
			$image->setAreas( $this->object->getAnswers() );
			$assessmentSetting = new ilSetting("assessment");
			$linecolor         = (strlen( $assessmentSetting->get( "imap_line_color" )
			)) ? "\"#" . $assessmentSetting->get( "imap_line_color" ) . "\"" : "\"#FF0000\"";
			$image->setLineColor( $linecolor );
			$image->setImagePath( $this->object->getImagePath() );
			$image->setImagePathWeb( $this->object->getImagePathWeb() );
		}
		$form->addItem( $image );

		// imagemapfile
		$imagemapfile = new ilFileInputGUI($this->lng->txt( 'add_imagemap' ), 'imagemapfile');
		$imagemapfile->setRequired( false );
		$form->addItem( $imagemapfile );
		return $form;
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
		// TODO - BEGIN: what exactly is done here? cant we use the parent method? 

		require_once './Modules/Test/classes/class.ilObjTest.php';
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

		$test_output = $this->getTestOutput($active_id, $pass, $is_postponed, $use_post_solutions, $show_feedback);
		$this->tpl->setVariable("QUESTION_OUTPUT", $test_output);
		$this->tpl->setVariable("FORMACTION", $formaction);

		// TODO - END: what exactly is done here? cant we use the parent method? 
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
		$show_manual_scoring = FALSE,
		$show_question_text = TRUE
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
			if(!$this->object->getIsMultipleChoice())
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
			else
			{
				// take the correct solution instead of the user solution
				foreach($this->object->answers as $index => $answer)
				{
					$points_checked   = $answer->getPoints();
					$points_unchecked = $answer->getPointsUnchecked();
					if($points_checked > $points_unchecked)
					{
						if($points_checked > 0)
						{
							array_push($solutions, array("value1" => $index));
						}
					}
				}
			}
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
		include_once "./Services/UICore/classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_imagemap_question_output_solution.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html",TRUE, TRUE, "Modules/TestQuestionPool");
		$questiontext = $this->object->getQuestion();
		if ($show_question_text==true)
		{
			$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		}
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
					$template->setVariable("ICON_OK", ilUtil::getImagePath("icon_ok.svg"));
					$template->setVariable("TEXT_OK", $this->lng->txt("answer_is_right"));
					$template->parseCurrentBlock();
				}
				else
				{
					$template->setCurrentBlock("icon_ok");
					if ($reached_points > 0)
					{
						$template->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_mostly_ok.svg"));
						$template->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_not_correct_but_positive"));
					}
					else
					{
						$template->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_not_ok.svg"));
						$template->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_wrong"));
					}
					$template->parseCurrentBlock();
				}
			}
		}
			
		if ($show_feedback)
		{
			$fb = $this->object->feedbackOBJ->getSpecificAnswerFeedbackTestPresentation(
					$this->object->getId(), $solution_id
			);
			
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
	
	function getPreview($show_question_only = FALSE, $showInlineFeedback = false)
	{
		if( is_object($this->getPreviewSession()) )
		{
			$user_solution = array_values($this->getPreviewSession()->getParticipantsSolution());
			
			include_once "./Modules/TestQuestionPool/classes/class.ilImagemapPreview.php";
			$preview = new ilImagemapPreview($this->object->getImagePath().$this->object->getImageFilename());
			foreach ($user_solution as $idx => $solution_value)
			{
				if (strcmp($solution_value, "") != 0)
				{
					$preview->addArea($solution_value, $this->object->answers[$solution_value]->getArea(), $this->object->answers[$solution_value]->getCoords(), $this->object->answers[$solution_value]->getAnswertext(), "", "", true, $this->linecolor);
				}
			}
			$preview->createPreview();
			$imagepath = $this->object->getImagePathWeb() . $preview->getPreviewFilename($this->object->getImagePath(), $this->object->getImageFilename());
		}
		else
		{
			$user_solution = array();
			$imagepath = $this->object->getImagePathWeb() . $this->object->getImageFilename();
		}
		
		// generate the question output
		include_once "./Services/UICore/classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_imagemap_question_output.html", TRUE, TRUE, "Modules/TestQuestionPool");

		if($this->getQuestionActionCmd())
		{
			$hrefArea = $this->ctrl->getLinkTargetByClass($this->getTargetGuiClass(), $this->getQuestionActionCmd());
		}
		else
		{
			$hrefArea = null;
		}

		foreach ($this->object->answers as $answer_id => $answer)
		{
			$parameter = "&amp;selImage=$answer_id";
			if(is_array($user_solution) && in_array($answer_id, $user_solution))
			{
				$parameter = "&amp;remImage=$answer_id";
			}

			if($hrefArea)
			{
				$template->setCurrentBlock("imagemap_area_href");
				$template->setVariable("HREF_AREA", $hrefArea . $parameter);
				$template->parseCurrentBlock();
			}
			
			$template->setCurrentBlock("imagemap_area");
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
		if($this->object->getIsMultipleChoice())
		{
			$user_solution = array();
		}
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
				if($this->object->getIsMultipleChoice())
				{
					$user_solution[] = $solution_value["value1"];
				}
				else
				{
					$user_solution = $solution_value["value1"];
				}
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
		include_once "./Services/UICore/classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_imagemap_question_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$this->ctrl->setParameterByClass($this->getTargetGuiClass(), "formtimestamp", time());
		$hrefArea = $this->ctrl->getLinkTargetByClass($this->getTargetGuiClass(), $this->getQuestionActionCmd());
		foreach ($this->object->answers as $answer_id => $answer)
		{
			$template->setCurrentBlock("imagemap_area");
			$parameter = "&amp;selImage=$answer_id";
			if(is_array($user_solution) && in_array($answer_id, $user_solution))
			{
				$parameter = "&amp;remImage=$answer_id";
			}
			$template->setVariable("HREF_AREA", $hrefArea . $parameter);
			$template->setVariable("SHAPE", $answer->getArea());
			$template->setVariable("COORDS", $answer->getCoords());
			$template->setVariable("ALT", ilUtil::prepareFormOutput($answer->getAnswertext()));
			$template->setVariable("TITLE", ilUtil::prepareFormOutput($answer->getAnswertext()));
			$template->parseCurrentBlock();
			if ($show_feedback)
			{
				if(!$this->object->getIsMultipleChoice() && strlen($user_solution) && $user_solution == $answer_id)
				{
					$feedback = $this->object->feedbackOBJ->getSpecificAnswerFeedbackTestPresentation(
							$this->object->getId(), $answer_id
					);
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
	 * Sets the ILIAS tabs for this question type
	 *
	 * @access public
	 * 
	 * @todo:	MOVE THIS STEPS TO COMMON QUESTION CLASS assQuestionGUI
	 */
	public function setQuestionTabs()
	{
		global $rbacsystem, $ilTabs;

		$ilTabs->clearTargets();
		
		$this->ctrl->setParameterByClass("ilAssQuestionPageGUI", "q_id", $_GET["q_id"]);
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
				$ilTabs->addTarget("edit_page",
					$this->ctrl->getLinkTargetByClass("ilAssQuestionPageGUI", "edit"),
					array("edit", "insert", "exec_pg"),
					"", "", $force_active);
			}

			$this->addTab_QuestionPreview($ilTabs);
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
			$ilTabs->addTarget("edit_question",
				$url,
				array("editQuestion", "save", "addArea", "addRect", "addCircle", "addPoly", 
					 "uploadingImage", "uploadingImagemap", "areaEditor",
					"removeArea", "saveShape", "saveEdit", "originalSyncForm"),
				$classname, "", $force_active);
		}

		// add tab for question feedback within common class assQuestionGUI
		$this->addTab_QuestionFeedback($ilTabs);

		// add tab for question hint within common class assQuestionGUI
		$this->addTab_QuestionHints($ilTabs);

		// add tab for question's suggested solution within common class assQuestionGUI
		$this->addTab_SuggestedSolution($ilTabs, $classname);

		// Assessment of questions sub menu entry
		if ($_GET["q_id"])
		{
			$ilTabs->addTarget("statistics",
				$this->ctrl->getLinkTargetByClass($classname, "assessment"),
				array("assessment"),
				$classname, "");
		}

		$this->addBackTab($ilTabs);
	}

	function getSpecificFeedbackOutput($active_id, $pass)
	{
		if( !$this->object->feedbackOBJ->specificAnswerFeedbackExists(array_values($this->object->getAnswers())) )
		{
			return '';
		}

		$output = '<table class="test_specific_feedback"><tbody>';

		foreach($this->object->getAnswers() as $idx => $answer)
		{
			$feedback = $this->object->feedbackOBJ->getSpecificAnswerFeedbackTestPresentation(
				$this->object->getId(), $idx
			);

			$output .= "<tr><td>{$answer->getAnswerText()}</td><td>{$feedback}</td></tr>";
		}

		$output .= '</tbody></table>';

		return $this->object->prepareTextareaOutput($output, TRUE);
	}

	/**
	 * Returns a list of postvars which will be suppressed in the form output when used in scoring adjustment.
	 * The form elements will be shown disabled, so the users see the usual form but can only edit the settings, which
	 * make sense in the given context.
	 *
	 * E.g. array('cloze_type', 'image_filename')
	 *
	 * @return string[]
	 */
	public function getAfterParticipationSuppressionAnswerPostVars()
	{
		return array();
	}

	/**
	 * Returns a list of postvars which will be suppressed in the form output when used in scoring adjustment.
	 * The form elements will be shown disabled, so the users see the usual form but can only edit the settings, which
	 * make sense in the given context.
	 *
	 * E.g. array('cloze_type', 'image_filename')
	 *
	 * @return string[]
	 */
	public function getAfterParticipationSuppressionQuestionPostVars()
	{
		return array();
	}

	/**
	 * Returns an html string containing a question specific representation of the answers so far
	 * given in the test for use in the right column in the scoring adjustment user interface.
	 *
	 * @param array $relevant_answers
	 *
	 * @return string
	 */
	public function getAggregatedAnswersView($relevant_answers)
	{
		return ''; //print_r($relevant_answers,true);
	}
}