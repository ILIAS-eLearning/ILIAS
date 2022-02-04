# Roadmap

## Short Term

**Component Logger**

Introduce a component specific logger for the "cron" component.

**Advanced Schedule Options**

To offer advanced schedule options for administrators a *crontab*-like
syntax should be supported to define the due date of a job. For parsing purposes
[PHP Cron Expression Parser](https://github.com/dragonmantank/cron-expression)
could be suggested (needs a Jour Fixe decision).

### Refactor Registration of Core Cron Jobs

Currently, mail template contexts can be defined in the component XML file.
These files are processed by \ilCronDefinitionProcessor::beginTag
and \ilCronDefinitionProcessor::endTag, which are called by the
\ilComponentDefinitionReader.

Current Problems:
* \ilCronManager::getJobInstance is used in multiple contexts.
* Certain dependencies (retrieved from `$DIC`) do not exist or are replaced with fake objects during setup,
  but the actual services are required in the constructors of the concrete cron job implementations.
* There should be an interface segregation for the registration and execution of core cron jobs.
* The problem is currently solved by using the `Reflection API` in the setup context.