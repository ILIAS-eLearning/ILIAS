# Roadmap

## Short Term

...

## Mid Term

- Add a decent internal service structure
- Improve DI handling to get more code under unit tests
- Use namespaces

### Sharing Workflow: Reuse of other code

The sharing workflows are re-using code classes like ilMailSearchObjectGUI which are located in other services but are lacking an interface definition (or being re-used without offering re-use at all). This makes it difficult to handle change or feature requests, e.g. https://docu.ilias.de/goto_docu_wiki_wpage_7582_1357.html

### Common interface for access handler

The workspace access handler shares method signatures with the core ilAccess handler. However they are missing a common interface as a contract with consuming components which would also simplify dependency handling.