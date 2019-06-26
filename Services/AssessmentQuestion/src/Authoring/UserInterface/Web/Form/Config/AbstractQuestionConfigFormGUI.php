<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form;

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
	 * @var \ilAsqQuestionAbstract
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
	public function __construct(\ilAsqQuestionAuthoringAbstract $qstAuthoring)
	{
		parent::__construct();
		$this->question = $qstAuthoring->getQuestion();
		$this->init($qstAuthoring);
	}
	
	/**
	 * @return \ilAsqQuestionAbstract
	 */
	public function getQuestion(): \ilAsqQuestionAbstract
	{
		return $this->question;
	}
	
	/**
	 * @param \ilAsqQuestionAbstract $question
	 */
	public function setQuestion(\ilAsqQuestionAbstract $question)
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
	final protected function init(\ilAsqQuestionAuthoring $qstAuthoring)
	{
		$this->setFormAction($this->ctrl->getFormAction($qstAuthoring));
		
		$this->setTableWidth('100%');
		$this->setMultipart(true);
		
		$this->setId($this->getQuestion()->getQuestionData()->getQuestionType()->getQuestionTypeId());
		$this->setTitle($this->getQuestion()->getQuestionData()->getQuestionType()->getTypeTag());

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
		// title
		$title = new \ilTextInputGUI($this->lng->txt("title"), "title");
		$title->setMaxLength(100);
		$title->setValue($this->question->getQuestionData()->getTitle());
		$title->setRequired(TRUE);
		$this->addItem($title);
		
		if( !$this->isLearningModuleContext() )
		{
			// author
			$author = new \ilTextInputGUI($this->lng->txt("author"), "author");
			$author->setValue($this->question->getQuestionData()->getAuthor());
			$author->setRequired(TRUE);
			$this->addItem($author);
			
			// description
			$description = new \ilTextInputGUI($this->lng->txt("description"), "comment");
			$description->setValue($this->question->getQuestionData()->getDescription());
			$description->setRequired(FALSE);
			$this->addItem($description);
		}
		else
		{
			// author as hidden field
			$hi = new \ilHiddenInputGUI("author");
			$author = ilUtil::prepareFormOutput($this->question->getQuestionData()->getAuthor());
			if (trim($author) == "")
			{
				$author = "-";
			}
			$hi->setValue($author);
			$this->addItem($hi);
			
		}
		
		// lifecycle
		$lifecycle = new \ilSelectInputGUI($this->lng->txt('qst_lifecycle'), 'lifecycle');
		$lifecycle->setOptions($this->question->getQuestionData()->getLifecycle()->getSelectOptions($this->lng));
		$lifecycle->setValue($this->question->getQuestionData()->getLifecycle()->getIdentifier());
		$this->addItem($lifecycle);
		
		// questiontext
		$question = new \ilTextAreaInputGUI($this->lng->txt("question"), "question");
		$question->setValue($this->question->getQuestionData()->getQuestionText());
		$question->setRequired(TRUE);
		$question->setRows(10);
		$question->setCols(80);
		
		if( $this->isLearningModuleContext() )
		{
			require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssSelfAssessmentQuestionFormatter.php';
			$question->setRteTags(ilAssSelfAssessmentQuestionFormatter::getSelfAssessmentTags());
			$question->setUseTagsForRteOnly(false);
			
		}
		elseif( $this->isRteEnabled() )
		{
			$question->setUseRte(TRUE);
			include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
			$question->setRteTags(ilObjAdvancedEditing::_getUsedHTMLTags("assessment"));
			$question->addPlugin("latex");
			$question->addButton("latex");
			$question->addButton("pastelatex");
			$question->setRTESupport($this->question->getQuestionData()->getQuestionId(), "qpl", "assessment");
		}
		$this->addItem($question);
		
		if( !$this->isLearningModuleContext() )
		{
			// duration
			$duration = new \ilDurationInputGUI($this->lng->txt("working_time"), "estimated");
			$duration->setShowHours(TRUE);
			$duration->setShowMinutes(TRUE);
			$duration->setShowSeconds(TRUE);
			list($ewtH, $ewtM, $ewtS) = explode(
				':', $this->question->getQuestionData()->getWorkingTime()
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
			if (strlen($this->question->getQuestionData()->getNrOfTries()))
			{
				$nr_tries = $this->question->getQuestionData()->getNrOfTries();
			}
			
			if ($nr_tries < 1)
			{
				$nr_tries = "";
			}
			
			$ni = new \ilNumberInputGUI($this->lng->txt("qst_nr_of_tries"), "nr_of_tries");
			$ni->setValue($nr_tries);
			$ni->setMinValue(0);
			$ni->setSize(5);
			$ni->setMaxLength(5);
			$this->addItem($ni);
		}
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
		if( count($this->getTaxonomies()) )
		{
			$sectHeader = new \ilFormSectionHeaderGUI();
			$sectHeader->setTitle($this->lng->txt('qpl_qst_edit_form_taxonomy_section'));
			$this->addItem($sectHeader);
			
			foreach($this->getTaxonomies() as $taxonomyId)
			{
				$taxonomy = new \ilObjTaxonomy($taxonomyId);
				$label = sprintf($this->lng->txt('qpl_qst_edit_form_taxonomy'), $taxonomy->getTitle());
				$postvar = "tax_node_assign_$taxonomyId";
				
				$taxSelect = new \ilTaxSelectInputGUI($taxonomy->getId(), $postvar, true);
				$taxSelect->setTitle($label);
				
				$taxNodeAssignments = new \ilTaxNodeAssignment(ilObject::_lookupType($this->question->getQuestionData()->getQuestionId()),
					$this->getQuestion()->getQuestionData()->getQuestionId(), 'quest', $taxonomyId
				);
				$assignedNodes = $taxNodeAssignments->getAssignmentsOfItem($this->question->getId());
				
				$taxSelect->setValue(array_map(function($assignedNode) {
					return $assignedNode['node_id'];
				}, $assignedNodes));
				
				$this->addItem($taxSelect);
			}
		}
	}
	
	public function addCommandButtons()
	{
		if( !$this->isLearningModuleContext() )
		{
			$this->addCommandButton('saveReturn', $this->lng->txt('save_return'));
		}
		
		$this->addCommandButton('save', $this->lng->txt('save'));
	}
	
	public function fillObject() {
		$this->setValuesByPost();
		if (!$this->checkInput()) {
			return false;
		}
		
		$success = true;
		try {
			$this->question->getQuestionData()->setTitle($this->getInput("title"));
			$this->question->getQuestionData()->setAuthor($this->getInput("author"));
			$this->question->getQuestionData()->setLifecycle(ilAsqQuestionLifecycle::getInstance($this->getInput("lifecycle")));
			$this->question->getQuestionData()->setQuestionText($this->getInput("question"));
			
			
			if( $this->isLearningModuleContext() )
			{
				$this->question->getQuestionData()->setNrOfTries($this->getInput("nr_of_tries"));
			}
			else
			{
				$this->question->getQuestionData()->setDescription($this->getInput("comment"));
				/** @var ilDurationInputGUI $durationItem */
				$durationItem = $this->getItemByPostVar("estimated");
				$duration = sprintf("%02d:%02d:%02d", $durationItem->getHours(), $durationItem->getMinutes(), $durationItem->getSeconds());
				$this->question->getQuestionData()->setWorkingTime($duration);
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
