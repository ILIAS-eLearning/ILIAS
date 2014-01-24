<?php

$ilDB->query("CREATE TABLE sahs_sc13_seq_condition(`condition` varchar(50), `seqNodeId` int(11), `measureThreshold` varchar(50), `operator` varchar(50), `referencedObjective` varchar(50) );");
$ilDB->query("ALTER TABLE sahs_sc13_seq_condition ADD PRIMARY KEY(seqNodeId);");

$ilDB->query("CREATE TABLE sahs_sc13_seq_course(`flow` tinyint DEFAULT 0, `choice` tinyint DEFAULT 1,  `forwardonly` tinyint DEFAULT 0,obj_id int(11));");
$ilDB->query("ALTER TABLE sahs_sc13_seq_course ADD PRIMARY KEY(obj_id);");

$ilDB->query("CREATE TABLE sahs_sc13_seq_mapinfo(`seqNodeId` int(11), `readNormalizedMeasure` tinyint, `readSatisfiedStatus` tinyint, `targetObjectiveID` varchar(50), `writeNormalizedMeasure` tinyint, `writeSatisfiedStatus` tinyint );");
$ilDB->query("ALTER TABLE sahs_sc13_seq_mapinfo ADD PRIMARY KEY(seqNodeId);");
$ilDB->query("CREATE INDEX targetObjectiveId ON sahs_sc13_seq_mapinfo(targetObjectiveID)");

$ilDB->query("CREATE TABLE sahs_sc13_seq_node(`seqNodeId` int(11) PRIMARY KEY AUTO_INCREMENT, `nodeName` varchar(50), `tree_node_id` int(11) );");
$ilDB->query("CREATE INDEX seq_id ON sahs_sc13_seq_node(seqNodeId);");
$ilDB->query("CREATE INDEX tree_node_id ON sahs_sc13_seq_node(tree_node_id);");
$ilDB->query("CREATE INDEX nodeName ON sahs_sc13_seq_node(nodeName);");

$ilDB->query("CREATE TABLE sahs_sc13_seq_seqtemplate(`seqNodeId` int(11), `id` varchar(50));");
$ilDB->query("CREATE INDEX sahs_sc13_seq_template_node_id ON sahs_sc13_seq_seqtemplate(seqNodeId,id);");

$ilDB->query("CREATE TABLE sahs_sc13_seq_objective(`seqNodeId` int(11), `minNormalizedMeasure` varchar(50), `objectiveID` varchar(200), `primary` tinyint, `satisfiedByMeasure` tinyint );");
$ilDB->query("ALTER TABLE sahs_sc13_seq_objective ADD PRIMARY KEY(seqNodeId);");

$ilDB->query("CREATE TABLE sahs_sc13_seq_item(`importId` varchar(32), `seqNodeId` int(11), `sahs_sc13_tree_node_id` int, `sequencingId` varchar(50),`nocopy` tinyint,`nodelete` tinyint,`nomove` tinyint);");
$ilDB->query("ALTER TABLE sahs_sc13_seq_item ADD PRIMARY KEY(seqNodeId);");
$ilDB->query("CREATE INDEX sahs_sc13_tree_nodeid ON sahs_sc13_seq_item(sahs_sc13_tree_node_id);");

$ilDB->query("CREATE TABLE sahs_sc13_seq_assignment(`identifier` varchar(50), `sahs_sc13_tree_node_id` int);");
$ilDB->query("ALTER TABLE sahs_sc13_seq_assignment ADD PRIMARY KEY(sahs_sc13_tree_node_id);");

$ilDB->query("CREATE TABLE sahs_sc13_seq_templates(`identifier` varchar(50),`fileName` varchar(50),`id` int PRIMARY KEY AUTO_INCREMENT);");
$ilDB->query("INSERT INTO sahs_sc13_seq_templates (identifier,filename) values ('pretestpost','pretest_posttest.xml');");
$ilDB->query("INSERT INTO sahs_sc13_seq_templates (identifier,filename) values ('linearpath','linear_path.xml');");
$ilDB->query("INSERT INTO sahs_sc13_seq_templates (identifier,filename) values ('linearpathforward','linear_path_forward.xml');");


$ilDB->query("CREATE TABLE sahs_sc13_seq_rule(`action` varchar(50), `childActivitySet` varchar(50), `conditionCombination` varchar(50), `seqNodeId` int(11), `minimumCount` int(11), `minimumPercent` varchar(50), `type` varchar(50) );");
$ilDB->query("ALTER TABLE sahs_sc13_seq_rule ADD PRIMARY KEY(seqNodeId);");

$ilDB->query("CREATE TABLE sahs_sc13_seq_sequencing(`importId` varchar(32), `activityAbsoluteDurationLimit` varchar(20), `activityExperiencedDurationLimit` varchar(20), `attemptAbsoluteDurationLimit` varchar(20), `attemptExperiencedDurationLimit` varchar(20), `attemptLimit` int(11), `beginTimeLimit` varchar(20), `choice` tinyint, `choiceExit` tinyint, `completionSetByContent` tinyint, `constrainChoice` tinyint, `seqNodeId` int(11), `endTimeLimit` varchar(20), `flow` tinyint, `forwardOnly` tinyint, `id` varchar(200), `measureSatisfactionIfActive` tinyint, `objectiveMeasureWeight` REAL, `objectiveSetByContent` tinyint, `preventActivation` tinyint, `randomizationTiming` varchar(50), `reorderChildren` tinyint, `requiredForCompleted` varchar(50), `requiredForIncomplete` varchar(50), `requiredForNotSatisfied` varchar(50), `requiredForSatisfied` varchar(50), `rollupObjectiveSatisfied` tinyint, `rollupProgressCompletion` tinyint, `selectCount` int(11), `selectionTiming` varchar(50), `sequencingId` varchar(50), `tracked` tinyint, `useCurrentAttemptObjectiveInfo` tinyint, `useCurrentAttemptProgressInfo` tinyint);");
$ilDB->query("ALTER TABLE sahs_sc13_seq_sequencing ADD PRIMARY KEY(seqNodeId);");
$ilDB->query("CREATE INDEX seq_sequencingid ON sahs_sc13_seq_sequencing(id);");

$ilDB->query("CREATE TABLE sahs_sc13_seq_tree(`child` int(11), `depth` smallint(5), `lft` int(11), `importid` varchar(32), `parent` int(11), `rgt` int(11) );");
$ilDB->query("CREATE INDEX child ON sahs_sc13_seq_tree(child);");
$ilDB->query("CREATE INDEX seq_importid_id ON sahs_sc13_seq_tree(importid);");
$ilDB->query("CREATE INDEX parent ON sahs_sc13_seq_tree(parent);");

?>