<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form;

use ilPropertyFormGUI;
use ilTextInputGUI;

/**
 * Class CreateQuestionFormGUI
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class CreateQuestionFormGUI extends ilPropertyFormGUI {
	const VAR_TITLE = 'title';
	const VAR_AUTHOR = 'author';
	const VAR_DESCRIPTION = 'description';
	const VAR_TEXT = 'text';

	public function __construct( ) {
		$this->initForm();

		parent::__construct();
	}

	/**
	 * Init settings property form
	 *
	 * @access private
	 */
	private function initForm() {
		$title = new ilTextInputGUI('title', self::VAR_TITLE);
		$title->setRequired(true);
		$this->addItem($title);

		$author = new ilTextInputGUI('author',self::VAR_AUTHOR);
		$author->setRequired(true);
		$this->addItem($author);

		$description = new ilTextInputGUI('description',self::VAR_DESCRIPTION);
		$this->addItem($description);

		$text = new ilTextInputGUI('text',self::VAR_TEXT);
		$text->setRequired(true);
		$this->addItem($text);

		$this->addCommandButton('create', 'Create');
	}

    /**
     * @return string
     */
	public function getQuestionTitle() : string {
		return $_POST[self::VAR_TITLE];
	}

    /**
     * @return string
     */
	public function getQuestionAuthor(): string {
		return $_POST[self::VAR_AUTHOR];
	}

    /**
     * @return string
     */
	public function getQuestionDescription() : string {
		return $_POST[self::VAR_DESCRIPTION];
	}

    /**
     * @return string
     */
	public function getQuestionText() : string {
		return $_POST[self::VAR_TEXT];
	}
}
