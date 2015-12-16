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

// Rubric Data
require_once('./Services/Tracking/classes/rubric/class.ilRubricDataConfig.php');
rubricDataConfig::installDB();

?>
<#2>
<?php

// Active Record
require_once('./Services/ActiveRecord/class.ActiveRecord.php');

// Remove rubric_label.weight column
if($ilDB->tableColumnExists('rubric_label','weight')){
    $ilDB->dropTableColumn('rubric_label','weight');    
}

// Remove rubric_behavior.rubric_label_id column 
if($ilDB->tableColumnExists('rubric_behavior','rubric_label_id')){
    $ilDB->dropTableColumn('rubric_behavior','rubric_label_id');    
}

// Delete any existing data
$ilDB->manipulate('truncate table rubric');
$ilDB->manipulate('truncate table rubric_behavior');
$ilDB->manipulate('truncate table rubric_behavior_seq');
$ilDB->manipulate('truncate table rubric_criteria');
$ilDB->manipulate('truncate table rubric_criteria_seq');
$ilDB->manipulate('truncate table rubric_data');
$ilDB->manipulate('truncate table rubric_data_seq');
$ilDB->manipulate('truncate table rubric_group');
$ilDB->manipulate('truncate table rubric_group_seq');
$ilDB->manipulate('truncate table rubric_label');
$ilDB->manipulate('truncate table rubric_label_seq');
$ilDB->manipulate('truncate table rubric_seq');


// Add rubric_weight table 
require_once('./Services/Tracking/classes/rubric/class.ilRubricWeightConfig.php');
rubricWeightConfig::installDB();
?>
