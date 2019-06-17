<?php
use ILIAS\AssessmentQuestion\AuthoringInfrastructure\Setup\sql\SetupDatabase;
//only for development usage!

chdir("../../../../../../");
global $DIC;

if (!file_exists(getcwd() . '/ilias.ini.php')) {
	header('Location: ./setup/setup.php');
	exit();
}

require_once 'Services/Context/classes/class.ilContext.php';
ilContext::init(ilContext::CONTEXT_WEB);

require_once 'Services/Init/classes/class.ilInitialisation.php';
ilInitialisation::initILIAS();

require_once 'Services/AssessmentQuestion/src/Authoring/Infrastructure/Setup/sql/SetupDatabase.php';
$setup_database = new SetupDatabase();
$setup_database->run();

$DIC->ctrl()->initBaseClass('ilStartUpGUI');
$DIC->ctrl()->setTargetScript('ilias.php');