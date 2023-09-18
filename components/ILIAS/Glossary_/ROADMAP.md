# Roadmap

## Short Term

### Get rid of multi definitions

See https://docu.ilias.de/goto_docu_wiki_wpage_7360_1357.html

Migration needed

- JOIN glossary_term and glossary_definition (sort by glossary term)
  - term 1, def 1
  - term 2, def 3
  - term 2, def 4 <-- act here
  - term 2, def 5 <-- act here
  - term 3, def 10
- If multiple rows for a term, for each row > 1
  - create new glossary_term entry with same values, but new ID (without import id)
  - change definition entry (e.g. def 4 and def 5 to point to new term ID), result:
    - term 1, def 1
    - term 2, def 3
    - term 7, def 4
    - term 8, def 5
    - term 3, def 10
  - AFTER
  - merge glossary_term and glossary_definition table
    - keeping only short_text and short_text_dirty field
  - update all code using definition ids to term ids

We will not duplicate referenced terms, taxonomy relations and advanced metadata (-> needs to be stated in feature wiki).

## Mid Term

### Use central Online/Offline code

Glossary still has its own online field in table "glossary". The object service should be used instead.

### Improve Architecture

- Introduce repository pattern
- Improve DI handling
- Factor business logic out of UI classes

## Long Term