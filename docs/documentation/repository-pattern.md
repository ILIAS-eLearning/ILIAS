# Repository Pattern

## What, why and how?

### Architectural Perspective

From the perspective of an architect of the application, a repository can be
defined as such:

**A repository is the single source of truth for a domain of an application.**

`Repository` here is a name that classifies the entities we want to create and
that fullfil a special purpose in our application design. The role is, to be a
`single source of truth`. `Single` here is crucial. It means, that only the
`Repository` and no other entity may tell or acknowledge facts. A `truth` or
`fact` here can be understood as some information or a state of the application.
The `Repository` does not need to know every truth about the application, but
rather specializes on one `domain` in the application. A `domain` can be understood
as a field of expertise or knowledge the application (or a component of the
application) cares about. So in fact, an application most likely won't have one
repository but rather many of them, that all take care of one domain in the
application.

### Technical Perspectice

This rather abstract perspective can be complemented with a more technical one.
Here, we can see, that a `Repository` in fact abstract the underlying database.
Via its interface it captures common patterns of access, may it be read or write,
to the knowledge in the domain it is reponsible for. Since it is a single entity
that mediates all access to the database, we can control where, if and when
persistence to the database actually happens.

Although a `Repository` can be understood as a database from a technical perspective
we should not confuse it with a database (as we will see later on) in general.
It is thus not the goal of a `Repository` to be a SQL-abstraction. An implementation
of a `Repository` over a relational database might rather use an SQL-abstraction
to implement the required access patterns.

### Alternatives

There are other patterns and concepts to implement persistency that could be
considered alternatives to the repository pattern. Two of those are used in ILIAS
and thus deserve a closer look here.

#### Active Record

An `Active Record` is an object that carries around its own persistence logic,
e.g. in the form of `read`- and `update`-methods that serialize the object to
a database. Most of the ilObjects are active records in that regard and ILIAS
also brings a service (`Service/ActiveRecord`) that makes it easy to implement
them.

Active records can be considered to be easier to understand and handle then
repositories. They have some well known [downsides](https://www.mehdi-khalili.com/orm-anti-patterns-part-1-active-record),
most prominently the SRP-violation which results in decreased testability and
modularization when using active records.


#### Just Query

By "Just Query" we mean the habit to just build a SQL-query according to ones needs
and ask the database to process it. This pattern is also found in ILIAS in numerous
places, often involving complex queries over multiple tables.

This approach has the advantage that it allows to precisely access the required data
(and not more), while the database may take care of executing the query efficiently.
One downside is that this approach is brittle regarding schema- or name changes in
the database, which is especially problematic when data from different components is
included. Many advantages of a repository can also be understood as disadvantages
or problems with "just queries".


### Advantages

* **The explicit interface makes writing tests easy.** - The repository introduces
an interface that captures all access patterns to the underlying data. This interface
can be written down as PHP `interface` and then used to create mocks during testing.
This of course means, that components using the repository do no create instances
of it themselves but instead get that dependency injected.
* **The explicit interface makes writing alternative implementations (import/export,
caching, ...) easy.** - The explicit interface has even more advantages. Although we
will most propably implement repositories over a relational database mostly, we are
not limited to that. We could well implement a repository that adds chaching over
another repository or reads its data from other sources like files.
* **The database schema is completely contained in the implementation of the repository.**
- If we implement the repository over a relational database, no other component
then the repository is allowed to access the data contained in the repositories
tables (by definition). We thus are free to change the schema whenever and however
we want, because nobody besides the repository should ever need access to that tables.
* **Database integrity can be handled in one place.** - Since all access to the
database is mediated through the repository we have an obvious place to handle
integrity constraints. We could use `ilAtomQuery` to coordinate writes to multiple
tables, while we can design the interface of the repository in a way that only
allows to make writes that respect integrity constraints.
* **Query and write patterns get meaningful names.** - Since all access patterns
are encoded as methods on the interface of the repository, these patterns automatically
acquire names that can help to understand code that is using the repository. This is
especially helpful compared to bare queries that only reveal their intent grudgingly.
* **The repository forces you think about access to the persistence layer.** - Since
components that want to use persistence need the repository we can see clearer where
persistency actually happens. Objects read from the repository may be passed to
other components for further processing while we can be sure that these may only
change these objects on the persitence layer if they also have the repository. Compared
to active records that could be saved by every component seeing the active record
objects this will clarify the architecture of a component regarding persistence.


### Disadvantages

* **You write an additional interface.** - No pain, no gain. Regarding an active
record or the "just query"-approach we should write an additional interface if
we really want to take advantage of the repository pattern.
* **You cannot just query or change some data on the database.** - We will most
probably come into situations where we currently just call `update()` on an
active record or `$ilDB->query(..)` to retrieve some data but do not have the
appropriate repository handy. This is the downside of having a clearer architecture.
* **You have to establish clear boundaries between different domains.** - Since
the repositories need to be authoritative for one domain, we in fact need to know
what that domain is (and what it is not). We cannot simply use the same piece of
data in two domains but instead need to duplicate if required.
* **Integrity over domains is hard.** - Repositories allow to keep integrity
constraints in one domain but do not offer strategies to coordinate writes over
multiple domains. We will either need to introduce further measures to allow
for transactions over multiple domains or our components will need to deal
with possible inconsistencies between different domains. This in fact is already
the case: Do we really know that the user we reference in our records exists in
the user database?
* **The repository pattern does not fit "reporting queries" well.** - Repositories
are good for working with object graphs, but there are situations where we need
an overview over data that spans multiple domains. This is often the case when we
have tables that are filled via queries containing multiple joins. We could use
multiple repositories but we will make more roundtrips to the database and possibly
load data we do not require then. If we need more performance we may build a
specialized repository (or tables only) that fit the needs of our report and gets
updated frequently or event-driven.
* **The repository pattern is not about performance.** - The repository is a way
to gain some advantages, but performance is not a target. Inside a repository
implementation we might use known measures, like introducing indizes on the
database or limiting the data we actually query. Caching is easy to implement
for a repository. Choosing good access patterns (i.e. methods on the repository
interface) may help a lot for both efforts. Getting good performance when
multiple repositories are involved may require to duplicate data in a new and
specialized repositories. Anyway, solving performance issues will require some
work and does not come for free when using the repository pattern.

### Guidelines for Implementation

* Write the interface and data-objects first while implementing consumers of the
repository.
* Inhect the repository as dependency.
* Make sure to understand that this is not about SQL. Do not try to build an
SQL-abstraction.
* Repositories work well with immutable objects. Try to think about how data flows
through the program.
* Reference other domains by ID only, only tell and acknowledge facts you know
about.
* You may write facilities that aggregate information from different repositories or
write to different repositories, but these may only know about the interfaces,
not the implementation.


## Use in ILIAS

The repository pattern may be applied under various circumstances in ILIAS:

* 

## Conventions

If you implement the repository in your component, you SHOULD folloe these
conventions:

* Call the repository `XYZRepository` where XYZ is the domain of your component
the repository controls.


## Examples
