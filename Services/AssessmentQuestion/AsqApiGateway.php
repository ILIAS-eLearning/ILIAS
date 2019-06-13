<?php
require_once "../../libs/composer/vendor/autoload.php";
require_once "../../src/Gateway/ApiGateway.php";
require_once "Services/AssessmentQuestion/src/UserInterface/Web/Form/CreateQuestionFormGUI.php";

use ILIAS\AssessmentQuestion\UserInterface\Web\Form\CreateQuestionFormGUI;
use Slim\Http\Response;

$app = new ApiGateway();

// Add route callbacks
$app->get('/CreateQuestions', function () {

		global $DIC;
		$DIC->ui()->mainTemplate()->loadStandardTemplate();
		$question_form = new CreateQuestionFormGUI();

		$DIC->ui()->mainTemplate()->setContent($question_form->getHTML());


		$response = new Response();
		$response->getBody()->write($DIC->ui()->mainTemplate()->parseCurrentBlock());

		return $response;
});
$app->run();