<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form;
use \ilPropertyFormGUI;
use \ilTextInputGUI;

class CreateQuestionFormGUI extends ilPropertyFormGUI {

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

		$title = new ilTextInputGUI('title', 'title');
		$this->addCommandButton('create', '');
		$this->addCommandButton('cancel','');
	}


}
