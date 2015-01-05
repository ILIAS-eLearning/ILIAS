<?php
/**
* gevImportRunner.php
*/

//settings and imports
ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);



//reset ilias for calls from somewhere else
$basedir = __DIR__; 
$basedir = str_replace('/Services/GEV/Import/classes', '', $basedir);
chdir($basedir);

//SIMPLE SEC !
//require "./Customizing/global/skin/genv/Services/GEV/simplePwdSec.php";


//context w/o user
require_once "./Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_WEB_NOAUTH);
require_once("./Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();


require_once("Services/GEV/Import/classes/class.gevUserImport.php");





$imp = new gevUserImport();
//$imp->webmode = false;

/*
$imp->fetchVFSUsers();
$imp->fetchVFSUserRoles();
$imp->fetchVFSEduRecords();

$imp->fetchGEVUsers();
$imp->fetchGEVUserRoles();
$imp->fetchGEVEduRecords();
*/


//$imp->createOrgStructure();

$imp->createOrUpdateUserAccounts();
//$imp->assignAllUserRoles(); //ROLES BEFORE ORG-UNITS
//$imp->assignAllUsersToOrgUnits();
//$imp->setUsersFromGroupExitToInactive();


$imp->importEduRecords();
$imp->fixEduRecords();



print '<br><br><hr>all through.';
?>
