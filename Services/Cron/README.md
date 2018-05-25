# Cron

**Table of Contents**
* [ilCronJobResult](#ilCronJobResult)

## ilCronJobResult

The class `ilCronJobResult` determine the status
of a current cron job.

The status are:
* `STATUS_INVALID_CONFIGURATION`
  This status will indicate the current configuration
  for this cron job is not correct and MUST be
  adjusted.
* `STATUS_NO_ACTION`
  This status will indicate that the cron job
  was not executed.
* `STATUS_OK`
  This status indicates that the cron job
  has been successfully finished.
* `STATUS_CRASHED`
  A critical failure has occurred during
  the execution of the cron job, which led
  to an critical error.
  The cron job needs to be restarted by
  the administrator.
* `STATUS_RESET`
  This status indicates that cron job
  has been reseted.
* `STATUS_FAIL`
  This status indicate that an non-critical
  error appeared in the execution of the cron
  job.

Every `run()`-Method of a cron job MUST return
an instance of `ilCronJobResult`
and MUST set status before returned by a method.

```php
public function run()
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

A message can be added additionally that will be
displayed in the GUI or the appropriate log files.
