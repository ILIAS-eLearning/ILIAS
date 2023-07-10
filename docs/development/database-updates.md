# How to do Database Updates in ILIAS?

Basically we can distinguish between two types of database updates: **Schema updates**
that change the structure of the database and **migrations** that move around data
from one place to the other.

Schema updates need to be performed after an update, before the installation goes
into production again, because ILIAS relies on the database to have a certain
structure. So these updates should be light and run quickly. Migrations are
concerned with potentially heavy tasks on the database, that might be performed
in the background while the system already is productive again.

Both variants are triggered by the [setup](src/Setup/README.md), so make sure
you have a basic understanding of how the setup works before looking into updating
the database.

Previous versions of ILIAS supported the so called `db-update-files`. These files
will keep on working for some time technically, but are deprecated as decided by
the [Jour Fixe on 2021-06-08](https://docu.ilias.de/goto_docu_wiki_wpage_5889_1357.html).

General directions on how to use the database in ILIAS are to be found [in the according
readme of Services/Database](Services/Database/README.md).

## Schema Updates

To create a schema update, you first need an integration with the setup. Create
a class that implements `ILIAS\Setup\Agent`, you MUST put it in the subfolder
`classes/Setup` in your component. If you only want to introduce some update steps
you could just extend from the `NullAgent`.

```php
use ILIAS\Setup;

class MySetupAgent extends Setup\Agent\NullAgent
{
}
```

Your actual updates of the database go into another file, which implements the
`ilDatabaseUpdateSteps` interface. The name SHOULD always start with `il$COMPONENT`
and end with `Steps`. You will want to put something descriptive in between, e.g.
`ilMyComponentSettingsTableSteps`. The file SHOULD always be put into the same folder
as the agent. You MAY put your steps in a `Steps` folder in the `Setup`-folder, if
you need further order in your folder. In the class, you need to implement one
`prepare` method that will be called before the steps actually get executed. The
setup will pass an `ilDBInterface`-instance to be used by the steps and it is recommended
to store the `ilDBInterface` into a property as shown below.

```php
class ilMyDBUpdateSteps implements ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db)
    {
        $this->db = $db;
    }
}
```

This new class then needs to be wired into the Agent:

```php
use ILIAS\Setup;

class MySetupAgent extends Setup\Agent\NullAgent
{
    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new ilDatabaseUpdateStepsExecutedObjective(new ilMyDBUpdateSteps());
    }
}
``` 

To ensure that the setup/status command will output the current database step status
of your component add the method `getStatusObjective` to your Agent. 

```php
use ILIAS\Setup;

class MySetupAgent extends Setup\Agent\NullAgent
{
    public function getStatusObjective(Setup\Metrics\Storage $storage) : Setup\Objective
    {
        return new ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new MyDBUpdateSteps());
    }
}
``` 

In the MyDBUpdateSteps you can add your consecutive steps by adding methods according
to this schema:

```php
class ilMyDBUpdateSteps implements ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function step_1()
    {
        //... your code using $this->db
    }

    public function step_2()
    {
        //... your code using $this->db
    }
}
```

The setup mechanism will call your step-methods in ascending order.
It will keep track about which method was already called and takes care
that the methods are only called once.

A few words of warning:

* Make sure to understand, that this mechanism really is about schema updates.
Do not perform other kinds of updates (e.g. the migrations, creating files, ...)
with this. There is a more general mechanism (the [`Objectives`](src/Setup/README.md#on-objective))
to do this.
* Only use the provided `\ilDBInterface` in the methods. Do not use other things from
the environment or the globals, they might not be there if you need them.
* It will be easier if one of your database update steps takes care of one table
or a set of closely related tables. You can have multiple classes with database update
steps by using multiple `ilDatabaseUpdateStepsExecutedObjective`s bundled via an
`ObjectiveCollection`.


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
will be called first and allows the migration to pull required ressources from the
environment. Via `getPreconditions`, the migration can announce which other
`Objective`s need to be achieved first to fill the environment with the required
ressources. With `getRemainingAmountOfSteps` you can tell the setup, how many steps
still need to be performed to finish the migration. When the administrator requests
migration steps to be performed, the `step` method will be called to perform the
single steps.
