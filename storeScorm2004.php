<?php
require_once("Services/InitScorm2004store/classes/class.ilInitialisation.php");
$ilInit = new ilInitialisation();
$GLOBALS['ilInit'] = $ilInit;
$ilInit->initILIAS();
?>