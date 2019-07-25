<?php

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AuthoringServiceSpecContract;

/**
 * When a component consumes the assessment question service for purposes
 * of authoring and managing questions like the current question pool object,
 * it is neccessary to handle the following use cases.
 *
 * @ilCtrl_Calls exObjQuestionPoolGUI: ilAsqQuestionAuthoringGUI
 */
class exObjQuestionPoolGUI
{
	/**
	 * The question creation and editing ui is handled by the Assessment Question Service itself. The control flow
	 * is to be forwarded to the ilAssessmentQuestionServiceGUI that comes as a regular control structure node.
	 *
	 * For the question creation screen the ilAsqQuestionAuthoringGUI simply renders a creation form in the
	 * tab context of the consumer, so the user is kept in the context of the question pool's question tab for example.
	 *
	 * For the screens of editing a question the ilAsqQuestionAuthoringGUI manages the question authoring tab context,
	 * as well as further forwardings in the control structure. All of the commands used in the question authoring ui
	 * are delegated to command class subnodes.
	 *
	 * To integrate the forward of to the Assessment Question Service two requirements need to be fullfilled:
	 * - a suitable control structure forward header is required (like above)
	 * - a suitable switch case within the executeCommand() method is necessary (like below)
	 */
	public function executeCommand()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		switch( $DIC->ctrl()->getNextClass($this) )
		{
			case 'ilasqquestionauthoringgui':
				
				$authoringGUI = $DIC->assessment()->control()->authoringGUI(
					$this->buildAsqAuthoringSpecification()
				);
				
				$DIC->ctrl()->forwardCommand($authoringGUI);
		}
	}
	
	/**
	 * The authoring service requires some information about the consuming container object (e.g. question pool).
	 * For this purpose a container specification object is available that needs to be constructed
	 * with the required information like:
	 * - parent obj/ref id,
	 * - available taxonomies (that are managed in the consumer)
	 * - the still required flag to distinguish between test/pool and learning module
	 *
	 * The container specification is also used to inject the required globals.
	 */
	public function buildAsqAuthoringSpecification() : AuthoringServiceSpecContract
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$containerBackLink = $DIC->ui()->factory()->link()->standard(
			'Back to Question Pool', $DIC->ctrl()->getLinkTarget($this, 'showQuestionList')
		);
		
		$authoringSpecification = $DIC->assessment()->specification()->authoring(
			$this->object->getId(),
			$DIC->user()->getId(),
			$containerBackLink
		)->addAdditionalConfigSection($this->buildAdditionalTaxonomiesConfigSection());
		
		return $authoringSpecification;
	}
	
	protected function buildAdditionalTaxonomiesConfigSection()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$sectionHeader = new ilFormSectionHeaderGUI();
		$sectionHeader->setTitle('Taxonomy Assignments');
		
		$sectionInputs = [];
		
		foreach($this->object->getTaxonomyIds() as $taxonomyId)
		{
			$sectionInputs[] = new ilTaxSelectInputGUI(
				$taxonomyId, "tax_{$taxonomyId}"
			);
		}
		
		return $DIC->assessment()->consumer()->questionConfigSection(
			$sectionHeader, $sectionInputs
		);
	}
	
	/**
	 * For question listings the query service provides a method to retrieve an stack of associative question data arrays
	 * for all questions that relate to us as the parent container. This structure can be simply used as data structure
	 * for any ilTable2 implementation.
	 */
	public function showQuestions()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		$queryService = $DIC->assessment()->service()->query();

		$questionsAsAssocArrayStack = $queryService->GetQuestionsOfContainerAsAssocArray(
			$this->object->getId()
		);
		
		/**
		 * - initialise any ilTable2GUI with this data array
		 * - render initialised ilTable2GUI
		 */
		
		$tableGUI = new exQuestionsTableGUI($this, 'showQuestionList', '');
		$tableGUI->setData($questionsAsAssocArrayStack);
		
		$tableHTML = $tableGUI->getHTML(); // render table
	}
	
	/**
	 * When a component provides import functionality for assessment questions, it needs to make use of the
	 * ILIAS QTI service to get any qti xml parsed to an QTI object graph provided by the QTI service.
	 * 
	 * To actually import the question as an assessment question the authoring service provides a method
	 * importQtiItem to be used. Simply pass the ilQtiItem and get it imported.
	 */
	public function importQuestions()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		
		
		/**
		 * parse any qti import xml using the QTI Service and retrieve
		 * an array containing ilQTIItem instances
		 */
		$qtiItems = array(); /* @var ilQTIItem[] $qtiItems */
		
		foreach($qtiItems as $qtiItem)
		{
			$authoringService = $DIC->assessment()->service()->authoring(
				$DIC->assessment()->consumer()->questionUuid(),
				$this->buildAsqAuthoringSpecification()
			);
			
			$authoringService->importQtiItem($qtiItem);
		}
	}
	
	/**
	 * For the deletion of questions the authoring service comes with a method deleteQuestion.
	 * Simply pass the question's UUID.
	 */
	public function deleteQuestion()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		$questionUuid = ''; // init from GET parameters
		
		$authoringService = $DIC->assessment()->service()->authoring(
			$DIC->assessment()->consumer()->questionUuid($questionUuid),
			$this->buildAsqAuthoringSpecification()
		);
		
		$authoringService->deleteQuestion();
	}
}