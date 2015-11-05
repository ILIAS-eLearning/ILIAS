<#1>
<?php

// Active Record
require_once('./Services/ActiveRecord/class.ActiveRecord.php');

// Rubric
require_once('./Services/Tracking/classes/rubric/class.ilRubricConfig.php');
rubricConfig::installDB();

// Rubric Label
require_once('./Services/Tracking/classes/rubric/class.ilRubricLabelConfig.php');
rubricLabelConfig::installDB();

// Rubric Group
require_once('./Services/Tracking/classes/rubric/class.ilRubricGroupConfig.php');
rubricGroupConfig::installDB();

// Rubric Criteria
require_once('./Services/Tracking/classes/rubric/class.ilRubricCriteriaConfig.php');
rubricCriteriaConfig::installDB();

// Rubric Behaviors
require_once('./Services/Tracking/classes/rubric/class.ilRubricBehaviorConfig.php');
rubricBehaviorConfig::installDB();

// Rubric Points
require_once('./Services/Tracking/classes/rubric/class.ilRubricPointConfig.php');
rubricPointConfig::installDB();

// Rubric Data
require_once('./Services/Tracking/classes/rubric/class.ilRubricDataConfig.php');
rubricDataConfig::installDB();

?>
