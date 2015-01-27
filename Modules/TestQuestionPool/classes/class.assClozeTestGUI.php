<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilGuiQuestionScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilGuiAnswerScoringAdjustable.php';

/**
 * Cloze test question GUI representation
 *
 * The assClozeTestGUI class encapsulates the GUI representation
 * for cloze test questions.
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Maximilian Becker <mbecker@databay.de>
 * 
 * @version		$Id$
 * 
 * @ingroup 	ModulesTestQuestionPool
 */
class assClozeTestGUI extends assQuestionGUI implements ilGuiQuestionScoringAdjustable, ilGuiAnswerScoringAdjustable
{
	/**
	* A temporary variable to store gap indexes of ilCtrl commands in the getCommand method
	*/
	private $gapIndex;
	
	/**
	* assClozeTestGUI constructor
	*
	* @param integer $id The database id of a image map question object
	*/
	public function __construct($id = -1)
	{
		parent::__construct();
		include_once "./Modules/TestQuestionPool/classes/class.assClozeTest.php";
		$this->object = new assClozeTest();
		if ($id >= 0)
		{
			$this->object->loadFromDb($id);
		}
	}

	function getCommand($cmd)
	{
		if (preg_match("/^(removegap|addgap)_(\d+)$/", $cmd, $matches))
		{
			$cmd = $matches[1];
			$this->gapIndex = $matches[2];
		}
		return $cmd;
	}

	/**
	 * Evaluates a posted edit form and writes the form data in the question object
	 *
	 * @param bool $always
	 *
	 * @return integer A positive value, if one of the required fields wasn't set, else 0
	 * @access private
	 */
	function writePostData($always = false)
	{
		// Bugfix for mantis: 14034 
		$save_return = true;
		if (is_array( $_POST['gap'] ))
		{
			foreach ($_POST['gap'] as $idx => $hidden)
			{
				$clozetype = $_POST['clozetype_' . $idx];

				$db_gap = $this->object->getGap($idx);
				if($db_gap->getGapType != $clozetype )
				{
					// if gap-type has been changed: set always = true and ignore "required inputs" and save new gap-type
					$always = true;
					// do not accept "save & return" action
					$save_return = false;
				}
			}
		}

		$hasErrors = (!$always) ? $this->editQuestion(true) : false;
		if (!$hasErrors)
		{
			$question_text = $_POST['question'];
			$question_text = $this->removeIndizesFromGapText( $question_text );
			$_POST['question'] = $question_text;
			$this->object->setQuestion($question_text);

			$this->writeQuestionGenericPostData();
			$this->object->setClozeText($_POST["question"]);
			$this->writeQuestionSpecificPostData();
			//$this->object->flushGaps();
			$this->writeAnswerSpecificPostData();
			$this->saveTaxonomyAssignments();
			if($save_return == false && $always == true)
			{
				$question_text = $_POST['question'];
				$question_text = $this->applyIndizesToGapText($question_text);
				$_POST['question'] = $question_text;

				return $this->editQuestion(true);
			}
			return 0;
		}

		$question_text = $_POST['question'];
		$question_text = $this->applyIndizesToGapText($question_text);
		$_POST['question'] = $question_text;
		return 1;
	}

	public function writeAnswerSpecificPostData($always = false)
	{
		if (is_array( $_POST['gap'] ))
		{
			if ( $this->ctrl->getCmd() != 'createGaps' )
			{
				$this->object->clearGapAnswers();
			}
			
			foreach ($_POST['gap'] as $idx => $hidden)
			{
				$clozetype = $_POST['clozetype_' . $idx];
				
				$this->object->setGapType( $idx, $clozetype );
				
				switch($clozetype)
				{
					case CLOZE_TEXT:

						$this->object->setGapShuffle( $idx, 0 );
						
						if ($this->ctrl->getCmd() != 'createGaps')
						{
							if (is_array( $_POST['gap_' . $idx]['answer'] ))
							{
								foreach ($_POST['gap_' . $idx]['answer'] as $order => $value)
								{
									$this->object->addGapAnswer( $idx, $order, $value );
								}
							}
							else
							{
								$this->object->addGapAnswer( $idx, 0, '' );
							}
						}
						
						if (is_array( $_POST['gap_' . $idx]['points'] ))
						{
							foreach ($_POST['gap_' . $idx]['points'] as $order => $value)
							{
								$this->object->setGapAnswerPoints( $idx, $order, $value );
							}
						}
						
						break;
						
					case CLOZE_SELECT:

						$this->object->setGapShuffle( $idx, (int)(isset($_POST["shuffle_$idx"]) && $_POST["shuffle_$idx"]) );

						if ($this->ctrl->getCmd() != 'createGaps')
						{
							if (is_array( $_POST['gap_' . $idx]['answer'] ))
							{
								foreach ($_POST['gap_' . $idx]['answer'] as $order => $value)
								{
									$this->object->addGapAnswer( $idx, $order, $value );
								}
							}
							else
							{
								$this->object->addGapAnswer( $idx, 0, '' );
							}
						}
						
						if (is_array( $_POST['gap_' . $idx]['points'] ))
						{
							foreach ($_POST['gap_' . $idx]['points'] as $order => $value)
							{
								$this->object->setGapAnswerPoints( $idx, $order, $value );
							}
						}
						
						break;

					case CLOZE_NUMERIC:
						
						$this->object->setGapShuffle( $idx, 0 );

						$gap = $this->object->getGap($idx);
						if (!$gap) break;
						
						$this->object->getGap($idx)->clearItems();

						if (array_key_exists( 'gap_' . $idx . '_numeric', $_POST ))
						{
							if ($this->ctrl->getCmd() != 'createGaps')
							{
								$this->object->addGapAnswer(
									$idx, 0, str_replace(",", ".", $_POST['gap_' . $idx . '_numeric'])
								);
							}

							$this->object->setGapAnswerLowerBound(
								$idx, 0, str_replace(",", ".", $_POST['gap_' . $idx . '_numeric_lower'])
							);

							$this->object->setGapAnswerUpperBound(
								$idx, 0, str_replace( ",", ".", $_POST['gap_' . $idx . '_numeric_upper'])
							);

							$this->object->setGapAnswerPoints( $idx, 0, $_POST['gap_' . $idx . '_numeric_points'] );
						}
						else
						{
							if ($this->ctrl->getCmd() != 'createGaps')
							{
								$this->object->addGapAnswer($idx, 0, '');
							}
							
							$this->object->setGapAnswerLowerBound($idx, 0, '');

							$this->object->setGapAnswerUpperBound($idx, 0, '');
						}
						
						break;
				}
			}
			if ($this->ctrl->getCmd() != 'createGaps')
			{
				$this->object->updateClozeTextFromGaps();
			}
		}
	}

	public function writeQuestionSpecificPostData($always = false)
	{
		$this->object->setTextgapRating( $_POST["textgap_rating"] );
		$this->object->setIdenticalScoring( $_POST["identical_scoring"] );
		$this->object->setFixedTextLength( $_POST["fixedTextLength"] );
	}

	/**
	* Creates an output of the edit form for the question
	*
	* @access public
	*/
	public function editQuestion($checkonly = FALSE)
	{
		$save = $this->isSaveCommand();
		$this->getQuestionTemplate();

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->outQuestionType());
		$form->setMultipart(FALSE);
		$form->setTableWidth("100%");
		$form->setId("assclozetest");

		// title, author, description, question, working time (assessment mode)
		$this->addBasicQuestionFormProperties($form);

		// Modify "instructive" question textbox... get rid of this crap asap.
		$q_item = $form->getItemByPostVar("question");
		$q_item->setValue($this->applyIndizesToGapText($q_item->getValue()));
		$q_item->setInfo($this->lng->txt("close_text_hint"));
		$q_item->setTitle($this->lng->txt("cloze_text"));

		$this->populateQuestionSpecificFormPart( $form );
		$this->populateAnswerSpecificFormPart( $form );
		$this->populateTaxonomyFormSection($form);

		$this->addQuestionFormCommandButtons($form);

		$errors = false;

		if ($save)
		{
			$form->setValuesByPost();
			$errors = !$form->checkInput();
			$form->setValuesByPost(); 	// again, because checkInput now performs the whole stripSlashes handling and we 
										// need this if we don't want to have duplication of backslashes
			if ($errors) $checkonly = false;
		}

		if (!$checkonly) $this->tpl->setVariable("QUESTION_DATA", $form->getHTML());
		return $errors;
	}

	function addBasicQuestionFormProperties($form)
	{
		// title
		$title = new ilTextInputGUI($this->lng->txt("title"), "title");
		$title->setValue($this->object->getTitle());
		$title->setRequired(TRUE);
		$form->addItem($title);

		if (!$this->object->getSelfAssessmentEditingMode())
		{
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
		}
		else
		{
			// author as hidden field
			$hi = new ilHiddenInputGUI("author");
			$author = ilUtil::prepareFormOutput($this->object->getAuthor());
			if (trim($author) == "")
			{
				$author = "-";
			}
			$hi->setValue($author);
			$form->addItem($hi);

		}

		// questiontext
		$question = new ilTextAreaInputGUI($this->lng->txt("question"), "question");
		$question->setValue($this->object->prepareTextareaOutput($this->object->getQuestion()));
		$question->setRequired(TRUE);
		$question->setRows(10);
		$question->setCols(80);
		if (!$this->object->getSelfAssessmentEditingMode())
		{
			if( $this->object->getAdditionalContentEditingMode() == assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_DEFAULT )
			{
				$question->setUseRte(TRUE);
				include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
				$question->setRteTags(ilObjAdvancedEditing::_getUsedHTMLTags("assessment"));
				$question->addPlugin("latex");
				$question->addButton("latex");
				$question->addButton("pastelatex");
				$question->setRTESupport($this->object->getId(), "qpl", "assessment");
			}
		}
		else
		{
			$question->setRteTags(self::getSelfAssessmentTags());
			$question->setUseTagsForRteOnly(false);
		}
		$form->addItem($question);

		$tpl = new ilTemplate("tpl.il_as_qpl_cloze_gap_button_code.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$tpl->setVariable('INSERT_GAP', $this->lng->txt('insert_gap'));
		$tpl->setVariable('CREATE_GAPS', $this->lng->txt('create_gaps'));
		$tpl->parseCurrentBlock();
		$button = new ilCustomInputGUI('&nbsp;','');
		$button->setHtml($tpl->get());
		$form->addItem($button);
		
		if (!$this->object->getSelfAssessmentEditingMode())
		{
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
		}
		else
		{
			// number of tries
			if (strlen($this->object->getNrOfTries()))
			{
				$nr_tries = $this->object->getNrOfTries();
			}
			else
			{
				$nr_tries = $this->object->getDefaultNrOfTries();
			}
			/*if ($nr_tries <= 0)
			{
				$nr_tries = 1;
			}*/

			if ($nr_tries < 0)
			{
				$nr_tries = 0;
			}

			$ni = new ilNumberInputGUI($this->lng->txt("qst_nr_of_tries"), "nr_of_tries");
			$ni->setValue($nr_tries);
			//$ni->setMinValue(1);
			$ni->setMinValue(0);
			$ni->setSize(5);
			$ni->setMaxLength(5);
			$ni->setRequired(true);
			$form->addItem($ni);
		}
	}

	public function populateQuestionSpecificFormPart(ilPropertyFormGUI $form)
	{
		// text rating
		if (!$this->object->getSelfAssessmentEditingMode())
		{
			$textrating   = new ilSelectInputGUI($this->lng->txt( "text_rating" ), "textgap_rating");
			$text_options = array(
				"ci" => $this->lng->txt( "cloze_textgap_case_insensitive" ),
				"cs" => $this->lng->txt( "cloze_textgap_case_sensitive" ),
				"l1" => sprintf( $this->lng->txt( "cloze_textgap_levenshtein_of" ), "1" ),
				"l2" => sprintf( $this->lng->txt( "cloze_textgap_levenshtein_of" ), "2" ),
				"l3" => sprintf( $this->lng->txt( "cloze_textgap_levenshtein_of" ), "3" ),
				"l4" => sprintf( $this->lng->txt( "cloze_textgap_levenshtein_of" ), "4" ),
				"l5" => sprintf( $this->lng->txt( "cloze_textgap_levenshtein_of" ), "5" )
			);
			$textrating->setOptions( $text_options );
			$textrating->setValue( $this->object->getTextgapRating() );
			$form->addItem( $textrating );

			// text field length
			$fixedTextLength = new ilNumberInputGUI($this->lng->txt( "cloze_fixed_textlength" ), "fixedTextLength");
			$ftl = $this->object->getFixedTextLength();
			if ($ftl == null)
			{
				$ftl = 0;
			}
			$fixedTextLength->setValue( ilUtil::prepareFormOutput( $ftl ) );
			$fixedTextLength->setMinValue( 0 );
			$fixedTextLength->setSize( 3 );
			$fixedTextLength->setMaxLength( 6 );
			$fixedTextLength->setInfo( $this->lng->txt( 'cloze_fixed_textlength_description' ) );
			$fixedTextLength->setRequired( false );
			$form->addItem( $fixedTextLength );

			// identical scoring
			$identical_scoring = new ilCheckboxInputGUI($this->lng->txt( "identical_scoring" ), "identical_scoring");
			$identical_scoring->setValue( 1 );
			$identical_scoring->setChecked( $this->object->getIdenticalScoring() );
			$identical_scoring->setInfo( $this->lng->txt( 'identical_scoring_desc' ) );
			$identical_scoring->setRequired( FALSE );
			$form->addItem( $identical_scoring );
		}
		return $form;
	}

	public function populateAnswerSpecificFormPart(ilPropertyFormGUI $form)
	{
		for ($gapCounter = 0; $gapCounter < $this->object->getGapCount(); $gapCounter++)
		{
			$this->populateGapFormPart( $form, $gapCounter );
		}
		return $form;
	}

	/**
	 * Populates a gap form-part.
	 * 
	 * This includes: A section header with the according gap-ordinal, the type select-box.
	 * Furthermore, this method calls the gap-type-specific methods for their contents.
	 *
	 * @param $form	 		ilPropertyFormGUI	Reference to the form, that receives the point.
	 * @param $gapCounter	integer				Ordinal number of the gap in the sequence of gaps
	 *
	 * @return ilPropertyFormGUI
	 */
	protected function populateGapFormPart($form, $gapCounter)
	{
		$gap    = $this->object->getGap( $gapCounter );

		if ($gap == null)
		{
			return $form;
		}

		$header = new ilFormSectionHeaderGUI();
		$header->setTitle( $this->lng->txt( "gap" ) . " " . ($gapCounter + 1) );
		$form->addItem( $header );

		$gapcounter = new ilHiddenInputGUI("gap[$gapCounter]");
		$gapcounter->setValue( $gapCounter );
		$form->addItem( $gapcounter );

		$gaptype = new ilSelectInputGUI($this->lng->txt( 'type' ), "clozetype_$gapCounter");
		$options = array(
			0 => $this->lng->txt( "text_gap" ),
			1 => $this->lng->txt( "select_gap" ),
			2 => $this->lng->txt( "numeric_gap" )
		);
		$gaptype->setOptions( $options );
		$gaptype->setValue( $gap->getType() );
		$form->addItem( $gaptype );

		if ($gap->getType() == CLOZE_TEXT)
		{
			if (count( $gap->getItemsRaw() ) == 0)
			{
				$gap->addItem( new assAnswerCloze("", 0, 0) );
			}
				
			$this->populateTextGapFormPart( $form, $gap, $gapCounter );
		}
		else if ($gap->getType() == CLOZE_SELECT)
		{
			if (count( $gap->getItemsRaw() ) == 0)
			{
				$gap->addItem( new assAnswerCloze("", 0, 0) );
			}	
			$this->populateSelectGapFormPart( $form, $gap, $gapCounter );
		}
		else if ($gap->getType() == CLOZE_NUMERIC)
		{
			if (count( $gap->getItemsRaw() ) == 0)
			{
				$gap->addItem( new assAnswerCloze("", 0, 0) );
			}
				
			foreach ($gap->getItemsRaw() as $item)
			{
				$this->populateNumericGapFormPart( $form, $item, $gapCounter );
			}
		}
		return $form;
	}

	/**
	 * Populates the form-part for a select gap.
	 * 
	 * This includes: The AnswerWizardGUI for the individual select items and points as well as 
	 * the the checkbox for the shuffle option.
	 *
	 * @param $form			ilPropertyFormGUI	Reference to the form, that receives the point.
	 * @param $gap			mixed				Raw text gap item.
	 * @param $gapCounter	integer				Ordinal number of the gap in the sequence of gaps
	 *
	 * @return ilPropertyFormGUI
	 */
	protected function populateSelectGapFormPart($form, $gap, $gapCounter)
	{
		include_once "./Modules/TestQuestionPool/classes/class.ilAnswerWizardInputGUI.php";
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerCloze.php";
		$values = new ilAnswerWizardInputGUI($this->lng->txt( "values" ), "gap_" . $gapCounter . "");
		$values->setRequired( true );
		$values->setQuestionObject( $this->object );
		$values->setSingleline( true );
		$values->setAllowMove( false );

		$values->setValues( $gap->getItemsRaw() );
		$form->addItem( $values );

		// shuffle
		$shuffle = new ilCheckboxInputGUI($this->lng->txt( "shuffle_answers" ), "shuffle_" . $gapCounter . "");
		$shuffle->setValue( 1 );
		$shuffle->setChecked( $gap->getShuffle() );
		$shuffle->setRequired( FALSE );
		$form->addItem( $shuffle );
		return $form;
	}

	/**
	 * Populates the form-part for a text gap.
	 * 
	 * This includes: The AnswerWizardGUI for the individual text answers and points.
	 * 
	 * @param $form			ilPropertyFormGUI	Reference to the form, that receives the point.
	 * @param $gap			mixed				Raw text gap item.
	 * @param $gapCounter	integer				Ordinal number of the gap in the sequence of gaps
	 *
	 * @return ilPropertyFormGUI
	 */
	protected function populateTextGapFormPart($form, $gap, $gapCounter)
	{
		// Choices
		include_once "./Modules/TestQuestionPool/classes/class.ilAnswerWizardInputGUI.php";
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerCloze.php";
		$values = new ilAnswerWizardInputGUI($this->lng->txt( "values" ), "gap_" . $gapCounter . "");
		$values->setRequired( true );
		$values->setQuestionObject( $this->object );
		$values->setSingleline( true );
		$values->setAllowMove( false );
		$values->setValues( $gap->getItemsRaw() );
		$form->addItem( $values );

		if( $this->object->getFixedTextLength() > 0 )
		{
			$values->setSize( $this->object->getFixedTextLength() );
			$values->setMaxLength( $this->object->getFixedTextLength() );
		}

		return $form;
	}

	/**
	 * Populates the form-part for a numeric gap.
	 * 
	 * This includes: The type selector, value, lower bound, upper bound and points.
	 * 
	 * @param $form			ilPropertyFormGUI	Reference to the form, that receives the point.
	 * @param $gap			mixed				Raw numeric gap item.
	 * @param $gapCounter	integer				Ordinal number of the gap in the sequence of gaps.
	 * 
	 * @return ilPropertyFormGUI
	 */
	protected function populateNumericGapFormPart($form, $gap, $gapCounter)
	{
		// #8944: the js-based ouput in self-assessment cannot support formulas
		if (!$this->object->getSelfAssessmentEditingMode())
		{
			$value = new ilFormulaInputGUI($this->lng->txt( 'value' ), "gap_" . $gapCounter . "_numeric");
			$value->setInlineStyle( 'text-align: right;' );

			$lowerbound = new ilFormulaInputGUI($this->lng->txt( 'range_lower_limit'), "gap_" . $gapCounter . "_numeric_lower");
			$lowerbound->setInlineStyle( 'text-align: right;' );

			$upperbound = new ilFormulaInputGUI($this->lng->txt( 'range_upper_limit'), "gap_" . $gapCounter . "_numeric_upper");
			$upperbound->setInlineStyle( 'text-align: right;' );
		} 
		else
		{
			$value = new ilNumberInputGUI($this->lng->txt( 'value' ), "gap_" . $gapCounter . "_numeric");
			$value->allowDecimals( true );

			$lowerbound = new ilNumberInputGUI($this->lng->txt( 'range_lower_limit'), "gap_" . $gapCounter . "_numeric_lower");
			$lowerbound->allowDecimals( true );

			$upperbound = new ilNumberInputGUI($this->lng->txt( 'range_upper_limit'), "gap_" . $gapCounter . "_numeric_upper");
			$upperbound->allowDecimals( true );
		}
		
		$value->setSize( 10 );
		$value->setValue( ilUtil::prepareFormOutput( $gap->getAnswertext() ) );
		$value->setRequired( true );
		$form->addItem( $value );

		$lowerbound->setSize( 10 );
		$lowerbound->setRequired( true );
		$lowerbound->setValue( ilUtil::prepareFormOutput( $gap->getLowerBound() ) );
		$form->addItem( $lowerbound );

		$upperbound->setSize( 10 );
		$upperbound->setRequired( true );
		$upperbound->setValue( ilUtil::prepareFormOutput( $gap->getUpperBound() ) );
		$form->addItem( $upperbound );

		if( $this->object->getFixedTextLength() > 0 )
		{
			$value->setSize( $this->object->getFixedTextLength() );
			$value->setMaxLength( $this->object->getFixedTextLength() );
			$lowerbound->setSize( $this->object->getFixedTextLength() );
			$lowerbound->setMaxLength( $this->object->getFixedTextLength() );
			$upperbound->setSize( $this->object->getFixedTextLength() );
			$upperbound->setMaxLength( $this->object->getFixedTextLength() );
		}

		$points = new ilNumberInputGUI($this->lng->txt( 'points' ), "gap_" . $gapCounter . "_numeric_points");
		$points->allowDecimals(true);
		$points->setSize( 3 );
		$points->setRequired( true );
		$points->setValue( ilUtil::prepareFormOutput( $gap->getPoints() ) );
		$form->addItem( $points );
		return $form;
	}

	/**
	* Create gaps from cloze text
	*/
	public function createGaps()
	{
		$this->writePostData(true);
		$this->object->saveToDb();
		$this->editQuestion();
	}

	/**
	* Remove a gap answer
	*/
	function removegap()
	{
		$this->writePostData(true);
		$this->object->deleteAnswerText($this->gapIndex, key($_POST['cmd']['removegap_' . $this->gapIndex]));
		$this->editQuestion();
	}

	/**
	* Add a gap answer
	*/
	function addgap()
	{
		$this->writePostData(true);
		$this->object->addGapAnswer($this->gapIndex, key($_POST['cmd']['addgap_' . $this->gapIndex])+1, "");
		$this->editQuestion();
	}

	/**
	* Creates an output of the question for a test
	*
	* @param string $formaction The form action for the test output
	* @param integer $active_id The active id of the current user from the tst_active database table
	* @param integer $pass The test pass of the current user
	* @param boolean $is_postponed The information if the question is a postponed question or not
	* @param boolean $use_post_solutions Fills the question output with answers from the previous post if TRUE, otherwise with the user results from the database
	* 
	* @access public
	*/
	function outQuestionForTest(
				$formaction, 
				$active_id, 
				$pass = NULL, 
				$is_postponed = FALSE, 
				$use_post_solutions = FALSE
	)
	{
		$test_output = $this->getTestOutput($active_id, $pass, $is_postponed, $use_post_solutions); 
		$this->tpl->setVariable("QUESTION_OUTPUT", $test_output);
		$this->tpl->setVariable("FORMACTION", $formaction);
	}

	/**
	 * Creates a preview output of the question
	 *
	 * @param bool $show_question_only
	 *
	 * @return string HTML code which contains the preview output of the question
	 * @access public
	 */
	function getPreview($show_question_only = FALSE)
	{
		// generate the question output
		include_once "./Services/UICore/classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_cloze_question_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$output = $this->object->getClozeText();
		foreach ($this->object->getGaps() as $gap_index => $gap)
		{
			switch ($gap->getType())
			{
				case CLOZE_TEXT:
					$gaptemplate = new ilTemplate("tpl.il_as_qpl_cloze_question_gap_text.html", TRUE, TRUE, "Modules/TestQuestionPool");
					$gaptemplate->setVariable("TEXT_GAP_SIZE", $this->object->getFixedTextLength() ? $this->object->getFixedTextLength() : $gap->getMaxWidth());
					$gaptemplate->setVariable("GAP_COUNTER", $gap_index);
					$output = preg_replace("/\[gap\].*?\[\/gap\]/", $gaptemplate->get(), $output, 1);
					break;
				case CLOZE_SELECT:
					$gaptemplate = new ilTemplate("tpl.il_as_qpl_cloze_question_gap_select.html", TRUE, TRUE, "Modules/TestQuestionPool");
					foreach ($gap->getItems() as $item)
					{
						$gaptemplate->setCurrentBlock("select_gap_option");
						$gaptemplate->setVariable("SELECT_GAP_VALUE", $item->getOrder());
						$gaptemplate->setVariable("SELECT_GAP_TEXT", ilUtil::prepareFormOutput($item->getAnswerText()));
						$gaptemplate->parseCurrentBlock();
					}
					$gaptemplate->setVariable("PLEASE_SELECT", $this->lng->txt("please_select"));
					$gaptemplate->setVariable("GAP_COUNTER", $gap_index);
					$output = preg_replace("/\[gap\].*?\[\/gap\]/", $gaptemplate->get(), $output, 1);
					break;
				case CLOZE_NUMERIC:
					$gaptemplate = new ilTemplate("tpl.il_as_qpl_cloze_question_gap_numeric.html", TRUE, TRUE, "Modules/TestQuestionPool");
					$gaptemplate->setVariable("TEXT_GAP_SIZE", $this->object->getFixedTextLength() ? $this->object->getFixedTextLength() : $gap->getMaxWidth());
					$gaptemplate->setVariable("GAP_COUNTER", $gap_index);
					$output = preg_replace("/\[gap\].*?\[\/gap\]/", $gaptemplate->get(), $output, 1);
					break;
			}
		}
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($output, TRUE));
		$questionoutput = $template->get();
		if (!$show_question_only)
		{
			// get page object output
			$questionoutput = $this->getILIASPage($questionoutput);
		}
		return $questionoutput;
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
		// get the solution of the user for the active pass or from the last pass if allowed
		$user_solution = array();
		if (($active_id > 0) && (!$show_correct_solution))
		{
			// get the solutions of a user
			$user_solution =& $this->object->getSolutionValues($active_id, $pass);
			if (!is_array($user_solution)) 
			{
				$user_solution = array();
			}
		} else {
			foreach ($this->object->gaps as $index => $gap)
			{
				$user_solution = array();
				
			}
		}

		include_once "./Services/UICore/classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_cloze_question_output_solution.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$output = $this->object->getClozeText();
		foreach ($this->object->getGaps() as $gap_index => $gap)
		{
			$gaptemplate = new ilTemplate("tpl.il_as_qpl_cloze_question_output_solution_gap.html", TRUE, TRUE, "Modules/TestQuestionPool");
			$found = array();
			foreach ($user_solution as $solutionarray)
			{
				if ($solutionarray["value1"] == $gap_index) $found = $solutionarray;
			}

			if ($active_id)
			{
				if ($graphicalOutput)
				{
					// output of ok/not ok icons for user entered solutions
					$details = $this->object->calculateReachedPoints($active_id, $pass, TRUE);
					$check = $details[$gap_index];
					if ($check["best"])
					{
						$gaptemplate->setCurrentBlock("icon_ok");
						$gaptemplate->setVariable("ICON_OK", ilUtil::getImagePath("icon_ok.png"));
						$gaptemplate->setVariable("TEXT_OK", $this->lng->txt("answer_is_right"));
						$gaptemplate->parseCurrentBlock();
					}
					else
					{
						$gaptemplate->setCurrentBlock("icon_not_ok");
						if ($check["positive"])
						{
							$gaptemplate->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_mostly_ok.png"));
							$gaptemplate->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_not_correct_but_positive"));
						}
						else
						{
							$gaptemplate->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_not_ok.png"));
							$gaptemplate->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_wrong"));
						}
						$gaptemplate->parseCurrentBlock();
					}
				}
			}
			if ($result_output)
			{
				$points = $this->object->getMaximumGapPoints($gap_index);
				$resulttext = ($points == 1) ? "(%s " . $this->lng->txt("point") . ")" : "(%s " . $this->lng->txt("points") . ")"; 
				$gaptemplate->setCurrentBlock("result_output");
				$gaptemplate->setVariable("RESULT_OUTPUT", sprintf($resulttext, $points));
				$gaptemplate->parseCurrentBlock();
			}
			switch ($gap->getType())
			{
				case CLOZE_TEXT:
					$solutiontext = "";
					if (($active_id > 0) && (!$show_correct_solution))
					{
						if ((count($found) == 0) || (strlen(trim($found["value2"])) == 0))
						{
							for ($chars = 0; $chars < $gap->getMaxWidth(); $chars++)
							{
								$solutiontext .= "&nbsp;";
							}
						}
						else
						{
							$solutiontext = ilUtil::prepareFormOutput($found["value2"]);
						}
					}
					else
					{
						$solutiontext = ilUtil::prepareFormOutput($gap->getBestSolutionOutput());
					}
					$gaptemplate->setVariable("SOLUTION", $solutiontext);
					$output = preg_replace("/\[gap\].*?\[\/gap\]/", $gaptemplate->get(), $output, 1);
					break;
				case CLOZE_SELECT:
					$solutiontext = "";
					if (($active_id > 0) && (!$show_correct_solution))
					{
						if ((count($found) == 0) || (strlen(trim($found["value2"])) == 0))
						{
							for ($chars = 0; $chars < $gap->getMaxWidth(); $chars++)
							{
								$solutiontext .= "&nbsp;";
							}
						}
						else
						{
							$item = $gap->getItem($found["value2"]);
							if (is_object($item))
							{
								$solutiontext = ilUtil::prepareFormOutput($item->getAnswertext());
							}
							else
							{
								for ($chars = 0; $chars < $gap->getMaxWidth(); $chars++)
								{
									$solutiontext .= "&nbsp;";
								}
							}
						}
					}
					else
					{
						$solutiontext = ilUtil::prepareFormOutput($gap->getBestSolutionOutput());
					}
					$gaptemplate->setVariable("SOLUTION", $solutiontext);
					$output = preg_replace("/\[gap\].*?\[\/gap\]/", $gaptemplate->get(), $output, 1);
					break;
				case CLOZE_NUMERIC:
					$solutiontext = "";
					if (($active_id > 0) && (!$show_correct_solution))
					{
						if ((count($found) == 0) || (strlen(trim($found["value2"])) == 0))
						{
							for ($chars = 0; $chars < $gap->getMaxWidth(); $chars++)
							{
								$solutiontext .= "&nbsp;";
							}
						}
						else
						{
							$solutiontext = ilUtil::prepareFormOutput($found["value2"]);
						}
					}
					else
					{
						$solutiontext = ilUtil::prepareFormOutput($gap->getBestSolutionOutput());
					}
					$gaptemplate->setVariable("SOLUTION", $solutiontext);
					$output = preg_replace("/\[gap\].*?\[\/gap\]/", $gaptemplate->get(), $output, 1);
					break;
			}
		}
		
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($output, TRUE));
		// generate the question output
		$solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html",TRUE, TRUE, "Modules/TestQuestionPool");
		$questionoutput = $template->get();

		$feedback = '';
		if($show_feedback)
		{
			$fb = $this->getGenericFeedbackOutput($active_id, $pass);
			$feedback .=  strlen($fb) ? $fb : '';
			
			$fb = $this->getSpecificFeedbackOutput($active_id, $pass);
			$feedback .=  strlen($fb) ? $fb : '';
		}
		if (strlen($feedback)) $solutiontemplate->setVariable("FEEDBACK", $feedback);
		
		$solutiontemplate->setVariable("SOLUTION_OUTPUT", $questionoutput);

		$solutionoutput = $solutiontemplate->get(); 
		
		if (!$show_question_only)
		{
			// get page object output
			$solutionoutput = '<div class="ilc_question_Standard">'.$solutionoutput."</div>";
		}
		
		return $solutionoutput;
	}

	public function getAnswerFeedbackOutput($active_id, $pass)
	{
		include_once "./Modules/Test/classes/class.ilObjTest.php";
		$manual_feedback = ilObjTest::getManualFeedback($active_id, $this->object->getId(), $pass);
		if (strlen($manual_feedback))
		{
			return $manual_feedback;
		}
		$correct_feedback = $this->object->feedbackOBJ->getGenericFeedbackTestPresentation($this->object->getId(), true);
		$incorrect_feedback = $this->object->feedbackOBJ->getGenericFeedbackTestPresentation($this->object->getId(), false);
		if (strlen($correct_feedback.$incorrect_feedback))
		{
			$reached_points = $this->object->calculateReachedPoints($active_id, $pass);
			$max_points = $this->object->getMaximumPoints();
			if ($reached_points == $max_points)
			{
				$output .= $correct_feedback;
			}
			else
			{
				$output .= $incorrect_feedback;
			}
		}
		$test = new ilObjTest($this->object->active_id);
		return $this->object->prepareTextareaOutput($output, TRUE);		
	}
	
	function getTestOutput(
				$active_id, 
				$pass = NULL, 
				$is_postponed = FALSE, 
				$use_post_solutions = FALSE, 
				$show_feedback = FALSE
	)
	{
		// get the solution of the user for the active pass or from the last pass if allowed
		$user_solution = array();
		if ($active_id)
		{
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			if (!ilObjTest::_getUsePreviousAnswers($active_id, true))
			{
				if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
			}
			$user_solution =& $this->object->getSolutionValues($active_id, $pass);
			if (!is_array($user_solution)) 
			{
				$user_solution = array();
			}
		}
		
		// generate the question output
		include_once "./Services/UICore/classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_cloze_question_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$output = $this->object->getClozeText();
		foreach ($this->object->getGaps() as $gap_index => $gap)
		{
			switch ($gap->getType())
			{
				case CLOZE_TEXT:
					$gaptemplate = new ilTemplate("tpl.il_as_qpl_cloze_question_gap_text.html", TRUE, TRUE, "Modules/TestQuestionPool");
					$gaptemplate->setVariable("TEXT_GAP_SIZE", $this->object->getFixedTextLength() ? $this->object->getFixedTextLength() : $gap->getMaxWidth());
					$gaptemplate->setVariable("GAP_COUNTER", $gap_index);
					foreach ($user_solution as $solution)
					{
						if (strcmp($solution["value1"], $gap_index) == 0)
						{
							$gaptemplate->setVariable("VALUE_GAP", " value=\"" . ilUtil::prepareFormOutput($solution["value2"]) . "\"");
						}
					}
					$output = preg_replace("/\[gap\].*?\[\/gap\]/", $gaptemplate->get(), $output, 1);
					break;
				case CLOZE_SELECT:
					$gaptemplate = new ilTemplate("tpl.il_as_qpl_cloze_question_gap_select.html", TRUE, TRUE, "Modules/TestQuestionPool");
					foreach ($gap->getItems() as $item)
					{
						$gaptemplate->setCurrentBlock("select_gap_option");
						$gaptemplate->setVariable("SELECT_GAP_VALUE", $item->getOrder());
						$gaptemplate->setVariable("SELECT_GAP_TEXT", ilUtil::prepareFormOutput($item->getAnswerText()));
						foreach ($user_solution as $solution)
						{
							if (strcmp($solution["value1"], $gap_index) == 0)
							{
								if (strcmp($solution["value2"], $item->getOrder()) == 0)
								{
									$gaptemplate->setVariable("SELECT_GAP_SELECTED", " selected=\"selected\"");
								}
							}
						}
						$gaptemplate->parseCurrentBlock();
					}
					$gaptemplate->setVariable("PLEASE_SELECT", $this->lng->txt("please_select"));
					$gaptemplate->setVariable("GAP_COUNTER", $gap_index);
					$output = preg_replace("/\[gap\].*?\[\/gap\]/", $gaptemplate->get(), $output, 1);
					break;
				case CLOZE_NUMERIC:
					$gaptemplate = new ilTemplate("tpl.il_as_qpl_cloze_question_gap_numeric.html", TRUE, TRUE, "Modules/TestQuestionPool");
					$gaptemplate->setVariable("TEXT_GAP_SIZE", $this->object->getFixedTextLength() ? $this->object->getFixedTextLength() : $gap->getMaxWidth());
					$gaptemplate->setVariable("GAP_COUNTER", $gap_index);
					foreach ($user_solution as $solution)
					{
						if (strcmp($solution["value1"], $gap_index) == 0)
						{
							$gaptemplate->setVariable("VALUE_GAP", " value=\"" . ilUtil::prepareFormOutput($solution["value2"]) . "\"");
						}
					}
					$output = preg_replace("/\[gap\].*?\[\/gap\]/", $gaptemplate->get(), $output, 1);
					break;
			}
		}
		
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($output, TRUE));
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
		
		$this->ctrl->setParameterByClass("ilAssQuestionPageGUI", "q_id", $_GET["q_id"]);
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$q_type = $this->object->getQuestionType();

		if (strlen($q_type))
		{
			$classname = $q_type . "GUI";
			$this->ctrl->setParameterByClass(strtolower($classname), "sel_question_types", $q_type);
			$this->ctrl->setParameterByClass(strtolower($classname), "q_id", $_GET["q_id"]);
#			$this->ctrl->setParameterByClass(strtolower($classname), 'prev_qid', $_REQUEST['prev_qid']);
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
	
			// edit page
			$ilTabs->addTarget("preview",
				$this->ctrl->getLinkTargetByClass("ilAssQuestionPageGUI", "preview"),
				array("preview"),
				"ilAssQuestionPageGUI", "", $force_active);
		}

		$force_active = false;
		$commands = $_POST["cmd"];
		if (is_array($commands))
		{
			foreach ($commands as $key => $value)
			{
				if (preg_match("/^removegap_.*/", $key, $matches) || 
					preg_match("/^addgap_.*/", $key, $matches)
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
			$ilTabs->addTarget("edit_question",
				$url,
				array("editQuestion", "originalSyncForm", "save", "createGaps", "saveEdit"),
				$classname, "", true);
		}

		// add tab for question feedback within common class assQuestionGUI
		$this->addTab_QuestionFeedback($ilTabs);
		
		// add tab for question hint within common class assQuestionGUI
		$this->addTab_QuestionHints($ilTabs);
		
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

                        global $___test_express_mode;
                        
                        if (!$_GET['test_express_mode'] && !$___test_express_mode) {
                            $ilTabs->setBackTarget($this->lng->txt("backtocallingtest"), "ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=$ref_id");
                        }
                        else {
                            $link = ilTestExpressPage::getReturnToPageLink();
                            $ilTabs->setBackTarget($this->lng->txt("backtocallingtest"), $link);
                        }
		}
		else
		{
			$ilTabs->setBackTarget($this->lng->txt("qpl"), $this->ctrl->getLinkTargetByClass("ilobjquestionpoolgui", "questions"));
		}
	}
	
	function getSpecificFeedbackOutput($active_id, $pass)
	{
		$feedback = '<table><tbody>';

		foreach ($this->object->gaps as $index => $answer)
		{
			$caption = $ordinal = $index+1 .':<i> ';
			foreach ($answer->items as $item)
			{
				$caption .= '"' . $item->getAnswertext().'" / ';
			}
			$caption = substr($caption, 0, strlen($caption)-3);
			$caption .= '</i>';

			$feedback .= '<tr><td>';

			$feedback .= $caption .'</td><td>';
			$feedback .= $this->object->feedbackOBJ->getSpecificAnswerFeedbackTestPresentation(
					$this->object->getId(), $index
			) . '</td> </tr>';
		}
		$feedback .= '</tbody></table>';

		return $this->object->prepareTextareaOutput($feedback, TRUE);
	}


	public function applyIndizesToGapText( $question_text )
	{
		$parts	= explode( '[gap', $question_text );
		$i = 0;
		$question_text = '';
		foreach ( $parts as $part )
		{
			if ( $i == 0 )
			{
				$question_text .= $part;
			}
			else
			{
				$question_text .= '[gap ' . $i . $part;
			}
			$i++;
		}
		return $question_text;
	}

	public function removeIndizesFromGapText( $question_text )
	{
		$parts         = preg_split( '/\[gap \d*\]/', $question_text );
		$question_text = implode( '[gap]', $parts );
		return $question_text;
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
		return array('fixedTextLength');
	}

	public function resetFormValuesForSuppressedPostvars(ilPropertyFormGUI $form)
	{
		$gapindex = 0;
		while($element = $form->getItemByPostVar('gap['.$gapindex.']'))
		{
			$type_selector = $form->getItemByPostVar('clozetype_'.$gapindex);
			$value = $type_selector->getValue();
			
			if($value == CLOZE_SELECT)
			{
				/** @var assClozeGap $the_gap */
				$the_gap = $this->object->getGap($gapindex);
				foreach($the_gap->getItemsRaw() as $itemindex => $answer_cloze)
				{
					$_POST['gap_'.$gapindex]['answer'][$itemindex] = $answer_cloze->getAnswertext();
				}
				/** @var ilAnswerWizardInputGUI $element */
				$element = $form->getItemByPostVar('gap_'.$gapindex);
				$element->setValues($the_gap->getItemsRaw());
				
				/** @var  $element */
				$element = $form->getItemByPostVar('shuffle_'.$gapindex);
				$element->setChecked($the_gap->getShuffle());
			}
			$gapindex++;
		}
	}

	public function getAggregatedAnswersView($relevant_answers)
	{
		$passes = array();
		foreach($relevant_answers as $pass)
		{
			$passes[$pass['active_fi'].'-'.$pass['pass']] = '-';
		}
		$passcount = count($passes);

		foreach($relevant_answers as $pass)
		{
			$actives[$pass['active_fi']] = $pass['active_fi'];
		}
		$usercount = count($actives);
		$tpl = new ilTemplate('tpl.il_as_aggregated_answers_header.html', true, true, "Modules/TestQuestionPool");
		$tpl->setVariable('HEADERTEXT', $this->lng->txt('overview'));
		$tpl->setVariable('NUMBER_OF_USERS_INFO', $this->lng->txt('number_of_users'));
		$tpl->setVariable('NUMBER_OF_USERS', $usercount);
		$tpl->setVariable('NUMBER_OF_PASSES_INFO', $this->lng->txt('number_of_passes'));
		$tpl->setVariable('NUMBER_OF_PASSES', $passcount);

		return $tpl->get() . $this->renderAggregateView( $this->aggregateAnswers($relevant_answers) );
	}

	public function aggregateAnswers($relevant_answers_chosen)
	{
		$gaps = array();

		foreach($relevant_answers_chosen as $answer)
		{
			$gaps[$answer['value1']][] = $answer;
		}

		return $gaps;
	}

	/**
	 * @param $aggregate
	 *
	 * @return ilTemplate
	 */
	public function renderAggregateView($aggregate)
	{
		$html = '';
		foreach($aggregate as $index => $gap)
		{
			$tpl = new ilTemplate('tpl.il_as_aggregated_answers_table.html', true, true, "Modules/TestQuestionPool");
			$tpl->setVariable( 'OPTION_HEADER', $this->lng->txt('answer') );
			$tpl->setVariable( 'COUNT_HEADER', $this->lng->txt('count') );

			$current_gap_definition = $this->object->getGap($index);
			switch ($current_gap_definition->getType())
			{
				case CLOZE_TEXT:
					$gaptype = $this->lng->txt('text_gap');
					$tpl = $this->getTextGapAggregation($index, $tpl, $gap);
					break;
				case CLOZE_SELECT:
					$gaptype = $this->lng->txt('select_gap');
					$tpl = $this->getSelectGapAggregation($tpl, $gap);
					break;
				case CLOZE_NUMERIC:
					$gaptype = $this->lng->txt('numeric_gap');
					$tpl = $this->getNumericGapAggregation($tpl, $gap);
					break;
			}
			$tpl->setVariable( 'AGGREGATION_HEADER', 
							   $this->lng->txt('gap')
							   . '&nbsp;' . ($index+1) 
							   . '&nbsp;<small>(' . $gaptype . ')</small>');
			$html .= $tpl->get();
		}

		return $html;
	}

	public function getTextGapAggregation($index, $tpl, $gap_answers)
	{
		$the_gap = $this->object->getGap($gap_answers[0]['value1']);
		foreach($the_gap->getItems() as $answer)
		{
			$answer_texts[] = $answer->getAnswertext();
		}
		$gapdata = array();
		foreach($gap_answers as $answer)
		{
			if(isset($gapdata[$answer['value2']]))
			{
				$gapdata[$answer['value2']]++;
			} else {
				$gapdata[$answer['value2']] = 1;
			}
		}
		$temp_counter = 0;
		foreach($gapdata as $answer => $count)
		{
			$tpl->setCurrentBlock( 'aggregaterow' );
			$tpl->setVariable( 'OPTION', '<p id="text_answer_' . $index . '_' . $temp_counter . '">' . $answer . '</p>');
			if(!in_array($answer, $answer_texts))
			   {
				   $tpl->setVariable( 'ACTION', '<input class="add_correction_answer submit" for="text_answer_' . $index . 
					   				  '_' . $temp_counter . '"  name="' . $index . '" value="Add"/>' );
				   $tpl->setVariable( 'POINTS', '<input type="text" id="text_points_' . $index . '_' . $temp_counter . '" value="0"/>' );
			   }
			$tpl->setVariable( 'COUNT', $count );
			$tpl->parseCurrentBlock();
			$temp_counter ++;
		}
		$tpl->touchBlock('js_cloze');
		return $tpl;
	}

	public function getSelectGapAggregation($tpl, $gap)
	{
		$gapdata = array();
		foreach($gap as $answer)
		{
			$current_gap = $this->object->getGap($answer['value1']);
			$items=$current_gap->getItemsRaw();
			$item = $items[$answer['value2']];
			$text = $item->getAnswertext();
			if(isset($gapdata[$text]))
			{
				$gapdata[$text]++;
			} else {
				$gapdata[$text] = 1;
			}
		}

		foreach($gapdata as $answer => $count)
		{
			$tpl->setCurrentBlock( 'aggregaterow' );
			$tpl->setVariable( 'OPTION', $answer );
			$tpl->setVariable( 'COUNT', $count );
			$tpl->parseCurrentBlock();
		}
		return $tpl;
	}

	public function getNumericGapAggregation($tpl, $gap)
	{
		$gapdata = array();
		foreach($gap as $answer)
		{
			if(isset($gapdata[$answer['value2']]))
			{
				$gapdata[$answer['value2']]++;
			} else {
				$gapdata[$answer['value2']] = 1;
			}
		}

		foreach($gapdata as $answer => $count)
		{
			$tpl->setCurrentBlock( 'aggregaterow' );
			$tpl->setVariable( 'OPTION', $answer );
			$tpl->setVariable( 'COUNT', $count );
			$tpl->parseCurrentBlock();
		}
		return $tpl;
	}

	public function reworkFormForCorrectionMode(ilPropertyFormGUI $form)
	{
		$gapindex = 0;
		while($element = $form->getItemByPostVar('gap['.$gapindex.']'))
		{
			$type_selector = $form->getItemByPostVar('clozetype_'.$gapindex);
			$type_selector->setDisabled(true);
			$value = $type_selector->getValue();
			switch ($value)
			{
				case CLOZE_TEXT:
					break;
				case CLOZE_SELECT:
					$this->disableSelectGapFields($form, $gapindex);
				case CLOZE_NUMERIC:
			}
			$gapindex++;
		}

		return $form;
	}
	
	public function disableSelectGapFields($form, $gapindex)
	{
		$element = $form->getItemByPostvar('gap_'.$gapindex);
		$element->setDisableActions(true);
		$element->setDisableText(true);
		
		/** @var ilCheckboxInputGUI $element */
		$element = $form->getItemByPostvar('shuffle_'.$gapindex);
		$element->setDisabled(true);
	}
}