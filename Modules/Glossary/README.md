# Glossaries

This README is work in progress.

## General Documentation

### Export

Business Rules
* **Referenced terms** are exported as they were terms of the exported glossary. This means on import the glossary will contain copies of the referenced terms.
* **References to auto-linked** glossaries will be established on import, if the referenced glossary has been imported before.
* The **Download Setting** is not exported/imported, since it requires additional export files of the glossary (e.g. HTML), which are not exported, too.


## Concepts

### Used Services

- Permissions
- Export
- Metadata
- Style
- Taxonomies
- Info

### Settings (table "glossary")
- Virtual Mode (none, subtree, level) (is to be abandoned)
- Presentation Mode (table, list)
- Snippet Length (Max length of first paragraph being presented in table overview, should be restricted to <= 4000 due to glossary_definition.short_text field length)
- Public export files (public_html_file, public_xml_file) being selected in export tab and presented to learners
- glo_menu_active (seems to be abandoned)
- Downloads Active Flag (controls tab for learner presentation)
- Show Taxonomy Flag (controls presentation of tax for learner, if taxonomy given)

### Auto Glossaries (table "glo_glossaries")
- Auto-Glossaries automatically scan the text of the "target" glossary and inserts links to terms of the "source" glossaries, if terms are matching.

### Terms (table "glossary_term")
- All terms of a glossary (same glo_id)
- id: Term ID
- glo_id: Glossary ID
- term: Term
- language (onyl metadata, no "multilinguism" feature)
- import_id
- create_date
- last_update

### Definition (table "glossary_definiton")
- All definitions of a term
- id: Definition ID
- term_id: Term ID
- short_text: "Snippet" of the definition (redundant for performance reasons)
- nr: Ordering of definition presentation of a term
- short_text_dirty: Being set by code to trigger re-calculation of short_text (e.g. when settings being saved, recalculation is triggered in table presentation of terms, ilGlossaryDefinition->updateShortText)

### Service Advanced Metadata
- Activation stored by using ilObjectServiceSettingsGUI
- Uses advanced metadata for terms
- If adv records defined, they will appear in term editing new "Metadata" tab and in the table presentation and full list presentation for learners
- Column Order (table "glo_advmd_col_order", glo_id, field_id, order_nr)

### Service Taxonomy
- Taxonomy Subtab always appears (should be activated in additional feature section)
- Taxonomy service stores Taxonomy <-> Glossary relation
- Glossary gets taxonomy being used by ilObjTaxonomy::getUsageOfObject(glossary ID)
- If available, taxonomy nodes are assigend to terms in the term settings screen
- Term <-> Tax Node relationship is store by using ilTaxNodeAssignment class

### Virtual Glossaries (to be abandoned)
- Virtual glossaries collect terms of other glossaries
- Same Level: All glossaries in the same category
- Subtree: All glossaries of current category and its children are collected

### Collected Terms
- Single terms can be selected and copied or referenced in other glossaries.
- Referenced terms are stored in table "glo_term_reference" (term_id in table does not belong to glo_id, it comes from other glossary)

### Main term retrieval
- Done in ilGlossaryTerm::getTermList

