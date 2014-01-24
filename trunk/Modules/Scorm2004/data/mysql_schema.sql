
CREATE TABLE cmi_comment(`cmi_comment_id` INTEGER PRIMARY KEY AUTO_INCREMENT, `cmi_node_id` INTEGER, `comment` TEXT, `timestamp` VARCHAR(20), `location` VARCHAR(255), `sourceIsLMS` TINYINT );
CREATE INDEX cmi_comment_id ON cmi_comment(cmi_comment_id);

CREATE INDEX cmi_id ON cmi_comment(cmi_node_id);

CREATE TABLE cmi_correct_response(`cmi_correct_response_id` INTEGER PRIMARY KEY AUTO_INCREMENT, `cmi_interaction_id` INTEGER, `pattern` VARCHAR(255) );
CREATE INDEX cmi_correct_response_id ON cmi_correct_response(cmi_correct_response_id);

CREATE INDEX cmi_correct_responsecmi_interaction_id ON cmi_correct_response(cmi_interaction_id);

CREATE TABLE cmi_interaction(`cmi_interaction_id` INTEGER PRIMARY KEY AUTO_INCREMENT, `cmi_node_id` INTEGER, `description` TEXT, `id` VARCHAR(255), `latency` VARCHAR(20), `learner_response` TEXT, `result` TEXT, `timestamp` VARCHAR(20), `type` VARCHAR(32), `weighting` REAL );
CREATE INDEX cmi_interaction_id ON cmi_interaction(cmi_interaction_id);

CREATE INDEX id ON cmi_interaction(id);

CREATE INDEX type ON cmi_interaction(type);

CREATE TABLE cmi_node(`accesscount` INTEGER, `accessduration` VARCHAR(20), `accessed` VARCHAR(20), `activityAbsoluteDuration` VARCHAR(20), `activityAttemptCount` INTEGER, `activityExperiencedDuration` VARCHAR(20), `activityProgressStatus` TINYINT, `attemptAbsoluteDuration` VARCHAR(20), `attemptCompletionAmount` REAL, `attemptCompletionStatus` TINYINT, `attemptExperiencedDuration` VARCHAR(20), `attemptProgressStatus` TINYINT, `audio_captioning` INTEGER, `audio_level` REAL, `availableChildren` VARCHAR(255), `cmi_node_id` INTEGER PRIMARY KEY AUTO_INCREMENT, `completion` REAL, `completion_status` VARCHAR(32), `completion_threshold` VARCHAR(32), `cp_node_id` INTEGER, `created` VARCHAR(20), `credit` VARCHAR(32), `delivery_speed` REAL, `exit` VARCHAR(255), `language` VARCHAR(5), `launch_data` TEXT, `learner_name` VARCHAR(255), `location` VARCHAR(255), `max` REAL, `min` REAL, `mode` VARCHAR(20), `modified` VARCHAR(20), `progress_measure` REAL, `raw` REAL, `scaled` REAL, `scaled_passing_score` REAL, `session_time` VARCHAR(20), `success_status` VARCHAR(255), `suspend_data` TEXT, `total_time` VARCHAR(20), `user_id` INTEGER );
CREATE INDEX cmi_itemcp_id ON cmi_node(cp_node_id);

CREATE INDEX completion_status ON cmi_node(completion_status);

CREATE INDEX credit ON cmi_node(credit);

CREATE INDEX id ON cmi_node(cmi_node_id);

CREATE INDEX user_id ON cmi_node(user_id);

CREATE TABLE cmi_objective(`cmi_interaction_id` INTEGER, `cmi_node_id` INTEGER, `cmi_objective_id` INTEGER PRIMARY KEY AUTO_INCREMENT, `completion_status` VARCHAR(32), `description` TEXT, `id` VARCHAR(255), `max` REAL, `min` REAL, `raw` REAL, `scaled` REAL, `progress_measure` REAL, `success_status` VARCHAR(32), `scope` VARCHAR(16) );
CREATE INDEX cmi_objective_id ON cmi_objective(cmi_objective_id);

CREATE INDEX cmi_objectivecmi_interaction_id ON cmi_objective(cmi_interaction_id);

CREATE INDEX id ON cmi_objective(id);

CREATE INDEX success_status ON cmi_objective(success_status);

CREATE TABLE cp_auxilaryResource(`auxiliaryResourceID` VARCHAR(255), `cp_node_id` INTEGER, `purpose` VARCHAR(255) );
ALTER TABLE cp_auxilaryResource ADD PRIMARY KEY(cp_node_id);

CREATE TABLE cp_condition(`condition` VARCHAR(50), `cp_node_id` INTEGER, `measureThreshold` VARCHAR(50), `operator` VARCHAR(50), `referencedObjective` VARCHAR(50) );
ALTER TABLE cp_condition ADD PRIMARY KEY(cp_node_id);

CREATE TABLE cp_dependency(`cp_node_id` INTEGER, `resourceId` VARCHAR(50) );
ALTER TABLE cp_dependency ADD PRIMARY KEY(cp_node_id);

CREATE INDEX cp_id ON cp_dependency(cp_node_id);

CREATE INDEX identifierref ON cp_dependency(resourceId);

CREATE TABLE cp_file(`cp_node_id` INTEGER, `href` VARCHAR(50) );
ALTER TABLE cp_file ADD PRIMARY KEY(cp_node_id);

CREATE INDEX cp_id ON cp_file(cp_node_id);

CREATE TABLE cp_hideLMSUI(`cp_node_id` INTEGER, `value` VARCHAR(50) );
ALTER TABLE cp_hideLMSUI ADD PRIMARY KEY(cp_node_id);

CREATE INDEX ss_sequencing_id ON cp_hideLMSUI(value);

CREATE TABLE cp_item(`completionThreshold` VARCHAR(50), `cp_node_id` INTEGER, `dataFromLMS` VARCHAR(255), `id` VARCHAR(200), `isvisible` VARCHAR(32), `parameters` VARCHAR(255), `resourceId` VARCHAR(200), `sequencingId` VARCHAR(50), `timeLimitAction` VARCHAR(30), `title` VARCHAR(255) );
ALTER TABLE cp_item ADD PRIMARY KEY(cp_node_id);

CREATE INDEX cp_itemidentifier ON cp_item(id);

CREATE INDEX ss_sequencing_id ON cp_item(sequencingId);

CREATE TABLE cp_manifest(`base` VARCHAR(200), `cp_node_id` INTEGER, `defaultOrganization` VARCHAR(50), `id` VARCHAR(200), `title` VARCHAR(255), `uri` VARCHAR(255), `version` VARCHAR(200) );
ALTER TABLE cp_manifest ADD PRIMARY KEY(cp_node_id);

CREATE INDEX identifier ON cp_manifest(id);

CREATE TABLE cp_mapinfo(`cp_node_id` INTEGER, `readNormalizedMeasure` TINYINT, `readSatisfiedStatus` TINYINT, `targetObjectiveID` VARCHAR(50), `writeNormalizedMeasure` TINYINT, `writeSatisfiedStatus` TINYINT );
ALTER TABLE cp_mapinfo ADD PRIMARY KEY(cp_node_id);

CREATE INDEX targetObjectiveId ON cp_mapinfo(targetObjectiveID);

CREATE TABLE cp_node(`cp_node_id` INTEGER PRIMARY KEY AUTO_INCREMENT, `nodeName` VARCHAR(50), `slm_id` INTEGER );
CREATE INDEX cp_id ON cp_node(cp_node_id);

CREATE INDEX nodeName ON cp_node(nodeName);

CREATE TABLE cp_objective(`cp_node_id` INTEGER, `minNormalizedMeasure` VARCHAR(50), `objectiveID` VARCHAR(200), `primary` TINYINT, `satisfiedByMeasure` TINYINT );
ALTER TABLE cp_objective ADD PRIMARY KEY(cp_node_id);

CREATE TABLE cp_organization(`cp_node_id` INTEGER, `id` VARCHAR(200), `objectivesGlobalToSystem` TINYINT, `sequencingId` VARCHAR(50), `structure` VARCHAR(200), `title` VARCHAR(255) );
ALTER TABLE cp_organization ADD PRIMARY KEY(cp_node_id);

CREATE INDEX cp_organizationid ON cp_organization(id);

CREATE INDEX ss_sequencing_id ON cp_organization(sequencingId);

CREATE TABLE cp_package(`created` VARCHAR(20), `identifier` VARCHAR(255), `jsdata` TEXT, `modified` VARCHAR(20), `obj_id` INTEGER, `persistPreviousAttempts` INTEGER, `settings` VARCHAR(255), `xmldata` TEXT );
ALTER TABLE cp_package ADD PRIMARY KEY(obj_id);

CREATE INDEX identifier ON cp_package(identifier);

CREATE TABLE cp_resource(`base` VARCHAR(200), `cp_node_id` INTEGER, `href` VARCHAR(250), `id` VARCHAR(200), `scormType` VARCHAR(32), `type` VARCHAR(30) );
ALTER TABLE cp_resource ADD PRIMARY KEY(cp_node_id);

CREATE INDEX import_id ON cp_resource(id);

CREATE TABLE cp_rule(`action` VARCHAR(50), `childActivitySet` VARCHAR(50), `conditionCombination` VARCHAR(50), `cp_node_id` INTEGER, `minimumCount` INTEGER, `minimumPercent` VARCHAR(50), `type` VARCHAR(50) );
ALTER TABLE cp_rule ADD PRIMARY KEY(cp_node_id);

CREATE TABLE cp_sequencing(`activityAbsoluteDurationLimit` VARCHAR(20), `activityExperiencedDurationLimit` VARCHAR(20), `attemptAbsoluteDurationLimit` VARCHAR(20), `attemptExperiencedDurationLimit` VARCHAR(20), `attemptLimit` INTEGER, `beginTimeLimit` VARCHAR(20), `choice` TINYINT, `choiceExit` TINYINT, `completionSetByContent` TINYINT, `constrainChoice` TINYINT, `cp_node_id` INTEGER, `endTimeLimit` VARCHAR(20), `flow` TINYINT, `forwardOnly` TINYINT, `id` VARCHAR(200), `measureSatisfactionIfActive` TINYINT, `objectiveMeasureWeight` REAL, `objectiveSetByContent` TINYINT, `preventActivation` TINYINT, `randomizationTiming` VARCHAR(50), `reorderChildren` TINYINT, `requiredForCompleted` VARCHAR(50), `requiredForIncomplete` VARCHAR(50), `requiredForNotSatisfied` VARCHAR(50), `requiredForSatisfied` VARCHAR(50), `rollupObjectiveSatisfied` TINYINT, `rollupProgressCompletion` TINYINT, `selectCount` INTEGER, `selectionTiming` VARCHAR(50), `sequencingId` VARCHAR(50), `tracked` TINYINT, `useCurrentAttemptObjectiveInfo` TINYINT, `useCurrentAttemptProgressInfo` TINYINT );
ALTER TABLE cp_sequencing ADD PRIMARY KEY(cp_node_id);

CREATE INDEX cp_sequencingid ON cp_sequencing(id);

CREATE TABLE cp_tree(`child` INTEGER, `depth` INTEGER, `lft` INTEGER, `obj_id` INTEGER, `parent` INTEGER, `rgt` INTEGER );
CREATE INDEX child ON cp_tree(child);

CREATE INDEX cp_treeobj_id ON cp_tree(obj_id);

CREATE INDEX parent ON cp_tree(parent);

CREATE TABLE object_data(`create_date` VARCHAR(20), `description` VARCHAR(128), `import_id` VARCHAR(50), `last_update` VARCHAR(20), `obj_id` INTEGER PRIMARY KEY AUTO_INCREMENT, `owner` INTEGER, `title` VARCHAR(70), `type` VARCHAR(4) );
CREATE TABLE object_reference(`obj_id` INTEGER, `ref_id` INTEGER PRIMARY KEY AUTO_INCREMENT );
CREATE INDEX object_referenceobj_id ON object_reference(obj_id);

CREATE TABLE sahs_lm(`api_adapter` VARCHAR(80), `api_func_prefix` VARCHAR(20), `auto_review` VARCHAR(1), `credit` VARCHAR(32), `default_lesson_mode` VARCHAR(32), `id` INTEGER, `online` VARCHAR(1), `type` VARCHAR(32) );
ALTER TABLE sahs_lm ADD PRIMARY KEY(id);

CREATE TABLE usr_data(`email` VARCHAR(80), `firstname` VARCHAR(32), `ilinc_id` INTEGER, `lastname` VARCHAR(32), `login` VARCHAR(80), `matriculation` VARCHAR(50), `passwd` VARCHAR(32), `title` VARCHAR(32), `usr_id` INTEGER PRIMARY KEY AUTO_INCREMENT );
CREATE TABLE lng_data(`module` VARCHAR(30), `identifier` VARCHAR(50), `lang_key` VARCHAR(2), `value` BLOB );
ALTER TABLE lng_data ADD PRIMARY KEY(module);

CREATE INDEX lng_data_module ON lng_data(module);

CREATE INDEX lng_data_lang_key ON lng_data(lang_key);

