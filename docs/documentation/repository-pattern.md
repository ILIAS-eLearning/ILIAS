# Repository Patterno

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

* *Active Record* - objects carry their persistence logic around
* *Just Query* - gather data by an appropriate SQL-query

### Advantages

* The explicit interface makes writing tests easy.
* The explicit interface makes writing alternative implementations (import/export,
caching, ...) easy.
* The database schema is completely contained in the implementation of the repository.
* Database integrity can be handled in one place.
* Query and write patterns get meaningful names.
* The repository forces you think about access to the persistence layer.

### Disadvantages

* You write an additional interface.
* You cannot just query or change some data on the database.
* You have to establish clear boundaries between different domains.
* Integrity over domains is hard.
* The repository pattern does not fit "reporting queries" well.
* The repository pattern is not about performance.
* Current ILIAS access patterns or components may be in your way.

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
