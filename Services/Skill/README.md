# Skill aka Competence Service

This README is work in progress.

# API

WIP

# General Documentation

This section documents the general concepts and structures of the Skill Service. These are internal implementations which SHOULD not be used outside of this module unless mentioned in the API section of this README.

**Skills vs Competences**

In ILIAS both terms are synonyms. In the UI we started with the "Skill" term and a "Skill Managment", so all technical classes and db tables are using the skill term. However later it has been decided to switch to the Competence term on the UI level.

**Skill Hierarchy**

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

**Other Concepts**

* [Import Export](#import-export)
* [Personal Skills](#personal-skills)
* [Skill Profile](#skill-profile)
* [Skill Resource](#skill-resource)
* [User Skill Level](#user-skill-level)
* [Assigned Materials](#)
* [Self Evaluations](#)
* [GAP Analysis](#)



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

**Business Rules**

* A Skill Template Reference can only reference Templates or Template Category Nodes which are on the top level (directly underneath the root node).
* When a Template or Template Category is deleted, all related Skill Template References will also be deleted.

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

## Import/Export

* When skills are imported, their original ID from the exporting installation is stored in `skl_tree_node.import_id`.
* Features that reference skills (e.g. local skill profiles) can re-instantiate these references on import by retrieving the new IDs
  through the methods `ilBasicSkill::getCommonSkillIdForImportId()` and/or `ilBasicSkill::getLevelIdForImportIdMatchSkill`. 
 

## Skill Profiles

### Local Skill Profiles

**Business Rules**

* Local skill profiles can be created, edited and deleted in courses and groups.
* Local skill profiles are listed in the global skill profile administration, too.
* Global skill profiles can be used and removed (but not deleted) from courses and groups. They cannot be edited in courses and groups.
* Courses and groups export their local skill profiles. However, assigned skill levels of profiles will only appear on import, if the
  corresponding skills have been imported in the global administration before.
* Local profiles can be exported in the global administration, too. However, this will not include the reference to the course
  or group. They will always be imported as global profiles.