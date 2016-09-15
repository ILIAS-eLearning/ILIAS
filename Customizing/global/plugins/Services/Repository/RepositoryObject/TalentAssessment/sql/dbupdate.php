<#1>
<?php
global $ilUser;

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment/classes/Settings/ilDB.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CareerGoal/classes/Settings/ilDB.php");
$career_goal_db = new \CaT\PluginsCareerGoal\Settings\ilDB($ilDB, $ilUser);
$settings_db = new \CaT\Plugins\TalentAssessment\Settings\ilDB($ilDB, $ilUser,$career_goal_db);
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

<#4>
<?php
global $ilUser;

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment/classes/Settings/ilDB.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CareerGoal/classes/Settings/ilDB.php");
$career_goal_db = new \CaT\PluginsCareerGoal\Settings\ilDB($ilDB, $ilUser);
$settings_db = new \CaT\Plugins\TalentAssessment\Settings\ilDB($ilDB, $ilUser,$career_goal_db);
$settings_db->install();
?>