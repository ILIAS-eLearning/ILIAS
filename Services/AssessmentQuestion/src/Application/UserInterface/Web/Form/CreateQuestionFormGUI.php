<?php

namespace ILIAS\AssessmentQuestion\Application\UserInterface\Web\Form;
//chdir("../../");
require_once "libs/composer/vendor/autoload.php";
require_once "Services/Form/classes/class.ilPropertyFormGUI.php";
require_once "Services/AssessmentQuestion/src/Application/UserInterface/Web/Controller/CreateQuestion.php";

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

		$this->setFormAction($DIC->ctrl()->getFormActionByClass(CreateQuestion::class));
		$title = new ilTextInputGUI('title', 'title');
		$this->addCommandButton('create', '');
		$this->addCommandButton('cancel','');
	}


}
