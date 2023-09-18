# Roadmap

## Short Term

### Refactoring

* Get rid of time dependency in `\ILIAS\Notifications\Repository\ilNotificationOSDRepository`
  and `\ILIAS\Notifications\ilNotificationDatabaseHandler`, use `\ILIAS\Data\Clock\ClockFactory` interface instead

### Update of Toast Interface

`VanishTime` and `DelayTime` should be moved to the interface, if the values could be configured in the respective consumers.
As long as this is not the case, they keep an implementation detail.

## Mid Term

## Long Term
