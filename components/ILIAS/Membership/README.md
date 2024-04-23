# Membership Service

## Business Rules

### Support Contacts

- Support Contacts with no open profile are listed only with their username, see https://mantis.ilias.de/view.php?id=27242

### News Cron Job

- The cron job sends all news after the last run of the job. If never run, the news of the last day will be sent.
- The news setting "Show News Starting From" will affect the set of news being sent. News before this date should not be sent.

### Minimum Members Check Cron Job

- The cron job sends out notifications for courses/groups with less members
than the configured minimum number. Notifications are only sent out at specific
times (see below), and only to users with 'Notification' enabled (see the
'Members' tab in the course/group).
- Notifications are sent out for a course/group if it fulfills all of
the following conditions:
  - The setting 'Limit Number of Members' is enabled, and 'Minimum Number'
  is set greater than zero.
  - The 'Start' of the course's/group's 'Period of Event' is either not
  set or is in the future.
  - If the setting 'Latest Exit Date' is set, it is in the past. If it is not
  set, the 'End' of the 'Limited Registration Period' is set and in the past.
- The previous conditions are evaluated when the cron job is executed.
Not only the date, but also the time of day are taken into account. If
the 'Period of Event' is given 'Without Time Indication', it is evaluated
as if the time were 00:00 (in UTC).
