# Roadmap

## Short Term

Atm the notification mail channel is always acitvated and not configurable via UI.
It should have its own checkbox within the notification administration.

* Get rid of time dependency in `\ILIAS\Notifications\Repository\ilNotificationOSDRepository`
  and `\ILIAS\Notifications\ilNotificationDatabaseHandler`, use `\ILIAS\Data\Clock\ClockFactory` interface instead

### Update of Toast Interface

`VanishTime` and `DelayTime` should be moved to the interface, if the values could be configured in the respective consumers.
As long as this is not the case, they keep an implementation detail.

## Mid Term

## Long Term
