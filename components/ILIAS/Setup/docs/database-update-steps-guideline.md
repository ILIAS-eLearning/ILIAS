# Database Update Steps Guideline

This document defines how database update steps **SHOULD** be implemented, so the database in kept in a consistent state
accross multiple major releases and different branches of the repository. It serves as a reference for [authorities who
sign off on code changes](../../../../docs/development/maintenance.md#authorities) and guides all ILIAS developers who
perform database updates.

This guide will refer to database updates as schmea updates (DDL), but it possibly applies to other operations (DML,
DQL, DCL) as well. Please note that we have [migrations](../README.md#on-migration) for more complex DML operations.

## Table of Contents

1. [How ILIAS Executes Database Updates](#1-how-ilias-executes-database-updates)
2. [Why Version-Namespaced Classes Are Recommended](#2-why-version-namespaces-are-recommended)
3. [Best Practices & Examples](#3-best-practices--examples)

## 1. How ILIAS Executes Database Updates

We use the [ILIAS Setup component](../README.md) to execute database updates. Other (ILIAS) components can provide an
implementation of the `\ilDatabaseUpdateSteps` interface, which is a collection of sequential database update
steps, that is achieved using the Setup's `\ilDatabaseUpdateStepsExecutedObjective` objective provided by their agent.

The Setup component tracks which steps have been executed, so updates are not performed more than once. This is done
using a combination of:

- the step number (gathered from your `step_<nr>()` methods)
- the FQDN of your implementation (class that implements `\ilDatabaseUpdateSteps`)

**It is therecore important that the FQDN of an implementation MUST NOT change.** Otherwise update steps are considered
new and are potentially executed more than once.

### 1.1 Database Update Steps Lifespan

Every `\ilDatabaseUpdateSteps` implementation has a finite lifespan, which is determined by when it was introduced and
how ILIAS installations move through different major versions.

ILIAS does not allow you to skip a major version during an upgrade (e.g. jumping directly from 8 to 10). However, minor
versions within a major version **COULD** be skipped. Since minor versions are also released after the next major
version is already published, this cannot be enforced properly. This means an installation which upgrades to a new major
version must also execute any update steps which have been skipped within the previous major version.

Now it becomes clear, that the next major versions **MUST** carry the same and possibly skipped database update steps
forward until the next major version is released. This guarantees that all update steps are eventually executed in the
appropriate order.

Concretely: if a skipped minor version falls under major version `n`, its update steps must still be executable when
upgrading to version `n+1`. To cover all such cases, every `\ilDatabaseUpdateSteps` implementation **MUST** remain in
the codebase until the initial release of version `n+2`, where it can be safely removed.  The initial release of a major
version is also the point at which the [database template](../../setup/sql/ilias3.sql) for new installations is updated,
which is why it serves as the safe removal point.

## 2. Why Version Namespaces Are Recommended

ILIAS supports the parallel maintenance of multiple major versions, typically two fully maintained versions, one version
for development, and one older version receiving only security bugfixes. This totals up to four distinct Git branches
which are maintained at some point in the ILIAS lifecycle.

If an implementation of `\ilDatabaseUpdateSteps` is updated in an older version but development has already continued
in a newer version of ILIAS, then database inconsistencies might be introduced due to divergent `step_<x>()` methods. In
other words, when a new `step_<x>()` method is added to an implementation on one branch, the same implementation on
another branch **MUST NOT** implement the same method for a different purpose.

The safest way to guarantee this is to **give each major version its own class**, so their FQDN never overlap. This way
e.g. `ILIAS\ComponentX\Setup\Database\V10\UpdateSteps::step_2()` is completely independent from
`ILIAS\ComponentX\Setup\Database\V11\UpdateSteps::step_2()`.

### 2.2. Recommended Namespace Pattern

To implement this consistently accross all ILIAS components and prevent possibly divergent database update steps,
developers **SHOULD** follow this namespace pattern:

```
<Vendor>\<Component>\<PathToUpdateSteps>\<Version>
```

Whereas the placeholders are replaced like:

- `<Vendor>`: the provider of your component (e.g. `ILIAS`)
- `<Component>`: the name of your component (e.g. `Setup`, `ResourceStorage`)
- `<PathToUpdateSteps>`: desired path/structure to your Setup-related classes and update steps (e.g. `Setup\Database`)
- `<Version>`: the ILIAS major version, prefixed with capital "V" (e.g. `V10`, `V11`)

## 3. Best Practices & Examples

### 3.1. Introducing Database Update Steps

To introduce new database update steps your component **MUST** implement the `\ilDatabaseUpdateSteps` interface, which
**SHOULD** be namespaced as described by the previous chapter. The interface description explains how methods **MUST**
look like, so the Setup can find and execute them properly.

```php
namespace ILIAS\ComponentX\Setup\Database\V10;

/** @since ILIAS 10 */
class DatabaseUpdateStepsOfX implements \ilDatabaseUpdateSteps
{
    public function step_1(): void
    {
        $this->db->createTable('x', [...]);
    }
}
```

If your database update steps are introduced to two or more supported versions, we still recommend to provide a
dedicated class for each major version, to guarantee other developers do not accidentally introduce divergent update
steps (i.e. `step_<x>()` methods).

To contribute your update steps to the system, your component needs to implement an `ILIAS\Setup\Agent` which returns an
instance of the `\ilDatabaseUpdateStepsExecutedObjective` objective that receives an instance of your
`\ilDatabaseUpdateSteps` implementation in `ILIAS\Setup\Agent::getInstallObjective()` or `::getUpdateObjective()`,
depending on your goal. Read the respective method descriptions for detailed instructions.

```php
namespace ILIAS\ComponentX\Setup;

class AgenfOfX implements \ILIAS\Setup\Agent
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

Bonus tip: use an `ILIAS\Setup\ObjectiveCollection` if you have more than one `\ilDatabaseUpdateSteps` implementation.

### 3.2. Grouping Database Update Steps

Besides using version namespaces, we also recommend to group database update steps strategically, rather than
consolidating them all in one single `\ilDatabaseUpdateSteps` implementation. This allows you to:

- remove your database update steps **at the end of their lifespan**, and
- ensure your objective(s) can be executed in a timely manner.

By strategically we mean that update steps **COULD** be grouped by the kind of operation, the table or -column they
affect, or a feature or bugfix they implement. There is no best strategy and this is primarily shaped by preference, but
its something to keep in mind. Smaller more and dedicated classes help others understand the goal of your database
updates and can even expresses what should happen to the database programatically:

```php
namespace ILIAS\ComponentX\Setup;

class AgenfOfX implements \ILIAS\Setup\Agent
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

Bonus tip: use an `ILIAS\Setup\ObjectiveWithPreconditions` to control the order of your `\ilDatabaseUpdateSteps`.

### 3.2. Removing Database Update Steps

As mentioned in previous chapters, database update steps have a finite lifespan, after which they **SHOULD** be removed
from the code-base to prevent the accumulation of unused code and reduce the maintenance overhead. An
`\ilDatabaseUpdateSteps` implementation reaches EOL `n+2` major versions after its introduction. At this point the
upgrade strategy and the release process of ILIAS will ensure that these update steps have been executed and are
contained inside the database template for new installations. This makes the implementations obsolete.

When removing database update steps you **MUST** ensure:

- only entire classes are removed, never individual `step_<x>()` methods (would cause divergence), and
- no additional `step_<x>()` methods were added since the introduction, otherwise the `n+2` resets to the last update.

To verify that an `\ilDatabaseUpdateSteps` implementation was not updated since its introduction, you can run the
following Git command:

```bash
SINCE="<branch>" FILE="<path>" sh -c 'git diff HEAD..$SINCE -- $FILE'
```

Whereas the placeholders are replaced like:

- `<branch>`: the branch of the major version of `n-2` (e.g. `release_10` for `trunk`, `release_9` for `release_11`)
- `<path>`: the path to the file you want check (e.g. `components/ILIAS/ComponentX/Setup/Database/V10/FooBar.php`)

If there were no new `step_<x>()` methods, the specified file can safely be removed.
