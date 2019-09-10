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

**Subservices**

- [Common Settings](##Common-Settings)


##Common Settings

The object service provides methods to include common settings into your settings forms and save them.

In ILIAS 5.4 all settings forms are still legacy forms. The following examples show how to modify these forms and save the returned values. In upcoming versions a similar procedure for UI framework forms should be provided.

**Custom Icons**

Custom icons used in almost all views that represent repository objects, e.g. lists or explorer trees.

Adding the setting to a form (SHOULD be placed in **Presentation** section):

```
$obj_service->commonSettings()->legacyForm($form, $this->object)->addIcon();
```
Saving the setting:
```
$obj_service->commonSettings()->legacyForm($form, $this->object)->saveIcon();
```


**Tile images**

Tile images are used in tile views of containers (starting with ILIAS 5.4).

Adding the setting to a form (SHOULD be placed in **Presentation** section):

```
$obj_service->commonSettings()->legacyForm($form, $this->object)->addTileImage();

```
Saving the setting:
```
$obj_service->commonSettings()->legacyForm($form, $this->object)->saveTileImage();
```
Get `ilObjectTileImage` instance for an object id:
```
$tile_image = $DIC->object()->commonSettings()->tileImage()->getByObjId($obj_id);
```

**Copying / Import / Export**

If objects are copied in the repository, the common settings including e.g. tile images and custom icons will be copied automatically.

For including these in your export you need to ensure to include the common entity of the object service as a tail dependency in your exporter:

```
public function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
{
    if ($a_entity == "your_main_entity_type") {
        $res = [];
        ...
        $res[] = array(
            "component" => "Services/Object",
            "entity" => "common",
            "ids" => $a_ids);
        return $res;
    }
}
```


# JF Decisions

8 Oct 2018

- General introduction of the service, including Common Settings subservice
- https://github.com/ILIAS-eLearning/ILIAS/pull/1211
