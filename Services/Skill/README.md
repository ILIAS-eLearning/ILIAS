# Skill aka Competence Service

This README is work in progress.

# API


# General Documentation

This section documents the general concepts and structures of the Skill Service. These are internal implementations which SHOULD not be used outside of this module unless mentioned in the API section of this README.

* [Skill Tree Nodes](#skill-tree-nodes)
* [Root](#root)
* [Basic Skill](#basic-skill)
* [Skill Category](#skill-category)
* [Skill Template](#skill-template)
* [Skill Template Category](#skill-template-category)
* [Skill Template Reference](#skill-template-reference)
* [Skill Tree Structure](#skill-tree-structure)
* [Skill Tree IDs](#skill-tree-ids)
* [Skill Levels](#skill-levels)
* [Personal Skills](#personal-skills)
* [Skill Profile](#skill-profile)
* [Skill Resource](#skill-resource)
* [User Skill Level](#user-skill-level)



## Skill Tree Nodes

Skills are organised in a hierarchical structure called the **Skill Tree**. Items of the tree are called tree nodes.

* **Code**: `class ilSkillTreeNode`: Base class for all skill tree nodes.
* **DB Tables**: `skl_tree_node`

### Properties
* **ID**: (`skl_tree_node.obj_id`)
* **Title**: (`skl_tree_node.title`)
* **Description**: (`skl_tree_node.description`)
* **Type**: (`skl_tree_node.type`)
    *  "skrt": Skill Root Node
    *  "skll": Skill
    *  "scat": Skill Category
    *  "sctr": Skill Category Template Reference
    *  "sktr": Skill Template Reference
    *  "sktp": Skill Template
    *  "sctp": Skill Category Template
* **Creation Date**: (`skl_tree_node.create_date`)
* **Selectable**: (`skl_tree_node.self_eval`)
 
 obj_id, title, description, type, create_date, self_eval, order_nr, status, creation_date, import_id

## Root

## Basic Skill

## Skill Category

## Skill Template

## Skill Template Category

## Skill Template Reference

## Skill Tree Structure

## Skill Tree IDs
