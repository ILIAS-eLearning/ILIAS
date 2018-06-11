# Cron

The key words “MUST”, “MUST NOT”, “REQUIRED”, “SHALL”,
“SHALL NOT”, “SHOULD”, “SHOULD NOT”, “RECOMMENDED”, “MAY”,
and “OPTIONAL” in this document are to be interpreted as
described in [RFC 2119](https://www.ietf.org/rfc/rfc2119.txt).

**Table of Contents**
* [ilCronJobResult](#ilCronJobResult)

## ilCronJobResult

The class `ilCronJobResult` determines the status
of a current cron job.

The status are:
* `STATUS_INVALID_CONFIGURATION`
  This status will indicate the current configuration
  for this cron job is not correct and MUST be
  adjusted.
* `STATUS_NO_ACTION`
This status indicates that the cron job did not perform any action.
Possible reasons to set this action:
  * A cron job responsible to sent emails didn't sent emails at all.
  * A cron job responsible for deleting orphaned objects did not find any object to delete.
  * A lucene cron job decided that the index does not require an update.
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
  This status indicates that an non-critical
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

A message SHOULD be added additionally.
If given, this message will be displayed in the cron job overview table.
