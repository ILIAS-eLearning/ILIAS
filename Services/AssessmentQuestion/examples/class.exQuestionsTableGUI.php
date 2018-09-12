<?php

/**
 * For that questions that are actually shown in the rows limit of the table corresponding question links
 * needs to be rendered. The ilAsqFactory can be used within the fillRow method to get an ilAsqQuestionAuthoring
 * instance for each question to get all neccessary links as an UI Link Component.
 *
 * The following links are provided:
 * - Question Preview Link
 * - Edit Question Config Link
 * - Edit Question Page Link
 * - Edit Feedbacks Link
 * - Edit Hints Link
 * - Question Statistic Link
 */
class exQuestionsTableGUI extends ilTable2GUI
{
	/**
	 * @var ilAsqFactory
	 */
	protected $asqFactory;
	
	/**
	 * exQuestionsTableGUI constructor.
	 * @param object $a_parent_obj
	 * @param string $a_parent_cmd
	 * @param string $a_template_context
	 */
	public function __construct($a_parent_obj, $a_parent_cmd = "", $a_template_context = "")
	{
		$this->asqFactory = new ilAsqFactory();

		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);
	}
	
	/**
	 * @param array $questionData
	 */
	public function fillRow($questionData)
	{
		/**
		 * use the associative array containing the question data
		 * for filling any table column with title, comment, points, etc.
		 */
		
		$this->tpl->setVariable('QUESTION_TITLE', $questionData['title']);
		
		/**
		 * use the questionId and the ilAsqFactory to get an ilAsqQuestionAuthoring instance
		 * that provides interface methods to get neccessary links related to the question
		 */
		
		$questionInstance = $this->asqFactory->getQuestionInstance( $questionData['questionId'] );
		$questionAuthoringGUI = $this->asqFactory->getAuthoringCommandInstance($questionInstance);
		
		$previewLinkComponent = $questionAuthoringGUI->getPreviewLink();
		
		$this->tpl->setVariable('QUESTION_HREF', $previewLinkComponent->getAction());
	}
}