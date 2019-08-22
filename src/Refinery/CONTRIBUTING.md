# Contributing

When contributing to this service,
please first discuss the change you wish to make via
ticket in mantis, email, or any other method with the coordinators of
this service before making a change.

Please note we have a code of conduct,
please follow it in all your interactions with the project.

## Roles

The ILIAS project has the role of the coordinator.
[Check the documentation](/docs/documentation/maintenance-coordinator.md)
about the function of this role.

## Reporting Bugs

Bugs must be reported via https://mantis.ilias.de in the `Refinery`
category. One of the [coordinators](/src/Refinery/maintenance.json)
must be assigned.

Bug fixes and improvements can be provided via a Pull Request.

A fix that redefines that behaviour of an implementation are considered
as a [breaking change](#breaking-changes).

## Features

Features and Breaking Changes must be discussed with the
[coordinators](/src/Refinery/maintenance.json)
of this component.

Features that also changes the behaviour of existing implementations
are considered as [breaking changes](#breaking-changes).

## Breaking Changes

A change that is not backwards compatible must be
announced one month in advance on the [Jour Fixe](https://docu.ilias.de/goto_docu_wiki_wpage_391_1357.html).

## Pull Request Process

* Update the README.md with details of changes to the interface, classes or
  general behaviour.
* Contact one of the coordinators in the Pull Request.
  The list of coordinators can be found [here](/src/Refinery/maintenance.json)
* Every Pull Request with actual code changes has to add or adapt unit tests.
* The title of the Pull Request should be prefixed with `Refinery:`.
* The Pull Request must pass the CI integration.
  Be aware of the currently supported PHP versions and optimize your code according
  to the supported versions.
* Keep the Pull Request as small as possible.
  Avoid unnecessary changes to speed up the review process.
* Use readable and understandable commit messages, so the reviewer can understand the
  intention of each commit.
