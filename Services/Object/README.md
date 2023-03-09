# Object Component

This component provides features for handling ILIAS objects like repository
objects or other objects being derived from `ilObject`.

**Topics**

- [Export Entities](#Export-Entities)
- [Object Service](#Object-Service)
- [Common Settings](#Common-Settings)
- [Offline Handling](#Offline Handling)


# Export Entities

The object component "Services/Object" provides the following entities, which
should be added as tail dependency, if the corresponding features are used.
All of them need the general object IDs to be passed.

- "transl": Multilanguage titles/descriptions
- "service_settings": Additional feature settings. (full documentation will follow)
- "icon": Custom Icons
- "tile": Tile Images
- "common": All of the entities above. Usually save to use this one,
even if not all features are used. This entity should be enhanced automatically
in the future, if more common properties/settings for objects will be introduced.

# Object Service


The object service can be obtained using the `DIC`:

```
$obj_service = $DIC->object();
```

## Common Settings

The object service provides methods to include common settings into your settings
forms and save them.

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

If objects are copied in the repository, the common settings including e.g.
tile images and custom icons will be copied automatically.

For including these in your export you need to ensure to include the common
entity of the object service as a tail dependency in your exporter:

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

# Offline Handling

To activate the offline handling in your object you will first need to add
it as an attribute of the object in the module.xml

```
<objects>
    <object id="exmpl" class_name="Example" dir="classes" offline_handling="1"></object>
</objects>
```

It should be noted, that whenever a module.xml is edited, the setup needs to be
called with the update parameter, so the changes can be applied.

Inside your GUI class for the object settings, after having created a settings
form and added a checkbox option for the online value to it, you will then need
to add the values to your update and save function like this:

```
protected function getEditFormCustomValues(array &$a_values)
    {
        $a_values["offline_input_postvar"] = !$this->object->getOfflineStatus();
    }
```

```
protected function updateCustom(ilPropertyFormGUI $a_form)
    {
        $this->object->setOfflineStatus(($a_form->getInput("offline_input_postvar") == 1 ) ? 0 : 1);
    }
```

Since we're usually asking in a form if the object should be online but they're
saved in the object_data table as offline=1 or offline=0, the values will need
to be inverted when getting and setting them


# JF Decisions

8 Oct 2018

- General introduction of the service, including Common Settings subservice
- https://github.com/ILIAS-eLearning/ILIAS/pull/1211
