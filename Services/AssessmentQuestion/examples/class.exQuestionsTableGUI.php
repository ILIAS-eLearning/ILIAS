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
	 * @var exObjQuestionPoolGUI
	 */
	protected $parent_obj;
	
	/**
	 * @param array $questionData
	 */
	public function fillRow($questionData)
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		$authoringService = $DIC->assessment()->service()->authoring(
			$this->parent_obj->buildAsqAuthoringSpecification()
		);
		
		/**
		 * use the associative array containing the question data
		 * for filling any table column with title, comment, points, etc.
		 */
		
		$this->tpl->setVariable('QUESTION_TITLE', $questionData['title']);
		
		/**
		 * use the questionId and the authoring service to get neccessary links
		 * related to the question (preview, config, page, feedback, hint, statistic, ...)
		 */
		
		$previewLinkComponent = $authoringService->getPreviewLink($questionData['questionId']);
		
		$this->tpl->setVariable('QUESTION_HREF', $previewLinkComponent->getAction());
	}
}