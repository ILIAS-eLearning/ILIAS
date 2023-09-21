# Roadmap

## Short Term

## Mid Term

### Abandon undocumented plugin slot

There is an undocumented plugin slot in the survey question pool module. If there is no interest in using it, it should be removed.

### Improve Architecture

- Introduce repository pattern
- Improve DI handling
- Factor business logic out of UI classes

### Refactoring of Constraint Handling

- Constraints for questions blocks are currently working, if the are internally assigned to the first question of the block (see README). This is error prone, e.g. adding a question at the end of a block will keep the constraint, adding a question on the front will remove the constraint. The constraint handling should be refactored. See https://mantis.ilias.de/view.php?id=27879

## Long Term