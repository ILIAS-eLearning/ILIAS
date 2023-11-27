# ILIAS Component Service

This service boostraps the rest of the system, logically and at runtime. It defines
how the system is initialized and how components integrate with each other.

The schema implemented here is described in [this paper](docs/development/components-and-directories.md).

## Core Concepts

### Component

### Entrypoint

## Why don't you use Immutable Objects in Dependencies

The dependencies of the components define a graph. That graph is read by analyzing
the `Component::init`s one after another. Building immutable graphs is hard in
general and not not worthwhile here, since we provide no public interface here.
