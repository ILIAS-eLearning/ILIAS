<?php

$ilDB->query("CREATE TABLE sahs_sc13_seq_condition(" . $ilDB->quoteIdentifier('condition') . " varchar(50), " . $ilDB->quoteIdentifier('seqNodeId') . " int(11), " . $ilDB->quoteIdentifier('measureThreshold') . " varchar(50), " . $ilDB->quoteIdentifier('operator') . " varchar(50), " . $ilDB->quoteIdentifier('referencedObjective') . " varchar(50) );");
$ilDB->query("ALTER TABLE sahs_sc13_seq_condition ADD PRIMARY KEY(seqNodeId);");

$ilDB->query("CREATE TABLE sahs_sc13_seq_course(" . $ilDB->quoteIdentifier('flow') . " tinyint DEFAULT 0, " . $ilDB->quoteIdentifier('choice') . " tinyint DEFAULT 1,  " . $ilDB->quoteIdentifier('forwardonly') . " tinyint DEFAULT 0,obj_id int(11));");
$ilDB->query("ALTER TABLE sahs_sc13_seq_course ADD PRIMARY KEY(obj_id);");

$ilDB->query("CREATE TABLE sahs_sc13_seq_mapinfo(" . $ilDB->quoteIdentifier('seqNodeId') . " int(11), " . $ilDB->quoteIdentifier('readNormalizedMeasure') . " tinyint, " . $ilDB->quoteIdentifier('readSatisfiedStatus') . " tinyint, " . $ilDB->quoteIdentifier('targetObjectiveID') . " varchar(50), " . $ilDB->quoteIdentifier('writeNormalizedMeasure') . " tinyint, " . $ilDB->quoteIdentifier('writeSatisfiedStatus') . " tinyint );");
$ilDB->query("ALTER TABLE sahs_sc13_seq_mapinfo ADD PRIMARY KEY(seqNodeId);");
$ilDB->query("CREATE INDEX targetObjectiveId ON sahs_sc13_seq_mapinfo(targetObjectiveID)");

$ilDB->query("CREATE TABLE sahs_sc13_seq_node(" . $ilDB->quoteIdentifier('seqNodeId') . " int(11) PRIMARY KEY AUTO_INCREMENT, " . $ilDB->quoteIdentifier('nodeName') . " varchar(50), " . $ilDB->quoteIdentifier('tree_node_id') . " int(11) );");
$ilDB->query("CREATE INDEX seq_id ON sahs_sc13_seq_node(seqNodeId);");
$ilDB->query("CREATE INDEX tree_node_id ON sahs_sc13_seq_node(tree_node_id);");
$ilDB->query("CREATE INDEX nodeName ON sahs_sc13_seq_node(nodeName);");

$ilDB->query("CREATE TABLE sahs_sc13_seq_seqtemplate(" . $ilDB->quoteIdentifier('seqNodeId') . " int(11), " . $ilDB->quoteIdentifier('id') . " varchar(50));");
$ilDB->query("CREATE INDEX sahs_sc13_seq_template_node_id ON sahs_sc13_seq_seqtemplate(seqNodeId,id);");

$ilDB->query("CREATE TABLE sahs_sc13_seq_objective(" . $ilDB->quoteIdentifier('seqNodeId') . " int(11), " . $ilDB->quoteIdentifier('minNormalizedMeasure') . " varchar(50), " . $ilDB->quoteIdentifier('objectiveID') . " varchar(200), " . $ilDB->quoteIdentifier('primary') . " tinyint, " . $ilDB->quoteIdentifier('satisfiedByMeasure') . " tinyint );");
$ilDB->query("ALTER TABLE sahs_sc13_seq_objective ADD PRIMARY KEY(seqNodeId);");

$ilDB->query("CREATE TABLE sahs_sc13_seq_item(" . $ilDB->quoteIdentifier('importId') . " varchar(32), " . $ilDB->quoteIdentifier('seqNodeId') . " int(11), " . $ilDB->quoteIdentifier('sahs_sc13_tree_node_id') . " int, " . $ilDB->quoteIdentifier('sequencingId') . " varchar(50)," . $ilDB->quoteIdentifier('nocopy') . " tinyint," . $ilDB->quoteIdentifier('nodelete') . " tinyint," . $ilDB->quoteIdentifier('nomove') . " tinyint);");
$ilDB->query("ALTER TABLE sahs_sc13_seq_item ADD PRIMARY KEY(seqNodeId);");
$ilDB->query("CREATE INDEX sahs_sc13_tree_nodeid ON sahs_sc13_seq_item(sahs_sc13_tree_node_id);");

$ilDB->query("CREATE TABLE sahs_sc13_seq_assignment(" . $ilDB->quoteIdentifier('identifier') . " varchar(50), " . $ilDB->quoteIdentifier('sahs_sc13_tree_node_id') . " int);");
$ilDB->query("ALTER TABLE sahs_sc13_seq_assignment ADD PRIMARY KEY(sahs_sc13_tree_node_id);");

$ilDB->query("CREATE TABLE sahs_sc13_seq_templates(" . $ilDB->quoteIdentifier('identifier') . " varchar(50)," . $ilDB->quoteIdentifier('fileName') . " varchar(50)," . $ilDB->quoteIdentifier('id') . " int PRIMARY KEY AUTO_INCREMENT);");
$ilDB->query("INSERT INTO sahs_sc13_seq_templates (identifier,filename) values ('pretestpost','pretest_posttest.xml');");
$ilDB->query("INSERT INTO sahs_sc13_seq_templates (identifier,filename) values ('linearpath','linear_path.xml');");
$ilDB->query("INSERT INTO sahs_sc13_seq_templates (identifier,filename) values ('linearpathforward','linear_path_forward.xml');");


$ilDB->query("CREATE TABLE sahs_sc13_seq_rule(" . $ilDB->quoteIdentifier('action') . " varchar(50), " . $ilDB->quoteIdentifier('childActivitySet') . " varchar(50), " . $ilDB->quoteIdentifier('conditionCombination') . " varchar(50), " . $ilDB->quoteIdentifier('seqNodeId') . " int(11), " . $ilDB->quoteIdentifier('minimumCount') . " int(11), " . $ilDB->quoteIdentifier('minimumPercent') . " varchar(50), " . $ilDB->quoteIdentifier('type') . " varchar(50) );");
$ilDB->query("ALTER TABLE sahs_sc13_seq_rule ADD PRIMARY KEY(seqNodeId);");
$ilDB->query("ALTER TABLE sahs_sc13_seq_rule ADD PRIMARY KEY(seqNodeId);");

$ilDB->query("CREATE TABLE sahs_sc13_seq_sequencing(" . $ilDB->quoteIdentifier('importId') . " varchar(32), " . $ilDB->quoteIdentifier('activityAbsoluteDurationLimit') . " varchar(20), " . $ilDB->quoteIdentifier('activityExperiencedDurationLimit') . " varchar(20), " . $ilDB->quoteIdentifier('attemptAbsoluteDurationLimit') . " varchar(20), " . $ilDB->quoteIdentifier('attemptExperiencedDurationLimit') . " varchar(20), " . $ilDB->quoteIdentifier('attemptLimit') . " int(11), " . $ilDB->quoteIdentifier('beginTimeLimit') . " varchar(20), " . $ilDB->quoteIdentifier('choice') . " tinyint, " . $ilDB->quoteIdentifier('choiceExit') . " tinyint, " . $ilDB->quoteIdentifier('completionSetByContent') . " tinyint, " . $ilDB->quoteIdentifier('constrainChoice') . " tinyint, " . $ilDB->quoteIdentifier('seqNodeId') . " int(11), " . $ilDB->quoteIdentifier('endTimeLimit') . " varchar(20), " . $ilDB->quoteIdentifier('flow') . " tinyint, " . $ilDB->quoteIdentifier('forwardOnly') . " tinyint, " . $ilDB->quoteIdentifier('id') . " varchar(200), " . $ilDB->quoteIdentifier('measureSatisfactionIfActive') . " tinyint, " . $ilDB->quoteIdentifier('objectiveMeasureWeight') . " REAL, " . $ilDB->quoteIdentifier('objectiveSetByContent') . " tinyint, " . $ilDB->quoteIdentifier('preventActivation') . " tinyint, " . $ilDB->quoteIdentifier('randomizationTiming') . " varchar(50), " . $ilDB->quoteIdentifier('reorderChildren') . " tinyint, " . $ilDB->quoteIdentifier('requiredForCompleted') . " varchar(50), " . $ilDB->quoteIdentifier('requiredForIncomplete') . " varchar(50), " . $ilDB->quoteIdentifier('requiredForNotSatisfied') . " varchar(50), " . $ilDB->quoteIdentifier('requiredForSatisfied') . " varchar(50), " . $ilDB->quoteIdentifier('rollupObjectiveSatisfied') . " tinyint, " . $ilDB->quoteIdentifier('rollupProgressCompletion') . " tinyint, " . $ilDB->quoteIdentifier('selectCount') . " int(11), " . $ilDB->quoteIdentifier('selectionTiming') . " varchar(50), " . $ilDB->quoteIdentifier('sequencingId') . " varchar(50), " . $ilDB->quoteIdentifier('tracked') . " tinyint, " . $ilDB->quoteIdentifier('useCurrentAttemptObjectiveInfo') . " tinyint, " . $ilDB->quoteIdentifier('useCurrentAttemptProgressInfo') . " tinyint);");
$ilDB->query("ALTER TABLE sahs_sc13_seq_sequencing ADD PRIMARY KEY(seqNodeId);");
$ilDB->query("CREATE INDEX seq_sequencingid ON sahs_sc13_seq_sequencing(id);");

$ilDB->query("CREATE TABLE sahs_sc13_seq_tree(" . $ilDB->quoteIdentifier('child') . " int(11), " . $ilDB->quoteIdentifier('depth') . " smallint(5), " . $ilDB->quoteIdentifier('lft') . " int(11), " . $ilDB->quoteIdentifier('importid') . " varchar(32), " . $ilDB->quoteIdentifier('parent') . " int(11), " . $ilDB->quoteIdentifier('rgt') . " int(11) );");
$ilDB->query("CREATE INDEX child ON sahs_sc13_seq_tree(child);");
$ilDB->query("CREATE INDEX seq_importid_id ON sahs_sc13_seq_tree(importid);");
$ilDB->query("CREATE INDEX parent ON sahs_sc13_seq_tree(parent);");

?>