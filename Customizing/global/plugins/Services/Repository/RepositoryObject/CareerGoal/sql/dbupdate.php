<#1>
<?php
global $ilUser;

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CareerGoal/classes/Settings/ilDB.php");
$settings_db = new \CaT\Plugins\CareerGoal\Settings\ilDB($ilDB, $ilUser);
$settings_db->install();
?>

<#2>
<?php
global $ilUser;

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CareerGoal/classes/Requirements/ilDB.php");
$settings_db = new \CaT\Plugins\CareerGoal\Requirements\ilDB($ilDB, $ilUser);
$settings_db->install();
?>

<#3>
<?php
global $ilUser;

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CareerGoal/classes/Observations/ilDB.php");
$settings_db = new \CaT\Plugins\CareerGoal\Observations\ilDB($ilDB, $ilUser);
$settings_db->install();
?>