<#1>
<?php
global $ilUser;

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CareerGoal/classes/Settings/ilDB.php");
$settings_db = new ilDB($ilDB, $ilUser);
$settings_db->install();
?>