<?php
require_once "../../libs/composer/vendor/autoload.php";
use Slim\Http\Response;
use ILIAS\Gateway\ApiGateway;


$app = new ApiGateway();


// Add route callbacks
$app->get('/CreateQuestions', function () {
	global $DIC;

	if(!file_exists(getcwd() . '/ilias.ini.php'))
	{
		header('Location: ./setup/setup.php');
		exit();
	}

	require_once 'Services/Context/classes/class.ilContext.php';
	ilContext::init(ilContext::CONTEXT_SAML);

	require_once 'Services/Init/classes/class.ilInitialisation.php';
	ilInitialisation::initILIAS();

	$DIC->ctrl()->initBaseClass('ilStartUpGUI');
	$DIC->ctrl()->setCmd('doSamlAuthentication');
	$DIC->ctrl()->setTargetScript('ilias.php');
	require_once "Services/AssessmentQuestion/src/Application/UserInterface/Web/Controller/CreateQuestion.php";
	$question_gui = new CreateQuestion();


	//$question_gui = new CreateQuestionGUI();
	//exit;
/*

	//$name = $args['name'];
	$response = new Response();
	$response->getBody()->write("Hello");

	return $response;*/
});
$app->run();