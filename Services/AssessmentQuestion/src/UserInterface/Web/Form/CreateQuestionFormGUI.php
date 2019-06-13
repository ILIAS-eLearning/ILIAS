<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form;
use \ilPropertyFormGUI;
use \ilTextInputGUI;

class CreateQuestionFormGUI extends ilPropertyFormGUI {
	const POSTVAR_TITLE = 'title';
	const POSTVAR_DESCRIPTION = 'description';

	public function __construct( ) {
		global $DIC;

		$this->initForm();

		parent::__construct();
	}

	/**
	 * Init settings property form
	 *
	 * @access private
	 */
	private function initForm() {
		global $DIC;

		$title = new ilTextInputGUI('title', self::POSTVAR_TITLE);
		$this->addItem($title);

		$description = new ilTextInputGUI('description',self::POSTVAR_DESCRIPTION);
		$this->addItem($description);

		$this->addCommandButton('create', 'Create');
	}

	public function getQuestionTitle() : string {
		return $_POST[self::POSTVAR_TITLE];
	}

	public function getQuestionDescription() : string {
		return $_POST[self::POSTVAR_DESCRIPTION];
	}
}
