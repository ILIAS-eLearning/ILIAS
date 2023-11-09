# Object Component

This component provides features for handling ILIAS objects like repository
objects or other objects being derived from `ilObject`.

**Topics**

- [Export Entities](#Export-Entities)
- [Object Service](#Object-Service)
- [Common Settings](#Object-Settings)
- [Offline Handling](#Offline-Handling)


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


The object service can be obtained using the `DIC`. It right now only offers legacy
functionality that will be removed with ILIAS 11. New functionality is planed for
ILIAS 9 or 10.

```
$obj_service = $DIC->object();
```

## Object Settings

The `ilObject` provides methods to include properties into your settings
forms and save them.

Currently you can retrieve and store:
* Title and Description
* Online Status
* Visibility of Actions in the Header (SHOULD be placed in **Presentation** section)
* Visibility of Title and Icon (SHOULD be placed in **Presentation** section)
* Custom Icons (SHOULD be placed in **Presentation** section)
* Tile Images (SHOULD be placed in **Presentation** section)

**Retrieving and Storing the Settings**

You can retrieve the form elements with

```
$object->getObjectProperties()->getProperty<the property you are looking for>()->toForm(
    ilLanguage $language,
    ILIAS\UI\Component\Input\Field\Factory $field_factory,
    ILIAS\Refinery\Factory $refinery);
```

Once you have added the corresponding elements to your form and received a
response back you can retrieve the $property from the form element with

```
$property = $input->getValue();
```

And then save it with:

```
$object->getObjectProperties()->storeProperty<the property you want to store>($property);
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


# JF Decisions

8 Oct 2018

- General introduction of the service, including Common Settings subservice
- https://github.com/ILIAS-eLearning/ILIAS/pull/1211
