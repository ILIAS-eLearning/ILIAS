# Roadmap

## Short Term

**Advanced Schedule Options**

To offer advanced schedule options for administrators a *crontab*-like
syntax should be supported to define the due date of a job. For parsing purposes
[PHP Cron Expression Parser](https://github.com/dragonmantank/cron-expression)
could be suggested (needs a Jour Fixe decision).

Feature Request: [Define Target Timespan for a Scheduled Cronjob](https://docu.ilias.de/goto_docu_wiki_wpage_5296_1357.html)

### Refactor Registration of Core Cron Jobs

Currently, core cron jobs can be defined in the component XML file.
These files are processed by `\ilCronDefinitionProcessor::beginTag`
and `\ilCronDefinitionProcessor::endTag`, which are called by the
`\ilComponentDefinitionReader`.

Current Problems:
* `\ilCronJobRepository::getJobInstance` is used in the registration process of a job.
* Certain dependencies (retrieved from `$DIC`) do not exist or are replaced with fake objects during setup,
  but the actual services are required in the constructors of the concrete cron job implementations registered.
* There should be an interface segregation for the registration and execution of core cron jobs.
* The problem is currently solved by using the `Reflection API` in the setup context.

## Past Refactorings

### ILIAS 8

**Component Logger**

Introduce a component specific logger for the `cron` component.

**Get rid of Static Methods / Introduce Dependency Injection**

More interfaces habe been introduced and almost all static methods have been removed.
Instead, dependency injection is used. A cron service is provided via the `$DIC`.