# How to do Database Updates in ILIAS?

Basically we can distinguish between two types of database updates: **Schema updates**
that change the structure of the database and **migrations** that move around data
from one place to the other.

Schema updates need to be performed after an update, before the installation goes
into production again, because ILIAS relies on the database to have a certain
structure. So these updates should be light and run quickly. Migrations are
concerned with potentially heavy tasks on the database, that might be performed
in the background while the system already is productive again.

Both variants are triggered by the [Setup](../../components/ILIAS/Setup/README.md), so make sure
you have a basic understanding of how the setup works before looking into updating
the database. Please also make sure you understand the [lifecycle of database update
steps](../../components/ILIAS/Setup/docs/database-update-steps-lifecycle.md), and how
they are managed accross multiple major releases.

Previous versions of ILIAS supported the so called `db-update-files`. These files
will keep on working for some time technically, but are deprecated as decided by
the [Jour Fixe on 2021-06-08](https://docu.ilias.de/goto_docu_wiki_wpage_5889_1357.html).

General directions on how to use the database in ILIAS are to be found [in the according
readme of ILIAS/Database](components/ILIAS/Database/README.md).

## Schema Updates

Make sure to understand, that this mechanism really is about schema updates.
Do not perform other kinds of updates (e.g. the migrations, creating files, ...)
with this. There is a more general mechanism (the [`Objectives`](../../components/ILIAS/Setup/README.md#on-objective))
to do this.

To introduce new database update steps your component **MUST** implement the `\ilDatabaseUpdateSteps` interface, which
**SHOULD** be namespaced as described by the previous chapter. The interface description explains how methods **MUST**
look like, so the Setup can find and execute them properly. Only use the provided `\ilDBInterface` in the methods. Do
not use other things from the environment or the globals, they might not be there if you need them.

```php
namespace ILIAS\ComponentX\Setup\Database\V10;

/** @since ILIAS 10 */
class DatabaseUpdateStepsOfX implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(ilDBInterface $db) : void
    {
        $this->db = $db;    
    }

    public function step_1(): void
    {
        $this->db->createTable('x', [...]);
    }
    
    public function step_2(): void
    {
        // ...
    }
}
```

**WARNING: It is important that the FQDN of an implementation MUST NOT change.** Otherwise update steps are considered
new and are potentially executed more than once. We also recommend to use 
[version namespaces](../../components/ILIAS/Setup/docs/database-update-steps-lifecycle.md#33-recommended-namespace-pattern)
This also means that legacy naming conventions which are still prevalent to this day, **MUST NOT** be addressed or
migrated in any way. They can be decalt with once they have reached the end of their lifespan and can be safely removed.

If your database update steps are introduced to two or more supported versions, we still recommend to provide a
dedicated class for each major version, to guarantee other developers do not accidentally introduce divergent update
steps (i.e. `step_<x>()` methods).

To contribute your update steps to the system, your component needs to implement an `ILIAS\Setup\Agent` which returns an
instance of the `\ilDatabaseUpdateStepsExecutedObjective` objective that receives an instance of your
`\ilDatabaseUpdateSteps` implementation in `ILIAS\Setup\Agent::getInstallObjective()` or `::getUpdateObjective()`,
depending on your goal. Read the respective method descriptions for detailed instructions.

```php
namespace ILIAS\ComponentX\Setup;

class AgentOfX extends \ILIAS\Setup\Agent\NullAgent
{
    // ...

    public function getUpdateObjective(?\ILIAS\Setup\Config $config = null): \ILIAS\Setup\Objective
    {
        return new \ilDatabaseUpdateStepsExecutedObjective(
            new \ILIAS\ComponentX\Setup\Database\V10\ilDatabaseUpdateStepsOfX(),
        );
    }
}
```

Use an `ILIAS\Setup\ObjectiveCollection` here if you have more than one `\ilDatabaseUpdateSteps` implementation (see 
[Grouping Database Update Steps](#grouping-database-update-steps)). Use an 
`ILIAS\Setup\Objective\ObjectiveWithPreconditions` to control the order of your `\ilDatabaseUpdateSteps`:

```php
namespace ILIAS\ComponentX\Setup;

class AgentOfX extends \ILIAS\Setup\Agent\NullAgent
{
    // ...

    public function getUpdateObjective(?\ILIAS\Setup\Config $config = null): \ILIAS\Setup\Objective
    {
        return new \ILIAS\Setup\Objective\ObjectiveWithPreconditions(
            // last:
            new \ilDatabaseUpdateStepsExecutedObjective(
                new \ILIAS\ComponentX\Setup\Database\V10\AlterTableXAddColumnY()
            ),
            // first:
            new \ilDatabaseUpdateStepsExecutedObjective(
                new \ILIAS\ComponentX\Setup\Database\V10\CreateTableX()
            ),
            // second, third, ...
        );
    }
}
```

To ensure that the setup/status command will output the current database step status
of your component add the method `getStatusObjective()` to your Agent as well: 

```php
namespace ILIAS\ComponentX\Setup;

class AgentOfX extends \ILIAS\Setup\Agent\NullAgent
{
    // ...

    public function getStatusObjective(Setup\Metrics\Storage $storage): Setup\Objective
    {
        return new \ilDatabaseUpdateStepsMetricsCollectedObjective(
            $storage,
            new \ILIAS\ComponentX\Setup\Database\V10\ilDatabaseUpdateStepsOfX(),
        );
    }
}
```

## Grouping Database Update Steps

Besides using version namespaces, we also recommend to group database update steps strategically, rather than
consolidating them all in one single `\ilDatabaseUpdateSteps` implementation. This allows you to:

- remove your database update steps **at the end of their lifespan**, and
- ensure your objective(s) can be executed in a timely manner.

By strategically we mean that update steps **COULD** be grouped by the kind of operation, the table or -column they
affect, or a feature or bugfix they implement. There is no best strategy and this is primarily shaped by preference, but
its something to keep in mind. Smaller and more dedicated classes help others understand the goal of your database
updates and can even expresses what should happen to the database programatically:

```php
namespace ILIAS\ComponentX\Setup;

class AgentOfX implements \ILIAS\Setup\Agent
{
    // ...

    public function getUpdateObjective(?ILIAS\Setup\Config $config = null): \ILIAS\Setup\Objective
    {
        return new \ILIAS\Setup\ObjectiveCollection(
            "Database update steps of Component X for ILIAS 10",
            true,
            new \ilDatabaseUpdateStepsExecutedObjective(new \ILIAS\ComponentX\Setup\Database\V10\CreateFooTable()),
            new \ilDatabaseUpdateStepsExecutedObjective(new \ILIAS\ComponentX\Setup\Database\V10\UpdateFooBarDefaultValue()),
            new \ilDatabaseUpdateStepsExecutedObjective(new \ILIAS\ComponentX\Setup\Database\V10\AlterFooBazMaxLength()),
            new \ilDatabaseUpdateStepsExecutedObjective(new \ILIAS\ComponentX\Setup\Database\V10\DeleteUnusedFooEntries()),
        );
    }
}
```

The example above demonstrates how speaking and grouped database update steps can already convey much of the information
on a programming level – without having a look at its concrete steps. The objective communicates very clearly that
throughout the major version 10 a new `foo` table will be added, whose `foo.bar` default value and `foo.baz` column type
is updated, and some unused entries are cleaned up.

## Migrations

To create migrations, you start similar to the database update steps. Create a class
that derives from `ILIAS\Setup\Agent`. The class MUST be in a subfolder `Setup` of
your component, maybe just extend from `NullAgent` for simplicity. Implement the
method `getMigrations`.

```php
class MySetupAgent extends NullAgent
{
    public function getMigrations() : array
    {
        return [
            "my_migration" => new MyMigration()
        ];
    }
}
```

The migration then needs to implement `ILIAS\Setup\Migration`:

```php
class MyMigration implements Setup\Migration
{
    /**
     * @return string - a meaningful and concise description for your migration.
     */
    public function getLabel() : string
    {
        return "Your migration label here.";
    }

    /**
     * Tell the default amount of steps to be executed for one run of the migration.
     * Return Migration::INFINITE if all units should be migrated at once.
     */
    public function getDefaultAmountOfStepsPerRun() : int
    {
        return 10;
    }

    /**
     * Objectives the migration depends on.
     *
     * @throw UnachievableException if the objective is not achievable
     * @return Objective[]
     */
    public function getPreconditions(Environment $environment) : array
    {
        return [];
    }

    /**
     * Prepare the migration by means of some environment.
     *
     * This is not supposed to modify the environment, but will be run to prime the
     * migration object to run `step` and `getRemainingAmountOfSteps` afterwards.
     */
    public function prepare(Environment $environment) : void
    {
        // Prepare the environment for the following steps here.
    }

    /**
     *  Run one step of the migration.
     */
    public function step(Environment $environment) : void
    {
        // Perform one step of the migration here.
    }

    /**
     * Count up how many "things" need to be migrated. This helps the admin to
     * decide how big he can create the steps and also how long a migration takes
     */
    public function getRemainingAmountOfSteps() : int
    {
        // Make some calculation to return the remaining amount of steps
    }
}
```

The `Migration`-interface makes it possible to break down a migration into distinct
steps. This allows administrators to control and monitor the migrations, which
potentially take a lot of time, closely. When the migration is executed, `prepare`
will be called first and allows the migration to pull required resources from the
environment. Via `getPreconditions`, the migration can announce which other
`Objective`s need to be achieved first to fill the environment with the required
resources. With `getRemainingAmountOfSteps` you can tell the setup, how many steps
still need to be performed to finish the migration. When the administrator requests
migration steps to be performed, the `step` method will be called to perform the
single steps.
