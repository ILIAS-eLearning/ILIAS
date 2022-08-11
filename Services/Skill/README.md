# Skill aka Competence Service

This README is work in progress.

# API

WIP

# General Documentation

This section documents the general concepts and structures of the Skill Service. These are internal implementations which SHOULD not be used outside of this module unless mentioned in the API section of this README.

**Skills vs Competences**

In ILIAS both terms are synonyms. In the UI we started with the "Skill" term and a "Skill Managment", so all technical classes and db tables are using the skill term. However later it has been decided to switch to the Competence term on the UI level.

**[Skill Hierarchy](#skill-hierarchy)**

* [Skill Tree Nodes](#skill-tree-nodes)
* [Root](#root)
* [Basic Skill](#basic-skill)
* [Skill Category](#skill-category)
* [Skill Template](#skill-template)
* [Skill Template Category](#skill-template-category)
* [Skill Template Reference](#skill-template-reference)
* [Skill Levels](#skill-levels)
* [Skill Tree](#skill-tree)
* [Virtual Skill Tree](#virtual-skill-tree)

**[Competences](#competences)**

* [Views](#views)
* [Competence Status](#competence-status)
* [User Skill Level](#user-skill-level)
* [Skill Resource](#skill-resource)
* [Assigned Materials](#assigned-materials)
* [Assigned Objects Tab](#assigned-objects-tab)
* [Import Export](#import-export)
* [Deleting Competences](#deleting-competences)

**[Competence Profiles](#competence-profiles)**

* [Completion Concept](#completion-concept)
* [GAP Analysis](#gap-analysis)
* [Local Profiles](#local-profiles)
* [Assigned Objects Tab](#assigned-objects-tab)


# Skill Hierarchy

## Skill Tree Nodes

Skills are organised in a hierarchical structure called the [Skill Tree](#skill-tree). Items of the tree are called tree nodes.

* **Code**: `class ilSkillTreeNode`: Base class for all skill tree nodes.
* **DB Tables**: `skl_tree_node`

**Properties**

* **ID**: (`skl_tree_node.obj_id`)
* **Title**: (`skl_tree_node.title`)
* **Description**: (`skl_tree_node.description`)
* **Type**: (`skl_tree_node.type`)
    * "skrt": Skill Root Node
    * "skll": Skill
    * "scat": Skill Category
    * "sctr": Skill Category Template Reference
    * "sktr": Skill Template Reference
    * "sktp": Skill Template
    * "sctp": Skill Category Template
* **Creation Date**: (`skl_tree_node.create_date`, currently redundant with `skl_tree_node.creation_date`? Second is only set on import)
* **Last Update**: (`skl_tree_node.last_update`, currently not properly set)
* **Selectable**: Determines if user can manually add a skill to his/her Achievements > Competences screen. (`skl_tree_node.self_eval`)
* **Order Number**: Used for ordering nodes with the same parent (`skl_tree_node.order_nr`).
* **Status**: (`skl_tree_node.status`).
    * ilSkillTreeNode::STATUS_PUBLISH (0) 
    * ilSkillTreeNode::STATUS_DRAFT (1) 
    * ilSkillTreeNode::STATUS_OUTDATED (2) 
* **Import ID**: il_(inst_id)_(type)_(id): inst_id is the Intallation or NIC ID of the exporting ILIAS installation, type is the node type and id the ID on the exporting system.

## Root

* **Code**: `class ilSkillRoot`

The root node of the tree. There is only one.

## Basic Skill

* **Code**: `class ilBasicSkill`

Represents a standard skill. A basic skill has a number of [Skill Levels](#skill-levels). Basic Skill nodes have either a [Skill Category](#skill-category) or the [Root](#root) as parent in the [Skill Tree](#skill-tree).

## Skill Category

* **Code**: `class ilSkillCategory`

A skill category is kind of a "folder" for skills. Skill Category nodes have either another [Skill Category](#skill-category) or the [Root](#root) as parent in the [Skill Tree](#skill-tree).

## Skill Template

* **Code**: `class ilSkillTemplate`

A skill template is like a basic skill, but serves as a template for re-use. A skill template has a number of [Skill Levels](#skill-levels). Skill Template nodes have either a [Skill Template Category](#skill-template-category) or the [Root](#root) as parent in the [Skill Tree](#skill-tree).

## Skill Template Category

* **Code**: `class ilSkillTemplateCategory`

A skill template category is kind of a "folder" for skill templates. Skill Template Category nodes have either another [Skill Template Category](#skill-template-category) or the [Root](#root) as parent in the [Skill Tree](#skill-tree).

## Skill Template Reference

* **Code**: `class ilSkillTemplateReference`
* **DB Tables**: `skl_templ_ref`

A skill template reference is the link between the main category tree and a skill template or template category. Skill Template Reference nodes have either a [Skill Category](#skill-category) or the [Root](#root) as parent in the [Skill Tree](#skill-tree).

**Properties**

* **Node ID**: Node ID (`skl_templ_ref.skl_node_id` referencing a `skl_tree_node.obj_id` of type "sktr")
* **Template Node ID**: Template Node ID (`skl_templ_ref.templ_id` referencing a `skl_tree_node.obj_id` of type "sktp" or "sctp")

**Business Rule**

* A Skill Template Reference can only reference Templates or Template Category Nodes which are on the top level (directly underneath the root node).

## Skill Tree

* **Code**: `class ilSkillTree`
* **DB Tables**: `skl_tree`, `skl_tree_node`

The Skill Tree organises the hiearchical structure of the competences. The implementation starts with a typical `ilTree` derivation using `skl_tree` as the tree table, where `skl_tree.child` holds references to `skl_tree_node.obj_id`. This results e.g. in the following structure (see rules for nesting of nodes in the previous chapters).

```
Root
 +-- Template Category (a)
 |     +-- Skill Template
 |     +-- Skill Template
 +-- Basic Skill
 +-- Skill Category
 |     +-- Basic Skill
 +-- Skill Category
 |     +-- Skill Template Reference (to a)
 +-- Skill Category
 |     +-- Skill Template Reference (to a)
 ...
```

All template nodes directly located under the root are re-usable templates that can be [referenced](#skill-template-reference):

```
Root
 +-- Template Category (a)
 |     +-- Skill Template
 |     +-- Skill Template
 ...
 +-- Template Category (b)
 |     +-- Skill Template
 ...
 +-- Skill Template (c)
 |
 ...
```

This [Skill Tree](#skill-tree) reflects the structure on the database level. This view is only presented in the UI of the Competence Management administration in ILIAS.

## Virtual Skill Tree

If the competence hierarchy is presented in the UI (e.g. for selecting a skill from the hierarchy), it is typically rendered as what we call the Virtual Skill Tree. The virtual tree embeds all templates at the place of their references:

(1) Skill Tree

```
Root
 +-- Template Category (a)
 |     +-- Skill Template (a.1)
 |     +-- Skill Template (a.2)
 +-- Basic Skill
 +-- Skill Category
 |     +-- Basic Skill
 +-- Skill Category
 |     +-- Skill Template Reference (to a)
 |     +-- Skill Template Reference (to a)
 ...
```

(2) Remove all template nodes:

```
Root
 +-- Basic Skill
 +-- Skill Category
 |     +-- Basic Skill
 +-- Skill Category
 |     +-- Skill Template Reference (to a)
 |     +-- Skill Template Reference (to a)
 ...
```

(3) Insert templates at the position of their references:

```
Root
 +-- Basic Skill
 +-- Skill Category
 |     +-- Basic Skill
 +-- Skill Category
 |     +-- Skill Template Reference -> Template Category (a)
 |            +-- Skill Template (a.1)
 |            +-- Skill Template (a.2)
 |     +-- Skill Template Reference -> Template Category (a)
 |            +-- Skill Template (a.1)
 |            +-- Skill Template (a.2)
 ...
```

This reflects the idea of re-using the templates in the competence hierarchy. A typical example is to use this for language competences:

```
Root
 +-- Basic Skill
 +-- Skill Category
 |     +-- Basic Skill
 +-- Skill Category "Language Competences"
 |     +-- Skill Template Reference "French" -> Template Category "Language" (a)
 |            +-- Speaking (a.1)
 |            +-- Reading (a.2)
 |     +-- Skill Template Reference "Spanish" -> Template Category "Language" (a)
 |            +-- Speaking (a.1)
 |            +-- Reading (a.2)
 ...
```

# Competences

## Views

* Personal View (User sees his own competences in "Achievements > Competences > Competence Records")
* Competence Profile View (User sees competences in a Competence Profile he is assigned to in "Achievements > Competences > Assigned Profiles")  
* Publishing Personal Competence Data (Blog, Portfolio, prospectively possibly Staff)
* Personal View in object context (e.g. Course, Group, Test, Survey)
* Administrative View in object context (Tutor or Administrator in objects)
* Global administrative View (Competence Management)

## Competence Status

1. Draft (Offline)
* The competence is only shown in the [Virtual Skill Tree](#virtual-skill-tree) of global administrative views. 
  Admins can e.g. assign the competence to a competence profile. But users cannot select them as a personal competence.
* The competence will not be shown anymore in user's Skill Profiles, Repository Objects and Personal Skills
* Entries of users for this competence remain if the competence will be published again

2. Published
* All functionalities for the competence are available

3. Outdated
* The competence cannot be selected in the [Virtual Skill Tree](#virtual-skill-tree) anymore, i.e.:
  * Users cannot add the competence to their Personal Competences
  * The competence cannot be assigned to a Competence Profile
  * The competence cannot be assigned to a Repository Object
* If a competence status has changed from “Published” to “Outdated”, users can still work with this competence if they added the competence to their Personal Competences before.
  Users can assign materials, get entries and see suggested resources.
* Competences, which were already assigned to Competence Profiles or Repository Objects, also remain and can be edited.  

## User Skill Level

### Achieving Skill Level

There are three different types of user skill levels:
* Self-Evaluation
  * Users can evaluate themselves by adding a competence to their Personal Competences and using the Actions dropdown
  * Users can evaluate themselves by completing a self-evaluation in a survey
  * If a user makes multiple self evaluations on the same day, only the last evaluation will be saved. The older evaluations from this day will be overwritten.
* Appraisal
  * Course/group members can achieve skill levels when the levels are assigned to them by an admin/tutor
  * Users can achieve skill levels when other users complete a 360-degree feedback for them
  * By activating the ´Triggered by Completion´ checkbox for a resource of a skill level, the completion status of the learning progress of the resource is taken into account for achieving the skill level.
    * Whenever the learning progress changes to completed, a skill level is achieved. Multiple entries per object are possible.
* Measurement
  * When a user finishes a test, a skill level can be achieved based on the result of the test

### Deletion of achieved Skill Level / Deletion of Objects

* If a repository object (course/group, survey, test,...) is deleted completely from the repository, the achieved skill levels of this object remain in the database
* Course: When an achieved skill level is deassigned from a user, the corresponding entry is removed from the database, too.
  When the achieved skill level for a user is edited, the existing level will be overwritten and no new level will be created.
* Survey: When the survey data of a user is deleted, the corresponding skill level entry remains in the database.
* Test: When test data of a user is deleted, the corresponding skill level entry remains in the database. 
* Learning Progress: When the learning progress status of the repository object is set to "Not completed", the corresponding skill level entry remains in the database.
* Learning Progress: When the learning progress  of the repository object is deactivated, the skill level entries of this object remain in the database.

## Skill Resource

* Resources can be assigned to each competence level.
* By activating the ´Suggested Resource´ checkbox, the assigned resources will be suggested:
  * In the Personal Competences view of a user, all suggested resources of all competence levels are shown.
  * In a Skill Profile view, the user only sees the suggested resources of the target level, if existing.
  
## Assigned Materials

* Users can assign materials (e.g. files) from their Personal Resources to a competence level
* These materials are shown below the entries of a competence in his Personal Competences view, as well as in a Skill Profile
* Technically, assigned materials have no effect, e.g. for the target level of a competence profile

## Assigned Objects Tab

* (Basic) Competence:
  * Courses/Groups are shown in the table, when the competence was added to the course/group (Course/Group > Competences > Competence Selection)
  * Surveys are shown in the table, when the competence was added to a question (Survey > Competences > Question/Competence Assignment)
  * Tests are shown in the table, when the competence was added to a question (Tests > Competences > Question/Competence Assignment)
* Competence Template Reference:
  * Like Basic Competences (see above)
* Competence Template:
  * The assigned objects of all Competence Template References for the given Competence Template will be shown. If multiple Competence Template References are assigned to the same object, the object will be shown only once.
* For Competence Categories and Competence Template Categories, the "Assigned Objects"-tab will be **not** shown.

## Import Export

* When skills are imported, their original ID from the exporting installation is stored in `skl_tree_node.import_id`.
* Features that reference skills (e.g. local skill profiles) can re-instantiate these references on import by retrieving the new IDs
  through the methods `ilBasicSkill::getCommonSkillIdForImportId()` and/or `ilBasicSkill::getLevelIdForImportIdMatchSkill`. 

##Deleting Competences

* Competences cannot be deleted, if:
  * Competence is used in a repository object
  * Competence is selected by users as Personal Competence
  * Users assigned material from their personal resources to a competence
  * Users have achieved a competence level by self-evaluation, appraisal or measurement.
  * Competence is used in Competence Profiles
  * Repository Objects are assigned as suggested resource for a competence
  
# Competence Profiles

## Completion Concept
* Self Evaluations do not affect the fulfilment of a target level in a competence profile
* There are multiple events where the Competence Profiles of users are checked whether they are fulfilled (completion status = 100%):
  * If an entry is written for a user, all Competence Profiles of the user are checked
  * If a user is assigned to a Competence Profile manually or by a role, the Competence Profile for the one user or all users of a role are checked (in future: OrgUnits, too)
  * If a Competence Profile is edited, i.e. a skill level is removed or added, the Competence Profile is checked for all assigned users/roles
  * The deletion of competences is intercepted by the general prevention of competence deletion when they are assigned to a Competence Profile. This may change in the future and therefore should be mentioned here.
* For every time a user fulfills a Competence Profile, an entry in the Learning History is written
* The fulfillment of a Competence Profile is given, when the completion status changes from <100% to 100%. This can happen multiple times, because Competence Profiles can be edited, and the fulfillment of a Skill Profile for a user can vanish later on.

## GAP Analysis

* Gap Analysis compares achieved user skill levels with target levels of a profile.
* To determine the need for working through suggested resources, ILIAS checks all current achieved levels (table `skl_user_has_level`) and compares the maximum achieved level with the profile level.
  * Note that single objects (e.g. tests when having multiple runs) may set subsequent lower achievement levels in `skl_user_has_level`. In this case the last test run will be stored in the current achieved levels (`skl_user_has_level`), which might not be the best run.
  * Example 1
    * Skill Level: 1,2,3,4
    * Profile Level: 3
    * Last achieved levels for objects:
      * Course A (1. January): Level 3
      * Test B (2. January): Level 2
    * -> The maximum of "all last achieved levels" is 3, so the profile level is "fulfilled".
  * Example 2
    * Skill Level: 1,2,3,4
    * Profile Level: 3
    * Last achieved levels for objects:
      * Test A (1. January): Level 3
      * Test A (2. January): Level 2
    * -> The maximum of "all last achieved levels" is 3, but the last run of Test A is level 2 so the profile level is "not fulfilled".
* When examining competence profiles within a container object (course/group), the competence entries of the container object,
  as well as the competence entries of all subobjects of the container object are taken into account for the achievement of the 
  profile's competence target.
  * Example
    * Skill Level: 1,2,3,4
    * Profile Level: 3
    * Achieved levels for objects:
      * Course A: Level 2
      * Test B (located in Course A): Level 3
    * -> The profile level in Course A is "fulfilled".

## Local Profiles

* Local skill profiles can be created, edited and deleted in courses and groups.
* Local skill profiles are listed in the global skill profile administration, too.
* Global skill profiles can be used and removed (but not deleted) from courses and groups. They cannot be edited in courses and groups.
* Courses and groups export their local skill profiles. However, assigned skill levels of profiles will only appear on import, if the
  corresponding skills have been imported in the global administration before.
* Local profiles can be exported in the global administration, too. However, this will not include the reference to the course
  or group. They will always be imported as global profiles.

## Assigned Objects Tab

* The assigned objects of all Basic Competences and Competence Template References, which are used within the Competence Profile, will be shown.
  If multiple Competences are assigned to the same object, the object will be shown only once.
* Additionally, objects will be shown, where the Competence Profile was assigned, currently only Courses/Groups (Course/Group > Competences > Profile Selection).
