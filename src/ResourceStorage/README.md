Resource Storage Service
========================

# Overview

The Resource Storage Service should be the central place where components and plugins should manage their user uploaded
files and resources.

Historically ILIAS uses the file system directly in its components to manage files. There are some conventions but
within the structure of a component files can be stored freely. Additional features such as versioning of files is
always the responsibility of the component or the plugin.

ILIAS 7 offers the Resource Storage Service for all components and plug-ins.

In summary, the service allows to exchange a user upload (UploadResult from the Upload-Service) for a unique resource
ID. During this exchange, the service takes care of the correct storage of the data (file as well as metadatan to the
file). There is no direct access of the component to the FileSystem, neither by native PHP functions nor by the
FileSystem service.
As soon as the UserUpload is needed again, e.g. to display or download it, the Resource-ID can be exchanged for a
corresponding delivery method. This process also does not directly access the file system by the component.

```
  Component                 +-+ Resource
                            | | Storage
  +----------+              | | Service
  | UPLOAD   +--------------> |
  +----------+              | |
                            | |
                            | |
  +----------+              | |
  | ID       <--------------+ |
  +----------+              | |
                            | |
                            | |
+---------------------------------+
                            | |
                            | |
  +----------+              | |
  | ID       +--------------> |
  +----------+              | |
                            | |
                            | |
  +----------+              | |
  | CONSUMER <--------------+ |
  +----------+              | |
                            | |
                            | |
                            +-+

```

## Terms

```
+---------------------------------------------------------------------+
| RESOURCE                                                            |
+---------------------------------------------------------------------+
|                                                                     |
| +--------------------+                                              |
| | IDENTIFICATION     |                                              |
| +--------------------+                                              |
|                                                                     |
| +-------------------+  +-------------------+  +-------------------+ |
| | REVISION          |  | REVISION          |  | REVISION          | |
| +-------------------+  +-------------------+  +-------------------+ |
| |                   |  |                   |  |                   | |
| | +---------------+ |  | +---------------+ |  | +---------------+ | |
| | | INFORMATION   | |  | | INFORMATION   | |  | | INFORMATION   | | |
| | +---------------+ |  | +---------------+ |  | +---------------+ | |
| | | "FILE"        | |  | | "FILE"        | |  | | "FILE"        | | |
| | +---------------+ |  | +---------------+ |  | +---------------+ | |
| |                   |  |                   |  |                   | |
| +-------------------+  +-------------------+  +-------------------+ |
|                                                                     |
+---------------------------------------------------------------------+
```

### Resource

A Resource is the abstract term for a "thing" uploaded by a user and passed to the Resource Storage Service for
management. The Resource is the combination of

- Identification
- Revisions
- Information about the revisions
- File or its contents (of the respective revisions)
- Stakeholders

### Identification

The identification represents the unique information about which resource is involved. This information is stored by the
component itself to be able to retrieve a resource later.

### Revision

Revisions contain the file together with metadata (information) and a version number. This means that a resource can
have multiple revisions. The management of these revisions is the responsibility of the Service. Revisions can be added,
changed or deleted from outside by the components.

### Information

Information holds general metadata about a revision, such as MimeType, size, title, ... . This information is stored by
the service.

### Consumer

Consumers allow to "consume" a resource. This is the interface closest to the FileSystem that is allowed to start an
action with the file of a revision, such as

- Download
- Get Stream
- Retrieve content
- Get the absolute Path of the file (only for legagy purposes)
- ...

A set of Comsumers is already part of the service.

### Stakeholder

Although resources are uploaded to ILIAS by users, these users are not only responsible for a resource but also for the
component in which the file has been uploaded. The service must be able to retrieve information from the component in
order to decide whether a resource is needed at all or not, e.g. during cleanup processes. Or, if a resource is removed
by the service for security reasons, the component must be informed about it, in order to carry out further cleanup
steps.

### StorageHandler (internal)

In order to be equipped for the future, the Storage Service internally uses so-called StorageHandlers to store files. In
the first step this is of course a FileSystem-based storage handler. However, the way is open here to expand this at a
later point in time.

# Usage

The use of the service should be as easy as possible from the point of view of the components and Plugins. The
components should only communicate with the service according to the principle shown above, i.e. exchange uploads for
identifications or exchange an identification for a consumer.

# Examples

In most cases most of the work is already done by the UI inputs and as a form developer you get the identification
directly from the service which can then be stored in your component.

In case you want to exchange an upload for an identification yourself, proceed as follows in your component:

```php
<?php
// ...
global $DIC;
$upload_result = $DIC['upload']->getResults()['my_uploaded_file'];
$stakeholder = new ilMyComponentResourceStakeholder();

$identification = $DIC['resource_storage']->manage()->upload($upload_result, $stakeholder);

// then store the $identification whereever I need it in my component


```

Suppose we already have a file stored in the Storage Service and would download it in our component. We had stored the
identification, with which a corresponding consumer can now be obtained to download the file.

```php
<?php
// ...
global $DIC;
$rid_string = $this->getMyResourceIDasString(); // we get the stored ID of the resource

$identification  = $DIC['resource_storage']->manage()->find($rid_string);
if (null !== $identification) {
    $DIC['resource_storage']->consume()->download($identification)->run();
} else {
    // there is no such resource in the storage service
}
```

Adding a new revision is as simple as that:

```php
<?php
// ...
global $DIC;
$rid_string = $this->getMyResourceIDasString(); // we get the stored ID of the resource

$identification  = $DIC['resource_storage']->manage()->find($rid_string);
$upload_result = $DIC['upload']->getResults()['my_uploaded_file'];
$stakeholder = new ilMyComponentResourceStakeholder();

if (null !== $identification) {
    $DIC['resource_storage']->manage()->appendNewRevision($identification, $upload_result, $stakeholder);
} else {
    // there is no such resource in the storage service
}
```

# Collections

In many cases a component does not only need a single resource to be stored, but wants to be able to use a collection of
resources (e.g. in an exercise unit several files can be delivered per person). For this purpose `Collections` can be
used in IRSS. A collection always contains a collection of ResourceIdentifications in a defined order.
The management of collections is done via:

```php
$DIC['resource_storage']->collection();
```

To use a collection, a new collection-id can be created:

```php
$rcid = $DIC['resource_storage']->collection()->id();
```

Or an existing collection-id can be called:

```php
$rcid = $DIC['resource_storage']->collection()->id("f0e564e2-5d48-4d33-a8e6-bdc2646900d7");
```

Retrieving the collection itself is done via:

```php
// $rcid see example above
$collection = $DIC['resource_storage']->collection()->get($rcid);
```

ResourceIdentifications can now be added to such a collection, e.g. after the upload. The collection must be saved
afterwards:

```php
$irss = $DIC['resource_storage'];
$rcid = $irss->collection()->id("f0e564e2-5d48-4d33-a8e6-bdc2646900d7");
$collection = $irss->collection()->get($rcid);

$this->upload->process();
$result = array_values($this->upload->getResults())[0];
if ($result->isOK()) {
    $id = $irss->manage()->upload($result, $this->stakeholder);
    $collection->add($id); // adding a resource to the collection
}
$irss->collection()->store($collection); // saving the collection
```

Collections can be explicitly assigned to a user ID, and such collections can later be retrieved and modified only on
the basis of this user ID:

```php
// create new collection for user 6
$rcid = $DIC['resource_storage']->collection()->id(null, 6); // return a collection with e.g. ID "f0e564e2-5d48-4d33-a8e6-bdc2646900d7"
// ... accessing the same collection with another user-id results in an exception
$rcid = $DIC['resource_storage']->collection()->id("f0e564e2-5d48-4d33-a8e6-bdc2646900d7", 13);
```

To get the ResourceIdentifications assigned to a collection, they can be accessed as follows:

```php
$rcid = $DIC['resource_storage']->collection()->id("f0e564e2-5d48-4d33-a8e6-bdc2646900d7");
$collection = $irss->collection()->get($rcid);

foreach ($collection->->getResourceIdentifications() as $rid) {
    // do something with the resource
    $file_stream = $DIC['resource_storage']->consume()->stream($rid)->getStream();
}
```

Collections can also be easily downloaded as a ZIP file.

```php
$rcid = $DIC['resource_storage']->collection()->id("f0e564e2-5d48-4d33-a8e6-bdc2646900d7");
$DIC['resource_storage']->consume()->downloadCollection($rcid)->run();
```

Besides storing collections (`store`) there are also `clone` and `remove`.

## Sorting and Ranges of Collections

A collection can be sorted for display, various options are available for this:

```php
use ILIAS\ResourceStorage\Collection\Collections;
/** @var Collections $collection_services */
$collection_services = $DIC['resource_storage']->collection();

$rcid = $collection_services ->id("f0e564e2-5d48-4d33-a8e6-bdc2646900d7");
$collection = $irss->collection()->get($rcid);

// By Title
$collection_sorted_by_title_asc = $collection_services->sort($collection)->asc()->byTitle();
$collection_sorted_by_title_desc = $collection_services->sort($collection)->desc()->byTitle();

// By Creation Date
$collection_sorted_by_creation_date_asc = $collection_services->sort($collection)->asc()->byCreationDate();
$collection_sorted_by_creation_date_desc = $collection_services->sort($collection)->desc()->byCreationDate();

// By File-Size
$collection_sorted_by_filesize_asc = $collection_services->sort($collection)->asc()->bySize();
$collection_sorted_by_filesize_desc = $collection_services->sort($collection)->desc()->bySize();
```

These are in-memory sorts. But if you want to store the sort permanently, you can do this as follows:

```php
$collection_services->sort($collection)->asc()->andSave()->bySize();
```

To display only a part of a collection, you can obtain a range of a collection. In this case you get
the `ResourceIdentification` directly either as `array` or as `\Generator`:

```php
use ILIAS\ResourceStorage\Collection\Collections;
/** @var Collections $collection_services */
$collection_services = $DIC['resource_storage']->collection();

$rcid = $collection_services ->id("f0e564e2-5d48-4d33-a8e6-bdc2646900d7");
$collection = $irss->collection()->get($rcid);

// Get only a range of the collection
$range_generator = $collection_services->rangeAsGenerator($collection, 10, 200);
$range_array = $collection_services->rangeAsArray($collection, 10, 200);
```

# Other (involved) Services

- UploadService, see [here](../FileUpload/README.md)
- FileSystem-Service, see [here](../Filesystem/README.md)

# Migration

- Migration will need at least the
  feature ["Migrate Command"](https://docu.ilias.de/goto_docu_wiki_wpage_6399_1357.html)
- A documentation how you can migrate your component is [here](../../Services/ResourceStorage/MIGRATIONS.md).

# Roadmap

Please see the roapmap of the service in [README.md](ROADMAP.md)

