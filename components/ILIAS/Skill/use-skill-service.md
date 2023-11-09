# Skill Service

This how-to describes how components can and should use the Skill Service. The Skill Service implements what is called in ILIAS the Competence Management. The different terms have historical reasons, as the feature has been called Skill Management in the beginning.

## The Competence Tree

Competences are organized in a hierarchical structure. Nodes that contain subnodes are called Competence Category, leaf nodes are Competences. These competences define an ordered list of competence levels.
 
This simple structure becomes complex with the introduction of competence templates. Competence templates are reusable subtrees that can be referenced within the main competence structure by Competence Template References.
 
The competence templates can either only consist of one basic competence template (without and subnodes) or of a competence template category including subnodes.

| Node | Internal Type | Purpose | Part of the Hierarchy |
| ---- | ---- | ---- | ---- |
| Root Node of Competence Tree | skrt | Root of the Competence Hierarchy |  |
| Competence Node | skll | Defines Competence Levels | Main Part |
| Competence Category Node | scat | Can contain competence and competence category nodes | Main Part |
| Competence Template Reference Node | sktr | References a competence template or a competence template category | Main Part |
| Competence Template Node | sktp | Defines Competence Levels | Template Part |
| Competence Template Category Node | sctp | Can contain competence templates or competence template categories | Template Part |

## Identifying a Competence

We now focus on the **leaf nodes** in the competence tree that define the competence levels (either type "skll" or type "sktp").
 
If no competence templates would be used, a competence could simply be identified by its **node ID**. But since competence templates can be reused multiple times we need a second node ID, the **ID of the competence reference** to identify a competence.
 
**Structure of an ID for a competence: `<skill_id>:<tref_id>`**
 
If the `skill_id` is the ID of a simple competence node (type "skll"), the `tref_id` must be 0. If the `skill_id` is the ID of a competence template node (type "sktp") the `tref_id` must be the ID of a reference node (type "sktr"). A reference node refers to a template identified by the root node of the template (either type "sktp" or "sctp"). The node with the ID `skill_id` must be within the subtree of this template.
 
The most important to keep in mind is that a competence is identified by the two parts `<skill_id>:<tref_id>`.