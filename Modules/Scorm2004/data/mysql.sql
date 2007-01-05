
--- ILIAS Open Source
--- --------------------------------
--- Implementation of ADL SCORM 2004
--- 
--- Copyright (c) 2005-2007 Alfred Kohnert.
--- 
--- This program is free software. The use and distribution terms for this software
--- are covered by the GNU General Public License Version 2
--- 	<http://opensource.org/licenses/gpl-license.php>.
--- By using this software in any fashion, you are agreeing to be bound by the terms 
--- of this license.
--- 
--- You must not remove this notice, or any other, from this software.

--- PRELIMINARY EDITION 
--- This is work in progress and therefore incomplete and buggy ... 
 

DROP TABLE IF EXISTS `cmi_comment`;
DROP TABLE IF EXISTS `cmi_correct_response`;
DROP TABLE IF EXISTS `cmi_interaction`;
DROP TABLE IF EXISTS `cmi_node`;
DROP TABLE IF EXISTS `cmi_objective`;
DROP TABLE IF EXISTS `cp_auxilaryResource`;
DROP TABLE IF EXISTS `cp_condition`;
DROP TABLE IF EXISTS `cp_dependency`;
DROP TABLE IF EXISTS `cp_file`;
DROP TABLE IF EXISTS `cp_hideLMSUI`;
DROP TABLE IF EXISTS `cp_item`;
DROP TABLE IF EXISTS `cp_manifest`;
DROP TABLE IF EXISTS `cp_mapinfo`;
DROP TABLE IF EXISTS `cp_node`;
DROP TABLE IF EXISTS `cp_objective`;
DROP TABLE IF EXISTS `cp_organization`;
DROP TABLE IF EXISTS `cp_package`;
DROP TABLE IF EXISTS `cp_resource`;
DROP TABLE IF EXISTS `cp_rule`;
DROP TABLE IF EXISTS `cp_sequencing`;
DROP TABLE IF EXISTS `cp_tree`;
DROP TABLE IF EXISTS `object_data`;
DROP TABLE IF EXISTS `object_reference`;
DROP TABLE IF EXISTS `sahs_lm`;
DROP TABLE IF EXISTS `usr_data`;

CREATE TABLE `cmi_comment` (
  `cmi_comment_id` TINYINT NOT NULL DEFAULT '0' AUTO_INCREMENT,
  `cmi_node_id` TINYINT NOT NULL DEFAULT '0',
  `comment` TEXT DEFAULT '',
  `date_time` FLOAT NOT NULL DEFAULT '0',
  `location` TEXT DEFAULT '',
  `sourceIsLMS` TINYINT NOT NULL DEFAULT 'false',
  PRIMARY KEY (`cmi_comment_id`)
);
CREATE INDEX `idx_cmi_comment_idx_cmi_comment_cmi_id` ON `cmi_comment` (`cmi_node_id`);
CREATE INDEX `idx_cmi_comment_idx_cmi_comment_cmi_comment_id` ON `cmi_comment` (`cmi_comment_id`);

CREATE TABLE `cmi_correct_response` (
  `cmi_correct_response_id` TINYINT NOT NULL DEFAULT '0' AUTO_INCREMENT,
  `cmi_interaction_id` TINYINT NOT NULL DEFAULT '0',
  `pattern` TEXT DEFAULT '',
  PRIMARY KEY (`cmi_correct_response_id`)
);
CREATE INDEX `idx_cmi_correct_response_idx_cmi_correct_response_cmi_correct_responsecmi_interaction_id` ON `cmi_correct_response` (`cmi_interaction_id`);
CREATE INDEX `idx_cmi_correct_response_idx_cmi_correct_response_cmi_correct_response_id` ON `cmi_correct_response` (`cmi_correct_response_id`);

CREATE TABLE `cmi_interaction` (
  `cmi_interaction_id` TINYINT NOT NULL DEFAULT '0' AUTO_INCREMENT,
  `cmi_node_id` TINYINT NOT NULL DEFAULT '0',
  `description` TEXT DEFAULT '',
  `id` TEXT DEFAULT '',
  `latency` FLOAT NOT NULL DEFAULT '0',
  `learner_response` TEXT DEFAULT '',
  `result` TEXT DEFAULT '',
  `timestamp` FLOAT NOT NULL DEFAULT '0',
  `type` TEXT DEFAULT '',
  `weighting` FLOAT NOT NULL DEFAULT '0',
  PRIMARY KEY (`cmi_interaction_id`)
);
CREATE INDEX `idx_cmi_interaction_idx_cmi_interaction_type` ON `cmi_interaction` (`type`);
CREATE INDEX `idx_cmi_interaction_idx_cmi_interaction_id` ON `cmi_interaction` (`id`);
CREATE INDEX `idx_cmi_interaction_idx_cmi_interaction_cmi_interaction_id` ON `cmi_interaction` (`cmi_interaction_id`);

CREATE TABLE `cmi_node` (
  `accesscount` TINYINT NOT NULL DEFAULT '0',
  `accessduration` FLOAT NOT NULL DEFAULT '0',
  `accessed` FLOAT NOT NULL DEFAULT '0',
  `activityAbsoluteDuration` FLOAT NOT NULL DEFAULT '0',
  `activityAttemptCount` TINYINT NOT NULL DEFAULT '0',
  `activityExperiencedDuration` FLOAT NOT NULL DEFAULT '0',
  `activityProgressState` TINYINT NOT NULL DEFAULT 'false',
  `attemptAbsoluteDuration` FLOAT NOT NULL DEFAULT '0',
  `attemptCompletionAmount` FLOAT NOT NULL DEFAULT '0',
  `attemptCompletionStatus` TINYINT NOT NULL DEFAULT 'false',
  `attemptExperiencedDuration` FLOAT NOT NULL DEFAULT '0',
  `attemptProgressStatus` TINYINT NOT NULL DEFAULT 'false',
  `audio_captioning` TINYINT NOT NULL DEFAULT '0',
  `audio_level` FLOAT NOT NULL DEFAULT '0',
  `availableChildren` TEXT DEFAULT '',
  `cmi_node_id` TINYINT NOT NULL DEFAULT '0' AUTO_INCREMENT,
  `completion` FLOAT NOT NULL DEFAULT '0',
  `completion_status` TEXT DEFAULT '',
  `completion_threshold` FLOAT NOT NULL DEFAULT '0',
  `cp_node_id` TINYINT NOT NULL DEFAULT '0',
  `created` FLOAT NOT NULL DEFAULT '0',
  `credit` TEXT DEFAULT '',
  `delivery_speed` FLOAT NOT NULL DEFAULT '0',
  `exit` TEXT DEFAULT '',
  `language` TEXT DEFAULT '',
  `launch_data` TEXT DEFAULT '',
  `learner_name` TEXT DEFAULT '',
  `location` TEXT DEFAULT '',
  `max` FLOAT NOT NULL DEFAULT '0',
  `min` FLOAT NOT NULL DEFAULT '0',
  `mode` TEXT DEFAULT '',
  `modified` FLOAT NOT NULL DEFAULT '0',
  `progress_measure` FLOAT NOT NULL DEFAULT '0',
  `raw` FLOAT NOT NULL DEFAULT '0',
  `scaled` FLOAT NOT NULL DEFAULT '0',
  `scaled_passing_score` FLOAT NOT NULL DEFAULT '0',
  `session_time` FLOAT NOT NULL DEFAULT '0',
  `success_status` TEXT DEFAULT '',
  `suspend_data` TEXT DEFAULT '',
  `total_time` FLOAT NOT NULL DEFAULT '0',
  `user_id` TINYINT NOT NULL DEFAULT '0',
  PRIMARY KEY (`cmi_node_id`)
);
CREATE INDEX `idx_cmi_node_idx_cmi_node_user_id` ON `cmi_node` (`user_id`);
CREATE INDEX `idx_cmi_node_idx_cmi_node_mode` ON `cmi_node` (`mode`);
CREATE INDEX `idx_cmi_node_idx_cmi_node_id` ON `cmi_node` (`cmi_node_id`);
CREATE INDEX `idx_cmi_node_idx_cmi_node_credit` ON `cmi_node` (`credit`);
CREATE INDEX `idx_cmi_node_idx_cmi_node_completion_status` ON `cmi_node` (`completion_status`);
CREATE INDEX `idx_cmi_node_idx_cmi_node_cmi_itemcp_id` ON `cmi_node` (`cp_node_id`);

CREATE TABLE `cmi_objective` (
  `cmi_interaction_id` TINYINT NOT NULL DEFAULT '0',
  `cmi_node_id` TINYINT NOT NULL DEFAULT '0',
  `cmi_objective_id` TINYINT NOT NULL DEFAULT '0' AUTO_INCREMENT,
  `completion_status` FLOAT NOT NULL DEFAULT '0',
  `description` TEXT DEFAULT '',
  `id` TEXT DEFAULT '',
  `max` FLOAT NOT NULL DEFAULT '0',
  `min` FLOAT NOT NULL DEFAULT '0',
  `raw` FLOAT NOT NULL DEFAULT '0',
  `scaled` FLOAT NOT NULL DEFAULT '0',
  `success_status` TEXT DEFAULT '',
  PRIMARY KEY (`cmi_objective_id`)
);
CREATE INDEX `idx_cmi_objective_idx_cmi_objective_success_status` ON `cmi_objective` (`success_status`);
CREATE INDEX `idx_cmi_objective_idx_cmi_objective_id` ON `cmi_objective` (`id`);
CREATE INDEX `idx_cmi_objective_idx_cmi_objective_cmi_objectivecmi_interaction_id` ON `cmi_objective` (`cmi_interaction_id`);
CREATE INDEX `idx_cmi_objective_idx_cmi_objective_cmi_objective_id` ON `cmi_objective` (`cmi_objective_id`);

CREATE TABLE `cp_auxilaryResource` (
  `auxiliaryResourceID` TEXT DEFAULT '',
  `cp_node_id` TINYINT NOT NULL DEFAULT '0' AUTO_INCREMENT,
  `purpose` TEXT DEFAULT '',
  PRIMARY KEY (`cp_node_id`)
);

CREATE TABLE `cp_condition` (
  `condition` TEXT DEFAULT '',
  `cp_node_id` TINYINT NOT NULL DEFAULT '0' AUTO_INCREMENT,
  `measureThreshold` TEXT DEFAULT '',
  `operator` TEXT DEFAULT '',
  `referencedObjective` TEXT DEFAULT '',
  PRIMARY KEY (`cp_node_id`)
);

CREATE TABLE `cp_dependency` (
  `cp_node_id` TINYINT NOT NULL DEFAULT '0' AUTO_INCREMENT,
  `resourceId` TEXT DEFAULT '',
  PRIMARY KEY (`cp_node_id`)
);
CREATE INDEX `idx_cp_dependency_idx_cp_dependency_identifierref` ON `cp_dependency` (`resourceId`);
CREATE INDEX `idx_cp_dependency_idx_cp_dependency_cp_id` ON `cp_dependency` (`cp_node_id`);

CREATE TABLE `cp_file` (
  `cp_node_id` TINYINT NOT NULL DEFAULT '0' AUTO_INCREMENT,
  `href` TEXT DEFAULT '',
  PRIMARY KEY (`cp_node_id`)
);
CREATE INDEX `idx_cp_file_idx_cp_file_cp_id` ON `cp_file` (`cp_node_id`);

CREATE TABLE `cp_hideLMSUI` (
  `cp_node_id` TINYINT NOT NULL DEFAULT '0' AUTO_INCREMENT,
  `value` TEXT NOT NULL DEFAULT '',
  PRIMARY KEY (`cp_node_id`)
);
CREATE INDEX `idx_cp_hideLMSUI_idx_cp_hideLMSUI_ss_sequencing_id` ON `cp_hideLMSUI` (`value`);

CREATE TABLE `cp_item` (
  `completionThreshold` TEXT DEFAULT '',
  `cp_node_id` TINYINT NOT NULL DEFAULT '0' AUTO_INCREMENT,
  `dataFromLMS` TEXT DEFAULT '',
  `id` TEXT DEFAULT '',
  `isvisible` TEXT DEFAULT '',
  `parameters` TEXT DEFAULT '',
  `resourceId` TEXT DEFAULT '',
  `sequencingId` TEXT DEFAULT '',
  `timeLimitAction` TEXT DEFAULT '',
  `title` TEXT DEFAULT '',
  PRIMARY KEY (`cp_node_id`)
);
CREATE INDEX `idx_cp_item_idx_cp_item_ss_sequencing_id` ON `cp_item` (`sequencingId`);
CREATE INDEX `idx_cp_item_idx_cp_item_cp_itemidentifier` ON `cp_item` (`id`);

CREATE TABLE `cp_manifest` (
  `base` TEXT DEFAULT '',
  `cp_node_id` TINYINT NOT NULL DEFAULT '0' AUTO_INCREMENT,
  `defaultOrganization` TEXT DEFAULT '',
  `id` TEXT DEFAULT '',
  `title` TEXT DEFAULT '',
  `uri` TEXT DEFAULT '',
  `version` TEXT DEFAULT '',
  PRIMARY KEY (`cp_node_id`)
);
CREATE INDEX `idx_cp_manifest_idx_cp_manifest_identifier` ON `cp_manifest` (`id`);

CREATE TABLE `cp_mapinfo` (
  `cp_node_id` TINYINT NOT NULL DEFAULT '0' AUTO_INCREMENT,
  `readNormalizedMeasure` TINYINT NOT NULL DEFAULT 'false',
  `readSatisfiedStatus` TINYINT NOT NULL DEFAULT 'false',
  `targetObjectiveID` TEXT DEFAULT '',
  `writeNormalizedMeasure` TINYINT NOT NULL DEFAULT 'false',
  `writeSatisfiedStatus` TINYINT NOT NULL DEFAULT 'false',
  PRIMARY KEY (`cp_node_id`)
);
CREATE INDEX `idx_cp_mapinfo_idx_cp_mapinfo_targetObjectiveId` ON `cp_mapinfo` (`targetObjectiveID`);

CREATE TABLE `cp_node` (
  `cp_node_id` TINYINT NOT NULL DEFAULT '0' AUTO_INCREMENT,
  `nodeName` TEXT DEFAULT '',
  `slm_id` TINYINT NOT NULL DEFAULT '0',
  PRIMARY KEY (`cp_node_id`)
);
CREATE INDEX `idx_cp_node_idx_cp_node_nodeName` ON `cp_node` (`nodeName`);
CREATE INDEX `idx_cp_node_idx_cp_node_cp_id` ON `cp_node` (`cp_node_id`);

CREATE TABLE `cp_objective` (
  `cp_node_id` TINYINT NOT NULL DEFAULT '0' AUTO_INCREMENT,
  `minNormalizedMeasure` TEXT DEFAULT '',
  `objectiveID` TEXT DEFAULT '',
  `primary` TINYINT NOT NULL DEFAULT 'false',
  `satisfiedByMeasure` TINYINT NOT NULL DEFAULT 'false',
  PRIMARY KEY (`cp_node_id`)
);

CREATE TABLE `cp_organization` (
  `cp_node_id` TINYINT NOT NULL DEFAULT '0' AUTO_INCREMENT,
  `id` TEXT DEFAULT '',
  `objectivesGlobalToSystem` TINYINT NOT NULL DEFAULT 'false',
  `sequencingId` TEXT DEFAULT '',
  `structure` TEXT DEFAULT '',
  `title` TEXT DEFAULT '',
  PRIMARY KEY (`cp_node_id`)
);
CREATE INDEX `idx_cp_organization_idx_cp_organization_ss_sequencing_id` ON `cp_organization` (`sequencingId`);
CREATE INDEX `idx_cp_organization_idx_cp_organization_cp_organizationid` ON `cp_organization` (`id`);

CREATE TABLE `cp_package` (
  `created` FLOAT NOT NULL DEFAULT '0',
  `identifier` TEXT DEFAULT '',
  `jsdata` TEXT DEFAULT '',
  `modified` FLOAT NOT NULL DEFAULT '0',
  `obj_id` TINYINT NOT NULL DEFAULT '0' AUTO_INCREMENT,
  `persistPreviousAttempts` TINYINT NOT NULL DEFAULT '0',
  `settings` TEXT DEFAULT '',
  `xmldata` TEXT DEFAULT '',
  PRIMARY KEY (`obj_id`)
);
CREATE UNIQUE INDEX `idx_cp_package_idx_cp_package_identifier` ON `cp_package` (`identifier`);

CREATE TABLE `cp_resource` (
  `base` TEXT DEFAULT '',
  `cp_node_id` TINYINT NOT NULL DEFAULT '0' AUTO_INCREMENT,
  `href` TEXT DEFAULT '',
  `id` TEXT DEFAULT '',
  `scormType` TEXT DEFAULT '',
  `type` TEXT DEFAULT '',
  PRIMARY KEY (`cp_node_id`)
);
CREATE INDEX `idx_cp_resource_idx_cp_resource_import_id` ON `cp_resource` (`id`);

CREATE TABLE `cp_rule` (
  `action` TEXT DEFAULT '',
  `childActivitySet` TEXT DEFAULT '',
  `conditionCombination` TEXT DEFAULT '',
  `cp_node_id` TINYINT NOT NULL DEFAULT '0' AUTO_INCREMENT,
  `minimumCount` TINYINT NOT NULL DEFAULT '0',
  `minimumPercent` TEXT DEFAULT '',
  `type` TEXT DEFAULT '',
  PRIMARY KEY (`cp_node_id`)
);

CREATE TABLE `cp_sequencing` (
  `activityAbsoluteDurationLimit` FLOAT NOT NULL DEFAULT '0',
  `activityExperiencedDurationLimit` FLOAT NOT NULL DEFAULT '0',
  `attemptAbsoluteDurationLimit` FLOAT NOT NULL DEFAULT '0',
  `attemptExperiencedDurationLimit` FLOAT NOT NULL DEFAULT '0',
  `attemptLimit` TINYINT NOT NULL DEFAULT '0',
  `beginTimeLimit` FLOAT NOT NULL DEFAULT '0',
  `choice` TINYINT NOT NULL DEFAULT 'false',
  `choiceExit` TINYINT NOT NULL DEFAULT 'false',
  `completionSetByContent` TINYINT NOT NULL DEFAULT 'false',
  `constrainChoice` TINYINT NOT NULL DEFAULT 'false',
  `cp_node_id` TINYINT NOT NULL DEFAULT '0' AUTO_INCREMENT,
  `endTimeLimit` FLOAT NOT NULL DEFAULT '0',
  `flow` TINYINT NOT NULL DEFAULT 'false',
  `forwardOnly` TINYINT NOT NULL DEFAULT 'false',
  `id` TEXT DEFAULT '',
  `measureSatisfactionIfActive` TINYINT NOT NULL DEFAULT 'false',
  `objectiveMeasureWeight` FLOAT NOT NULL DEFAULT '0',
  `objectiveSetByContent` TINYINT NOT NULL DEFAULT 'false',
  `preventActivation` TINYINT NOT NULL DEFAULT 'false',
  `randomizationTiming` TEXT DEFAULT '',
  `reorderChildren` TINYINT NOT NULL DEFAULT 'false',
  `requiredForCompleted` TEXT DEFAULT '',
  `requiredForIncomplete` TEXT DEFAULT '',
  `requiredForNotSatisfied` TEXT DEFAULT '',
  `requiredForSatisfied` TEXT DEFAULT '',
  `rollupObjectiveSatisfied` TINYINT NOT NULL DEFAULT 'false',
  `rollupProgressCompletion` TINYINT NOT NULL DEFAULT 'false',
  `selectCount` TINYINT NOT NULL DEFAULT '0',
  `selectionTiming` TEXT DEFAULT '',
  `tracked` TINYINT NOT NULL DEFAULT 'false',
  `useCurrentAttemptObjectiveInfo` TINYINT NOT NULL DEFAULT 'false',
  `useCurrentAttemptProgressInfo` TINYINT NOT NULL DEFAULT 'false',
  PRIMARY KEY (`cp_node_id`)
);
CREATE INDEX `idx_cp_sequencing_idx_cp_sequencing_cp_sequencingid` ON `cp_sequencing` (`id`);

CREATE TABLE `cp_tree` (
  `child` TINYINT NOT NULL DEFAULT '0',
  `depth` TINYINT NOT NULL DEFAULT '0',
  `lft` TINYINT NOT NULL DEFAULT '0',
  `obj_id` TINYINT NOT NULL DEFAULT '0',
  `parent` TINYINT NOT NULL DEFAULT '0',
  `rgt` TINYINT NOT NULL DEFAULT '0'
);
CREATE INDEX `idx_cp_tree_idx_cp_tree_parent` ON `cp_tree` (`parent`);
CREATE INDEX `idx_cp_tree_idx_cp_tree_cp_treeobj_id` ON `cp_tree` (`obj_id`);
CREATE INDEX `idx_cp_tree_idx_cp_tree_child` ON `cp_tree` (`child`);

CREATE TABLE `object_data` (
  `create_date` FLOAT NOT NULL DEFAULT '0',
  `description` TEXT NOT NULL DEFAULT '',
  `import_id` TEXT NOT NULL DEFAULT '',
  `last_update` FLOAT NOT NULL DEFAULT '0',
  `obj_id` TINYINT NOT NULL DEFAULT '0' AUTO_INCREMENT,
  `owner` TINYINT NOT NULL DEFAULT '0',
  `title` TEXT NOT NULL DEFAULT '',
  `type` TEXT NOT NULL DEFAULT '',
  PRIMARY KEY (`obj_id`)
);

CREATE TABLE `object_reference` (
  `obj_id` TINYINT NOT NULL DEFAULT '0',
  `ref_id` TINYINT NOT NULL DEFAULT '0' AUTO_INCREMENT,
  PRIMARY KEY (`ref_id`)
);
CREATE INDEX `idx_object_reference_idx_object_reference_object_referenceobj_id` ON `object_reference` (`obj_id`);

CREATE TABLE `sahs_lm` (
  `api_adapter` TEXT DEFAULT '',
  `api_func_prefix` TEXT DEFAULT '',
  `auto_review` TEXT DEFAULT '',
  `credit` TEXT DEFAULT '',
  `default_lesson_mode` TEXT DEFAULT '',
  `id` TINYINT NOT NULL DEFAULT '0' AUTO_INCREMENT,
  `online` TEXT DEFAULT '',
  `type` TEXT DEFAULT '',
  PRIMARY KEY (`id`)
);

CREATE TABLE `usr_data` (
  `email` TEXT NOT NULL DEFAULT '',
  `firstname` TEXT NOT NULL DEFAULT '',
  `ilinc_id` TINYINT NOT NULL DEFAULT '0',
  `lastname` TEXT NOT NULL DEFAULT '',
  `login` TEXT NOT NULL DEFAULT '',
  `matriculation` TEXT DEFAULT '',
  `passwd` TEXT NOT NULL DEFAULT '',
  `title` TEXT NOT NULL DEFAULT '',
  `usr_id` TINYINT NOT NULL DEFAULT '0' AUTO_INCREMENT,
  PRIMARY KEY (`usr_id`)
);

INSERT INTO `sahs_lm` (`id`, `type`) 
	VALUES (100, 'SCORM 2004');
	
INSERT INTO `usr_data` (`usr_id`, `firstname`, `lastname`, `email`, `login`, `passwd`, `title`) 
	VALUES (50, 'Achilles', 'Peleus', 'Achilles.Peleus@localhost', 'achilleus', 'peleus', '');

