# Roadmap

## Short Term

### Narrow the ini interfaces
`IliasIni` (23 getters) and `ClientIni` (20 getters) expose everything the two ini files contain to every
consumer. Each consumer uses a small slice: `HTTP` the https detection, `Filesystem` the directories, and
`Database` will need the connection settings (see its roadmap). Once the consumers that are migrated next are
known, their slices should become interfaces of their own (e.g. a `DatabaseConnectionIni`), implemented by
`IliasIniFile` / `ClientIniFile`, so a component depends on what it reads and not on the whole file.

### Remove `ilRuntime`
`ilRuntime` is deprecated in favour of `ILIAS\Environment\Configuration\Server\ServerConfiguration` and only
delegates to `PhpServerConfiguration`. It is still used by `FileDelivery\Delivery` and `ilErrorHandling`; both
should receive `ServerConfiguration` and the singleton can go.

## Mid Term

...

## Long Term

...
