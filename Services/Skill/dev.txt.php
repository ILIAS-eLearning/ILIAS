Skill Management
----------------

Optes/5.1, Streamline Self Evaluation, allow objects to trigger self evaluation
===============================================================================

Point 1
-------
Migrate skl_self_eval/skl_self_eval_level content to skl_user_has_level/skl_user_skill_level

skl_self_eval (5.0):
- id (pk)
- user_id
- top_skill_id
- created
- last_update

skl_self_eval_level (5.0):
- skill_id (pk)
- tref_id (pk)
- user_id (pk)
- top_skill_id (pk)
- level_id
- last_update

skl_user_has_level (5.0):
- level_id (pk)
- user_id (pk)
- tref_id (pk)
- trigger_obj_id (pk)
- skill_id
- status_date
- trigger_ref_id
- trigger_title
- trigger_obj_type

skl_user_skill_level (5.0):
- level_id (pk)
- user_id (pk)
- tref_id (pk)
- trigger_obj_id (pk)
- skill_id
- status (not in skl_user_has_level, 1 for achieved, 0 for not achieved)
- valid (not in skl_user_has_level, alsways 1, does not seem to be used)
- status_date
- trigger_ref_id
- trigger_title
- trigger_obj_type

Issues
------
- skl_self_eval in use? (does not look like it, no entries written)
- skl_user_has_level has level_id in ok, not skill_id (should be no problem)
- new field self_eval (tinyint, pk) for skl_user_has_level and skl_user_skill_level (1! for self evals)
-- ilBasicSkill->writeUserSkillLevelStatus (added self_eval param)
-- ilBasicSkill->getMaxLevelPerType (added self_eval param)
-- ilBasicSkill->getAllLevelEntriesOfUser (added self_eval param)
-- ilBasicSkill->getMaxLevelPerObject (added self_eval param)
- trigger_obj_id will be 0, if user makes self evaluation
- trigger_ref_id will be 0
- trigger_title will be ""
- trigger_obj_type will be ""
- status_date will be set to last_update
- status -> 1 (achieved)
- valid -> 1 (valid)

Point 2
-------

General Refactoring:
- ilSkillSelfEvaluationGUI is not used anymore -> remove
- ilSelfEvaluationTableGUI is not used anymore -> remove
- ilSkillSelfEvaluation is not used anymore -> remove
- Table skl_self_eval not used anymore -> remove

Todo 4.4
========
- Nutzung von Skills von Benutzern/in Objekten im Skillmanagement sichtbar machen (done)
- Löschen von Skills verhinden, wenn von Benutzern oder OBjekten in Benutzung (done)
- Objekte sollen Nutzung "anmelden" (done)
- Skill-Template muss in 360 nutzbar sein (done)
- (tiefer) Skill Explorer inkl. Referenzen (done)
  - Modules/Survey/classes/class.ilSurveySkillExplorer.php
  -> ilSkillSelectorGUI
- historische Darstellung aller "has levels" (inkl. Datum + Objekttitel) (done)
- Resources müssen Template/Basis Kombi zuordbar sein (done)
- Skill Referenzen Editing verbieten (done)
- spider netz anzeigen (done)
- streamline draft status (done)
- outdated status (done)
- Resource Selection > neue Explorerklasse (done)
- replace ilSkillProfileAssignmentExplorer (done)

- prevent draft if items are in use
- prevent skill level deletion, if skills are in use
- show draft/outdated status of parent in settings
- ilSkillSelektorGUI: keine Basisskillreferenzen selektierbar
- make order number optional

- User Guide anpassen. (angefangen)
-- trigger dokumentieren
- self_eval flag in has_level (pk)? ->
  - self evalution in diese Tabellen übertragen
  - 360 self eval übernahmen (mit flag)

Types
=====

"skrt": Skill Root Node
"skll": Skill
"scat": Skill Category
"sctr": Skill Category Template Reference
"sktr": Skill Template Reference
"sktp": Skill Template
"sctp": Skill Category Template
 

ID Concept
==========

Common Skill ID: <skill_id>:<tref_id>
- <skill_id> of type
  - "skll" (then <tref_id> is 0)
  - "sktp" (then <tref_id> is not 0)
  - "scat" (then <tref_id> is 0)
  - "sctp" (then <tref_id> is not 0)
- <tref_id> either of type "sktr" or "sctr" or 0


Allgemeine Skill Tree ID: <skl_tree_id>:<skl_template_tree_id>
<skl_tree_id> vom Typ
  - "skrt" (dann <skl_template_tree_id> gleich 0)
  - "scat" (dann <skl_template_tree_id> gleich 0)
  - "skll" (dann <skl_template_tree_id> gleich 0)
  - "sktr"
  - "sctr" (nicht implementiert !?)
<skl_template_tree_id> entweder vom Typ "sktr" oder "sctr"
  - "sktp" ( muss unter von sctr/sktr oben referenziertem Knoten vorkommen)
  - "sctp" ( muss unter von sctr oben referenziertem Knoten vorkommen)


skl_user_skill_level ***user ilBasicSkill
- wie skl_user_has_level, kein primary key

skl_user_has_level ***user ilBasicSkill
- pk: level_id (determiniert skill_id), user_id, trigger_obj_id, tref_id

skl_personal_skill ***user ilPersonalSkill
- pk: user_id, skill_node_id
- skills sind nur im "Hauptbaum" "selectable"!

skl_assigned_material ***user ilPersonalSkill (ok)
- pk: user_id, top_skill_id, skill_id, tref_id, level_id, wsp_id
- User assignment

skl_self_eval


skl_self_eval_level ***user ilPersonalSkill + ilSkillSelfEvaluation (ok)
- pk: user_id, top_skill_id, skill_id, tref_id

skl_profile
- pk: id

skl_profile_level ***profile ilSkillProfile (ok)
- pk: profile_id, base_skill_id, tref_id

skl_skill_resource ***object ilSkillResources (ok)
- pk: base_skill_id, tref_id, rep_ref_id

skl_templ_ref
- pk: skl_node_id

skl_tree

skl_tree_node

skl_usage ***object ilSkillUsage (ok)


Non-tree classes
================

ilSkillTreeNode


ilBasicSkill is ilSkillTreeNode

Tree classes
============

ilSkillTree (classic tree class)
- Table skl_tree joins table skl_tree_node
- getSkillTreePath($a_base_skill_id, $a_tref_id = 0)

ilVirtualSkillTree
- Base class that merges the main skill tree with the template trees to one virtual tree
- uses <skl_tree_id>:<skl_template_tree_id> IDs internally

Explorer classes
=================

ilPersonalSkillExplorerGUI (external use)
- extends ilTreeExplorerGUI
- used in ilPersonalSkillsGUI
- offers selectable basic skills, refs or categories (nothing within templates)

ilVirtualSkillTreeExplorerGUI
- no instances created in Modules/Services
- only extended by
-- ilSkillSelectorGUI
-- ilSkillTreeExplorerGUI

ilSkillSelectorGUI (external use)
- extends ilVirtualSkillTreeExplorerGUI
- used in ilSurveySkillGUI (should be used in repository objects that want assign skills/levels to anything else
- lists whole virtual tree, offers basic skills (or basic skill templates with tref) for selection
  transforms into <skill_id>:<tref_id> IDs for selection

ilSkillTreeExplorerGUI (internal use)
- extends ilVirtualSkillTreeExplorerGUI
- used in ilObjSkillManagementGUI
- offers links for all nodes but stops at reference nodes

ilTemplateTreeExplorerGUI (internal use)
- extends ilTreeExplorerGUI
- used in ilObjSkillManagementGUI


Survey
======

svy_quest_skill
- pk: q_id
- fields: base_skill_id, tref_id

svy_skill_threshold
- pk: survey_id, base_skill_id, tref_id, level_id
