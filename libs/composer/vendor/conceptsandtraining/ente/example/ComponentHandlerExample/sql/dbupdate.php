<#1>
<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ComponentHandlerExample/vendor/autoload.php");

global $DIC;

$ente_db = new \CaT\Ente\ILIAS\ilProviderDB($DIC->database(), $DIC->repositoryTree(), $DIC["ilObjDataCache"], []);

$ente_db->createTables();

?>
