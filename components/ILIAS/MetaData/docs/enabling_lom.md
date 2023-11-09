# Enabling LOM in ILIAS Objects

> This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../../docs/development/contributing.md#pull-request-to-the-repositories).

In this documentation, we will outline briefly how support for 
Learning Object Metadata (LOM) can be added to an ILIAS object.

### Object: Creation, Changing Settings, Deletion

`ilObject` already offers methods to manage the LOM of your object:
`ilObject::createMetaData` creates an initial metadata set for the
object, `ilObject::updateMetaData` updates title and description in
LOM with the title and description from the settings of the object
(and creates a new set if the object does not have one yet),
`ilObject::deleteMetaData` deletes the metadata of the object, and
`ilObject::cloneMetaData` copies the object's metadata to a target
object.

These methods are not used out of the box in `ilObject`, so you have
to call them as appropriate in your object's `create`, `update`,
etc.

Note that the implementation of the methods assumes that your object
is a repository object. If it is a sub-object, you have to overwrite
them in your component, and set the ID-triple in `ilMD` as described
[here](identifying_objects.md).

### LOM Editor

In order to show the 'Metadata' tab with the LOM editor, you have to
add the `ilObjectMetadataGUI` to your control flow, and it will take
care of the rest: have your object's main GUI forward commands to
`ilObjectMetadataGUI`, and activate the tab with ID `'meta_data'`
when it does. In addition, add your object's type to the array in
`ilObjectMetadataGUI::isLOMAvailable`. 

If your object is a repository object, you can just pass the object
itself to the constructor of `ilObjectMetadataGUI`, and it will extract
the [ID-triple](identifying_objects.md) automatically. However, if your object is a sub-object of
a parent repository object, you have to  pass that parent to the constructor
instead, along with the type and `obj_id` of your object.  In this case,
a combination of the parent's type and your object's type in the form
`'{type of parent}:{type}'` (e.g. `'lm:pg'`) has to be added to the array in
`ilObjectMetadataGUI::isLOMAvailable`. 

If your object is not a repository object, and also does not have a
fixed parent, then you have to pass `null` as the object in the constructor
of `ilObjectMetadataGUI`, and `':{type}'` (e.g. `':mob'`) has to be added to the
array in `ilObjectMetadataGUI::isLOMAvailable`.

#### Listening to Changes in LOM

The `ilObjectMetadataGUI` (and `Services\Object` in general) already
takes care of changing the title and description of your object when
the corresponding elements in LOM are changed in the editor, see
`ilObject::doMDUpdateListener`. If you want to change or extend this
process in particular, overwrite either the aforementioned method or
`ilObject::beforeMDUpdateListener`.

If you want your object to react to changes in its metadata beyond that,
you can register some class in your component as an observer
via `ilObjectMetaDataGUI::addMDObserver`.

Note that if your object is not a repository object and does not have
a fixed parent, `ilObjectMetadataGUI` does not automatically register
your object as an observer of LOM. In this case, in order for title
and description to be updated along with LOM, you have to register
by hand, again via `ilObjectMetaDataGUI::addMDObserver` for the section
`'General'`.

#### Examples

In `ilObjGroupGUI::executeCommand`:

    switch ($next_class) {
        ...
        case 'ilobjectmetadatagui':
            if (!$this->access->checkAccess('write', '', $this->object->getRefId())) {
                $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->WARNING);
            }
            $this->tabs_gui->activateTab('meta_data');
            $this->ctrl->forwardCommand(new ilObjectMetaDataGUI($this->object));
            break;

In `ilObjMediaObjectGUI::executeCommand`:

    switch ($next_class) {
        case 'ilobjectmetadatagui':
            $md_gui = new ilObjectMetaDataGUI(null, $this->object->getType(), $this->object->getId());
            // object is subtype, so we have to do it ourselves
            $md_gui->addMDObserver($this->object, 'MDUpdateListener', 'General');
            ...
            $this->ctrl->forwardCommand($md_gui);
            break;

### Show LOM on Info Screen

In order to display some elements of your object's LOM on its 'Info'
tab, call `ilInfoScreenGUI::addMetaDataSections` when building the
info screen. As parameters, you have to pass the familiar [ID-triple](identifying_objects.md).

### Import/Export

In order for LOM to be included in the import and export of your
object, you need to add `Services/MetaData` to the dependencies in
`getXmlExportTailDependencies` in your `ilXmlExporter`. Add the
following entry to the array returned there:

    $dependencies[] = [
        'component' => 'Services/MetaData',
        'entity' => 'md',
        'ids' => $ids,
    ];

`$ids` is an array of the IDs of the to be exported objects. Each ID
is composed of the [ID-triple](identifying_objects.md), joined into one colon-separated
string: `{obj_id of repository object}:{obj_id}:{type}`, e.g. `456:54:pg`
for the [Page in a LM example](identifying_objects.md).

In addition, in `importRecord` of your object's `ilDataSet`, you need
to add a mapping for the exported LOM from the exported object to the
newly imported one:

    $a_mapping->addMapping(
        'Services/MetaData',
        'md',
        $old_id,
        $new_id
    );

`$old_id` and `$new_id` follow the same scheme as the `$ids` above.

### Lucene Search

To make sure that when searching ILIAS via Lucene your object's LOM
can also be searched, include the following line in your `LuceneObjectDefinition`:

    <xi:include href="../../Services/MetaData/LuceneDataSource.xml" />
