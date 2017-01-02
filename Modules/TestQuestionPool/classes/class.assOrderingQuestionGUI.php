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
	
	public function deleteElementPicture()
	{
		$orderingElementInput = $this->object->buildOrderingImagesInputGui();
		
		foreach($orderingElementInput->getElementList() as $orderingElement)
		{
			if( !$orderingElement->isImageRemovalRequest() )
			{
				continue;
			}
			
			$this->object->dropImageFile( $orderingElement->getImageRemovalRequest() );
		}
		
		$this->writePostData(true);
		$this->editQuestion();
	}

	public function uploadElementPictures()
	{
		$this->lng->loadLanguageModule('form');

		$orderingElementInput = $this->object->buildOrderingImagesInputGui();
		
		if( !$orderingElementInput->checkInput() )
		{
			$this->uploadAlert = $orderingElementInput->getAlert();
			ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
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
		if( !is_array($_POST['answers']) )
		{
			throw new ilTestQuestionPoolException('form submit request missing the form submit!?');
		}
		
		#$submittedElementList = $this->object->fetchSolutionListFromFormSubmissionData($_POST);
		$submittedElementList = $this->object->fetchSolutionListFromSubmittedForm($form);
		
		$replacementElementList = new ilAssOrderingElementList();
		$replacementElementList->setQuestionId($this->object->getId());
		
		$currentElementList = $this->object->getOrderingElementList();
		
		foreach($submittedElementList as $submittedElement)
		{
			if( $this->object->hasOrderingTypeUploadSupport() )
			{
				if( $submittedElement->isImageUploadAvailable() )
				{
					$suffix = strtolower(array_pop(explode(".", $submittedElement->getUploadImageName())));
					if( in_array($suffix, array("jpg", "jpeg", "png", "gif")) )
					{
						$submittedElement->setUploadImageName( $this->object->buildHashedImageFilename(
							$submittedElement->getUploadImageName()
						));
						
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
				
				if( $this->object->isOrderingTypeNested() )
				{
					$submittedElement->setContent($storedElement->getContent());
				}
				else
				{
					$submittedElement->setIndentation($storedElement->getIndentation());
				}
				
				if( $this->object->isImageReplaced($submittedElement, $storedElement) )
				{
					$this->object->dropImageFile($storedElement->getContent());
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
			$orderingElementInput = $this->object->buildOrderingImagesInputGui();
			$this->object->initOrderingElementAuthoringProperties($orderingElementInput);
			
			if( $this->uploadAlert !== null )
			{
				$orderingElementInput->setAlert($this->uploadAlert);
			}
		}
		elseif( $this->object->getOrderingType() == OQ_TERMS )
		{
			$orderingElementInput = $this->object->buildOrderingTextsInputGui();
			$this->object->initOrderingElementAuthoringProperties($orderingElementInput);
		}
		else // OQ_NESTED_TERMS, OQ_NESTED_PICTURES
		{
			$orderingElementInput = $this->object->buildNestedOrderingElementInputGui();
			$this->object->initOrderingElementAuthoringProperties($orderingElementInput);
			
			$orderingElementInput->setStylingDisabled($this->isPdfOutputMode());
		}
		
		$orderingElementInput->setElementList( $this->object->getOrderingElementList() );
		
		$form->addItem($orderingElementInput);

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
		
		// inits {this->editForm} and performs validation
		$hasErrors = $this->editQuestion($avoidOutput = true)
		
		if( !$forceSaving )
		{
			// this case seems to be a regular save call, so we consider
			// the validation result for the decision of saving as well
			
			$savingAllowed = !$hasErrors;
		}
		elseif( !$this->isSaveCommand() )
		{
			// this case seems to handle the mode/view switching requests,
			// so saving must not be avoided, even for inputs invalid by business rules
			
			$this->editQuestion($avoidOutput = true);
			$this->editForm->setValuesByPost(); // manipulation and distribution of values 
			$this->editForm->checkInput(); // manipulations regular style input propeties
		}
		
		if ($savingAllowed)
		{
			require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
			$this->writeQuestionGenericPostData();
			$this->writeQuestionSpecificPostData(new ilPropertyFormGUI());
			$this->writeAnswerSpecificPostData($this->editForm);
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
		$forceCorrectSolution = FALSE,
		$show_manual_scoring = FALSE,
		$show_question_text = TRUE
	)
	{
		$solutionOrderingList = $this->object->getOrderingElementListForSolutionOutput(
			$forceCorrectSolution, $active_id, $pass
		);

		$answers_gui = $this->object->buildNestedOrderingElementInputGui();
		
		if( $forceCorrectSolution )
		{
			$answers_gui->setContext(ilAssNestedOrderingElementsInputGUI::CONTEXT_CORRECT_SOLUTION_PRESENTATION);
		}
		else
		{
			$answers_gui->setContext(ilAssNestedOrderingElementsInputGUI::CONTEXT_USER_SOLUTION_PRESENTATION);
		}
		
		$answers_gui->setInteractionEnabled(false);
		
		$answers_gui->setElementList( $solutionOrderingList );
		
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
	
	function getPreview($show_question_only = FALSE, $showInlineFeedback = false)
	{
		if( $this->getPreviewSession() && $this->getPreviewSession()->hasParticipantSolution() )
		{
			$solutionOrderingElementList = $this->object->getSolutionOrderingElementList(
				$this->getPreviewSession()->getParticipantsSolution()
			);
		}
		else
		{
			$solutionOrderingElementList = $this->object->getShuffledOrderingElementList();
		}
		
		$answers = $this->object->buildNestedOrderingElementInputGui();
		$answers->setContext(ilAssNestedOrderingElementsInputGUI::CONTEXT_QUESTION_PREVIEW);
		$answers->setInteractionEnabled($this->isUserInputOutputMode());
		$answers->setElementList($solutionOrderingElementList);
		
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
		
	public function getTestOutput($activeId, $pass = NULL, $isPostponed = FALSE, $userSolutionPost = FALSE, $inlineFeedback = false)
	{
		$orderingGUI = $this->object->buildNestedOrderingElementInputGui();
		
		$solutionOrderingElementList = $this->object->getSolutionOrderingElementListForTestOutput(
			$orderingGUI, $userSolutionPost, $activeId, $pass
		);
		
		$template = new ilTemplate('tpl.il_as_qpl_ordering_output.html', TRUE, TRUE, 'Modules/TestQuestionPool');
		
		$orderingGUI->setContext(ilAssNestedOrderingElementsInputGUI::CONTEXT_USER_SOLUTION_SUBMISSION);
		$orderingGUI->setElementList( $solutionOrderingElementList );
		
		$template->setCurrentBlock('nested_ordering_output');
		$template->setVariable('NESTED_ORDERING',$orderingGUI->getHTML());
		$template->parseCurrentBlock();

		$template->setVariable('QUESTIONTEXT', $this->object->prepareTextareaOutput($this->object->getQuestion(), true));

		$pageoutput = $this->outQuestionPage('', $isPostponed, $activeId, $template->get());

		return $pageoutput;
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
					"", "", false);
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