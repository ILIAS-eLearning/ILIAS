<?php
/**
* gevImportRunner.php
*/

die('not yet');


//settings and imports
ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);



//reset ilias for calls from somewhere else
$basedir = __DIR__; 
$basedir = str_replace('/Services/GEV/Import/classes', '', $basedir);
chdir($basedir);

//SIMPLE SEC !
//require "simplePwdSec.php";


//context w/o user
require_once "./Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_WEB_NOAUTH);
require_once("./Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();


/*
require_once("Services/GEV/Import/classes/class.gevUserImport.php");
$imp = new gevUserImport();
$imp->webmode = false;
*/


/*
$imp->fetchVFSUsers();
$imp->fetchVFSUserRoles();
$imp->fetchVFSEduRecords();

$imp->fetchGEVUsers();
$imp->fetchGEVUserRoles();
$imp->fetchGEVEduRecords();
*/


//$imp->createOrgStructure();

//$imp->createOrUpdateUserAccounts();
//$imp->assignAllUserRoles(); //ROLES BEFORE ORG-UNITS
//$imp->assignAllUsersToOrgUnits();

//$imp->setUsersFromGroupExitToInactive();


//$imp->importEduRecords();
//$imp->fixEduRecords();

//$imp->reassignMiZsForExitUsers();
//$imp->switchHA84FromSuperiorToEmployee();
//$imp->reassignGEV_AVL();
//$imp->setHistoryUsersToActive();
//$imp->importCertificates();

//$imp->fixCertificationPeriodFromBWVId();
//$imp->fixVFSTPService();
//$imp->fixNAAssignment();





require_once("Services/GEV/Import/classes/class.gevQuestionImport.php");
$imp = new gevQuestionImport();
$imp->webmode = false;

$imp->data_directory = '/home/ildata/ilclientGenerali2/ilclientGenerali2/question_import';

$imp->importPools();



print '<br><br><hr>all through.';
?>
