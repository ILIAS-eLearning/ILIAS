# Roadmap


## Short Term

### Put Database under Coordinator-Model
The goal is to put the Database under the Coordinator-Model. The service is crucial for the whole system and should not be managed by a single person.

### Hand out the Database through the Component Bootstrap
Today the component provides `ILIAS\Database\Connection` as a lazy proxy of `ilDBPdo`
(`ReflectionClass::newLazyProxy()`), whose initializer returns `$GLOBALS['DIC']->database()`. The connection
itself is still built outside the component, by `ilInitialisation::initDatabase()` via
`ilDBWrapperFactory::getWrapper(IL_DB_TYPE)`.

The goal is that the component builds and hands out the connection itself:

- `Database.php` takes `ILIAS\Environment\Configuration\Installation\ClientIni` and derives both the driver
  details (`InnoDBDetails` / `GaleraDetails`) and the credentials from it, instead of `IL_DB_TYPE` and
  `ilDBPdo::initFromIniFile()` reading `$DIC['ilClientIniFile']`. That interface already exposes
  `getDatabaseType()`, `-Host()`, `-User()`, `-Password()` and `-Name()`; only a getter for `db.port` is
  missing.
- The initializer of the lazy proxy then connects that `ilDBPdo` instead of reaching into `$DIC`,
  `$DIC['ilDB']` becomes a plain binding in `AllModernComponents`, and `ilInitialisation::initDatabase()` is
  emptied.

Two properties of the lazy proxy have to be kept in mind until then:

- The initializer must return an instance of exactly `ilDBPdo`; a subclass or a test double registered as
  `$DIC['ilDB']` fails with a `TypeError` on first use. `ilDBWrapperFactory::getWrapper()` always returns an
  `ilDBPdo` today.
- A lazy object initializes on property access, not on method entry. Methods of `ilDBPdo` that do not touch
  a property run on the uninitialized instance.

One constraint shapes the final solution: the bootstrap reader executes the closures at build time
(`Component/src/Dependencies/Reader.php`), so nothing may connect there. `ilDBPdo::__construct()` only takes
its `Details` and does not connect, so the object can be built at build time as long as `connect()` happens on
first use, which is exactly what the lazy proxy provides.

Setup keeps its own path: `ilDatabaseSetupAgent` builds a connection from the data an installer enters, before
any client ini exists.


## Mid Term

### Narrow the contract other components consume
`Connection` inherits the complete legacy surface of `ilDBInterface`. The components that have been migrated
to the bootstrap so far (AccessControl, Filesystem, Logging, ResourceStorage) call a small part of it, and four
methods carry most of the traffic: `quote`, `query`, `manipulate` and `fetchObject`. Schema manipulation,
which makes up a large part of the surface, belongs to setup and update steps, not to a component asking for
rows.

The goal is a small, driver-agnostic contract for reading and writing rows that `Connection` narrows down to,
so no builder or mapper has to reimplement the full legacy interface.

### Reduce the number of consumers
Around 2400 call sites still reach the database through `$DIC->database()` or `$DIC['ilDB']`, and roughly 450
signatures type against `ilDBInterface`. Each component that gets revised is an opportunity to convert its own
share to an injected dependency; there is no separate project that could do this centrally.

### Project: Establish Referential Integrity
Currently (ILIAS 8) ILIAS doesn't use advanced database-built-in functionalities that ensure the integrity of stored data.

The benefits ILIAS demands from the Database Management System (DBMS) regarding data value correctness on field-level currently are:

- uniqueness, by primary or unique indexes
- 'not null' (without a defined default) for essential required fields
- low-level datatype warranty on field-level, reasonable for numeric- and date/time-types, sometimes poor for unspecific varchar fields (examples: email and client_ip in `usr_data`)
- 
To improve data quality and to support code maintaining modern DBMSs offer several options ILIAS COULD use:

- Referential Integrity (Foreign Keys)
- Stored Procedures & Functions
- Trigger

For more information visit the project page: [Project: Establish Referential Integrity](https://docu.ilias.de/goto_docu_wiki_wpage_7319_1357.html) 


## Long Term

### Query-Builder and and ORM 
The goal is to implement a Query-Builder and an ORM or move to a framework which provides these features such as Doctrine.

The narrowed contract described under Mid Term is the precondition: as long as components consume the full
`ilDBInterface`, no builder or mapper can be put underneath them without reimplementing that surface.

