# Roadmap

## Short Term

### Remove the deprecated legacy classes
- `ilFileServicesPolicy` extends `ILIAS\FileServices\Policy\FileServicesPolicy` and is still used in five
  other files.
- `ilFileServicesSettings` decorates `ILIAS\Filesystem\Configuration\FilesystemConfig`.
  `Container::fileServiceSettings()` already returns `FilesystemConfig`; the remaining eleven consumers of the
  accessor should receive `FilesystemConfig` through their constructor, after which both the accessor and the
  class can go.

### Replace `UploadRestrictionBypassLegacyProxy`
The permission to upload files the restrictions would reject is checked through `$DIC->rbac()`, because
AccessControl offers no public permission check yet (see the DI roadmap). The proxy should be replaced by an
implementation using that check once it exists.

## Mid Term

...

## Long Term

...

See also the roadmaps of [Filesystem](../Filesystem/ROADMAP.md), [FileUpload](../FileUpload/ROADMAP.md) and
[ResourceStorage](../ResourceStorage/ROADMAP.md).
