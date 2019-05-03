# Roadmap

## Short Term

### Common Settings Subservice

The Common Settings subservice has been introduced with ILIAS 5.4 but is far from complete. The approach should be transfered to the following settings:

- Title and Description (being aware of partial multilingual support)
- Availability (including online/offline)
- Sorting
- Additional Features: These settings should move to a subtab, see https://docu.ilias.de/goto_docu_wiki_wpage_5271_1357.html so the handling will be different from the settings being embedded in the main settings form. However this subservice should provide functions to manage the subtab as well.

## Mid Term

### Factories

Factories for `ilObj...`, `ilObj...GUI`, `ilObj...ListGUI`, `ilObj...Access` instances should be provided through `$DIC->object()` service object.


## Long Term

### Refactor ilObject and related classes

The code of `ilObject` and related classes should be refactored into multiple subservices being accessible through `$DIC->object()`.

### Introduce Repository Pattern

The Repository Pattern should be introduced to decouple the persistence layer.