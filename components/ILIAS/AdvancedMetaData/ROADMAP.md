# Roadmap

## Short Term

## Mid Term

### Improve Stability and Code Quality

#### Better UI for Select Options

The current form for options of (multi-)select fields was reworked using
legacy inputs, see [PR 7108](https://github.com/ILIAS-eLearning/ILIAS/pull/7108).

The form, along with all other forms to create/edit fields, should be
moved to KS, and streamlined.

#### Repository for Records, Field Definitions, and Values

The data structure of AdvancedMetaData consists mainly of three hierarchical
entities: records, which contain field definitions, which contain values.
The latter are also always related to an ILIAS Object (or subobject).

There should be one collective repository covering records, field definitions,
and values s.t. consistency in the data (e.g. on deletion) can be maintained
easily. This repository should not always work its way from records as the
root all the way down to the values; in most of the use cases, one does not
need the whole hierarchy, and building it up anyways would be detrimental to
performance.

The main use cases seem to require either records with their fields, or values
for a group of objects with their respective fields. The former is relevant
for managing records, either in the Administration or in the context of
specific objects (see e.g. `ilAdvancedMDSettingsGUI`), and the latter for
managing and displaying metadata values of objects (see e.g.
`ilAdvancedMDRecordGUI`).

Apart from that, the repository should also facilitate searching for
objects that carry specific metadata values for specific fields.

Auxiliary data like translations, record scopes, record selection of objects,
etc. should not be understood as their own entities, but rather as properties
of the main entities, and should be treated as such.

As the collective repository then requires a reasonable amount of internal
infrastructure, it should delegate to smaller classes. Overall, an
assembly-like structure seems sensible.

The larger repository should build on what was started for 
`FieldDefinitions` in `Repository\FieldDefinition`.

#### Data Objects for Records, Field Definitions, and Values

There already are data objects that one can continue to make use of
(`ilAdvancedMDRecord`, `ilAdvancedMDFieldDefinition`, the classes in ADT).
Those objects can be gradually refactored to fit into the repository
pattern.

As a first step, one could even have their static `getInstance` methods
call the new repository, to avoid refactoring consumers.

#### Type-specific Properties of Field Definitions

Most field definitions have a few bespoke configuration options. Some
of these options are even persisted in a type-specific table in the 
database (see `adv_mdf_enum` and e.g. `ilAdvancedMDFieldDefinitionSelect`).
In order to distribute all field definitions from a single source, but
not bloat that source too much, reading and manipulating these options
should be done in type-specific classes.

The process for this could be as follows, e.g. when reading out
a field definition: a central class reads out all universal properties
of the definition, and compiles them as a `GenericData` object. In parallel,
type-specific properties are read out in one of a number of type-specific
classes (delegated to from the central class), and compiled as a
`TypeSpecificData` object. The actual data object for the field definition
is then built with these two objects as properties.

#### API

Currently, AdvancedMetaData is used by consumers mainly via various
different static methods, distributed across a few classes. A cleaner,
centralized API should be offered, organized around concrete use cases
for the metadata. A small part of this, for the use of custom metadata
in KS data tables, has already been implemented.

The API should in particular offer the option to search for objects with
specific values for a given field.

#### Redesign `ilAdvancedMDClaimingPlugin`

The plugin slot `ilAdvancedMDClaimingPlugin` should be redesigned to
fit with the new repository. It would probably be easiest to expose an
abstraction of the repository via the API to accomodate programatically
managing metadata sets.

#### Bespoke Tables for Type-Specific Data

Currently, most of the type-specific option of fields are persisted as 
serialized arrays in the column `field_values` in the table `adv_mdf_definition`.
Every type should instead store these options in their own tables. `field_values`
should be removed.

## Long Term
