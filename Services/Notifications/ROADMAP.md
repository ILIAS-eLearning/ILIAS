# Roadmap

## Done

### ILIAS 8
* PHP 8: For the upcoming ILIAS 8 release it is necessary to guarantee the compatibility for PHP 8.
* Update of Toast Interface `VanishTime` and `DelayTime` should be moved to the interface, if the
  values could be configured in the respective consumers. As long as this is not the case,
  they keep an implementation detail.
* Prevention of multiclass Files: All classes should have their own file similar to their name.
  Files with multiple classes like [ilNotificationConfig](Services/Notifications/classes/class.ilNotificationConfig.php)
  should be prevented.
* paragraph-ui.js: [This](Services/COPage/Editor/js/src/components/paragraph/ui/paragraph-ui.js) uses the
  OSDNotifications in an uncommon way and therefore as to be updated
* paragraph-ui.js: [This](Services/COPage/Editor/js/src/ui/page-modifier.js) may be deprecated or should be
  aligned with the new Notifications

## Short Term

### Refactoring

* Get rid of time dependency in `\ILIAS\Notifications\Repository\ilNotificationOSDRepository`
  and `\ILIAS\Notifications\ilNotificationDatabaseHandler`, use `\ILIAS\Data\Clock\ClockFactory` interface instead