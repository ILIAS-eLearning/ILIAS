# Learning Sequence Object

## Extend allowed sub-objects
In order to extend createable types for LSO, a plugin will have to add 'lso'
to the array returned by il[PluginName]Plugin::getParentTypes().

The Plugin should also implement LearningProgress and a KioskModeView;
please refer to src/KioskMode/README.md, 'Implementing a Provider'.

## online-status of objects
Offline-items are not shown in user's curriculum.

Since ILIAS does not provide a generic way to check online/offline status of objects,
and not all objects implement this, there is a lookup in LSItemOnlineStatus.
Extend this with the according calls in order to use online/offline mechanism
within LSO.

Default is "true", meaning the object is "online".
