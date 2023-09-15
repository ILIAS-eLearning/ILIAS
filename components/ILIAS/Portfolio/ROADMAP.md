# Roadmap

## Short Term

...

## Mid Term

- Further improce the internal service structure
- Improve DI handling to get more code under unit tests
- Use more namespaces

### Refactor Javascript Code

The included code is small, but uses jQuery and includes a dependency to the tree icons and course component. This should be solvable in a better way and possibly moved 

### Use repository pattern

Queries to the three internal tables usr_portfolio, usr_portfolio_page and usr_prtf_acl should be moved into a repository class. Data objects should be used.


### Sharing Workflow: Reuse of other code

The sharing workflows are re-using code classes like ilMailSearchObjectGUI which are located in other services but are lacking an interface definition (or being re-used without offering re-use at all). This makes it difficult to handle change or feature requests, e.g. https://docu.ilias.de/goto_docu_wiki_wpage_7582_1357.html

### Common interface for access handler

The portfolio access handler shares method signatures with the core ilAccess handler. However they are missing a common interface as a contract with consuming components which would also simplify dependency handling.

### Introduce IRSS

The banner images should be abandoned or its implementation should be migrated to the IRSS.
