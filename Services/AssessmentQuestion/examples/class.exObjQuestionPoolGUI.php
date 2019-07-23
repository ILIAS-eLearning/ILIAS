<?php

/**
 * When a component consumes the assessment question service for purposes
 * of authoring and managing questions like the current question pool object,
 * it is neccessary to handle the following use cases.
 *
 * @ilCtrl_Calls exObjQuestionPoolGUI: ilAssessmentQuestionServiceGUI
 */
class exObjQuestionPoolGUI
{
	/**
	 * The question creation and editing ui is handled by the Assessment Question Service itself. The control flow
	 * is to be forwarded to the ilAssessmentQuestionServiceGUI that comes as a regular control structure node.
	 *
	 * For the question creation screen the ilAssessmentQuestionServiceGUI simply renders a creation form in the
	 * tab context of the consumer, so the user is kept in the context of the question pool's question tab for example.
	 *
	 * For the screens of editing a question the ilAssessmentQuestionServiceGUI manages the question authoring tab context,
	 * as well as further forwardings in the control structure. All of the commands used in the question authoring ui
	 * are delegated to sub command classes.
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
			case 'ilassessmentquestionservicegui':
				
				$serviceGUI = $DIC->question()->authoringServiceGUI(
					$this->buildConsumerContainerSpecifications()
				);
				
				$DIC->ctrl()->forwardCommand($serviceGUI);
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
	protected function buildConsumerContainerSpecification() : AsqConsumerContainerSpec
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$containerBackLink = $DIC->ui()->factory()->link()->standard(
			'Back to Question Pool', $DIC->ctrl()->getLinkTarget($this, 'showQuestionList')
		);
		
		$containerSpecification = $DIC->question()->consumerContainerSpecification(
			
			$DIC->ui()->mainTemplate(),
			$DIC->language(),
			
			$containerBackLink,
			$this->object->getId(),
			$this->object->getRefId(),
			$this->object->getAvailableTaxonomyIds(),
			
			// still required as long as we not have merged the
			// two kinds of rendering a question to the client
			$containerIsLearningModule = false,
			
			$DIC->user()->getId()
		);
		
		return $containerSpecification;
	}
	
	/**
	 * For question listings the authoring service provides a method to retrieve
	 * an array of associative question data arrays. This structure can be simply used as
	 * data structure for any ilTable2 implementation.
	 */
	public function showQuestions()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		$authoringService = $DIC->question()->authoringService(
			$this->buildConsumerContainerSpecification()
		);

		$questionsAsAssocArrayStack = $authoringService->GetQuestionsAsAssocArrayStack();
		
		/**
		 * initialise any ilTable2GUI with this data array
		 * render initialised ilTable2GUI
		 */
		
		$tableGUI = new exQuestionsTableGUI($this, 'showQuestionList', '');
		$tableGUI->setData($questionsAsAssocArrayStack);
		
		$tableHTML = $tableGUI->getHTML(); // render table
	}
	
	/**
	 * When a component provides import functionality for assessment questions, it needs to make use of the
	 * ILIAS QTI service to get any qti xml parsed to an QTI object graph provided by the QTI service.
	 * 
	 * To actually import the question as an assessment question the ilAsqQuestion interface method
	 * fromQtiItem can be used. To retrieve an empty ilAsqQuestion instance, the question type of the
	 * QtiItem needs to be determined.
	 * 
	 * For the question type determination the ilAsqService class provides a corresponding method.
	 * An instance of the service class can be requested using $DIC->question()->service().
	 */
	public function importQuestions()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		$parentObjectId = 0; // init with question pool object id
		
		/**
		 * parse any qti import xml using the QTI Service and retrieve
		 * an array containing ilQTIItem instances
		 */
		$qtiItems = array(); /* @var ilQTIItem[] $qtiItems */
		
		foreach($qtiItems as $qtiItem)
		{
			$questionType = $DIC->question()->service()->determineQuestionTypeByQtiItem($qtiItem);
			$questionInstance = $DIC->question()->getEmptyQuestionInstance($questionType);
			
			$questionInstance->fromQtiItem($qtiItem);
			$questionInstance->setParentId($parentObjectId);
			$questionInstance->save();
		}
	}
	
	/**
	 * When a component provides export functionality for assessment questions, it needs the ilAsqQuestion
	 * interface method toQtiXML to retrieve an qti item xml string. Since the QTI service does not support
	 * to fetch an QTI xml string based on an QTI object graph, the current implementation of returning
	 * the xml string itself will be kept within the toQtiXML interface method.
	 * 
	 * To export one or more assessment questions the ilAsqFactory provides factory methods
	 * to get single or multiple ilAsqQuestion instances.
	 */
	public function exportQuestions()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		$parentObjectId = 0; // init with question pool object id
		
		/**
		 * get questions managed by this parent object
		 */
		$questions = $DIC->question()->getQuestionInstances($parentObjectId);
		
		/**
		 * build QTI xml string that will be used for any kind of export
		 */
		
		$qtiXML = '';
		
		foreach($questions as $questionInstance)
		{
			$qtiXML .= $questionInstance->toQtiXML();
		}
	}
	
	/**
	 * For the deletion of questions the authoring service comes with a method DeleteQuestion().
	 */
	public function deleteQuestion()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		$questionId = 0; // init from GET parameters
		
		$authoringService = $DIC->question()->authoringService(
			$this->buildConsumerContainerSpecification()
		);
		
		$authoringService->DeleteQuestion($questionId);
	}
}