# Repository Pattern

## What, why and how?

### Architectural Perspective

**A repository is the single source of truth for a domain of an application.**

* *Repository* is the name of the entity in question
* *single source of truth*
	* *single* means that not other entity may tell or acknowledge facts
	* *truth* can be understood as facts and state in the application
* The *domain* is the area of expertise or knowledge the application (or component
of the application cares about.

### Technical Perspectice

A repository...

* ...abstracts the database.
* ...captures patterns of requests to the database.
* ...mediates all access to the database.
* ...controls where and when persistence happens.

Although a repository is a database from a technical perspective, it is not an SQL-
abstraction.

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
