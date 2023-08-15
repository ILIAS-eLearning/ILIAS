# Cron

The keywords “MUST”, “MUST NOT”, “REQUIRED”, “SHALL”,
“SHALL NOT”, “SHOULD”, “SHOULD NOT”, “RECOMMENDED”, “MAY”,
and “OPTIONAL” in this document are to be interpreted as
described in [RFC 2119](https://www.ietf.org/rfc/rfc2119.txt).

**Table of Contents**
* [Implementing and Configuring a Cron-Job](#implementing-and-configuring-a-cron-job)
  * [Providing a Cron-Job](#providing-a-cron-job)
  * [ilCronJob](#ilcronjob)
  * [ilCronJobResult](#ilCronJobResult)
  * [Schedule](#schedule)
  * [Settings](#settings)
  * [Customizing](#custimizing) 
  * [Misc](#misc)
* [Cron Job Execution](#cron-job-execution)
* [Permission Context](#permission-context)


## Implementing and Configuring a Cron-Job

To give more control of if and when cron-jobs are executed to administrators a 2nd implementation of cron-jobs
has been added to ILIAS 4.4+. All existing cron-jobs have been migrated and thus moved to their respective modules
and services. The top-level directory "cron/" will probably be kept because of cron.php but should otherwise be empty
at some point.

### Providing a Cron-Job

A module or service has to "announce" its cron-jobs to the system by adding them to their respective
module.xml or service.xml.

- The job id has to be unique.
- An optional path can be added if the module/service directory layout differs from the ILIAS standard.

```php
<?xml version = "1.0" encoding = "UTF-8"?>
<service xmlns="http://www.w3.org" version="$Id$"
   id="trac">
   ...
   <crons>
      <cron id="lp_object_statistics" class="ilLPCronObjectStatistics" />
   </crons>
</service>
```

There are 3 basic concepts: cron-job, schedule and cron-result. Using them as intended should make testing
and/or debugging of cron-jobs far easier than before.

### ilCronJob

This base class must be extended by every cron-job. Besides the methods mentioned below `isDue()`
is noteworthy as it is the central point by which the system decides if a cron-job is to be run or not.
The "condition" of the existing CronCheck would have to be implemented here.

Several abstract methods have to be implemented to make a new cron-job usable:

- `getId()`: returns the Id as defined in the module.xml or service.xml
- `hasAutoActivation()`: is the cron-job active after "installation" or should it be activated manually?
- `hasFlexibleSchedule()`: can the schedule be edited by an adminstrator or is it static?
- `getDefaultScheduleType()`: see Schedule
- `getDefaultScheduleValue()`: see Schedule
- `run()`: process the cron-job

### ilCronJobResult

The class `ilCronJobResult` determines the status of a current cron job.

The status are:
* `STATUS_INVALID_CONFIGURATION`
  This status will indicate the current configuration
  for this cron job is not correct and MUST be adjusted.
* `STATUS_NO_ACTION`
This status indicates that the cron job did not perform any action.
Possible reasons to set this action:
  * A cron job responsible to sent emails didn't sent emails at all.
  * A cron job responsible for deleting orphaned objects did not find any object to delete.
  * A lucene cron job decided that the index does not require an update.
* `STATUS_OK`
  This status indicates that the cron job has been successfully finished.
* `STATUS_CRASHED`
  A critical failure has occurred during
  the execution of the cron job, which led
  to an critical error.
  The cron job needs to be restarted by
  the administrator.
* `STATUS_RESET`
  This status indicates that cron job has been rested.
* `STATUS_FAIL`
  This status indicates that an non-critical
  error appeared in the execution of the cron
  job.

Every `run()`-Method of a cron job MUST return
an instance of `ilCronJobResult`
and MUST set status before returned by a method.

```php
public function run(): ilCronJobResult
{
  $result = new ilCronJobResult();

  try {
    $procedure->execute();
    $result->setStatus(ilCronJobResult::STATUS_OK);
  } catch (Exception $exception) {
    $result->setStatus(ilCronJobResult::STATUS_FAIL);
    $result->setMessage($exception->getMessage());
  }

  return $result;
}
```

A message SHOULD be added additionally.
If given, this message will be displayed in the cron job overview table.

### Schedule

As the cron-tab (for 4.4+) should be configured in a way that it runs every few minutes the schedule is
crucial to minimize system load by only running cron-jobs when really needed.

The are several schedule types available. The most basic are daily, weekly, monthly, quarterly and yearly.
Furthermore a schedule can be given in minutes, hours and days.
If the cron-job is set to a flexible schedule it can be configured by administrators. In any case a default
schedule type and value (see above) has to be given to give users a clue when a job is due.

Use the following enum cases to implement your desired schedule:

```php
\ILIAS\Cron\Schedule\CronJobScheduleType::SCHEDULE_TYPE_DAILY;
\ILIAS\Cron\Schedule\CronJobScheduleType::SCHEDULE_TYPE_IN_MINUTES;
\ILIAS\Cron\Schedule\CronJobScheduleType::SCHEDULE_TYPE_IN_HOURS;
\ILIAS\Cron\Schedule\CronJobScheduleType::SCHEDULE_TYPE_IN_DAYS;
\ILIAS\Cron\Schedule\CronJobScheduleType::SCHEDULE_TYPE_WEEKLY;
\ILIAS\Cron\Schedule\CronJobScheduleType::SCHEDULE_TYPE_MONTHLY;
\ILIAS\Cron\Schedule\CronJobScheduleType::SCHEDULE_TYPE_QUARTERLY;
\ILIAS\Cron\Schedule\CronJobScheduleType::SCHEDULE_TYPE_YEARLY;
```

### Settings

A cron-job may define its own custom settings. The following methods can be used:

- `hasCustomSettings()`
- `addCustomSettingsToForm()`
- `saveCustomSettings()`
- `addExternalSettingsToForm()`
  - This method can be used to integrate cron-job specific settings into 1 or more administration settings forms.
  - The concept of "External Settings" is currently undocumented.

### Customizing

- `getTitle()`
- `getDescription()`
- `activationWasToggled()`
  - This hook will be called each time the activation status of the cron-job is changed.
    This way the cron-job can notify other classes about this.

### Misc

The `isDue()` method decides if a cron-job is run or not. As a default it checks the schedule or runs anyways
if triggered manually by an administrator. The specific cron-job is free to decide by any means necessary
if it is currently active or not. A flexible schedule should of course mind the current settings.

A cron-job is set to crashed if it has been running for 3 hours straight and has not "pinged" once.
The `$DIC->cron()->manager()->ping()` method will reset this counter thus enabling the cron-job to run as
long as needed. So if you know that your cron-job might take ages add a `ping()` call at some sensible point
in your `run()` method.

A cron-job is locked when running. The cron manager will take care of this and prevent concurrent runs.
So as mentioned above the cron-tab can safely be set to every few minutes.

## Cron Job Execution

In order to execute the cron job manager, the following command MUST be used:

```shell
/usr/bin/php [PATH_TO_ILIAS]/cron/cron.php run-jobs <user> <client_id>
```

The `<user>` MUST be a valid (but arbitrary) user account of the ILIAS installation.
The `<client_id>` MUST be the client id of the ILIAS installation.

## Permission Context

Implementations of `ilCronJob` MUST NOT rely on specific permissions (e.g. RBAC).
Generally said, there MUST NOT be any expectations regarding given permissions
at all in the context of a cron job. Please keep this in mind when you structure
your code layers.