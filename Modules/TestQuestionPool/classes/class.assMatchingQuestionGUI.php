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
* Matching question GUI representation
*
* The assMatchingQuestionGUI class encapsulates the GUI representation
* for matching questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assMatchingQuestionGUI extends assQuestionGUI
{
	/**
	* assMatchingQuestionGUI constructor
	*
	* The constructor takes possible arguments an creates an instance of the assMatchingQuestionGUI object.
	*
	* @param integer $id The database id of a image map question object
	* @access public
	*/
	function __construct($id = -1)
	{
		parent::__construct();
		include_once "./Modules/TestQuestionPool/classes/class.assMatchingQuestion.php";
		$this->object = new assMatchingQuestion();
		$this->setErrorMessage($this->lng->txt("msg_form_save_error"));
		if ($id >= 0)
		{
			$this->object->loadFromDb($id);
		}
	}

	function getCommand($cmd)
	{
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
			$this->object->setShuffle($_POST["shuffle"]);
			$this->object->setThumbGeometry($_POST["thumb_geometry"]);
			$this->object->setElementHeight($_POST["element_height"]);
			// adding estimated working time
			$this->object->setEstimatedWorkingTime(
				ilUtil::stripSlashes($_POST["Estimated"]["hh"]),
				ilUtil::stripSlashes($_POST["Estimated"]["mm"]),
				ilUtil::stripSlashes($_POST["Estimated"]["ss"])
			);

			// Delete all existing answers and create new answers from the form data
			$this->object->flushMatchingPairs();
			$this->object->flushTerms();
			$this->object->flushDefinitions();
			$saved = false;

			// add terms
			include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingTerm.php";
			foreach ($_POST['terms']['answer'] as $index => $answer)
			{
				$filename = $_POST['terms']['imagename'][$index];
				if (strlen($_FILES['terms']['name']['image'][$index]))
				{
					// upload the new file
					$name = $_FILES['terms']['name']['image'][$index];
					if ($this->object->setImageFile($_FILES['terms']['tmp_name']['image'][$index], $this->object->getEncryptedFilename($name)))
					{
						$filename = $this->object->getEncryptedFilename($name);
					}
					else
					{
						$filename = "";
					}
				}
				$this->object->addTerm(new assAnswerMatchingTerm(ilUtil::stripSlashes($answer), $filename, $_POST['terms']['identifier'][$index]));
			}

			// add definitions
			include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingDefinition.php";
			foreach ($_POST['definitions']['answer'] as $index => $answer)
			{
				$filename = $_POST['definitions']['imagename'][$index];
				if (strlen($_FILES['definitions']['name']['image'][$index]))
				{
					// upload the new file
					$name = $_FILES['definitions']['name']['image'][$index];
					if ($this->object->setImageFile($_FILES['definitions']['tmp_name']['image'][$index], $this->object->getEncryptedFilename($name)))
					{
						$filename = $this->object->getEncryptedFilename($name);
					}
					else
					{
						$filename = "";
					}
				}
				$this->object->addDefinition(new assAnswerMatchingDefinition(ilUtil::stripSlashes($answer), $filename, $_POST['definitions']['identifier'][$index]));
			}

			// add matching pairs
			include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingPair.php";
			foreach ($_POST['pairs']['points'] as $index => $points)
			{
				$term_id = $_POST['pairs']['term'][$index];
				$definition_id = $_POST['pairs']['definition'][$index];
				$this->object->addMatchingPair($this->object->getTermWithIdentifier($term_id), $this->object->getDefinitionWithIdentifier($definition_id), $points);
			}

			return 0;
		}
		else
		{
			return 1;
		}
	}
	
	/**
	* Upload an image
	*/
	public function uploadterms()
	{
		$this->writePostData(true);
		$position = key($_POST['cmd']['uploadterms']);
		$this->editQuestion();
	}

	/**
	* Remove an image
	*/
	public function removeimageterms()
	{
		$this->writePostData(true);
		$position = key($_POST['cmd']['removeimageterms']);
		$filename = $_POST['terms']['imagename'][$position];
		$this->object->removeTermImage($position);
		$this->editQuestion();
	}

	/**
	* Upload an image
	*/
	public function uploaddefinitions()
	{
		$this->writePostData(true);
		$position = key($_POST['cmd']['uploaddefinitions']);
		$this->editQuestion();
	}

	/**
	* Remove an image
	*/
	public function removeimagedefinitions()
	{
		$this->writePostData(true);
		$position = key($_POST['cmd']['removeimagedefinitions']);
		$filename = $_POST['definitions']['imagename'][$position];
		$this->object->removeDefinitionImage($position);
		$this->editQuestion();
	}

	public function addterms()
	{
		$this->writePostData();
		$position = key($_POST["cmd"]["addterms"]);
		$this->object->insertTerm($position+1);
		$this->editQuestion();
	}

	public function removeterms()
	{
		$this->writePostData();
		$position = key($_POST["cmd"]["removeterms"]);
		$this->object->deleteTerm($position);
		$this->editQuestion();
	}

	public function adddefinitions()
	{
		$this->writePostData();
		$position = key($_POST["cmd"]["adddefinitions"]);
		$this->object->insertDefinition($position+1);
		$this->editQuestion();
	}

	public function removedefinitions()
	{
		$this->writePostData();
		$position = key($_POST["cmd"]["removedefinitions"]);
		$this->object->deleteDefinition($position);
		$this->editQuestion();
	}

	public function addpairs()
	{
		$this->writePostData();
		$position = key($_POST["cmd"]["addpairs"]);
		$this->object->insertMatchingPair($position+1);
		$this->editQuestion();
	}

	public function removepairs()
	{
		$this->writePostData();
		$position = key($_POST["cmd"]["removepairs"]);
		$this->object->deleteMatchingPair($position);
		$this->editQuestion();
	}

	/**
	* Creates an output of the edit form for the question
	*
	* @access public
	*/
	function editQuestion($checkonly = FALSE)
	{
		$save = ((strcmp($this->ctrl->getCmd(), "save") == 0) || (strcmp($this->ctrl->getCmd(), "saveEdit") == 0)) ? TRUE : FALSE;
		$this->getQuestionTemplate();

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->outQuestionType());
		$form->setMultipart(true);
		$form->setTableWidth("100%");
		$form->setId("matching");

		// Edit mode
		$hidden = new ilHiddenInputGUI("matching_type");
		$hidden->setValue($matchingtype);
		$form->addItem($hidden);
		
		// title, author, description, question, working time (assessment mode)
		$this->addBasicQuestionFormProperties($form);

		// shuffle
		$shuffle = new ilSelectInputGUI($this->lng->txt("shuffle_answers"), "shuffle");
		$shuffle_options = array(
			0 => $this->lng->txt("no"),
			1 => $this->lng->txt("matching_shuffle_terms_definitions"),
			2 => $this->lng->txt("matching_shuffle_terms"),
			3 => $this->lng->txt("matching_shuffle_definitions")
		);
		$shuffle->setOptions($shuffle_options);
		$shuffle->setValue($this->object->getShuffle());
		$shuffle->setRequired(FALSE);
		$form->addItem($shuffle);

		$element_height = new ilNumberInputGUI($this->lng->txt("element_height"), "element_height");
		$element_height->setValue($this->object->getElementHeight());
		$element_height->setRequired(false);
		$element_height->setMaxLength(6);
		$element_height->setMinValue(20);
		$element_height->setSize(6);
		$element_height->setInfo($this->lng->txt("element_height_info"));
		$form->addItem($element_height);

		$geometry = new ilNumberInputGUI($this->lng->txt("thumb_geometry"), "thumb_geometry");
		$geometry->setValue($this->object->getThumbGeometry());
		$geometry->setRequired(true);
		$geometry->setMaxLength(6);
		$geometry->setMinValue(20);
		$geometry->setSize(6);
		$geometry->setInfo($this->lng->txt("thumb_geometry_info"));
		$form->addItem($geometry);
		
		// Definitions
		include_once "./Modules/TestQuestionPool/classes/class.ilMatchingWizardInputGUI.php";
		$definitions = new ilMatchingWizardInputGUI($this->lng->txt("definitions"), "definitions");
		$definitions->setRequired(true);
		$definitions->setQuestionObject($this->object);
		$definitions->setTextName($this->lng->txt('definition_text'));
		$definitions->setImageName($this->lng->txt('definition_image'));
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingDefinition.php";
		$definitionvalues = (count($this->object->getDefinitions())) ? $this->object->getDefinitions() : array(new assAnswerMatchingDefinition());
		$definitions->setValues($definitionvalues);
		$form->addItem($definitions);
		
		// Terms
		include_once "./Modules/TestQuestionPool/classes/class.ilMatchingWizardInputGUI.php";
		$terms = new ilMatchingWizardInputGUI($this->lng->txt("terms"), "terms");
		$terms->setRequired(true);
		$terms->setQuestionObject($this->object);
		$terms->setTextName($this->lng->txt('term_text'));
		$terms->setImageName($this->lng->txt('term_image'));
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingTerm.php";
		$termvalues = (count($this->object->getTerms())) ? $this->object->getTerms() : array(new assAnswerMatchingTerm());
		$terms->setValues($termvalues);
		$form->addItem($terms);
		
		// Matching Pairs
		include_once "./Modules/TestQuestionPool/classes/class.ilMatchingPairWizardInputGUI.php";
		$pairs = new ilMatchingPairWizardInputGUI($this->lng->txt('matching_pairs'), 'pairs');
		$pairs->setRequired(true);
		$pairs->setTerms($this->object->getTerms());
		$pairs->setDefinitions($this->object->getDefinitions());
		$pairs->setPairs($this->object->getMatchingPairs());
		$form->addItem($pairs);

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

	function outQuestionForTest($formaction, $active_id, $pass = NULL, $is_postponed = FALSE, $user_post_solution = FALSE)
	{
		$test_output = $this->getTestOutput($active_id, $pass, $is_postponed, $user_post_solution); 
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
		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_matching_output_solution.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html",TRUE, TRUE, "Modules/TestQuestionPool");
		
		$solutions = array();
		if (($active_id > 0) && (!$show_correct_solution))
		{
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			$solutions =& $this->object->getSolutionValues($active_id, $pass);
			$solution_script .= "";
		}
		else
		{
			foreach ($this->object->getMatchingPairs() as $pair)
			{
				array_push($solutions, array("value1" => $pair->term->identifier, "value2" => $pair->definition->identifier));
			}
		}

		foreach ($solutions as $solution)
		{
			$definition = $this->object->getDefinitionWithIdentifier($solution['value2']);
			$term = $this->object->getTermWithIdentifier($solution['value1']);

			if (is_object($definition))
			{
				if (strlen($definition->picture))
				{
					$template->setCurrentBlock('definition_image');
					$template->setVariable('ANSWER_IMAGE_URL', $this->object->getImagePathWeb() . $this->object->getThumbPrefix() . $definition->picture);
					$template->setVariable('ANSWER_IMAGE_ALT', (strlen($definition->text)) ? ilUtil::prepareFormOutput($definition->text) : ilUtil::prepareFormOutput($definition->picture));
					$template->setVariable('ANSWER_IMAGE_TITLE', (strlen($definition->text)) ? ilUtil::prepareFormOutput($definition->text) : ilUtil::prepareFormOutput($definition->picture));
					$template->setVariable('URL_PREVIEW', $this->object->getImagePathWeb() . $definition->picture);
					$template->setVariable("TEXT_PREVIEW", $this->lng->txt('preview'));
					$template->setVariable("IMG_PREVIEW", ilUtil::getImagePath('enlarge.gif'));
					$template->setVariable("TEXT_DEFINITION", (strlen($definition->text)) ? $this->lng->txt('definition') . ' ' . ($i+1) . ': ' . ilUtil::prepareFormOutput($definition->text) : $this->lng->txt('definition') . ' ' . ($i+1));
					$template->parseCurrentBlock();
				}
				else
				{
					$template->setCurrentBlock('definition_text');
					$template->setVariable("DEFINITION", ilUtil::prepareFormOutput($definition->text));
					$template->parseCurrentBlock();
				}
			}
			if (is_object($term))
			{
				if (strlen($term->picture))
				{
					$template->setCurrentBlock('term_image');
					$template->setVariable('ANSWER_IMAGE_URL', $this->object->getImagePathWeb() . $this->object->getThumbPrefix() . $term->picture);
					$template->setVariable('ANSWER_IMAGE_ALT', (strlen($term->text)) ? ilUtil::prepareFormOutput($term->text) : ilUtil::prepareFormOutput($term->picture));
					$template->setVariable('ANSWER_IMAGE_TITLE', (strlen($term->text)) ? ilUtil::prepareFormOutput($term->text) : ilUtil::prepareFormOutput($term->picture));
					$template->setVariable('URL_PREVIEW', $this->object->getImagePathWeb() . $term->picture);
					$template->setVariable("TEXT_PREVIEW", $this->lng->txt('preview'));
					$template->setVariable("TEXT_TERM", (strlen($term->text)) ? $this->lng->txt('term') . ' ' . ($i+1) . ': ' . ilUtil::prepareFormOutput($term->text) : $this->lng->txt('term') . ' ' . ($i+1));
					$template->setVariable("IMG_PREVIEW", ilUtil::getImagePath('enlarge.gif'));
					$template->parseCurrentBlock();
				}
				else
				{
					$template->setCurrentBlock('term_text');
					$template->setVariable("TERM", ilUtil::prepareFormOutput($term->text));
					$template->parseCurrentBlock();
				}
			}
			if (($active_id > 0) && (!$show_correct_solution))
			{
				if ($graphicalOutput)
				{
					// output of ok/not ok icons for user entered solutions
					$ok = FALSE;
					foreach ($this->object->getMatchingPairs() as $pair)
					{
						if (is_object($term)) if (($pair->definition->identifier == $definition->identifier) && ($pair->term->identifier == $term->identifier)) $ok = true;
					}
					if ($ok)
					{
						$template->setCurrentBlock("icon_ok");
						$template->setVariable("ICON_OK", ilUtil::getImagePath("icon_ok.gif"));
						$template->setVariable("TEXT_OK", $this->lng->txt("answer_is_right"));
						$template->parseCurrentBlock();
					}
					else
					{
						$template->setCurrentBlock("icon_ok");
						$template->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_not_ok.gif"));
						$template->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_wrong"));
						$template->parseCurrentBlock();
					}
				}
			}
			$template->setCurrentBlock("row");
			if ($this->object->getEstimatedElementHeight() > 0)
			{
				$template->setVariable("ELEMENT_HEIGHT", " style=\"height: " . $this->object->getEstimatedElementHeight() . "px;\"");
			}
			$template->setVariable("TEXT_MATCHES", $this->lng->txt("matches"));
			$template->parseCurrentBlock();
		}

		if ($result_output)
		{
			$points = 0.0;
			foreach ($this->object->getMatchingPairs() as $pair)
			{
				foreach ($solutions as $solution)
				{
					if (($solution['value2'] == $pair->definition->identifier) && ($solution['value1'] == $pair->term->identifier))
					{
						$points += $pair->points;
					}
				}
				$resulttext = ($points == 1) ? "(%s " . $this->lng->txt("point") . ")" : "(%s " . $this->lng->txt("points") . ")"; 
				$template->setCurrentBlock("result_output");
				$template->setVariable("RESULT_OUTPUT", sprintf($resulttext, $points));
				$template->parseCurrentBlock();
			}
		}
		
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
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
		$template = new ilTemplate("tpl.il_as_qpl_matching_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		
		// shuffle output
		$terms = $this->object->getTerms();
		$definitions = $this->object->getDefinitions();
		switch ($this->object->getShuffle())
		{
			case 1:
				$terms = $this->object->pcArrayShuffle($terms);
				$definitions = $this->object->pcArrayShuffle($definitions);
				break;
			case 2:
				$terms = $this->object->pcArrayShuffle($terms);
				break;
			case 3:
				$definitions = $this->object->pcArrayShuffle($definitions);
				break;
		}
		$maxcount = max(count($terms), count($definitions));
		for ($i = 0; $i < count($definitions); $i++)
		{
			$definition = $definitions[$i];
			if (is_object($definition))
			{
				if (strlen($definition->picture))
				{
					$template->setCurrentBlock('definition_image');
					$template->setVariable('ANSWER_IMAGE_URL', $this->object->getImagePathWeb() . $this->object->getThumbPrefix() . $definition->picture);
					$template->setVariable('ANSWER_IMAGE_ALT', (strlen($definition->text)) ? ilUtil::prepareFormOutput($definition->text) : ilUtil::prepareFormOutput($definition->picture));
					$template->setVariable('ANSWER_IMAGE_TITLE', (strlen($definition->text)) ? ilUtil::prepareFormOutput($definition->text) : ilUtil::prepareFormOutput($definition->picture));
					$template->setVariable('URL_PREVIEW', $this->object->getImagePathWeb() . $definition->picture);
					$template->setVariable("TEXT_PREVIEW", $this->lng->txt('preview'));
					$template->setVariable("IMG_PREVIEW", ilUtil::getImagePath('enlarge.gif'));
					$template->setVariable("TEXT_DEFINITION", (strlen($definition->text)) ? $this->lng->txt('definition') . ' ' . ($i+1) . ': ' . ilUtil::prepareFormOutput($definition->text) : $this->lng->txt('definition') . ' ' . ($i+1));
					$template->parseCurrentBlock();
				}
				else
				{
					$template->setCurrentBlock('definition_text');
					$template->setVariable("DEFINITION", ilUtil::prepareFormOutput($definition->text));
					$template->parseCurrentBlock();
				}
			}

			$template->setCurrentBlock('option');
			$template->setVariable("VALUE_OPTION", 0);
			$template->setVariable("TEXT_OPTION", ilUtil::prepareFormOutput($this->lng->txt('please_select')));
			$template->parseCurrentBlock();
			$j = 1;
			foreach ($terms as $term)
			{
				$template->setCurrentBlock('option');
				$template->setVariable("VALUE_OPTION", $term->identifier);
				$template->setVariable("TEXT_OPTION", (strlen($term->text)) ? $this->lng->txt('term') . ' ' . ($j) . ': ' . ilUtil::prepareFormOutput($term->text) : $this->lng->txt('term') . ' ' . ($j));
				$template->parseCurrentBlock();
				$j++;
			}
			
			$template->setCurrentBlock('row');
			$template->setVariable("TEXT_MATCHES", $this->lng->txt("matches"));
			if ($this->object->getEstimatedElementHeight() > 0)
			{
				$template->setVariable("ELEMENT_HEIGHT", " style=\"height: " . $this->object->getEstimatedElementHeight() . "px;\"");
			}
			$template->setVariable("QUESTION_ID", $this->object->getId());
			$template->setVariable("DEFINITION_ID", $definition->identifier);
			$template->parseCurrentBlock();
		}

		foreach ($terms as $term)
		{
			if (strlen($term->picture))
			{
				$template->setCurrentBlock('term_image');
				$template->setVariable('ANSWER_IMAGE_URL', $this->object->getImagePathWeb() . $this->object->getThumbPrefix() . $term->picture);
				$template->setVariable('ANSWER_IMAGE_ALT', (strlen($term->text)) ? ilUtil::prepareFormOutput($term->text) : ilUtil::prepareFormOutput($term->picture));
				$template->setVariable('ANSWER_IMAGE_TITLE', (strlen($term->text)) ? ilUtil::prepareFormOutput($term->text) : ilUtil::prepareFormOutput($term->picture));
				$template->setVariable('URL_PREVIEW', $this->object->getImagePathWeb() . $term->picture);
				$template->setVariable("TEXT_PREVIEW", $this->lng->txt('preview'));
				$template->setVariable("TEXT_TERM", (strlen($term->text)) ? $this->lng->txt('term') . ' ' . ($i+1) . ': ' . ilUtil::prepareFormOutput($term->text) : $this->lng->txt('term') . ' ' . ($i+1));
				$template->setVariable("IMG_PREVIEW", ilUtil::getImagePath('enlarge.gif'));
				$template->parseCurrentBlock();
			}
			else
			{
				$template->setCurrentBlock('term_text');
				$template->setVariable("TERM", ilUtil::prepareFormOutput($term->text));
				$template->parseCurrentBlock();
			}
			$template->touchBlock('terms');
		}

		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$template->setVariable("TEXT_TERMS", ilUtil::prepareFormOutput($this->lng->txt('available_terms')));
		$template->setVariable('TEXT_SELECTION', ilUtil::prepareFormOutput($this->lng->txt('selection')));
		$questionoutput = $template->get();
		if (!$show_question_only)
		{
			// get page object output
			$questionoutput = $this->getILIASPage($questionoutput);
		}
		return $questionoutput;
	}

	function getTestOutputJS($active_id, $pass = NULL, $is_postponed = FALSE, $user_post_solution = FALSE)
	{
		
	}

	function getTestOutput($active_id, $pass = NULL, $is_postponed = FALSE, $user_post_solution = FALSE)
	{
		if ($this->object->getOutputType() == OUTPUT_JAVASCRIPT)
		{
			return $this->getTestOutputJS($active_id, $pass, $is_postponed, $user_post_solution);
		}
		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_matching_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		
		if ($active_id)
		{
			$solutions = NULL;
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			if (!ilObjTest::_getUsePreviousAnswers($active_id, true))
			{
				if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
			}
			if (is_array($user_post_solution)) 
			{ 
				$solutions = array();
				foreach ($user_post_solution['matching'][$this->object->getId()] as $definition => $term)
				{
					array_push($solutions, array("value1" => $term, "value2" => $definition));
				}
			}
			else
			{ 
				$solutions =& $this->object->getSolutionValues($active_id, $pass);
			}
		}

		
		// shuffle output
		$terms = $this->object->getTerms();
		$definitions = $this->object->getDefinitions();
		switch ($this->object->getShuffle())
		{
			case 1:
				$terms = $this->object->pcArrayShuffle($terms);
				$definitions = $this->object->pcArrayShuffle($definitions);
				break;
			case 2:
				$terms = $this->object->pcArrayShuffle($terms);
				break;
			case 3:
				$definitions = $this->object->pcArrayShuffle($definitions);
				break;
		}
		$maxcount = max(count($terms), count($definitions));
		for ($i = 0; $i < count($definitions); $i++)
		{
			$definition = $definitions[$i];
			if (is_object($definition))
			{
				if (strlen($definition->picture))
				{
					$template->setCurrentBlock('definition_image');
					$template->setVariable('ANSWER_IMAGE_URL', $this->object->getImagePathWeb() . $this->object->getThumbPrefix() . $definition->picture);
					$template->setVariable('ANSWER_IMAGE_ALT', (strlen($definition->text)) ? ilUtil::prepareFormOutput($definition->text) : ilUtil::prepareFormOutput($definition->picture));
					$template->setVariable('ANSWER_IMAGE_TITLE', (strlen($definition->text)) ? ilUtil::prepareFormOutput($definition->text) : ilUtil::prepareFormOutput($definition->picture));
					$template->setVariable('URL_PREVIEW', $this->object->getImagePathWeb() . $definition->picture);
					$template->setVariable("TEXT_PREVIEW", $this->lng->txt('preview'));
					$template->setVariable("IMG_PREVIEW", ilUtil::getImagePath('enlarge.gif'));
					$template->setVariable("TEXT_DEFINITION", (strlen($definition->text)) ? $this->lng->txt('definition') . ' ' . ($i+1) . ': ' . ilUtil::prepareFormOutput($definition->text) : $this->lng->txt('definition') . ' ' . ($i+1));
					$template->parseCurrentBlock();
				}
				else
				{
					$template->setCurrentBlock('definition_text');
					$template->setVariable("DEFINITION", ilUtil::prepareFormOutput($definition->text));
					$template->parseCurrentBlock();
				}
			}

			$template->setCurrentBlock('option');
			$template->setVariable("VALUE_OPTION", 0);
			$template->setVariable("TEXT_OPTION", ilUtil::prepareFormOutput($this->lng->txt('please_select')));
			$template->parseCurrentBlock();
			$j = 1;
			foreach ($terms as $term)
			{
				$template->setCurrentBlock('option');
				$template->setVariable("VALUE_OPTION", $term->identifier);
				$template->setVariable("TEXT_OPTION", (strlen($term->text)) ? $this->lng->txt('term') . ' ' . ($j) . ': ' . ilUtil::prepareFormOutput($term->text) : $this->lng->txt('term') . ' ' . ($j));
				foreach ($solutions as $solution)
				{
					if ($solution["value1"] == $term->identifier && $solution["value2"] == $definition->identifier)
					{
						$template->setVariable("SELECTED_OPTION", " selected=\"selected\"");
					}
				}
				$template->parseCurrentBlock();
				$j++;
			}
			
			$template->setCurrentBlock('row');
			$template->setVariable("TEXT_MATCHES", $this->lng->txt("matches"));
			if ($this->object->getEstimatedElementHeight() > 0)
			{
				$template->setVariable("ELEMENT_HEIGHT", " style=\"height: " . $this->object->getEstimatedElementHeight() . "px;\"");
			}
			$template->setVariable("QUESTION_ID", $this->object->getId());
			$template->setVariable("DEFINITION_ID", $definition->identifier);
			$template->parseCurrentBlock();
		}

		foreach ($terms as $term)
		{
			if (strlen($term->picture))
			{
				$template->setCurrentBlock('term_image');
				$template->setVariable('ANSWER_IMAGE_URL', $this->object->getImagePathWeb() . $this->object->getThumbPrefix() . $term->picture);
				$template->setVariable('ANSWER_IMAGE_ALT', (strlen($term->text)) ? ilUtil::prepareFormOutput($term->text) : ilUtil::prepareFormOutput($term->picture));
				$template->setVariable('ANSWER_IMAGE_TITLE', (strlen($term->text)) ? ilUtil::prepareFormOutput($term->text) : ilUtil::prepareFormOutput($term->picture));
				$template->setVariable('URL_PREVIEW', $this->object->getImagePathWeb() . $term->picture);
				$template->setVariable("TEXT_PREVIEW", $this->lng->txt('preview'));
				$template->setVariable("TEXT_TERM", (strlen($term->text)) ? $this->lng->txt('term') . ' ' . ($i+1) . ': ' . ilUtil::prepareFormOutput($term->text) : $this->lng->txt('term') . ' ' . ($i+1));
				$template->setVariable("IMG_PREVIEW", ilUtil::getImagePath('enlarge.gif'));
				$template->parseCurrentBlock();
			}
			else
			{
				$template->setCurrentBlock('term_text');
				$template->setVariable("TERM", ilUtil::prepareFormOutput($term->text));
				$template->parseCurrentBlock();
			}
			$template->touchBlock('terms');
		}

		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$template->setVariable("TEXT_TERMS", ilUtil::prepareFormOutput($this->lng->txt('available_terms')));
		$template->setVariable('TEXT_SELECTION', ilUtil::prepareFormOutput($this->lng->txt('selection')));

		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$questionoutput = $template->get();
		$pageoutput = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput);
		return $pageoutput;

return;
		$allterms = $this->object->getTerms();
		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_matching_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		
		// shuffle output
		$keys = array_keys($this->object->matchingpairs);
		$keys2 = array_keys($this->object->getTerms());
		if (is_array($user_post_solution))
		{
			$keys = $_SESSION["matching_keys"];
			$keys2 = $_SESSION["matching_keys2"];
		}
		else
		{
			if ($this->object->getShuffle())
			{
				if (($this->object->getShuffle() == 3) || ($this->object->getShuffle() == 1))
					$keys = $this->object->pcArrayShuffle(array_keys($this->object->matchingpairs));
				if (($this->object->getShuffle() == 2) || ($this->object->getShuffle() == 1))
					$keys2 = $this->object->pcArrayShuffle($keys2);
			}
		}
		$_SESSION["matching_keys"] = $keys;
		$_SESSION["matching_keys2"] = $keys2;

		if ($active_id)
		{
			$solutions = NULL;
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			if (!ilObjTest::_getUsePreviousAnswers($active_id, true))
			{
				if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
			}
			if (is_array($user_post_solution)) 
			{ 
				$solutions = array();
				foreach ($user_post_solution as $key => $value)
				{
					if (preg_match("/sel_matching_(\d+)/", $key, $matches))
					{
						array_push($solutions, array("value1" => $value, "value2" => $matches[1]));
					}
				}
			}
			else
			{ 
				$solutions =& $this->object->getSolutionValues($active_id, $pass);
			}
			foreach ($solutions as $idx => $solution_value)
			{
				if ($this->object->getOutputType() == OUTPUT_JAVASCRIPT)
				{
					if (($solution_value["value2"] > -1) && ($solution_value["value1"] > -1))
					{
						$template->setCurrentBlock("restoreposition");
						$template->setVariable("TERM_ID", $solution_value["value1"]);
						$template->setVariable("PICTURE_DEFINITION_ID", $solution_value["value2"]);
						$template->parseCurrentBlock();
					}
				}
			}
		}
		if ($this->object->getOutputType() == OUTPUT_JAVASCRIPT)
		{
			include_once "./Services/YUI/classes/class.ilYuiUtil.php";
			ilYuiUtil::initDragDrop();
			
			// create pictures/definitions
			$arrayindex = 0;
			foreach ($keys as $idx)
			{
				$answer = $this->object->matchingpairs[$idx];
				if ($this->object->get_matching_type() == MT_TERMS_PICTURES)
				{
					$template->setCurrentBlock("js_match_picture");
					$template->setVariable("DEFINITION_ID", $answer->getPictureId());
					$template->setVariable("IMAGE_HREF", $this->object->getImagePathWeb() . $answer->getPicture());
					$thumbweb = $this->object->getImagePathWeb() . $this->object->getThumbPrefix() . $answer->getPicture();
					$thumb = $this->object->getImagePath() . $this->object->getThumbPrefix() . $answer->getPicture();
					if (!@file_exists($thumb)) $this->object->rebuildThumbnails();
					$template->setVariable("THUMBNAIL_HREF", $thumbweb);
					$template->setVariable("THUMB_ALT", $this->lng->txt("image"));
					$template->setVariable("THUMB_TITLE", $this->lng->txt("image"));
					$template->parseCurrentBlock();
				}
				else
				{
					$template->setCurrentBlock("js_match_definition");
					$template->setVariable("DEFINITION", $this->object->prepareTextareaOutput($answer->getDefinition(), TRUE));
					$template->parseCurrentBlock();
				}
				$template->setCurrentBlock("droparea");
				$template->setVariable("ID_DROPAREA", $answer->getDefinitionId());
				$template->setVariable("ID_DROPAREA", $answer->getDefinitionId());
				if ($this->object->getElementHeight() >= 20)
				{
					$template->setVariable("ELEMENT_HEIGHT", " style=\"height: " . $this->object->getElementHeight() . "px;\"");
				}
				$template->parseCurrentBlock();
				$template->setCurrentBlock("init_dropareas");
				$template->setVariable("COUNTER", $arrayindex++);
				$template->setVariable("ID_DROPAREA", $answer->getDefinitionId());
				$template->parseCurrentBlock();
			}

			// create terms
			$arrayindex = 0;
			foreach ($keys2 as $termid)
			{
				$template->setCurrentBlock("draggable");
				$template->setVariable("ID_DRAGGABLE", $termid);
				$template->setVariable("VALUE_DRAGGABLE", $this->object->prepareTextareaOutput($this->object->getTermWithId($termid)));
				if ($this->object->getElementHeight() >= 20)
				{
					$template->setVariable("ELEMENT_HEIGHT", " style=\"height: " . $this->object->getElementHeight() . "px;\"");
				}
				$template->parseCurrentBlock();
				$template->setCurrentBlock("init_draggables");
				$template->setVariable("COUNTER", $arrayindex++);
				$template->setVariable("ID_DRAGGABLE", $termid);
				$template->parseCurrentBlock();
			}
			
			$template->setVariable("RESET_BUTTON", $this->lng->txt("reset_terms"));
			
			$this->tpl->setVariable("LOCATION_ADDITIONAL_STYLESHEET", ilUtil::getStyleSheetLocation("output", "test_javascript.css", "Modules/TestQuestionPool"));
		}
		else
		{
			foreach ($keys as $idx)
			{
				$answer = $this->object->matchingpairs[$idx];
				foreach ($keys2 as $comboidx)
				{
					$template->setCurrentBlock("matching_selection");
					$template->setVariable("VALUE_SELECTION", $comboidx);
					$template->setVariable("TEXT_SELECTION", ilUtil::prepareFormOutput($allterms[$comboidx]));
					foreach ($solutions as $solution)
					{
						if ((strcmp($solution["value1"], $comboidx) == 0) && ($answer->getDefinitionId() == $solution["value2"]))
						{
							$template->setVariable("SELECTED_SELECTION", " selected=\"selected\"");
						}
					}
					$template->parseCurrentBlock();
				}
				if ($this->object->get_matching_type() == MT_TERMS_PICTURES)
				{
					$template->setCurrentBlock("standard_matching_pictures");
					$template->setVariable("DEFINITION_ID", $answer->getPictureId());
					$template->setVariable("IMAGE_HREF", $this->object->getImagePathWeb() . $answer->getPicture());
					$thumbweb = $this->object->getImagePathWeb() . $this->object->getThumbPrefix() . $answer->getPicture();
					$thumb = $this->object->getImagePath() . $this->object->getThumbPrefix() . $answer->getPicture();
					if (!@file_exists($thumb)) $this->object->rebuildThumbnails();
					$template->setVariable("THUMBNAIL_HREF", $thumbweb);
					$template->setVariable("THUMB_ALT", $this->lng->txt("image"));
					$template->setVariable("THUMB_TITLE", $this->lng->txt("image"));
					$template->parseCurrentBlock();
				}
				else
				{
					$template->setCurrentBlock("standard_matching_terms");
					$template->setVariable("DEFINITION", $this->object->prepareTextareaOutput($answer->getDefinition(), TRUE));
					$template->parseCurrentBlock();
				}

				$template->setCurrentBlock("standard_matching_row");
				if ($this->object->getElementHeight() >= 20)
				{
					$template->setVariable("ELEMENT_HEIGHT", " style=\"height: " . $this->object->getElementHeight() . "px;\"");
				}
				$template->setVariable("MATCHES", $this->lng->txt("matches"));
				$template->setVariable("DEFINITION_ID", $answer->getDefinitionId());
				$template->setVariable("PLEASE_SELECT", $this->lng->txt("please_select"));
				$template->parseCurrentBlock();
			}
		}
		
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$questionoutput = $template->get();
		$pageoutput = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput);
		return $pageoutput;
	}

	/**
	* check input fields
	*/
	function checkInput()
	{
		if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["question"]))
		{
			return false;
		}
		return true;
	}


	/**
	* Saves the feedback for a single choice question
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
			// edit question properties
			$ilTabs->addTarget("edit_properties",
				$url,
				array("editQuestion", "save", "saveEdit", "removeimageterms", "uploadterms", "removeimagedefinitions", "uploaddefinitions",
					"addpairs", "removepairs", "addterms", "removeterms", "adddefinitions", "removedefinitions"),
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
