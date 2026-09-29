# Roadmap

## Short Term

### Settle the open questions of the migration guideline
The review of [PR 11430](https://github.com/ILIAS-eLearning/ILIAS/pull/11430) raised three questions that
concern every migration and were deliberately not answered for single components there. They should be
decided in `docs/component-bootstrap-migration.md` and then be applied to the already migrated components at
once:

- **FQDN instead of imports** in `<Component>.php`: currently a recommendation, not a rule.
- **Naming of interface and implementation**: the migrated components use `<Interface>` with `<Interface>Impl`
  or `Default<Interface>`; alternatives on the table are separate `Interface`/`Implementation` namespaces
  (as in the UI framework) and the PSR style `<Name>Interface`.
- **Closure or proxy** to retrieve a dependency late: closures are used where a dependency is not available at
  build time (`VirusScannerPreProcessor`) or to break a cycle (`ResourceStorage\StorageHandler\Migrator`). The
  guideline should say whether this is acceptable or whether a named proxy is expected.

A fourth point worth a sentence in the guideline: the bootstrap validates `$implement[]` with `instanceof`, so
services that share an implementation but are wired as distinct types (like the six configured filesystems)
need one class per type.

## Mid Term

...

## Long Term

...