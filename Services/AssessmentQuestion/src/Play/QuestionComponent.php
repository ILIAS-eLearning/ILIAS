<?php

namespace ILIAS\AssessmentQuestion\Play;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Answer;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionDto;
use ilTemplate;

/**
 * Class DefaultPresenter
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class QuestionComponent {

	private $question;

	private $presenter;

	private $editor;

	public function __construct(QuestionDto $question) {
		$this->question = $question;

		$presenter_class = $question->getPlayConfiguration()->getPresenterClass();
		$this->presenter = new $presenter_class($question);

		$this->editor = $this->presenter->getEditor();
	}

	public function renderHtml() : string {
		$tpl = new ilTemplate("tpl.question_view.html", true, true, "Services/AssessmentQuestion");

		$tpl->setCurrentBlock('question');
		$tpl->setVariable('QUESTION_OUTPUT', $this->presenter->generateHtml());
		$tpl->parseCurrentBlock();

		return $tpl->get();
	}

	public function readAnswer() : string {
		return $this->editor->readAnswer();
	}

	public function setAnswer(Answer $answer) {
		$this->editor->setAnswer($answer->getValue());
	}
}