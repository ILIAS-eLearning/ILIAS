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
<#3>
<?php

// Rename rubric_data.behavior_comment to rubric_data.criteria_comment
$ilDB->renameTableColumn("rubric_data", "behavior_comment", "criteria_comment");

// Rename rubric_data.rubric_behavior_id to rubric_data.rubric_criteria_id
$ilDB->renameTableColumn("rubric_data", "rubric_behavior_id", "rubric_criteria_id");

// Remove rubric_data.rubric_label_id
$ilDB->dropTableColumn("rubric_data", "rubric_label_id");

// Add rubric_data.criteria_point
$ilDB->addTableColumn("rubric_data", "criteria_point", array("type" => "integer", "length" => 3));

?>
<#4>
<?php
$ilDB->modifyTableColumn('rubric_behavior', 'description',array("type" => "text", "length" => 1000));
?>
<#5>
<?php
$ilDB->addTableColumn("rubric", "locked", array("type" => "timestamp"));
?>
<#6>
<?php
$ilDB->modifyTableColumn('rubric_weight', 'weight_min',array("type" => "float"));
$ilDB->modifyTableColumn('rubric_weight', 'weight_max',array("type" => "float"));
?>
<#7>
<?php
$ilDB->modifyTableColumn('rubric_data', 'criteria_point',array("type" => "float"));
?>
<#8>
<?php
$ilDB->addTableColumn("rubric", "complete", array("type" => "boolean"));
?>
<#9>
<?php
$ilDB->addTableColumn("rubric", "grading_locked", array("type" => "timestamp"));
$ilDB->addTableColumn("rubric", "grading_locked_by", array("type" => "integer", "length" => 4));
?>
<#10>
<?php
require_once('./Services/Tracking/classes/rubric/class.ilRubricGradeHistoryConfig.php');
$rubricHistory = new rubricGradeHistoryConfig();
$rubricHistory->installDB();
?>
<#11>
<?php
// Remove rubric_data.rubric_label_id
if($ilDB->tableColumnExists('rubric','grading_locked')){
    $ilDB->dropTableColumn('rubric','grading_locked');
}

if($ilDB->tableColumnExists('rubric','grading_locked_by')){
    $ilDB->dropTableColumn('rubric','grading_locked_by');
}
//add new gradelock table after removing old locking info. (this is for unlocking/locking multiple rubrics)
require_once('./Services/Tracking/classes/rubric/class.ilRubricGradeLockConfig.php');
$rubricLock = new rubricGradeLockConfig();
$rubricLock->installDB();

?>