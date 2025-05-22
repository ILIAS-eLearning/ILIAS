<?php /**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

exit; ?>

Tables
======

glossary (glossary settings)
- PK id
- FK id -> object_data.obj_id

glossary_term (glossary terms)
- PK id
- FK glo_id -> glossary.id

glossary_definition (term definitions) --> outdated/abandoned
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