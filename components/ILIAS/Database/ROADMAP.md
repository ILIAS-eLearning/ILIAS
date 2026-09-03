# Roadmap


## Short Term

### Put Database under Coordinator-Model
The goal is to put the Database under the Coordinator-Model. The service is crucial for the whole system and should not be managed by a single person.

### Hand out the Database through the Component Bootstrap
Today the component only declares `ILIAS\Database\PDO\External` and implements it with `DBLegacyProxy`
(`src/DBLegacyProxy.php`), 633 lines that forward every call to `$GLOBALS['DIC']['ilDB']`. The connection
itself is still built outside the component, by `ilInitialisation::initDatabase()` via
`ilDBWrapperFactory::getWrapper(IL_DB_TYPE)`.

The goal is that the component builds and hands out the connection itself:

- `Database.php` takes `ILIAS\Environment\Configuration\Instance\ClientIni` and derives both the driver
  details (`InnoDBDetails` / `GaleraDetails`) and the credentials from it, instead of `IL_DB_TYPE` and
  `ilDBPdo::initFromIniFile()` reading `$DIC['ilClientIniFile']`. That interface already exposes
  `getDatabaseType()`, `-Host()`, `-User()`, `-Password()` and `-Name()`; only a getter for `db.port` is
  missing.
- `$DIC['ilDB']` becomes a plain binding in `AllModernComponents`, `ilInitialisation::initDatabase()` is
  emptied, and `DBLegacyProxy` is deleted.

One constraint shapes the solution: the bootstrap reader executes `$implement[]` closures at build time
(`Component/src/Dependencies/Reader.php`), so nothing may connect there. `ilDBPdo::__construct()` only takes
its `Details` and does not connect, so the object can be built at build time as long as `connect()` happens on
first use. `ReflectionClass::newLazyProxy()` is the tool for that step, once the component hands out a
concrete class of its own; it cannot replace `DBLegacyProxy` today, because it needs an instantiable class and
the bootstrap wires interfaces.

Setup keeps its own path: `ilDatabaseSetupAgent` builds a connection from the data an installer enters, before
any client ini exists.


## Mid Term

### Narrow the contract other components consume
`External` inherits the complete legacy surface: 88 methods from `ilDBInterface` plus 22 from
`ilDBPdoInterface`. The components that have been migrated to the bootstrap so far (AccessControl, Filesystem,
Logging, ResourceStorage) call 20 distinct methods of it, and four of them carry most of the traffic: `quote`,
`query`, `manipulate` and `fetchObject`. Schema manipulation, which makes up a large part of the surface,
belongs to setup and update steps, not to a component asking for rows.

The goal is a small, driver-agnostic contract for reading and writing rows, with `External` kept as a
deprecated alias until its consumers have moved over.

Two open questions resolve themselves at that point, and only there:

- **The name.** `External` was chosen in contrast to the pre-existing `Internal`, i.e. "the part of the legacy
  interface other components may see". A name that says what the contract does (`Facade`, `Gateway`, `Consumer`
  or something closer to querying) is worth having, but attaching it to 110 inherited legacy methods would put
  a good name on the thing we intend to remove.
- **The namespace.** Consumers currently depend on `ILIAS\Database\PDO`. Moving the interface up to
  `ILIAS\Database` while it still extends `ilDBPdoInterface` would hide the coupling rather than remove it.
  Once the contract no longer inherits PDO specifics, the move is honest and mechanical.

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

