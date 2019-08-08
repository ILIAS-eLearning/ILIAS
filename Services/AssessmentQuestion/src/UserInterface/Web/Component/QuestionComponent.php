<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component;

use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\AbstractEditor;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Presenter\AbstractPresenter;
use ilTemplate;

/**
 * Class QuestionComponent
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class QuestionComponent {

    /**
     * @var QuestionDto
     */
	private $question;
    /**
     * @var AbstractPresenter
     */
	private $presenter;
    /**
     * @var AbstractEditor
     */
	private $editor;

	public function __construct(QuestionDto $question) {
		$this->question = $question;

		$presenter_class = QuestionPlayConfiguration::getPresenterClass($question->getPlayConfiguration());
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