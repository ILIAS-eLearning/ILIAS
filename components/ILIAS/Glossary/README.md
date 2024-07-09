# Glossaries

This README is work in progress.

## General Documentation

### Export

Business Rules
* **Referenced terms** are exported as they were terms of the exported glossary. This means on import the glossary will contain copies of the referenced terms.
* **References to auto-linked** glossaries will be established on import, if the referenced glossary has been imported before.
* The **Download Setting** is not exported/imported, since it requires additional export files of the glossary (e.g. HTML), which are not exported, too.

### Permissions

Business Rules
* In the Presentation view of Collection Glossaries, there is no permission check for the selected glossaries.


## Concepts

### Used Services

- Permissions
- Export
- Metadata
- Style
- Taxonomies
- Info

### Settings (table "glossary")
- Content Assembly (none, collection)
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

### Collection Glossaries
- Collection glossaries collect terms of other (manually selected) glossaries

### Collected Terms
- Single terms can be selected and copied or referenced in other glossaries.
- Referenced terms are stored in table "glo_term_reference" (term_id in table does not belong to glo_id, it comes from other glossary)

### Main term retrieval
- Done in ilGlossaryTerm::getTermList


## Flashcards
The flashcard training can be activated in the settings of a glossary. If activated, the flashcards are the primary
displayed tab. For the flashcard training, it can be chosen if the term of the flashcard is displayed first and the
learner has to guess the definition or vice versa.

### Basic principle
The flashcards are based on the training scheme of Sebasian Leitner. There are five boxes for the flashcards.
In the beginning, all term-definitons-pairs are located in box 1. If the learner opens box 1 and clicks "I got it right"
for a term-definition-pair, it is moved to box 2. From box 2, it is moved to box 3 and so on. The aim is to get all
term-definition-pairs to box 5, the last box which cannot be opened. If the learner clicks on "I was wrong" for a
term-definition-pair, it is always moved back to box 1.
For more information about the training scheme, have a look [here](https://en.wikipedia.org/wiki/Flashcard).

### Special features
There are a few special features with the flashcards in ILIAS:
* If the learner opens a box containing flashcards which has been already presented to her on the same calendar day,
she will be informed about it and can decide if she wants to see only "older" flashcards or also flashcards from
today's date. If there are only flashcards from today's date, the learner can only continue with these flashcards.
* In genereal, flashcards are sorted by date within a box. That means flashcards, which have not been displayed for the
longest time, will be shown to the learner first. There is an exception when multiple flashcards were last accessed
on the same calendar day. These flashcards are not sorted to the second, but mixed randomly.
* By clicking on "Reset All Boxes" in the overview, all flashcards are moved back to box 1. The dates of the flashcards
are reset, too.
