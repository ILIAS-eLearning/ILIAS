> This documentation does not warrant completeness or correctness, and is probably outdated. Reports of
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contributions via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories)
are greatly appreciated.

# Export/Import of Components, esp. Repository Resources

## Export

Export of a repository resource in this context means: content export without user related data. There are other exports like exports of members data in courses or groups. This how-to focuses on the export of a repository resource as a whole.

ILIAS provides support for a standard way of representing ILIAS export screens and for a standardized modularized export process.

### Export User Interface: Class ilExportGUI

The export tab should be placed accoring to the [tabs guidelines](https://docu.ilias.de/goto_docu_wiki_wpage_481_1357.html). The export screen should be implemented by using class `ilExportGUI` which is located in `Services/Export/classes`. The basic lines of code needed are:

```php
// the code is placed in the ilObj...GUI class of the repository resource
...
* @ilCtrl_Calls ilObj...GUI: ilExportGUI
...
// in execute command:
...
include_once("./Services/Export/classes/class.ilExportGUI.php");
$exp_gui = new ilExportGUI($this); // $this is the ilObj...GUI class of the resource
$exp_gui->addFormat("xml");
$ret = $ilCtrl->forwardCommand($exp_gui);
...
// in set/get tabs:
if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
{
    $tabs_gui->addTab("export",
        $lng->txt("export"),
        $this->ctrl->getLinkTargetByClass("ilexportgui", ""));
}
...
```

The simple example above can be found in `Modules/Exercise/classes/class.ilObjExerciseGUI.php`. A more complex usage can be found in learning modules, class `Modules/LearningModule/classes/class.ilObjContentObjectGUI.php`. This uses the `addFormat()` method to add multiple formats and custom columns and multi commands:

```php
// in executeCommand()
...
include_once("./Services/Export/classes/class.ilExportGUI.php");
$exp_gui = new ilExportGUI($this);
$exp_gui->addFormat("xml", "", $this, "export");
$exp_gui->addFormat("html", "", $this, "exportHTML");
$exp_gui->addFormat("scorm", "", $this, "exportSCORM");
$exp_gui->addCustomColumn($lng->txt("cont_public_access"),
    $this, "getPublicAccessColValue");
$exp_gui->addCustomMultiCommand($lng->txt("cont_public_access"),
    $this, "publishExportFile");
$ret = $ilCtrl->forwardCommand($exp_gui);
...
```

### Class il<Component>Exporter
For the XML export the export user interface class calls a generic export method ilExport->exportObject (Services/Export). To use this generic export, you need to implement an il\<Module\>Exporter class that is derived from `ilXmlExporter`.

The method exportObject of ilExport triggers a **modularized export process** that is **not limited to repository resources**. It **can be used by any component** of ILIAS for different sets of entities. E.g. the service MetaData implements a class `ilMetaDataExporter` and the module MediaPool implements a class `ilMediaPoolExporter`.

An il\<Component\>Exporter class must provide:

- a `getXmlRepresentation()` method
- a `getValidSchemaVersions()` method
- a `getXmlExportHeadDependencies()` method, if there are any dependencies to other components, that should be exported **before** the data of the current component
- a `getXmlExportTailDependencies()` method, if there are any dependencies to other components, that should be exported **after** the data of the current component

Additionally the class may implement an `init()` method that is called at the beginning of the procedure.

#### getXmlRepresentation()

The method getXmlRepresentation() must return the XML for a given entity, target release and id.

- **entity**: This is a string that represents the entity that should be exported, e.g. the object type like "lm", "file", "frm" or another identifier that is recognised by the component. E.g. the metadata component uses only one entity named "md".
- **target_release**: A string like "4.1.0" that identifies the target release for the export. This allows to implement export routines for older versions than the current one.
- **id**: The ID is the ID of the concrete entity. If an entity ID consists of multiple parts, they should be concatenated in one string using the ":" separator between each part.

The following example shows the implementation of the getXmlRepresentation method for meta data:

```php
public function getXmlRepresentation($a_entity, $a_target_release, $a_id)
{
    include_once("./Services/MetaData/classes/class.ilMD2XML.php");
    $id = explode(":", $a_id);
    $mdxml = new ilMD2XML($id[0], $id[1], $id[2]);
    $mdxml->setExportMode();
    $mdxml->startExport();
 
    return $mdxml->getXml();
}
```

#### getXmlExportHeadDependencies() and getXmlExportTailDependencies()

These method must return the dependencies for a given entity and target release and multiple ids.

An example for a dependency is "every media object has metadata". In this case the media object component is responsible for providing the XML for the media object, but not for the metadata. The latter is defined as a "tail dependency": The metadata should be put into the export package after the media object has been exported.

To decide whether something is a head or a tail dependency (should be imported before or after the current component), it is important to consider how the package will be imported and how relations between the data are resolved.

The import processes the data in the same sequence as the export did. The import process provides an "ID mapping" feature to get new IDs (the ones created when imported) for old ones (the ones in the package).

If data of component (a) needs the new IDs of component (b) when being imported, (b) should be exported before (a) and if (b) depends on (a), (a) should be a tail dependency in the `ilExporter` clas of component (b).

Example: The metadata IDs of a media object are derived from the ID of a media object. If a media object has ID 5, the corresponding metadata ID will be "0:5:mob". This means, the media object should be imported before the metadata and the metadata should be a tail dependency in the media object export.

```php
public function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
{
    $md_ids = array();
    foreach ($a_ids as $mob_id)
    {
        $md_ids[] = "0:".$mob_id.":mob";
    }
    return array (
        array(
            "component" => "Services/MetaData",
            "entity" => "md",
            "ids" => $md_ids)
        );
}
```

#### getValidSchemaVersions()
The export process relies on schema definitions (xsd files) that define the XML returned by the `ilExporter` classes. The `getValidSchemaVersions()` method must return all schema versions that the component can currently export to. ILIAS chooses the first one, that has min/max constraints which fit to the target release. Please put the newest schemas on top.

```php
function getValidSchemaVersions($a_entity)
{
    return array (
        "4.1.0" => array(
            "namespace" => "http://www.ilias.de/Services/MediaObjects/mob/4_1",
            "xsd_file" => "ilias_mob_4_1.xsd",
            "uses_dataset" => true,
            "min" => "4.1.0",
            "max" => "")
    );
}
```

The xsd files are located in the xml folder of ILIAS. The namespace should always have the format `https://www.ilias.de/<Services|Modules>/<Component>/<entity>/<release number in the format x_x>`\.

#### Output of the Export Process

The standard export process creates a file named `<timestamp>__<installation id>__<entity>_<id>.zip`. This zip file contains a `manifest.xml` file on the top level, that includes basic information and references to `export.xml` files that have been created by the components involved during the export process.

```php
<Manifest MainEntity="mep" Title="My Pool" TargetRelease="4.1.0"
InstallationId="4411" InstallationUrl="http://scott.local/ilias">
    <ExportFile Component="Services/MediaObjects" Path="Services/MediaObjects/set_1/export.xml"/>
    <ExportFile Component="Services/MetaData" Path="Services/MetaData/set_1/export.xml"/>
    <ExportFile Component="Modules/MediaPool" Path="Modules/MediaPool/set_1/export.xml"/>
    <ExportFile Component="Services/MediaObjects" Path="Services/MediaObjects/set_2/export.xml"/>
    <ExportFile Component="Services/MetaData" Path="Services/MetaData/set_2/export.xml"/>
    <ExportFile Component="Modules/File" Path="Modules/File/set_1/export.xml"/>
    <ExportFile Component="Services/MetaData" Path="Services/MetaData/set_3/export.xml"/>
    <ExportFile Component="Services/COPage" Path="Services/COPage/set_1/export.xml"/>
</Manifest>
```

The example above shows the manifest file for a media pool which lists all components that are involved in the export of the media pool and their export files.

## Import

ILIAS also provides a standardized import process for packages that have been created with the standard export process. The process for importing a repository resource is started with a call of `ilImport->importObject` (Services/Export). All involved components must provide a class `il<Component>Importer` that handles the import of the corresponding data.

### Class il<Component>Importer

This class must be derived from `ilXmlImporter` (Services/Export). The class must provide:

- a method `importXmlRepresentation()` that imports the XML of the export.

Additionally the class may implement an `init()` method that is called at the beginning of the procedure.

```php
function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
{
    $new_id = $a_mapping->getMapping("Services/MetaData", "md", $a_id);
    if ($new_id != "")
    {
        include_once("./Services/MetaData/classes/class.ilMDXMLCopier.php");
        $id = explode(":", $new_id);
        $xml_copier = new ilMDXMLCopier($a_xml, $id[0], $id[1], $id[2]);
        $xml_copier->startParsing();
    }
}
```

The example above imports the XML data that has been created by the corresponding `il<Component>Exporter` class, in this case `ilMetaDataExporter`. The ID provided is the ID of the export file. In this case the new id depends on the object that depends on the meta data, e.g. a media object. The media object has been imported before in this case and provided mapping information for the metadata via the mapping object that is passed to importXmlRepresentation. The mapping object allows to add (set) old-to-new-ID mappings and to lookup them up (get).

```php
// code in Services/MediaObjects that creates new media objects from
// import xml and adds mapping for metadata
$newObj = new ilObjMediaObject();
...
$newObj->create();
...
$a_mapping->addMapping("Services/MetaData", "md",
    "0:".$old_id.":mob", "0:".$newObj->getId().":mob");
```

## Using Dataset Classes for Import/Export

It is up to the component, how the XML for the export is created and imported. The component can, but does not need to, make use of dataset classes that create XML in a generic way without the need to use the ilXmlWriter class for export or individual sax import parsers classes.
