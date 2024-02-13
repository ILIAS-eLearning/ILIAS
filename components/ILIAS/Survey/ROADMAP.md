# Roadmap

## Ongoing

### Improve Architecture

- Move all DB/Session access to repository pattern
- Service/Factory structure, improve DI handling
- Factor business logic out of UI classes

## Short Term

The survey module is using a larger number of HTML templates, sometimes even inputs are rendered via HTML templates. This should be removed and transferred to KS presentations, as a part of this https://mantis.ilias.de/view.php?id=25211 should be fixed.

### Improve Usability of question table

The legacy question table contains some obsucur and unique features. When migrating to the KS data table these things (e.g. indentation) must go.

### Refactoring of Constraint Handling

- Constraints for questions blocks are currently working, if the are internally assigned to the first question of the block (see README). This is error prone, e.g. adding a question at the end of a block will keep the constraint, adding a question on the front will remove the constraint. The constraint handling should be refactored. See https://mantis.ilias.de/view.php?id=27879

## Mid Term

### Invitation

There is a misconception about what the invitation feature does, see https://docu.ilias.de/goto_docu_wiki_wpage_6098_1357.html and https://mantis.ilias.de/view.php?id=36335
