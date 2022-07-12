# Roadmap

## Short Term

###Refactoring

######PHP 8

For the upcoming ILIAS8 Release it is necessary to guarantee the compatibility for php 8.

######Prevention of static calls

The DB operations of [ilNotificationOSDHandler](Services/Notifications/classes/class.ilNotificationOSDHandler.php) should
be abstracted inside a new, dynamic repository.

######Update of Toast Interface

`VanishTime` and `Delay Time` should be moved to the interface, if the values could be configured in the respective consumers.
As long as this is not the case, they keep an implementation detail.

######Prevention of multiclass Files

All classes should have their own file similar to their name. Files with multiple classes like [ilNotificationConfig](Services/Notifications/classes/class.ilNotificationConfig.php)
should be prevented.

###Update off AdminNodes

With the future changes of the [DB Update Objective](https://github.com/ILIAS-eLearning/ILIAS/pull/3925) 
there has to be some minor change to the Notification Update Steps (Step 1)
[See](https://github.com/ILIAS-eLearning/ILIAS/pull/3740#discussion_r801925631)

###Update off unique Notifications

######paragraph-ui.js

[This](Services/COPage/Editor/js/src/components/paragraph/ui/paragraph-ui.js) uses the OSDNotifications in an uncommon way
and therefore as to be updated

######page-modifier.js

[This](Services/COPage/Editor/js/src/ui/page-modifier.js) may be deprecated or should be aligned with the new Notifications