<?php
use ILIAS\AssessmentQuestion\Infrastructure\Setup\sql\SetupDatabase;
//only for development usage!

chdir("../../../../../");
global $DIC;

if (!file_exists(getcwd() . '/ilias.ini.php')) {
	header('Location: ./setup/setup.php');
	exit();
}

require_once 'Services/Context/classes/class.ilContext.php';
ilContext::init(ilContext::CONTEXT_WEB);

require_once 'Services/Init/classes/class.ilInitialisation.php';
ilInitialisation::initILIAS();

require_once 'Services/AssessmentQuestion/src/Infrastructure/Setup/sql/SetupDatabase.php';
try {
    $setup_database = new SetupDatabase();
    $setup_database->run();
} catch(Exception $e) {
    echo "Fehler: Gegebenfalls 'Composer du' durchführen!".$e->getMessage();
}

