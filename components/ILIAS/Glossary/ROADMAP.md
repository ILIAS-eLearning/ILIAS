# Roadmap

## Short Term

### Get rid of obsolete metadata entries

See FR: https://docu.ilias.de/goto_docu_wiki_wpage_7360_1357.html

Up to ILIAS 8, it was possible to define LOM for glossary definitions. Since multiple definitons per term are abandoned 
from ILIAS 9 onwards, LOM for definitions have also been abandoned (see Jour Fixe decision in FR). Now, there is
"dead metadata" in the corresponding database tables, which should be deleted.

## Mid Term

### Use central Online/Offline code

Glossary still has its own online field in table "glossary". The object service should be used instead.

### Improve Architecture

- Introduce repository pattern
- Improve DI handling
- Factor business logic out of UI classes

## Long Term
