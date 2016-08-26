<#1>
<?php
global $ilUser;

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment/classes/Settings/ilDB.php");
$settings_db = new \CaT\Plugins\TalentAssessment\Settings\ilDB($ilDB, $ilUser);
$settings_db->install();
?>