<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Presenter;



use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\AbstractEditor;

/**
 * Abstract Class AbstractPresenter
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class AbstractPresenter {
	/**
	 * @var QuestionDto
	 */
	protected $question;
	/**
	 * @var AbstractEditor
	 */
	protected $editor;

    /**
     * AbstractPresenter constructor.
     *
     * @param QuestionDto $question
     */
	public function __construct(QuestionDto $question) {
		$this->question = $question;

		$editor_class = QuestionPlayConfiguration::getEditorClass($question->getPlayConfiguration());
		$this->editor = new $editor_class($question);
	}

    /**
     * @return AbstractEditor
     */
	public function getEditor() {
		return $this->editor;
	}

	/**
	 * @return string
	 */
	abstract public function generateHtml(): string;

	/**
	 * @return array|null
	 */
	public static function generateFields(): ?array {
		return null;
	}
}