
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
 
DROP TABLE "cmi_comment";
DROP TABLE "cmi_correct_response";
DROP TABLE "cmi_interaction";
DROP TABLE "cmi_node";
DROP TABLE "cmi_objective";
DROP TABLE "cp_auxilaryResource";
DROP TABLE "cp_condition";
DROP TABLE "cp_dependency";
DROP TABLE "cp_file";
DROP TABLE "cp_hideLMSUI";
DROP TABLE "cp_item";
DROP TABLE "cp_manifest";
DROP TABLE "cp_mapinfo";
DROP TABLE "cp_node";
DROP TABLE "cp_objective";
DROP TABLE "cp_organization";
DROP TABLE "cp_package";
DROP TABLE "cp_resource";
DROP TABLE "cp_rule";
DROP TABLE "cp_sequencing";
DROP TABLE "cp_tree";
DROP TABLE "object_data";
DROP TABLE "object_reference";
DROP TABLE "sahs_lm";
DROP TABLE "usr_data";

CREATE TABLE "cmi_comment" (
  "cmi_comment_id" INTEGER PRIMARY KEY,
  "cmi_node_id" INTEGER,
  "comment" TEXT,
  "date_time" REAL,
  "location" TEXT,
  "sourceIsLMS" INTEGER
);
CREATE INDEX "idx_cmi_comment_cmi_comment_id" ON "cmi_comment" ("cmi_comment_id");
CREATE INDEX "idx_cmi_comment_cmi_id" ON "cmi_comment" ("cmi_node_id");

CREATE TABLE "cmi_correct_response" (
  "cmi_correct_response_id" INTEGER PRIMARY KEY,
  "cmi_interaction_id" INTEGER,
  "pattern" TEXT
);
CREATE INDEX "idx_cmi_correct_response_cmi_correct_response_id" ON "cmi_correct_response" ("cmi_correct_response_id");
CREATE INDEX "idx_cmi_correct_response_cmi_correct_responsecmi_interaction_id" ON "cmi_correct_response" ("cmi_interaction_id");

CREATE TABLE "cmi_interaction" (
  "cmi_interaction_id" INTEGER PRIMARY KEY,
  "cmi_node_id" INTEGER,
  "description" TEXT,
  "id" TEXT,
  "latency" REAL,
  "learner_response" TEXT,
  "result" TEXT,
  "timestamp" REAL,
  "type" TEXT,
  "weighting" REAL
);
CREATE INDEX "idx_cmi_interaction_cmi_interaction_id" ON "cmi_interaction" ("cmi_interaction_id");
CREATE INDEX "idx_cmi_interaction_id" ON "cmi_interaction" ("id");
CREATE INDEX "idx_cmi_interaction_type" ON "cmi_interaction" ("type");

CREATE TABLE "cmi_node" (
  "accesscount" INTEGER,
  "accessduration" REAL,
  "accessed" REAL,
  "activityAbsoluteDuration" REAL,
  "activityAttemptCount" INTEGER,
  "activityExperiencedDuration" REAL,
  "activityProgressState" INTEGER,
  "attemptAbsoluteDuration" REAL,
  "attemptCompletionAmount" REAL,
  "attemptCompletionStatus" INTEGER,
  "attemptExperiencedDuration" REAL,
  "attemptProgressStatus" INTEGER,
  "audio_captioning" INTEGER,
  "audio_level" REAL,
  "availableChildren" TEXT,
  "cmi_node_id" INTEGER PRIMARY KEY,
  "completion" REAL,
  "completion_status" TEXT,
  "completion_threshold" REAL,
  "cp_node_id" INTEGER,
  "created" REAL,
  "credit" TEXT,
  "delivery_speed" REAL,
  "exit" TEXT,
  "language" TEXT,
  "launch_data" TEXT,
  "learner_name" TEXT,
  "location" TEXT,
  "max" REAL,
  "min" REAL,
  "mode" TEXT,
  "modified" REAL,
  "progress_measure" REAL,
  "raw" REAL,
  "scaled" REAL,
  "scaled_passing_score" REAL,
  "session_time" REAL,
  "success_status" TEXT,
  "suspend_data" TEXT,
  "total_time" REAL,
  "user_id" INTEGER
);
CREATE INDEX "idx_cmi_node_cmi_itemcp_id" ON "cmi_node" ("cp_node_id");
CREATE INDEX "idx_cmi_node_completion_status" ON "cmi_node" ("completion_status");
CREATE INDEX "idx_cmi_node_credit" ON "cmi_node" ("credit");
CREATE INDEX "idx_cmi_node_id" ON "cmi_node" ("cmi_node_id");
CREATE INDEX "idx_cmi_node_mode" ON "cmi_node" ("mode");
CREATE INDEX "idx_cmi_node_user_id" ON "cmi_node" ("user_id");

CREATE TABLE "cmi_objective" (
  "cmi_interaction_id" INTEGER,
  "cmi_node_id" INTEGER,
  "cmi_objective_id" INTEGER PRIMARY KEY,
  "completion_status" REAL,
  "description" TEXT,
  "id" TEXT,
  "max" REAL,
  "min" REAL,
  "raw" REAL,
  "scaled" REAL,
  "success_status" TEXT
);
CREATE INDEX "idx_cmi_objective_cmi_objective_id" ON "cmi_objective" ("cmi_objective_id");
CREATE INDEX "idx_cmi_objective_cmi_objectivecmi_interaction_id" ON "cmi_objective" ("cmi_interaction_id");
CREATE INDEX "idx_cmi_objective_id" ON "cmi_objective" ("id");
CREATE INDEX "idx_cmi_objective_success_status" ON "cmi_objective" ("success_status");

CREATE TABLE "cp_auxilaryResource" (
  "auxiliaryResourceID" TEXT,
  "cp_node_id" INTEGER,
  "purpose" TEXT,
  PRIMARY KEY ("cp_node_id")
);

CREATE TABLE "cp_condition" (
  "condition" TEXT,
  "cp_node_id" INTEGER,
  "measureThreshold" TEXT,
  "operator" TEXT,
  "referencedObjective" TEXT,
  PRIMARY KEY ("cp_node_id")
);

CREATE TABLE "cp_dependency" (
  "cp_node_id" INTEGER,
  "resourceId" TEXT,
  PRIMARY KEY ("cp_node_id")
);
CREATE INDEX "idx_cp_dependency_cp_id" ON "cp_dependency" ("cp_node_id");
CREATE INDEX "idx_cp_dependency_identifierref" ON "cp_dependency" ("resourceId");

CREATE TABLE "cp_file" (
  "cp_node_id" INTEGER,
  "href" TEXT,
  PRIMARY KEY ("cp_node_id")
);
CREATE INDEX "idx_cp_file_cp_id" ON "cp_file" ("cp_node_id");

CREATE TABLE "cp_hideLMSUI" (
  "cp_node_id" INTEGER,
  "value" TEXT,
  PRIMARY KEY ("cp_node_id")
);
CREATE INDEX "idx_cp_hideLMSUI_ss_sequencing_id" ON "cp_hideLMSUI" ("value");

CREATE TABLE "cp_item" (
  "completionThreshold" TEXT,
  "cp_node_id" INTEGER,
  "dataFromLMS" TEXT,
  "id" TEXT,
  "isvisible" TEXT,
  "parameters" TEXT,
  "resourceId" TEXT,
  "sequencingId" TEXT,
  "timeLimitAction" TEXT,
  "title" TEXT,
  PRIMARY KEY ("cp_node_id")
);
CREATE INDEX "idx_cp_item_cp_itemidentifier" ON "cp_item" ("id");
CREATE INDEX "idx_cp_item_ss_sequencing_id" ON "cp_item" ("sequencingId");

CREATE TABLE "cp_manifest" (
  "base" TEXT,
  "cp_node_id" INTEGER,
  "defaultOrganization" TEXT,
  "id" TEXT,
  "uri" TEXT,
  "title" TEXT,
  "version" TEXT,
  PRIMARY KEY ("cp_node_id")
);
CREATE INDEX "idx_cp_manifest_identifier" ON "cp_manifest" ("id");

CREATE TABLE "cp_mapinfo" (
  "cp_node_id" INTEGER,
  "readNormalizedMeasure" INTEGER,
  "readSatisfiedStatus" INTEGER,
  "targetObjectiveID" TEXT,
  "writeNormalizedMeasure" INTEGER,
  "writeSatisfiedStatus" INTEGER,
  PRIMARY KEY ("cp_node_id")
);
CREATE INDEX "idx_cp_mapinfo_targetObjectiveId" ON "cp_mapinfo" ("targetObjectiveID");

CREATE TABLE "cp_node" (
  "cp_node_id" INTEGER PRIMARY KEY,
  "nodeName" TEXT,
  "slm_id" INTEGER
);
CREATE INDEX "idx_cp_node_cp_id" ON "cp_node" ("cp_node_id");
CREATE INDEX "idx_cp_node_nodeName" ON "cp_node" ("nodeName");

CREATE TABLE "cp_objective" (
  "cp_node_id" INTEGER,
  "minNormalizedMeasure" TEXT,
  "objectiveID" TEXT,
  "primary" INTEGER,
  "satisfiedByMeasure" INTEGER,
  PRIMARY KEY ("cp_node_id")
);

CREATE TABLE "cp_organization" (
  "cp_node_id" INTEGER,
  "id" TEXT,
  "objectivesGlobalToSystem" INTEGER,
  "sequencingId" TEXT,
  "structure" TEXT,
  "title" TEXT,
  PRIMARY KEY ("cp_node_id")
);
CREATE INDEX "idx_cp_organization_ss_sequencing_id" ON "cp_organization" ("sequencingId");

CREATE TABLE "cp_package" (
  "identifier" TEXT,
  "obj_id" INTEGER,
  "persistPreviousAttempts" INTEGER,
  "settings" TEXT,
  PRIMARY KEY ("obj_id")
);
CREATE UNIQUE INDEX "idx_cp_package_identifier" ON "cp_package" ("identifier");

CREATE TABLE "cp_resource" (
  "base" TEXT,
  "cp_node_id" INTEGER,
  "href" TEXT,
  "id" TEXT,
  "scormType" TEXT,
  "type" TEXT,
  PRIMARY KEY ("cp_node_id")
);
CREATE INDEX "idx_cp_resource_import_id" ON "cp_resource" ("id");

CREATE TABLE "cp_rule" (
  "action" TEXT,
  "childActivitySet" TEXT,
  "conditionCombination" TEXT,
  "cp_node_id" INTEGER,
  "minimumCount" INTEGER,
  "minimumPercent" TEXT,
  "type" TEXT,
  PRIMARY KEY ("cp_node_id")
);

CREATE TABLE "cp_sequencing" (
  "activityAbsoluteDurationLimit" REAL,
  "activityExperiencedDurationLimit" REAL,
  "attemptAbsoluteDurationLimit" REAL,
  "attemptExperiencedDurationLimit" REAL,
  "attemptLimit" INTEGER,
  "beginTimeLimit" REAL,
  "choice" INTEGER,
  "choiceExit" INTEGER,
  "completionSetByContent" INTEGER,
  "constrainChoice" INTEGER,
  "cp_node_id" INTEGER,
  "endTimeLimit" REAL,
  "flow" INTEGER,
  "forwardOnly" INTEGER,
  "id" TEXT,
  "measureSatisfactionIfActive" INTEGER,
  "objectiveMeasureWeight" REAL,
  "objectiveSetByContent" INTEGER,
  "preventActivation" INTEGER,
  "randomizationTiming" TEXT,
  "reorderChildren" INTEGER,
  "requiredForCompleted" TEXT,
  "requiredForIncomplete" TEXT,
  "requiredForNotSatisfied" TEXT,
  "requiredForSatisfied" TEXT,
  "rollupObjectiveSatisfied" INTEGER,
  "rollupProgressCompletion" INTEGER,
  "selectCount" INTEGER,
  "selectionTiming" TEXT,
  "tracked" INTEGER,
  "useCurrentAttemptObjectiveInfo" INTEGER,
  "useCurrentAttemptProgressInfo" INTEGER,
  PRIMARY KEY ("cp_node_id")
);

CREATE TABLE "cp_tree" (
  "child" INTEGER,
  "depth" INTEGER,
  "lft" INTEGER,
  "obj_id" INTEGER,
  "parent" INTEGER,
  "rgt" INTEGER
);
CREATE INDEX "idx_cp_tree_child" ON "cp_tree" ("child");
CREATE INDEX "idx_cp_tree_cp_treeobj_id" ON "cp_tree" ("obj_id");
CREATE INDEX "idx_cp_tree_parent" ON "cp_tree" ("parent");

CREATE TABLE "object_data" (
  "create_date" REAL,
  "description" TEXT,
  "import_id" TEXT,
  "last_update" REAL,
  "obj_id" INTEGER PRIMARY KEY,
  "owner" INTEGER,
  "title" TEXT,
  "type" TEXT
);

CREATE TABLE "object_reference" (
  "obj_id" INTEGER,
  "ref_id" INTEGER PRIMARY KEY
);
CREATE INDEX "idx_object_reference_object_referenceobj_id" ON "object_reference" ("obj_id");

CREATE TABLE "sahs_lm" (
  "api_adapter" TEXT,
  "api_func_prefix" TEXT,
  "auto_review" TEXT,
  "credit" TEXT,
  "default_lesson_mode" TEXT,
  "id" INTEGER,
  "online" TEXT,
  "type" TEXT,
  PRIMARY KEY ("id")
);

CREATE TABLE "usr_data" (
  "email" TEXT,
  "firstname" TEXT,
  "ilinc_id" INTEGER,
  "lastname" TEXT,
  "login" TEXT,
  "matriculation" TEXT,
  "passwd" TEXT,
  "title" TEXT,
  "usr_id" INTEGER PRIMARY KEY
);

INSERT INTO "sahs_lm" ("id", "type") 
	VALUES (100, 'SCORM 2004');
	
INSERT INTO "usr_data" ("usr_id", "firstname", "lastname", "email", "login", "passwd", "title") 
	VALUES (50, 'Achilles', 'Peleus', 'Achilles.Peleus@localhost', 'achilleus', 'peleus', '');

