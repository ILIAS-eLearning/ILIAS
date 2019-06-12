<?php
namespace ILIAS\AssessmentQuestion\Application\UserInterface\Web\Controller;

use ILIAS\AssessmentQuestion\Application\UserInterface\Web\Form\CreateQuestionFormGUI;
require_once "../../Services/AssessmentQuestion/src/Application/UserInterface/Web/Form/CreateQuestionFormGUI.php";


/**
 * Class CreateQuestion
 *
 * @ilCtrl_isCalledBy CreateQuestion: ilRepositoryGUI
 */
class CreateQuestion {

	public function __construct() {

		//$GLOBALS['DIC']->ui()->mainTemplate()->loadStandardTemplate();

		$this->render();
	}

	public function render() {

		$form = new CreateQuestionFormGUI();
return $form->getHTML();
		//$GLOBALS['DIC']->ui()->mainTemplate()->setContent($form->getHTML());
	}
}