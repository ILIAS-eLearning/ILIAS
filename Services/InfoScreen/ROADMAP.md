# Roadmap

## Short Term

...

## Mid Term

- Improve DI handling
- Factor business logic out of UI classes
- More unit tests

### Get rid of info screen as start screen

Some components use the info screen as a starting screen into a workflow, e.g. the tests or surveys. This kind of use should be abandonded to make these workflows less dependent from the info screen, e.g. it should be possible to deactivate the info screen without disabling key features of components.

### Migrate to KS components

The main layout of the info screen is rendered using a legacy template. This needs to be replaced by a KS component. Currently there is no suitable KS component, so this needs a UI concept first.