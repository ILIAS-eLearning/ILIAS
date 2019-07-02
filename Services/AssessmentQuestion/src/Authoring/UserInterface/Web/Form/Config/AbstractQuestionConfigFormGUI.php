<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Question;

/**
 * Class AbstractQuestionFormGUI
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Services/AssessmentQuestion
 */
abstract class AbstractQuestionConfigFormGUI extends \ilPropertyFormGUI
{
	/**
	 * @var Question
	 */
	protected $question;
	
	/**
	 * @var int[]
	 */
	protected $taxonomies;
	
	/**
	 * @var bool
	 */
	protected $rteEnabled;
	
	/**
	 * @var bool
	 */
	protected $learningModuleContext;
	
	/**
	 * AbstractQuestionConfigFormGUI constructor.
	 */
	public function __construct(Question $qstAuthoring)
	{
		parent::__construct();
		$this->question = $qstAuthoring->getQuestion();
		$this->init($qstAuthoring);
	}
	
	/**
	 * @return Question
	 */
	public function getQuestion(): Question
	{
		return $this->question;
	}
	
	/**
	 * @param Question $question
	 */
	public function setQuestion(Question $question)
	{
		$this->question = $question;
	}
	
	/**
	 * @return int[]
	 */
	public function getTaxonomies(): array
	{
		return $this->taxonomies;
	}
	
	/**
	 * @param int[] $taxonomies
	 */
	public function setTaxonomies(array $taxonomies)
	{
		$this->taxonomies = $taxonomies;
	}
	
	/**
	 * @return bool
	 */
	public function isRteEnabled(): bool
	{
		return $this->rteEnabled;
	}
	
	/**
	 * @param bool $rteEnabled
	 */
	public function setRteEnabled(bool $rteEnabled)
	{
		$this->rteEnabled = $rteEnabled;
	}
	
	/**
	 * @return bool
	 */
	public function isLearningModuleContext(): bool
	{
		return $this->learningModuleContext;
	}
	
	/**
	 * @param bool $learningModuleContext
	 */
	public function setLearningModuleContext(bool $learningModuleContext)
	{
		$this->learningModuleContext = $learningModuleContext;
	}
	
	/**
	 * this method does build the form with its properties.
	 * @throws \ilTaxonomyException
	 */
	final protected function init(Question $qstAuthoring)
	{
		$this->setFormAction($this->ctrl->getFormAction($qstAuthoring));
		
		$this->setTableWidth('100%');
		$this->setMultipart(true);
		
		$this->setId($this->getQuestion()->getAggregateId()->getId());
		$this->setTitle($this->getQuestion()->getTitle());

		$this->addQuestionGenericProperties();
		$this->addQuestionSpecificProperties();
		$this->addAnswerSpecificProperties();
		
		$this->addTaxonomyFormSection();
		
		$this->addCommandButtons();
	}
	
	/**
	 * this method does add properties that relates to every question type
	 */
	protected function addQuestionGenericProperties()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		// title
		$title = new \ilTextInputGUI($DIC->language()->txt("title"), "title");
		$title->setMaxLength(100);
		$title->setValue($this->getQuestion()->getTitle());
		$title->setRequired(TRUE);
		$this->addItem($title);

		// author
		$author = new \ilTextInputGUI($DIC->language()->txt("author"), "author");
		$author->setValue($this->getQuestion()->getCreator());
		$author->setRequired(TRUE);
		$this->addItem($author);

		// description
		$description = new \ilTextInputGUI($DIC->language()->txt("description"), "comment");
		$description->setValue($this->getQuestion()->getDescription());
		$description->setRequired(FALSE);
		$this->addItem($description);
		
		// questiontext
		$question = new \ilTextAreaInputGUI($DIC->language()->txt("question"), "question");
		$question->setValue($this->getQuestion()->getQuestionText());
		$question->setRequired(TRUE);
		$question->setRows(10);
		$question->setCols(80);
		
		/*if( $this->isLearningModuleContext() )
		{
			$question->setRteTags(\ilAssSelfAssessmentQuestionFormatter::getSelfAssessmentTags());
			$question->setUseTagsForRteOnly(false);
			
		}
		elseif( $this->isRteEnabled() )
		{
			$question->setUseRte(TRUE);
			$question->setRteTags(\ilObjAdvancedEditing::_getUsedHTMLTags("assessment"));
			$question->addPlugin("latex");
			$question->addButton("latex");
			$question->addButton("pastelatex");
			$question->setRTESupport($this->getQuestion()->getQuestionData()->getQuestionId(), "qpl", "assessment");
		}
		$this->addItem($question);
		
		if( !$this->isLearningModuleContext() )
		{
			// duration
			$duration = new \ilDurationInputGUI($DIC->language()->txt("working_time"), "estimated");
			$duration->setShowHours(TRUE);
			$duration->setShowMinutes(TRUE);
			$duration->setShowSeconds(TRUE);
			list($ewtH, $ewtM, $ewtS) = explode(
				':', $this->getQuestion()->getQuestionData()->getWorkingTime()
			);
			$duration->setHours($ewtH);
			$duration->setMinutes($ewtM);
			$duration->setSeconds($ewtS);
			$duration->setRequired(FALSE);
			$this->addItem($duration);
		}
		else
		{
			$nr_tries = 0;
			// number of tries
			if (strlen($this->getQuestion()->getQuestionData()->getNrOfTries()))
			{
				$nr_tries = $this->getQuestion()->getQuestionData()->getNrOfTries();
			}
			
			if ($nr_tries < 1)
			{
				$nr_tries = "";
			}
			
			$ni = new \ilNumberInputGUI($DIC->language()->txt("qst_nr_of_tries"), "nr_of_tries");
			$ni->setValue($nr_tries);
			$ni->setMinValue(0);
			$ni->setSize(5);
			$ni->setMaxLength(5);
			$this->addItem($ni);
		}*/
	}
	
	/**
	 * this method does add properties that relates to the concerns of the question
	 * for a specific question type
	 */
	abstract protected function addQuestionSpecificProperties();
	
	/**
	 * this method does add properties that relates to the concerns of the question's answers
	 * for a specific question type
	 */
	abstract protected function addAnswerSpecificProperties();
	
	/**
	 * @throws \ilTaxonomyException
	 */
	protected function addTaxonomyFormSection()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		if( count($this->getTaxonomies()) )
		{
			$sectHeader = new \ilFormSectionHeaderGUI();
			$sectHeader->setTitle($DIC->language()->txt('qpl_qst_edit_form_taxonomy_section'));
			$this->addItem($sectHeader);
			
			foreach($this->getTaxonomies() as $taxonomyId)
			{
				$taxonomy = new \ilObjTaxonomy($taxonomyId);
				$label = sprintf($DIC->language()->txt('qpl_qst_edit_form_taxonomy'), $taxonomy->getTitle());
				$postvar = "tax_node_assign_$taxonomyId";
				
				$taxSelect = new \ilTaxSelectInputGUI($taxonomy->getId(), $postvar, true);
				$taxSelect->setTitle($label);
				
				$taxNodeAssignments = new \ilTaxNodeAssignment(\ilObject::_lookupType($this->getQuestion()->getQuestionData()->getQuestionId()),
					$this->getQuestion()->getQuestionData()->getQuestionId(), 'quest', $taxonomyId
				);
				$assignedNodes = $taxNodeAssignments->getAssignmentsOfItem($this->getQuestion()->getId());
				
				$taxSelect->setValue(array_map(function($assignedNode) {
					return $assignedNode['node_id'];
				}, $assignedNodes));
				
				$this->addItem($taxSelect);
			}
		}
	}
	
	public function addCommandButtons()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		if( !$this->isLearningModuleContext() )
		{
			$this->addCommandButton('saveReturn', $DIC->language()->txt('save_return'));
		}
		
		$this->addCommandButton('save', $DIC->language()->txt('save'));
	}
	
	public function fillObject() {
		$this->setValuesByPost();
		if (!$this->checkInput()) {
			return false;
		}
		
		$success = true;
		try {
			$this->getQuestion()->getQuestionData()->setTitle($this->getInput("title"));
			$this->getQuestion()->getQuestionData()->setAuthor($this->getInput("author"));
			$this->getQuestion()->getQuestionData()->setLifecycle(\ilAsqQuestionLifecycle::getInstance($this->getInput("lifecycle")));
			$this->getQuestion()->getQuestionData()->setQuestionText($this->getInput("question"));
			
			
			if( $this->isLearningModuleContext() )
			{
				$this->getQuestion()->getQuestionData()->setNrOfTries($this->getInput("nr_of_tries"));
			}
			else
			{
				$this->getQuestion()->getQuestionData()->setDescription($this->getInput("comment"));
				/** @var \ilDurationInputGUI $durationItem */
				$durationItem = $this->getItemByPostVar("estimated");
				$duration = sprintf("%02d:%02d:%02d", $durationItem->getHours(), $durationItem->getMinutes(), $durationItem->getSeconds());
				$this->getQuestion()->getQuestionData()->setWorkingTime($duration);
			}
			
			$this->fillQuestionSpecificProperties();
			$this->fillAnswerSpecificProperties();
		} catch (\ilException $e) {
			\ilUtil::sendFailure($e->getMessage());
			$success = false;
		}
		return $success;
	}
	
	abstract protected function fillQuestionSpecificProperties();
	
	abstract protected function fillAnswerSpecificProperties();
}
