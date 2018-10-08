# Object Service

This service provides features for handling ILIAS objects like repository objects or other objects being derived from `ilObject`.

The object service can be obtained using the `DIC`:

```
$obj_service = $DIC->object();
```

For convenience the base class ilObjectGUI provides a method to obtain the object service.

```
// in classes that derive from ilObjectGUI you may use:
$obj_service = $this->getObjectService();

```

## Common Settings

The object service provides methods to include common settings into your settings forms and save them.

In ILIAS 5.4 all settings forms are still legacy forms. The following examples show how to modify these forms and save the returned values. In upcoming versions a similar procedure for UI framework forms should be provided.

**Custom Icons**

Custom icons used in almost all views that represent repository objects, e.g. lists or explorer trees.

Adding the setting to a form:

```
$obj_service->commonSettings()->legacyForm($form, $this->object)->addIcon();
```
Saving the setting:
```
$obj_service->commonSettings()->legacyForm($form, $this->object)->saveIcon();
```


**Tile images**

Tile images are used in tile views of containers (starting with ILIAS 5.4).

Adding the setting to a form:

```
$obj_service->commonSettings()->legacyForm($form, $this->object)->addTileImage();

```
Saving the setting:
```
$obj_service->commonSettings()->legacyForm($form, $this->object)->saveTileImage();
```
