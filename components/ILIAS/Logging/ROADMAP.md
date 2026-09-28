# Roadmap

## Short Term

### Move UI to KS, activities

The Logging administration should be moved to KS, and the GUI classes
refactored. This can be done with the help of activities.

## Mid Term

### Allow Components register and configure loggers

Instead of giving every component and plugin a logger by default, there
should be a mechanism for components to configure their loggers (e.g. 
should the log level be changeable in the GUI, for loggers that can't
depend on the database). They should also be able to register additional
loggers.

## Long Term

...
