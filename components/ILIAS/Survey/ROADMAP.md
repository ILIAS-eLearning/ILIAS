# Roadmap

## Short Term

The survey module is using a larger number of HTML templates, sometimes even inputs are rendered via HTML templates. This should be removed and transferred to KS presentations, as a part of this https://mantis.ilias.de/view.php?id=25211 should be fixed.

## Mid Term

### Improve Architecture

- Introduce repository pattern
- Improve DI handling
- Factor business logic out of UI classes

### Refactoring of Constraint Handling

- Constraints for questions blocks are currently working, if the are internally assigned to the first question of the block (see README). This is error prone, e.g. adding a question at the end of a block will keep the constraint, adding a question on the front will remove the constraint. The constraint handling should be refactored. See https://mantis.ilias.de/view.php?id=27879

## Long Term