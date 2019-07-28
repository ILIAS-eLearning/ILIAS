<?php

namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Section;


use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Question;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionData;
use ilTextAreaInputGUI;
use ilTextInputGUI;

class QuestionDataSection {

	/**
	 * @var Question
	 */
	protected $question;

	const LNG_KEY_LABEL = 'question_data';
	const LNG_KEY_BYLINE = '';

	const VAR_TITLE = 'title';
	const VAR_AUTHOR = 'author';
	const VAR_DESCRIPTION = 'description';
	const VAR_QUESTION_TEXT = 'text';

	public function __construct(Question $question) {
		$this->question = $question;
	}


	public function getInputItems(): array {

		$question_data = $this->question->getData();

		$input_item[self::VAR_TITLE] =  new ilTextInputGUI('title', self::VAR_TITLE);
		$input_item[self::VAR_TITLE]->setRequired(true);
		$input_item[self::VAR_TITLE]->setValue($question_data->getTitle());


		$input_item[self::VAR_AUTHOR] = new ilTextInputGUI('author', self::VAR_AUTHOR);
		$input_item[self::VAR_AUTHOR]->setRequired(true);
		$input_item[self::VAR_AUTHOR]->setValue($question_data->getAuthor());


		$input_item[self::VAR_DESCRIPTION] = new ilTextInputGUI('description', self::VAR_DESCRIPTION);
		$input_item[self::VAR_DESCRIPTION]->setValue($question_data->getDescription());

		$input_item[self::VAR_QUESTION_TEXT] = new ilTextAreaInputGUI('question', self::VAR_QUESTION_TEXT);
		$input_item[self::VAR_QUESTION_TEXT]->setRequired(true);
		$input_item[self::VAR_QUESTION_TEXT]->setValue($question_data->getQuestionText());
		$input_item[self::VAR_QUESTION_TEXT]->setRows(10);

		return $input_item;
	}
}