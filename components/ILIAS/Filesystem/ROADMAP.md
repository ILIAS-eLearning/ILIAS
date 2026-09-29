# Roadmap

## Short Term

### Remove the static `FilesystemsAware` trait
`FilesystemsAware` reads `$DIC->filesystem()` into a static property. Its users should receive the
`Filesystems` service (or the single filesystem they need) through their constructor instead.

## Mid Term

### Reconsider the configured filesystem hierarchy
`ConfiguredFilesystem` is extended by six classes (`ConfiguredFilesystemWeb`, `-Storage`, `-Temp`,
`-Customizing`, `-Libs`, `-NodeModules`) that only pass their location to the constructor. They exist
because the bootstrap needs a distinct type per service. If the migration guideline settles on a different
way to wire several instances of one type, the subclasses can collapse into one class.

## Long Term

...
