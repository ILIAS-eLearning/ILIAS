<#1>
<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ComponentProviderExample/vendor/autoload.php");

global $DIC;

$ente_db = new \CaT\Ente\ILIAS\ilProviderDB($DIC->database(), $DIC->repositoryTree(), $DIC["ilObjDataCache"], []);

$ente_db->createTables();

?>

<#2>
<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ComponentProviderExample/classes/Settings/ilDB.php");
$db = new \CaT\Plugins\ComponentProviderExample\Settings\ilDB($ilDB);
$db->install();

?>
