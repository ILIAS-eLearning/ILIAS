<?php exit; ?>

Tables
======

glossary (glossary settings)
- PK id
- FK id -> object_data.obj_id

glossary_term (glossary terms)
- PK id
- FK glo_id -> glossary.id

glossary_definition (term definitions)
- PK id
- FK term_id -> glossary_term.id

glo_advmd_col_order (order for advanced metadata fields)
- PK glo_id, field_id
- FK glo_id -> glossary.id
- FK field_id ->

glo_glossaries (auto linked glossaries)
- FK id -> glossary.id (source glossary)
- FK glo_id -> glossary.id (auto linked target glossary)

glo_term_reference
- glo_id -> glossary.id
- term_id -> glossary_term.id (term not belonging to glo_id glossary)

Term References
===============

- Editing Style is used from source glossary
- Editing Adv. Metadata fields are used from source glossary
- Auto linked glossaries are used from source glossary
- Referencing glossary determines presentation