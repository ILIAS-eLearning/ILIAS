<?php

use ILIAS\Services\AssessmentQuestion\PublicApi\Authoring\AuthoringService;
use ILIAS\UI\Component\Link\Link;

/**
 * When a component consumes the assessment question service for purposes
 * of authoring and managing questions like the current question pool object,
 * it is neccessary to handle the following use cases.
 *
 * @ilCtrl_Calls exObjQuestionPoolGUI: ilAsqQuestionAuthoringGUI
 */
class exObjQuestionPoolGUI {

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
	 * - a suitable switch case within the executeCommand() method is necessary
	 */

	/**
	 * @var AuthoringService
	 */
	protected $authoring_service;


	public function __construct() {
		/* @var ILIAS\DI\Container $DIC */ global $DIC;

		$this->authoring_service = $DIC->assessment()->questionAuthoring($this->object->getId(), $DIC->user()->getId());
	}


	public function executeCommand() {
		global $DIC;
		/* @var ILIAS\DI\Container $DIC */

		switch ($DIC->ctrl()->getCmdClass()) {
			case 'ilasqquestionauthoringgui':
				$this->forwardToQuestionAuthoringGUI($this->authoring_service->currentOrNewQuestionId());
				break;
			default:
				switch ($DIC->ctrl()->getCmd()) {
					case 'showQuestions':
						$this->showQuestions();
						break;
					//TODO
				}
		}
	}


	protected function forwardToQuestionAuthoringGUI(QuestionId $question_id) {
		global $DIC;

		$DIC->ctrl()->forwardCommand($this->authoring_service->question($question_id, $this->getBacklink())->getAuthoringGUI());
	}


	/**
	 * For question listings the query service provides a method to retrieve an stack of associative question data arrays
	 * for all questions that relate to us as the parent container. This structure can be simply used as data structure
	 * for any ilTable2 implementation.
	 */
	public function showQuestions() {
		global $DIC;
		/* @var ILIAS\DI\Container $DIC */

		$creationLinkComponent = $this->authoring_service->question($DIC->assessment()->currentOrNewQuestionId(), $this->getBacklink())->widthAdditionalConfigSection($this->buildAdditionalTaxonomiesConfigSection())
			->getCreationLink([ 'ilRepositoryObjectGUI', 'exObjQuestionPoolGUI' ]);

		$button = ilLinkButton::getInstance();
		$button->setCaption($creationLinkComponent->gegetLabel());
		$button->setUrl($creationLinkComponent->getAction());
		$toolbar = new ilToolbarGUI();
		$toolbar->addButtonInstance($button);

		$queryService = $this->authoring_service->questionList();
		$questionsAsAssocArrayStack = $queryService->GetQuestionsOfContainerAsAssocArray();

		/**
		 * - initialise any ilTable2GUI with this data array
		 * - render initialised ilTable2GUI
		 */

		$tableGUI = new exQuestionsTableGUI($this, 'showQuestionList', '');
		$tableGUI->setData($questionsAsAssocArrayStack);

		$toolbarHTML = $toolbar->getHTML(); // render toolbar including create question button
		$tableHTML = $tableGUI->getHTML(); // render table containing question list
	}


	/**
	 * When a component provides import functionality for assessment questions, it needs to make use of the
	 * ILIAS QTI service to get any qti xml parsed to an QTI object graph provided by the QTI service.
	 *
	 * To actually import the question as an assessment question the authoring service provides a method
	 * importQtiItem to be used. Simply pass the ilQtiItem and get it imported.
	 */
	public function importQuestions() {
		global $DIC;
		/* @var ILIAS\DI\Container $DIC */

		/**
		 * parse any qti import xml using the QTI Service and retrieve
		 * an array containing ilQTIItem instances
		 */
		$qtiItems = array();
		/* @var ilQTIItem[] $qtiItems */

		foreach ($qtiItems as $qtiItem) {
			$this->authoring_service->questionImport()->importQtiItem($qtiItem);
		}
	}


	/**
	 * For the deletion of questions the authoring service comes with a method deleteQuestion.
	 * Simply pass the question's UUID.
	 */
	public function deleteQuestion() {
		global $DIC;
		/* @var ILIAS\DI\Container $DIC */

		$questionUuid = ''; // init from GET parameters

		$this->authoring_service->question($questionUuid,$this->getBacklink())->deleteQuestion();
	}

	protected function getBacklink(): Link {
		global $DIC;

		return $DIC->ui()->factory()->link()->standard('Back to Question Pool', $DIC->ctrl()->getLinkTarget($this, 'showQuestionList'));
	}


	/**
	 * @return AdditionalConfigSectionDto
	 */
	protected function buildAdditionalTaxonomiesConfigSection(): AdditionalConfigSectionDto {
		global $DIC;
		/* @var \ILIAS\DI\Container $DIC */

		$sectionHeader = new ilFormSectionHeaderGUI();
		$sectionHeader->setTitle('Taxonomy Assignments');

		$config_section = new AdditionalConfigSectionDto($sectionHeader);

		foreach ($this->object->getTaxonomyIds() as $taxonomyId) {
			$config_section->appendSectionInput(new ilTaxSelectInputGUI($taxonomyId, "tax_{$taxonomyId}"));
		}

		return $config_section;
	}
}