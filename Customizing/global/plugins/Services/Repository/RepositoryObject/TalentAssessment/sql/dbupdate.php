<#1>
<?php
global $ilUser;

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment/classes/Settings/ilDB.php");
$settings_db = new \CaT\Plugins\TalentAssessment\Settings\ilDB($ilDB, $ilUser);
$settings_db->install();
?>

<#2>
<?php
global $ilUser;

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment/classes/Observator/ilDB.php");
$settings_db = new \CaT\Plugins\TalentAssessment\Observator\ilDB($ilDB, $ilUser);
$settings_db->createLocalRoleTemplate(\CaT\Plugins\TalentAssessment\ilActions::OBSERVATOR_ROLE_NAME,"");
?>

<#3>
<?php
global $ilUser;
$b = new \CaT\Plugins\CareerGoal\Observations\ilDB($ilDB, $ilUser);
$settings_db = new \CaT\Plugins\TalentAssessment\Observations\ilDB($ilDB, $ilUser, $b);
$settings_db->install();
?>