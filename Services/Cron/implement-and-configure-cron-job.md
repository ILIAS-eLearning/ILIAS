# Implementing and Configuring a Cron-Job

To give more control of if and when cron-jobs are executed to administrators a 2nd implementation of cron-jobs has been added to ILIAS 4.4+. All existing cron-jobs should be migrated and thus moved to their respective modules and services. The top-level directory "cron/" will probably be kept because of cron.php but should otherwise be empty at some point.

### module.xml/service.xml

A module or service has to "announce" its cron-jobs to the system by adding them to their respective module.xml or service.xml.

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

There are 3 basic concepts: cron-job, schedule and cron-result. Using them as intended should make testing and/or debugging of cron-jobs far easier than before.

### ilCronJob

This base class must be extended by every cron-job. Besides the methods mentioned above `isActive()` is noteworthy as it is the central point by which the system decides if a cron-job is to be run or not. The "condition" of the existing CronCheck would have to be implemented here.

Several abstract methods have to be implemented to make a new cron-job usable:

- `[string] getId()`: returns the Id as defined in the module.xml or service.xml
- `[bool] hasAutoActivation()`: is the cron-job active after "installation" or should it be activated manually?
- `[bool] hasFlexibleSchedule()`: can the schedule be edited by an adminstrator or is it static?
- `[int] getDefaultScheduleType()`: see Schedule
- `[int] getDefaultScheduleValue()`: see Schedule
- `[ilCronJobResult] run()`: process the cron-job

### Schedule

As the cron-tab (for 4.4+) should be configured in a way that it runs every few minutes the schedule is crucial to minimize system load by only running cron-jobs when really needed.
 
The are several schedule types available. The most basic are daily, weekly, monthly, quarterly and yearly. Furthermore a schedule can be given in minutes, hours and days. If the cron-job is set to a flexible schedule it can be configured by administrators. In any case a default schedule type and value (see above) has to be given to give users a clue when a job is due.

Use the following constants to implement your desired schedule:
 
```php
const SCHEDULE_TYPE_DAILY = 1;
const SCHEDULE_TYPE_IN_MINUTES = 2;    
const SCHEDULE_TYPE_IN_HOURS = 3;
const SCHEDULE_TYPE_IN_DAYS = 4;
const SCHEDULE_TYPE_WEEKLY = 5;
const SCHEDULE_TYPE_MONTHLY = 6;
const SCHEDULE_TYPE_QUARTERLY = 7;
const SCHEDULE_TYPE_YEARLY = 8;
```

### ilCronJobResult
The `run()` method of the cron-job is expected to return an instance of ilCronJobResult. For now it has 3 properties:

- `[int] status`
    - invalid configuration: the cron-job cannot run because some configuration is missing or invalid, it will be deactivated
    - no action: no data has been found to process, this is not an error but just a hint for a tester that actually nothing happened in the last run
    - ok: processing was done and no error occured
    - crashed: an error occured and the cron-job failed
    - reset: the cron-job was reset by an administrator after crashing and has not been run yet
- `[string] code`: some kind of status code if needed
- `[string] message`: some kind of human-readable message about the results of the last run 

## Settings

A cron-job may define its own custom settings. The following methods can be used:

- `[bool] hasCustomSettings()`
- `addCustomSettingsToForm()`
- `[bool] saveCustomSettings()`
- `addExternalSettingsToForm()`
    - This method can be used to integrate cron-job specific settings into 1 or more administration settings forms.
    - The concept of "External Settings" is currently undocumented.

## Customizing

- `[string] getTitle()`
- `[string] getDescription()`
- `activationWasToggled()`
    - This hook will be called each time the activation status of the cron-job is changed. This way the cron-job can notify other classes about this.

## Misc

The `isActive()` method decides if a cron-job is run or not. As a default it checks the schedule or runs anyways if triggered manually by an administrator. The specific cron-job is free to decide by any means necessary if it is currently active or not. A flexible schedule should of course mind the current settings.

A cron-job is set to crashed if it has been running for 3 hours straight and has not "pinged" once. The `ilCronManager::ping()` method will reset this counter thus enabling the cron-job to run as long as needed. So if you know that your cron-job might take ages add a `ping()` call at some sensible point in your `run()` method.

A cron-job is locked when running. The cron manager will take care of this and prevent concurrent runs. So as mentioned above the cron-tab can safely be set to every few minutes.