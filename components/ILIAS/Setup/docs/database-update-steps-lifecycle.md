# Database Update Steps Lifecycle

This document defines how database update steps **SHOULD** be implemented, so the database is kept in a consistent state
accross multiple major releases and different branches of the repository. It serves as a reference for [authorities who
sign off on code changes](../../../../docs/development/maintenance.md#authorities) and guides all ILIAS developers who
perform database updates.

This guide will refer to database updates as schema updates (DDL), but it possibly applies to other operations (DML,
DQL, DCL) as well. Please note that we have [migrations](../README.md#on-migration) for more complex DML operations.

If you are looking for how to implement database update steps in general, please refer to the document on
[database updates](../../../../docs/development/database-updates.md).

## Table of Contents

TBD

## 1. How ILIAS Executes Database Updates

We use the [ILIAS Setup component](../README.md) to execute database updates. Other (ILIAS) components can provide an
implementation of the `\ilDatabaseUpdateSteps` interface, which is a collection of sequential database update
steps, that is achieved using the Setup's `\ilDatabaseUpdateStepsExecutedObjective` objective provided by their agent.

The Setup component tracks which steps have been executed, so updates are not performed more than once. This is done
using a combination of:

- the step number (gathered from your `step_<nr>()` methods)
- the FQDN of your implementation (class that implements `\ilDatabaseUpdateSteps`)

**WARNING: It is important that the FQDN of an implementation MUST NOT change.** Otherwise update steps are considered
new and are potentially executed more than once.

### 2. Database Update Steps Lifespan

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
the codebase until the initial release of version `n+2`, where it can be safely removed. The initial release of a major
version is also the point at which the [database template](../../Database/sql/ilias3.sql) for new installations is
updated, which is why it serves as the safe removal point.

### 3. Best Practices

ILIAS supports the parallel maintenance of multiple major versions, typically two fully maintained versions, one version
for development, and one older version receiving only security bugfixes. This totals up to four distinct Git branches
which are maintained at some point in the ILIAS lifecycle.

If an implementation of `\ilDatabaseUpdateSteps` is updated in an older version but development has already continued
in a newer version of ILIAS, then database inconsistencies might be introduced due to divergent `step_<x>()` methods. In
other words, when a new `step_<x>()` method is added to an implementation on one branch, the same implementation on
another branch **MUST NOT** implement the same method for a different purpose.

To avoid such inconsistencies, we can implement database update steps according to the recommended practices in the
following chapters.

### 3.1. Update steps for one major version

Database update steps which are only introduced to one major version can easily avoid divergent update steps by using a
version namespace. These are usually update steps related to features, which are added during the development phase in
trunk, but it could also affect bugfixes which need to be tailored to one major version.

The safest way to guarantee this is to **give the major version its own class**, so the FQDN of this update step never
overlaps. This way e.g. `ILIAS\ComponentX\Setup\Database\V10\UpdateSteps::step_2()` is completely independent from
`ILIAS\ComponentX\Setup\Database\V11\UpdateSteps::step_2()`.

We recommend developers **SHOULD** use following [version namespace pattern](#33-recommended-namespace-pattern).

#### 3.1.2. Example

This example demonstrates the lifecycle of a database update step introduced during development for trunk back when
release_11 was the latest major release.

| `n`   | Version          | Branch     | Action                                                     |
|-------|------------------|------------|------------------------------------------------------------|
| `n-1` | ILIAS 11.X       | release_11 | -                                                          |
| `n`   | ILIAS 12.0-alpha | trunk      | Add `ILIAS\ComponentX\Setup\Database\V12\CreateTableX`     |
| `n`   | ILIAS 12.0       | release_12 | Major release of ILIAS 12                                  |
| `n+1` | ILIAS 13.0-alpha | trunk      | Open trunk for development of ILIAS 13                     |
| `n+1` | ILIAS 13.0-alpha | release_13 | Keep `ILIAS\ComponentX\Setup\Database\V12\CreateTableX`    |
| `n+1` | ILIAS 13.0       | release_13 | Major release of ILIAS 13                                  |
| `n+2` | ILIAS 14.0-alpha | trunk      | Open trunk for development of ILIAS 14                     |
| `n+2` | ILIAS 14.0-alpha | trunk      | Remove `ILIAS\ComponentX\Setup\Database\V12\CreateTableX`  |

`n` represents the introduction of the update step and the latest `n+2` represents **the safe removal point** where the
database template for ILIAS 13 is updated and contains the update step.

### 3.2. Update steps for multiple major versions

Database update steps which are introduced to multiple major versions are more tricky. These are usually bugfixes that
address the same issue in multiple major versions, but it could also affect the backport of features. The key challenge
is that introducing the same update step(s) to more than one major version creates a dilemma:

- If different FQDNs are used (e.g. `…\V10\UpdateTableX`, `…\V11\UpdateTableX`, `…\V12\UpdateTableX`), each update step
is considered new by the Setup component and could potentially be executed more than once. To prevent this, each
implementation would need to ensure they are idempotent for themselves. However, this approach is brittle and limited by
our database abstraction (`\ilDBInterface`), where e.g. table information cannot be easily retrieved to check if some
column type is already changed.
- If the same FQDN is used (e.g. `ILIAS\ComponentX\Setup\Database\V10\CreateTableX` for all versions), the update step
will not be considered new by the Setup component, but we loose the safeguard against divergent step methods
(`step_<x>()`) since the FQDN and implementation are the same accross different major versions and corresponding
branches.

We therefore recommend that developers **SHOULD** use the same FQDN accross all major versions and **SHOULD** follow the
[grouping database of update steps](../../../../docs/development/database-updates.md#grouping-database-update-steps).
When using the same FQDN across multiple releases, use the lowest major version number in the [recommended namespace
pattern](#33-recommended-namespace-pattern)

#### 3.2.1. Example

This example demonstrates the lifecycle of a database update step which needs to be introduced into multiple releases.
This could be the case if i.e. a bug is reported for ILIAS 11 but affected versions are 10 and 12 (trunk) as well.

| `n`   | Version          | Branch     | Action                                                    |
|-------|------------------|------------|-----------------------------------------------------------|
| `n-1` | ILIAS 9.X        | release_9  | -                                                         |
| `n`   | ILIAS 10.0       | release_10 | -                                                         |
| `n`   | ILIAS 10.1       | release_10 | Add `ILIAS\ComponentX\Setup\Database\V10\UpdateTableX`    |
| `n`   | ILIAS 10.2       | release_10 | -                                                         |
| `n+1` | ILIAS 11.1       | release_11 | Pick `ILIAS\ComponentX\Setup\Database\V10\UpdateTableX`   |
| `n+1` | ILIAS 11.2       | release_11 | -                                                         |
| `n+2` | ILIAS 12.0-alpha | trunk      | Pick `ILIAS\ComponentX\Setup\Database\V10\UpdateTableX`   |
| `n+2` | ILIAS 12.0       | release_12 | Major release of ILIAS 12                                 |
| `n+3` | ILIAS 13.0-alpha | trunk      | Open trunk for development of ILIAS 13                    |
| `n+3` | ILIAS 13.0-alpha | trunk      | Remove `ILIAS\ComponentX\Setup\Database\V10\UpdateTableX` |
| `n+2` | ILIAS 12.1       | release_12 | Remove `ILIAS\ComponentX\Setup\Database\V10\UpdateTableX` |

`n` represents the lowest version of where the update step is added. While `n+2` is considered **the safe removal
point**, it **SHOULD** only be removed when trunk is opened the next version (`n+3`). This will ensure that developers
who are actively working on trunk are not subject to the bug until the database template is updated. The correct removal
point is therefore `n+3`, but the update step **COULD** be removed in `n+2` after that as well.

#### 3.3 Recommended Namespace Pattern

To implement this consistently across all ILIAS components and prevent possibly divergent database update steps,
developers **SHOULD** follow this namespace pattern:

```
<Vendor>\<Component>\<PathToUpdateSteps>\<Version>
```

Whereas the placeholders are replaced like:

- `<Vendor>`: the provider of your component (e.g. `ILIAS`)
- `<Component>`: the name of your component (e.g. `Setup`, `ResourceStorage`)
- `<PathToUpdateSteps>`: desired path/structure to your Setup-related classes and update steps (e.g. `Setup\Database`)
- `<Version>`: the ILIAS major version, prefixed with capital "V" (e.g. `V10`, `V11`)

## 4. Removing Database Update Steps

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

- `<branch>`: the branch of the major version of `n -2` (e.g. `release_10` for `trunk`, `release_9` for `release_11`)
- `<path>`: the path to the file you want check (e.g. `components/ILIAS/ComponentX/Setup/Database/V10/FooBar.php`)

If there were no new `step_<x>()` methods, the specified file can safely be removed.
