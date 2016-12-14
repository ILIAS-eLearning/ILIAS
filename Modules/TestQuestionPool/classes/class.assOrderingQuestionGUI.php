<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilGuiQuestionScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilGuiAnswerScoringAdjustable.php';
require_once './Modules/Test/classes/inc.AssessmentConstants.php';

/**
 * Ordering question GUI representation
 *
 * The assOrderingQuestionGUI class encapsulates the GUI representation for ordering questions.
 *
 * @author	Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author	Björn Heyser <bheyser@databay.de>
 * @author	Maximilian Becker <mbecker@databay.de>
 *         
 * @version	$Id$
 *          
 * @ingroup ModulesTestQuestionPool
 * @ilCtrl_Calls assOrderingQuestionGUI: ilFormPropertyDispatchGUI
 */
class assOrderingQuestionGUI extends assQuestionGUI implements ilGuiQuestionScoringAdjustable, ilGuiAnswerScoringAdjustable
{
	/**
	 * @var assOrderingQuestion
	 */
	public $object;
	
	private $uploadAlert = null;

	public $old_ordering_depth = array();
	public $leveled_ordering = array();

	/**
	 * @var bool
	 */
	private $clearAnswersOnWritingPostDataEnabled;
	
	/**
	 * assOrderingQuestionGUI constructor
	 *
	 * The constructor takes possible arguments an creates an instance of the assOrderingQuestionGUI object.
	 *
	 * @param integer $id The database id of a ordering question object
	 *                    
	 * @return assOrderingQuestionGUI
	 */
	public function __construct($id = -1)
	{
		parent::__construct();
		include_once "./Modules/TestQuestionPool/classes/class.assOrderingQuestion.php";
		$this->object = new assOrderingQuestion();
		if ($id >= 0)
		{
			$this->object->loadFromDb($id);
		}
		$this->object->setOutputType(OUTPUT_JAVASCRIPT); 
		
		$this->clearAnswersOnWritingPostDataEnabled = false;
	}

	/**
	 * @param boolean $clearAnswersOnWritingPostDataEnabled
	 */
	public function setClearAnswersOnWritingPostDataEnabled($clearAnswersOnWritingPostDataEnabled)
	{
		$this->clearAnswersOnWritingPostDataEnabled = $clearAnswersOnWritingPostDataEnabled;
	}

	/**
	 * @return boolean
	 */
	public function isClearAnswersOnWritingPostDataEnabled()
	{
		return $this->clearAnswersOnWritingPostDataEnabled;
	}

	public function changeToPictures()
	{
		if($this->object->getOrderingType() != OQ_NESTED_PICTURES && $this->object->getOrderingType() != OQ_PICTURES)
		{
			$this->setClearAnswersOnWritingPostDataEnabled(true);
		}
		
		$this->writePostData(true);
		$this->object->setOrderingType(OQ_PICTURES);
		$this->object->saveToDb();

		$this->editQuestion();
	}

	public function changeToText()
	{
		if($this->object->getOrderingType() != OQ_NESTED_TERMS && $this->object->getOrderingType() != OQ_TERMS)
		{
			$this->setClearAnswersOnWritingPostDataEnabled(true);
		}
		
		$this->writePostData(true);
		$this->object->setOrderingType(OQ_TERMS);
		$this->object->saveToDb();

		$this->editQuestion();
	}

	public function orderNestedTerms()
	{
		$this->writePostData(true);
		$this->object->setOrderingType(OQ_NESTED_TERMS);
		$this->object->saveToDb();

		$this->editQuestion();
	}

	public function orderNestedPictures()
	{
		$this->writePostData(true);
		$this->object->setOrderingType(OQ_NESTED_PICTURES);
		$this->object->saveToDb();

		$this->editQuestion();
	}
	
	public function removeimageanswers()
	{
		$this->writePostData(true);
		
		$randomIdentifier = key($_POST['cmd']['removeimageanswers']);
		$orderingElement = $this->object->getOrderingElementList()->getElementByRandomIdentifier($randomIdentifier);
		$this->object->dropImageFile($orderingElement);
	
		$this->editQuestion();
	}

	/**
	 * @return \ilImageWizardInputGUI
	 */
	private function getAnswerImageFileUploadWizardFormProperty()
	{
		require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssOrderingImagesInputGUI.php';
		$answers = new ilAssOrderingImagesInputGUI($this->lng->txt("answers"), "answers");
		$answers->setRequired(TRUE);
		$answers->setQuestionObject($this->object);
		$answers->setInfo($this->lng->txt('ordering_answer_sequence_info'));
		$answers->setAllowMove(TRUE);
		$answers->setValues($this->object->getOrderingElementList()->getRandomIdentifierIndexedElements());
		return $answers;
	}

	public function uploadanswers()
	{
		$this->lng->loadLanguageModule('form');

		$inp = $this->getAnswerImageFileUploadWizardFormProperty();

		if( !$inp->checkInput() )
		{
			$this->uploadAlert = $inp->getAlert();
			ilUtil::sendFailure($inp->getAlert());
		}

		$this->writePostData(true);
		$this->editQuestion();
	}

	public function writeQuestionSpecificPostData(ilPropertyFormGUI $form)
	{
		$this->object->setThumbGeometry( $_POST["thumb_geometry"] );
		$this->object->setElementHeight( $_POST["element_height"] );
		//$this->object->setOrderingType( $_POST["ordering_type"] );
		$this->object->setPoints($_POST["points"]);
	}

	public function writeAnswerSpecificPostData(ilPropertyFormGUI $form)
	{
		$currentElementList = $this->object->getOrderingElementList();
		$replacementElementList = new ilAssOrderingElementList();
		$replacementElementList->setQuestionId($this->object->getId());
		
		foreach((array)$_POST["answers"] as $submittedElement)
		{
			/* @var ilAssOrderingElement $submittedElement */
			
			if( $this->object->hasOrderingTypeUploadSupport() )
			{
				// new file
				if( strlen($submittedElement->getUploadImageFile()) )
				{
					// check suffix						
					$suffix = strtolower(array_pop(explode(".", $submittedElement->getUploadImageName())));
					if( in_array($suffix, array("jpg", "jpeg", "png", "gif")) )
					{
						// hash the original filename
						
						$submittedElement->setUploadImageName( $this->object->buildHashedImageFilename(
							$submittedElement->getUploadImageName()
						));
						
						// move uploaded
						
						$wasImageFileStored = $this->object->storeImageFile(
							$submittedElement->getUploadImageFile(), $submittedElement->getUploadImageName()
						);
						
						if( $wasImageFileStored )
						{
							if( $this->object->isImageFileStored( $submittedElement->getContent() ) )
							{
								$this->object->dropImageFile( $submittedElement->getContent() );
							}

							$submittedElement->setContent( $submittedElement->getUploadImageName() );
						}
					}
				}
			}
			
			if( $currentElementList->elementExistByRandomIdentifier($submittedElement->getRandomIdentifier()) )
			{
				$storedElement = $currentElementList->getElementByRandomIdentifier(
					$submittedElement->getRandomIdentifier()
				);
				
				$submittedElement->setSolutionIdentifier($storedElement->getSolutionIdentifier());
				
				if( !$this->object->isOrderingTypeNested() )
				{
					$submittedElement->setIndentation($storedElement->getIndentation());
				}
				
				if( $this->object->isImageReplaced($submittedElement, $storedElement) )
				{
					$this->object->dropImageFile($storedElement);
				}
			}
			
			$replacementElementList->addElement($submittedElement);
		}
		
		if( $this->isClearAnswersOnWritingPostDataEnabled() )
		{
			$replacementElementList->clearElementContents();
		}
		
		if( $this->object->hasOrderingTypeUploadSupport() )
		{
			$obsoleteElementList = $currentElementList->getDifferenceElementList($replacementElementList);
			
			foreach($obsoleteElementList as $obsoleteElement)
			{
				$this->object->dropImageFile($obsoleteElement->getContent());
			}
		}
		
		$this->object->setOrderingElementList($replacementElementList);
	}

	public function populateAnswerSpecificFormPart(ilPropertyFormGUI $form)
	{
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->lng->txt('oq_header_ordering_elements'));
		$form->addItem($header);

		if( $this->object->getOrderingType() == OQ_PICTURES )
		{
			$answerImageUpload = $this->getAnswerImageFileUploadWizardFormProperty();
			if ($this->uploadAlert !== null)
			{
				$answerImageUpload->setAlert( $this->uploadAlert );
			}
			$form->addItem( $answerImageUpload );
		}
		elseif( $this->object->getOrderingType() == OQ_TERMS )
		{
			require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssOrderingTextsInputGUI.php';
			$answers = new ilAssOrderingTextsInputGUI($this->lng->txt( "answers" ), "answers");
			$answers->setValues($this->object->getOrderingElementList()->getElements());
			$answers->setAllowMove( TRUE );
			$answers->setRequired( TRUE );
			
			$answers->setInfo( $this->lng->txt( 'ordering_answer_sequence_info' ) );
			$form->addItem( $answers );
		}
		else // OQ_NESTED_TERMS, OQ_NESTED_PICTURES
		{
			require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssNestedOrderingElementsInputGUI.php';
			$answers = new ilAssNestedOrderingElementsInputGUI($this->lng->txt( "answers" ), "answers");
			$answers->setInfo( $this->lng->txt( 'ordering_answer_sequence_info' ) );
			$answers->setStylingDisabled($this->isPdfOutputMode());
			$answers->setElementImagePath( $this->object->getImagePathWeb() );
			$answers->setThumbPrefix($this->object->getThumbPrefix());
			$answers->setOrderingType( $this->object->getOrderingType() );
			$answers->setValues( $this->object->getOrderingElementList()->getElements() );
			
			$form->addItem( $answers );
		}

		return $form;
	}

	public function populateQuestionSpecificFormPart(\ilPropertyFormGUI $form)
	{
		$orderingtype = $this->object->getOrderingType();

		// Edit mode

		//$hidden = new ilHiddenInputGUI("ordering_type");
		//$hidden->setValue( $orderingtype );
		//$form->addItem( $hidden );

		if (!$this->object->getSelfAssessmentEditingMode())
		{
			$element_height = new ilNumberInputGUI($this->lng->txt( "element_height" ), "element_height");
			$element_height->setValue( $this->object->getElementHeight() );
			$element_height->setRequired( false );
			$element_height->setMaxLength( 6 );
			$element_height->setMinValue( 20 );
			$element_height->setSize( 6 );
			$element_height->setInfo( $this->lng->txt( "element_height_info" ) );
			$form->addItem( $element_height );
		}

		if ($orderingtype == OQ_PICTURES)
		{
			$geometry = new ilNumberInputGUI($this->lng->txt( "thumb_geometry" ), "thumb_geometry");
			$geometry->setValue( $this->object->getThumbGeometry() );
			$geometry->setRequired( true );
			$geometry->setMaxLength( 6 );
			$geometry->setMinValue( 20 );
			$geometry->setSize( 6 );
			$geometry->setInfo( $this->lng->txt( "thumb_geometry_info" ) );
			$form->addItem( $geometry );
		}

		// points
		$points = new ilNumberInputGUI($this->lng->txt( "points" ), "points");
		$points->allowDecimals( true );
		$points->setValue( $this->object->getPoints() );
		$points->setRequired( TRUE );
		$points->setSize( 3 );
		$points->setMinValue( 0 );
		$points->setMinvalueShouldBeGreater( true );
		$form->addItem( $points );
		
		return $form;
	}

	private function isUploadAnswersCommand()
	{
		return $this->ctrl->getCmd() == 'uploadanswers';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function writePostData($forceSaving = false)
	{
		$savingAllowed = true; // assume saving allowed first
		
		if( !$forceSaving )
		{
			// this case seems to be a regular save call,
			// so we consider the return as permission to save
			
			$savingAllowed = !(
				$hasErrors = $this->editQuestion($avoidOutput = true)
			);
		}
		elseif( !$this->isSaveCommand() )
		{
			// in this case $this->editQuestion(true) has not executed any validation
			
			$this->editQuestion($avoidOutput = true); // init {this->editForm}
			$this->editForm->setValuesByPost(); // manipulation and distribution of values 
			$this->editForm->checkInput(); // manipulations regular style input propeties
		}
		
		if ($savingAllowed)
		{
			require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
			$this->writeQuestionGenericPostData();
			$this->writeQuestionSpecificPostData(new ilPropertyFormGUI());
			$this->writeAnswerSpecificPostData(new ilPropertyFormGUI());
			$this->saveTaxonomyAssignments();
			
			return 0; // return 0 = all fine, was saved either forced or validated
		}
		
		return 1; // return 1 = something went wrong, no saving happened
	}
	
	/**
	 * Creates an output of the edit form for the question
	 */
	public function editQuestion($checkonly = FALSE)
	{
		$save = $this->isSaveCommand();
		$this->getQuestionTemplate();

		$orderingtype = $this->object->getOrderingType();

		require_once "./Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$this->editForm = $form;

		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->outQuestionType());
		$form->setMultipart(($orderingtype == OQ_PICTURES) ? TRUE : FALSE);
		$form->setTableWidth("100%");
		$form->setId("ordering");
		// title, author, description, question, working time (assessment mode)
		$this->addBasicQuestionFormProperties( $form );
		$this->populateQuestionSpecificFormPart($form );
		$this->populateAnswerSpecificFormPart( $form );

		if (true || !$this->object->getSelfAssessmentEditingMode())
		{
			$this->populateCommandButtons($form);
		}

		$this->populateTaxonomyFormSection($form);
		$this->addQuestionFormCommandButtons($form);
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

	private function populateCommandButtons(ilPropertyFormGUI $form)
	{
		switch( $this->object->getOrderingType() )
		{
			case OQ_TERMS:

				$form->addCommandButton("changeToPictures", $this->lng->txt("oq_btn_use_order_pictures"));
				$form->addCommandButton("orderNestedTerms", $this->lng->txt("oq_btn_nest_terms"));
				break;

			case OQ_PICTURES:

				$form->addCommandButton("changeToText", $this->lng->txt("oq_btn_use_order_terms"));
				$form->addCommandButton("orderNestedPictures", $this->lng->txt("oq_btn_nest_pictures"));
				break;

			case OQ_NESTED_TERMS:

				$form->addCommandButton("changeToPictures", $this->lng->txt("oq_btn_use_order_pictures"));
				$form->addCommandButton("changeToText", $this->lng->txt("oq_btn_define_terms"));
				break;

			case OQ_NESTED_PICTURES:

				$form->addCommandButton("changeToText", $this->lng->txt("oq_btn_use_order_terms"));
				$form->addCommandButton("changeToPictures", $this->lng->txt("oq_btn_define_pictures"));
				break;
		}
	}
	
	protected function getOrderingElementListForSolutionOutput($forceCorrectSolution, $activeId, $passIndex)
	{
		if( $forceCorrectSolution || !$activeId || !$passIndex )
		{
			return $this->object->getOrderingElementList();
		}
		
		$solutionValues = $this->object->getSolutionValues($activeId, $passIndex);
		
		if( !count($solutionValues) )
		{
			return $this->object->getShuffledOrderingElementList();
		}
		
		return $this->object->getSolutionOrderingElementList($solutionValues);
	}

	/**
	 * Get the question solution output
	 *
	 * @param integer $active_id             The active user id
	 * @param integer $pass                  The test pass
	 * @param boolean $graphicalOutput       Show visual feedback for right/wrong answers
	 * @param boolean $result_output         Show the reached points for parts of the question
	 * @param boolean $show_question_only    Show the question without the ILIAS content around
	 * @param boolean $show_feedback         Show the question feedback
	 * @param boolean $show_correct_solution Show the correct solution instead of the user solution
	 * @param boolean $show_manual_scoring   Show specific information for the manual scoring output
	 * @param bool    $show_question_text
	 *
	 * @return string The solution output of the question as HTML code
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
		$solutionOrderingList = $this->object->getOrderingElementListForSolutionOutput();
		
		require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssNestedOrderingElementsInputGUI.php';
		$answers_gui = new ilAssNestedOrderingElementsInputGUI($this->lng->txt("answers"), "answers");
		$answers_gui->setInstanceId($this->object->getId());
		$answers_gui->setOrderingType($this->object->getOrderingType());
		$answers_gui->setElementImagePath($this->object->getImagePathWeb());
		$answers_gui->setThumbPrefix($this->object->getThumbPrefix());
		
		$answers_gui->setInteractionEnabled(false);
		
		$answers_gui->setMultiValues(
			$solutionOrderingList->getRandomIdentifierIndexedElements()
		);
		
		$answers_gui->setCorrectnessTrueElementList(
			$solutionOrderingList->getParityTrueElementList($this->object->getOrderingElementList())
		);
		
		$solution_html = $answers_gui->getHTML();
	
		$template = new ilTemplate("tpl.il_as_qpl_nested_ordering_output_solution.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$template->setVariable('SOLUTION_OUTPUT', $solution_html);
		if ($show_question_text==true)
		{
			$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($this->object->getQuestion(), TRUE));
		}
		$questionoutput = $template->get();
	
		$solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html",TRUE, TRUE, "Modules/TestQuestionPool");
		$solutiontemplate->setVariable("SOLUTION_OUTPUT", $questionoutput);

		if($show_feedback)
		{
			$feedback = '';
			
			if( !$this->isTestPresentationContext() )
			{
				$fb = $this->getGenericFeedbackOutput($active_id, $pass);
				$feedback .= strlen($fb) ? $fb : '';
			}
			
			$fb = $this->getSpecificFeedbackOutput($active_id, $pass);
			$feedback .=  strlen($fb) ? $fb : '';
			
			if (strlen($feedback))
			{
				$solutiontemplate->setVariable("FEEDBACK", $this->object->prepareTextareaOutput($feedback, true));
			}
		}

		if( $show_question_only )
		{
			return $solutiontemplate->get();
		}
	
		return $this->getILIASPage( $solutiontemplate->get() );
		
		// is this template still in use? it is not used at this point any longer!
		// $template = new ilTemplate("tpl.il_as_qpl_ordering_output_solution.html", TRUE, TRUE, "Modules/TestQuestionPool");
	}
	
	/**
	 * @return ilAssOrderingElementList
	 * @throws ilTestQuestionPoolException
	 */
	protected function getOrderingElementListForPreviewOutput()
	{
		if( !$this->getPreviewSession() || !$this->getPreviewSession()->hasParticipantSolution() )
		{
			return $this->object->getShuffledOrderingElementList();
		}
		
		return $this->object->getSolutionOrderingElementList($this->getPreviewSession()->getParticipantSolution());
	}
	
	function getPreview($show_question_only = FALSE, $showInlineFeedback = false)
	{
		$solutionOrderingElementList = $this->getOrderingElementListForPreviewOutput();
		
		require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssNestedOrderingElementsInputGUI.php';
		$answers = new ilAssNestedOrderingElementsInputGUI($this->lng->txt("answers"), "answers");
		$answers->setInteractionEnabled($this->isUserInputOutputMode());
		$answers->setOrderingType($this->object->getOrderingType());
		$answers->setElementImagePath($this->object->getImagePathWeb());
		$answers->setThumbPrefix($this->object->getThumbPrefix());
		$answers->setMultiValues($solutionOrderingElementList->getRandomIdentifierIndexedElements());
		
		$template = new ilTemplate("tpl.il_as_qpl_ordering_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		
		$template->setCurrentBlock('nested_ordering_output');
		$template->setVariable('NESTED_ORDERING', $answers->getHTML());
		$template->parseCurrentBlock();
		
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($this->object->getQuestion(), true));
		
		if( $show_question_only )
		{
			return $template->get();
		}
		
		return $this->getILIASPage($template->get());
		
		//$this->tpl->addJavascript("./Modules/TestQuestionPool/templates/default/ordering.js");
	}

	private function getRandomIdToAnswerMap()
	{
		$randomIdToAnswerMap = array();

		foreach( $this->object->orderElements as $answer)
		{
			$randomIdToAnswerMap[$answer->getRandomId()] = $answer;
		}

		return $randomIdToAnswerMap;
	}

	function getTestOutput($active_id, $pass = NULL, $is_postponed = FALSE, $user_post_solution = FALSE, $inlineFeedback = false)
	{
		global $tpl;
		
		$tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_javascript.css", "Modules/TestQuestionPool"));
		
		// generate the question output
		include_once "./Services/UICore/classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_ordering_output.html", TRUE, TRUE, "Modules/TestQuestionPool");

		$this->object->setOutputType(OUTPUT_JAVASCRIPT);
		// shuffle output
		$keys = array();
		if (is_array($user_post_solution))
		{
			$keys = $_SESSION["ordering_keys"];
		}
		else
		{
			$keys = array_keys($this->object->orderElements);
			$keys = $this->object->getShuffler()->shuffle($keys);
		}
		$_SESSION["ordering_keys"] = $keys;


		if ($this->object->getOrderingType() == OQ_NESTED_TERMS
		|| $this->object->getOrderingType() == OQ_NESTED_PICTURES)
		{
	
			require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssNestedOrderingElementsInputGUI.php';
			$answerGUI = new ilAssNestedOrderingElementsInputGUI($this->lng->txt("answers"), "answers");
			$answerGUI->setInstanceId('participant');
			$answerGUI->setOrderingType($this->object->getOrderingType());
			$answerGUI->setElementImagePath($this->object->getImagePathWeb());
			$answerGUI->setThumbPrefix($this->object->getThumbPrefix());

			$answerMap = $this->getRandomIdToAnswerMap();

			$answerArray = array();
			$shuffleAnswers = false;

			if( is_array($user_post_solution) && isset($user_post_solution['answers_ordering__participant']) )
			{
				$answers_ordering = $_POST['answers_ordering__participant'];
				$user_solution_hierarchy = json_decode($answers_ordering);
				$with_random_id = true;
				$this->object->setLeveledOrdering($user_solution_hierarchy, $with_random_id);

				$newOrderingElementList = new ilAssOrderingElementList();
				$newOrderingElementList->setQuestionId($this->object->getId());
				
				foreach($this->object->leveled_ordering as $randomId => $depth)
				{
					$element = $this->object->getOrderingElementList()->getElementByRandomIdentifier($randomId);
					$element->setPosition($newOrderingElementList->getNextPosition());
					$element->setIndentation($depth);
					
					$newOrderingElementList->addElement($element);
				}
			}
			else
			{
				include_once "./Modules/Test/classes/class.ilObjTest.php";

				if (!ilObjTest::_getUsePreviousAnswers($active_id, true))
				{
					if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
				}

				$solutions = $this->object->getUserSolutionPreferingIntermediate($active_id, $pass);

				if( count($solutions) )
				{
					foreach($solutions as $solution)
					{
						list($randomId, $depth) = explode(':', $solution['value2']);

						$answ = new ASS_AnswerOrdering(
							$answerMap[$randomId]->getAnswertext(), $randomId, $depth
						);

						$answerArray[] = $answ;
					}
				}
				else
				{
					$answerArray = $this->object->orderElements;
					$shuffleAnswers = true;
				}
			}

			$answerGUI->setObjAnswersArray($answerArray, $shuffleAnswers);

			$template->setCurrentBlock('nested_ordering_output');
			$template->setVariable('NESTED_ORDERING',$answerGUI->getHtml($shuffleAnswers));
			$template->parseCurrentBlock();
			$questiontext = $this->object->getQuestion();
			$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
			$questionoutput = $template->get();
			$pageoutput = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput);
			return $pageoutput;
		}
		else
		{
			$this->tpl->addJavascript("./Modules/TestQuestionPool/templates/default/ordering.js");

			// BEGIN: onsubmit form action for javascript enabled ordering questions
			$this->tpl->setVariable("ON_SUBMIT", "return $('div.ilVerticalOrderingQuestion').ilOrderingQuestion('saveOrder');");
			// END: onsubmit form action for javascript enabled ordering questions

			// get the solution of the user for the active pass or from the last pass if allowed
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
						if (preg_match("/order_(\d+)/", $key, $matches))
						{
							foreach ( $this->object->getOrderElements() as $answeridx => $answer)
							{
								if ($answer->getRandomID() == $matches[1])
								{
									array_push($solutions, array("value1" => $answeridx, "value2" => $value));
								}
							}
						}
					}
				}
				else
				{
					$solutions = $this->object->getUserSolutionPreferingIntermediate($active_id, $pass);
				}

				$jssolutions = array();
				foreach ($solutions as $idx => $solution_value)
				{
					if ((strcmp($solution_value["value2"], "") != 0) && (strcmp($solution_value["value1"], "") != 0))
					{
						$jssolutions[$solution_value["value2"]] = $solution_value["value1"];
					}
				}
				if(count($jssolutions))
				{
					ksort($jssolutions);
					$initial_order = array();
					foreach($jssolutions as $key => $value)
					{
						if(is_object($this->object->getAnswer($value)))
						{
							$initial_order[] = 'id_' . $this->object->getAnswer($value)->getRandomID();
						}
					}

					$template->setVariable('INITIAL_ORDER', json_encode($initial_order));
				}
				else
				{
					$template->setVariable('INITIAL_ORDER', json_encode(array()));
				}
			}

			foreach ($keys as $idx)
			{
				$answer = $this->object->orderElements[$idx];
				if ($this->object->getOrderingType() == OQ_PICTURES)
				{
					$template->setCurrentBlock("ordering_row_javascript_pictures");
					$template->setVariable("PICTURE_HREF", $this->object->getImagePathWeb() . $answer->getAnswertext());
					$thumbweb = $this->object->getImagePathWeb() . $this->object->getThumbPrefix() . $answer->getAnswertext();
					$thumb = $this->object->getImagePath() . $this->object->getThumbPrefix() . $answer->getAnswertext();
					if (!@file_exists($thumb)) $this->object->rebuildThumbnails();
					$template->setVariable("THUMB_HREF", $thumbweb);
					$template->setVariable("THUMB_ALT", $this->lng->txt("thumbnail"));
					$template->setVariable("THUMB_TITLE", $this->lng->txt("thumbnail"));
					$template->setVariable("ENLARGE_HREF", ilUtil::getImagePath("enlarge.svg", FALSE));
					$template->setVariable("ENLARGE_ALT", $this->lng->txt("enlarge"));
					$template->setVariable("ENLARGE_TITLE", $this->lng->txt("enlarge"));
					$template->setVariable("ANSWER_ID", $answer->getRandomID());
					$template->parseCurrentBlock();
				}
				else
				{
					$template->setCurrentBlock("ordering_row_javascript_text");
					$template->setVariable("ANSWER_TEXT", $this->object->prepareTextareaOutput($answer->getAnswertext(), TRUE));
					$template->setVariable("ANSWER_ID", $answer->getRandomID());
					$template->parseCurrentBlock();
				}
			}
			if($this->object->getOrderingType() == OQ_PICTURES)
			{
				$template->setVariable("RESET_POSITIONS", $this->lng->txt("reset_pictures"));
			}
			else
			{
				$template->setVariable("RESET_POSITIONS", $this->lng->txt("reset_definitions"));
			}

			$questiontext = $this->object->getQuestion();
			$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
			$template->setVariable("QUESTION_ID", $this->object->getId());
			$questionoutput = $template->get();
			$pageoutput = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput);
			$this->tpl->addJavascript("./Modules/TestQuestionPool/templates/default/ordering.js");
			return $pageoutput;
		}
	}

	/**
	 * Sets the ILIAS tabs for this question type
	 *
	 * @access public
	 * 
	 * @todo:	MOVE THIS STEPS TO COMMON QUESTION CLASS assQuestionGUI
	 */
	function setQuestionTabs()
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
			$ilTabs->addTarget("edit_question",
				$url,
				array("orderNestedTerms","orderNestedPictures","editQuestion", "save", "saveEdit", "addanswers", "removeanswers", "changeToPictures", "uploadanswers", "changeToText", "upanswers", "downanswers", "originalSyncForm"),
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
		if( !$this->object->feedbackOBJ->specificAnswerFeedbackExists($this->object->getOrderElements()) )
		{
			return '';
		}

		$output = '<table class="test_specific_feedback"><tbody>';

		foreach( $this->object->getOrderElements() as $idx => $answer)
		{
			$feedback = $this->object->feedbackOBJ->getSpecificAnswerFeedbackTestPresentation(
				$this->object->getId(), $idx
			);

			$output .= "<tr><td>{$answer->getAnswerText()}</td><td>{$feedback}</td></tr>";
		}

		$output .= '</tbody></table>';

		return $this->object->prepareTextareaOutput($output, TRUE);
	}
	
	private function getDepthRecursive($child, $ordering_depth)
	{
		if(is_array($child->children))
		{
			foreach($child->children as $grand_child)
			{
				$ordering_depth++;
				$this->leveled_ordering[] = $ordering_depth;
				$this->getDepthRecursive($grand_child, $ordering_depth);
			}
		}
		else
		{
			$ordering_depth++;
			$this->leveled_ordering[] = $ordering_depth;
		}
	}

	public function setLeveledOrdering($new_hierarchy)
	{
		foreach($new_hierarchy as $id)
		{
			$ordering_depth = 0;
			$this->leveled_ordering[] = $ordering_depth;

			if(is_array($id->children))
			{
				foreach($id->children as $child)
				{
					$this->getDepthRecursive($child, $ordering_depth);
				}
			}
		}
	}

	private function getOldLeveledOrdering()
	{
		global $ilDB;

		$res = $ilDB->queryF('SELECT depth FROM qpl_a_ordering WHERE question_fi = %s ORDER BY solution_key ASC',
			array('integer'), array($this->object->getId()));
		while($row = $ilDB->fetchAssoc($res))
		{
			$this->old_ordering_depth[] = $row['depth'];
		}
		return $this->old_ordering_depth;
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
		return  $this->renderAggregateView(
					$this->aggregateAnswers( $relevant_answers, $this->object->getOrderElements() ) )->get();
	}

	public function aggregateAnswers($relevant_answers_chosen, $answers_defined_on_question)
	{
		$passdata = array(); // Regroup answers into units of passes.
		foreach($relevant_answers_chosen as $answer_chosen)
		{
			$passdata[$answer_chosen['active_fi'].'-'. $answer_chosen['pass']][$answer_chosen['value2']] = $answer_chosen['value1'];
		}
		
		$variants = array(); // Determine unique variants.
		foreach($passdata as $key => $data)
		{
			$hash = md5(implode('-', $data));
			$value_set = false;
			foreach ($variants as $vkey => $variant)
			{
				if ($variant['hash'] == $hash)
				{
					$variant['count']++;
					$value_set = true;
				}
			}
			if (!$value_set)
			{
				$variants[$key]['hash'] = $hash;
				$variants[$key]['count'] = 1;
			}
		}

		$aggregate = array(); // Render aggregate from variant.
		foreach ($variants as $key => $variant_entry)
		{
			$variant = $passdata[$key];
			
			foreach($variant as $variant_key => $variant_line)
			{
				$i = 0;
				$aggregated_info_for_answer['count'] = $variant_entry['count'];
				foreach ($answers_defined_on_question as $answer)
				{
					$i++;
					$aggregated_info_for_answer[$i . ' - ' . $answer->getAnswerText()] 
						= $passdata[$key][$i];
				}
				
			}
			$aggregate[] = $aggregated_info_for_answer;
		}
		return $aggregate;
	}

	/**
	 * @param $aggregate
	 *
	 * @return ilTemplate
	 */
	public function renderAggregateView($aggregate)
	{
		$tpl = new ilTemplate('tpl.il_as_aggregated_answers_table.html', true, true, "Modules/TestQuestionPool");

		foreach ($aggregate as $line_data)
		{
			$tpl->setCurrentBlock( 'aggregaterow' );
			$count = array_shift($line_data);
			$html = '<ul>';
			foreach($line_data as $key => $line)
			{
				$html .= '<li>'. ++$line .'&nbsp;-&nbsp;' .$key. '</li>';
			}
			$html .= '</ul>';
			$tpl->setVariable( 'COUNT', $count );
			$tpl->setVariable( 'OPTION', $html );

			$tpl->parseCurrentBlock();
		}
		return $tpl;
	}
}