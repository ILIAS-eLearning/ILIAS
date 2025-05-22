# Custom Export Options
The export allows for custom export options.
Custom export options can be implemented if a component desires to offer a custom export for different file types.
The default export only allows for the standard ILIAS xml export.
Custom Export Options extend the class _ILIAS\Export\ExportHandler\Consumer\ExportOption\ilBasicHandler_:

```php
use ILIAS\Export\ExportHandler\Consumer\ExportOption\BasicHandler as ilBasicExportOption;
```

## Table of Contents
- [Custom Export Options](#custom-export-options)
- [Methods](#methods)
  - [init](#init)
  - [getExportType](#getexporttype)
  - [getExportOptionId](#getexportoptionid)
  - [getSupportedRepositoryObjectTypes](#getsupportedrepositoryobjecttypes)
  - [isObjectSupported]()
  - [isPublicAccessPossible](#ispublicaccesspossible)
  - [getLabel](#getlabel)
  - [onExportOptionSelected](#onexportoptionselected)
  - [onDeleteFiles](#ondeletefiles)
  - [onDownloadFiles](#ondownloadfiles)
  - [onDownloadWithLink](#ondownloadwithlink)
  - [getFiles](#getfiles)
  - [getFileSelection](#getfileselection)
- [file_identifiers]()
- [context]()
- [Example implementation](#example-implementation)


## Methods
In order to implement a custom export option the following methods need to be defined.
Most methods do not offer a default implementation.

### init:
```php
public function init(Container $DIC): void
```
Is called once on creation of the object.
This method can be used to initalize dependencies.
The constructor cannot be used for this.
Class instances are created by using the implemented interface as a reference and as such the required constructor parameters would not be known on initialization.
To enshure that the constructor is not changed it is marked as final in **ilBasicExportOption**.

### getExportType:
```php
public function getExportType(): string;
```
This method returns the file type of the export.
The standard export returns _xml_. 
Other types could be _html_ or _json_.

### getExportOptionId:
```php
public function getExportOptionId(): string;
```
This method returns a unique identifier of this export option.
This is needed to differenciate between the export options of an repository object.
If multiple export options of an repository object share an identifier, they cannot be displayed together in the export tab.
The identifier of the standard xml export is _expxml_.

### getSupportedRepositoryObjectTypes:
```php
public function getSupportedRepositoryObjectTypes(): array
```
This method returns an array of repository object types, for example \['crs', 'grp'].
The export option is offered in the export tab of each repository object that matches one of the returned types. 


### isObjectSupported
```php
public function isObjectSupported(
    ObjectId $object_id
): bool;
```
This method returns true if the export option should be displayed in the export tab for an object with the given object id.

### isPublicAccessPossible:
```php
public function isPublicAccessPossible(): bool;
```
This method returns true if users should be allowed to mark this export option as 'Public Access' and false otherwise.
By default this setting is set to false in **ilBasicExportOption**.
Files that are marked as 'Public Access' can be accessed via download links, e.g. on he Info Page, and automatically harvested.


**The return value should not be set to true if files of this export option containe private information.**

### getLabel:
```php
public function getLabel(): string;
```
This method provides the label used by UI elements to display the export option in the export tab.

### onExportOptionSelected:
```php
public function onExportOptionSelected(
    ilExportHandlerConsumerContextInterface $context
): void;
```
This method is called if the export option is selected in the export tab.
Implement the behavior to create an export file here.
For example, the standard xml export forwards to the export selection table gui.
For further info on context see [Context](#context)

### onDeleteFiles:
```php
public function onDeleteFiles(
    ilExportHandlerConsumerContextInterface $context,
    ilExportHandlerConsumerFileIdentifierCollectionInterface $file_identifiers
): void;
```
This method implements deletion of files that match the [file_identifiers](#file_identifiers).
For further info on context see [context](#context).

### onDownloadFiles:
```php
public function onDownloadFiles(
    ilExportHandlerConsumerContextInterface $context,
    ilExportHandlerConsumerFileIdentifierCollectionInterface $file_identifiers
): void;
```
This method implements the download of files that match the [file_identifiers](#file_identifiers).
For further info on context see [context](#context).

### onDownloadWithLink:
```php
public function onDownloadWithLink(
    ReferenceId $reference_id,
    ilExportHandlerConsumerFileIdentifierInterface $file_identifier
): void;
```
This method implements the download of files that match the [file_identifier](#file_identifiers).
It is called if a resource is accessed via a download link.

### getFiles:
```php
public function getFiles(
    ilExportHandlerConsumerContextInterface $context
): ilExportHandlerFileInfoCollectionInterface;
```
This method collects all files that the export option has stored for the object in the current context and returns them as a file info collection.
The object in the current context can be accessed with the help of [context](#context).
A file info can be created in two ways, either from a **ResourceIdentification** or a **SplFileInfo**.
This choice determines value of the file identifiers that are provided to the other methods by [file_identifiers](#file_identifiers).

- If the file info is created by using a **ResourceIdentification**, than the [file_identifiers](#file_identifiers) hold the serialized resource identification.
- If the file info is created by using a **SplFileInfo**, than the [file_identifiers](#file_identifiers) hold a composit id of the export type and the file name.
The composit id structure is _\<export type>:\<file name>_.

[context](#context) offers a file info collection builder to help with the creation of the return value.

### getFileSelection:
```php
public function getFileSelection(
    ilExportHandlerConsumerContextInterface $context,
    ilExportHandlerConsumerFileIdentifierCollectionInterface $file_identifiers
): ilExportHandlerFileInfoCollectionInterface;
```
Similar to [getFiles](#getfiles), but should only return the files that match the [file_identifiers](#file_identifiers). 
For further info on context see [context](#context).

## file_identifiers:
_file_identifiers_ is a collection and can be iterated over:

```php
/** @var ILIAS\Export\ExportHandler\Consumer\File\Identifier\Collection $file_identifiers */
# iterate over file identifiers:
foreach ($file_identifiers as $file_identifier) {
    # Access the file identifier:
    $file_id = $file_identifier->getIdentifier();
}
# An array of all file identifiers as strings can also be accessed via:
$array_of_file_identifier_strings = $file_identifiers->toStringArray();
```
_file_id_ in the code above is either the file name or a resource id.
Which one can be decided in the implementation of [getFiles](#getfiles).

## context:
Context holds information about the export gui and the exported object.
It also offers a file info collection builder that can be used to create the return values of [getFiles](#getfiles) and
[getFileSelection](#getfileselection).

```php
/** @var ILIAS\Export\ExportHandler\I\Consumer\Context\HandlerInterface $context */
# Access the export gui object with:
$export_gui = $context->exportGUIObject();

# Access the export object with:
$export_obj = $context->exportObject()

# Create a file info collection:
$collection_builder = $context->fileCollectionBuilder();
# Add a ResourceIdentification
$collection_builder = $collection_builder->withResourceIdentifier(
    $resource_id,
    $object_id, # object id of the export object
    $this # the export option
);
# Or add a SPLFileInfo
$collection_builder = $collection_builder->withSPLFileInfo(
    $spl_file_info,
    $object_id, # object id of the export object
    $this # the export option
)
```

## Example implementation:
The implementation of the default xml export option can be found here: [Implementation](./classes/class.ilExportExportOptionXML.php)