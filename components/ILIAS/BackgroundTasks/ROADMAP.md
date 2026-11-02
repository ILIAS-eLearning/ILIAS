# Roadmap

## Mid Term

### Build all Tasks and Values from their Contributions

Components contribute their tasks and values to the bootstrap, see `Registry\Registry`. The task
factory and the persistence clone these contributions. A task or value nobody contributed is still
built by the `Dependencies\Injector`, which resolves its constructor arguments from the legacy
container. This fallback is needed as long as not all components contribute their tasks and values,
and as long as plugins cannot contribute to the bootstrap at all.

The goal is to build all tasks and values from their contributions and to remove the `Injector`
together with its `DependencyMap`. Before that, every component putting tasks or values into
buckets has to contribute them and pass their dependencies through the constructor, and plugins
need a way to contribute theirs.

### Move the Initialisation out of ilInitialisation

The task factory, the persistence, the task manager and the injector are still set up in
`ilInitialisation::initBackgroundTasks()` and `ilInitialisation::initInjector()` and reach the
rest of the system through the legacy container, while only the `Registry` comes from the bootstrap.
Scripts calling `ilInitialisation::initILIAS()` directly get an empty `Registry`. The goal is to
initialise BackgroundTasks completely through the component bootstrap, as described in
`components/ILIAS/Component/docs/component-bootstrap-migration.md`, and to bridge the services into
the legacy container through `AllModernComponents` as long as they are needed there. This includes
putting the settings read from `ilias.ini.php` (`concurrency`, `number_of_concurrent_tasks`) behind
a configuration interface and replacing the static `BasicPersistence::instance()` and the global
`$DIC` it uses.
