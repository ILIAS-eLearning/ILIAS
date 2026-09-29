# Roadmap

## Short Term

### Break the cycle between `Migrator` and `ResourceBuilder` without a closure
`ResourceBuilder` needs the `Migrator`, and the `Migrator` needs to remove resources whose file is missing,
which is a `ResourceBuilder` operation. The cycle is currently broken by passing `Migrator` a
`\Closure(StorableResource): void` that calls `ResourceBuilder::remove()`. Extracting the removal into a
service of its own that both depend on would remove the cycle instead of deferring it.

### Remove `InitResourceStorage`
`Init\Dependencies\InitResourceStorage` is no longer used at runtime. It is kept for
`ilResourceStorageMigrationHelper` in the setup and for its `D_*` constants. Both should move into the
component, then the class can be deleted.

## Mid Term

...

## Long Term

...
