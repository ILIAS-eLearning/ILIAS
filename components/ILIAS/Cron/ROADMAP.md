# Roadmap

## Short Term

**Advanced Schedule Options**

To offer advanced schedule options for administrators a *crontab*-like
syntax should be supported to define the due date of a job. For parsing purposes
[PHP Cron Expression Parser](https://github.com/dragonmantank/cron-expression)
could be suggested (needs a Jour Fixe decision).

Feature Request: [Define Target Timespan for a Scheduled Cronjob](https://docu.ilias.de/goto_docu_wiki_wpage_5296_1357.html)

## Past Refactorings

### ILIAS 12

**Registration of Core Cron Jobs**

Core cron jobs are now contributed via the component class (`Component::init()`).
The `CronJobRegistry` gathers all contributed jobs.

### ILIAS 8

**Component Logger**

Introduce a component specific logger for the `cron` component.

**Get rid of Static Methods / Introduce Dependency Injection**

More interfaces habe been introduced and almost all static methods have been removed.
Instead, dependency injection is used. A cron service is provided via the `$DIC`.