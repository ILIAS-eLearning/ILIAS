<?php

namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form;
use \ilPropertyFormGUI;
use \ilTextInputGUI;

class CreateQuestionFormGUI extends ilPropertyFormGUI {
	const QuestionVAR_TITLE = 'title';
	const QuestionVAR_DESCRIPTION = 'description';
	const QuestionVAR_TEXT = 'text';

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
		$title = new ilTextInputGUI('title', self::QuestionVAR_TITLE);
		$title->setRequired(true);
		$this->addItem($title);

		$description = new ilTextInputGUI('description',self::QuestionVAR_DESCRIPTION);
		$this->addItem($description);

		$text = new ilTextInputGUI('text',self::QuestionVAR_TEXT);
		$this->addItem($text);

		$this->addCommandButton('create', 'Create');
	}

	public function getQuestionTitle() : string {
		return $_POST[self::QuestionVAR_TITLE];
	}

	public function getQuestionDescription() : string {
		return $_POST[self::QuestionVAR_DESCRIPTION];
	}

	public function getQuestionText() : string {
		return $_POST[self::QuestionVAR_TEXT];
	}
}
