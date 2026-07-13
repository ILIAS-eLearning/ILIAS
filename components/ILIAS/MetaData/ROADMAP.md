# Roadmap

### Rename OERHarvester subfolder

`OERHarvester` should rather be called something like `OERPublishing`.

### Organize Constants

Reused constants should be collected into bespoke classes as 
appropriate, instead of being scattered across the component.

### Get URI from Data Factory

Get URI from the data factory, instead of instantiating it
directly.

### Clean up Remains of Migrations for Ilias 10

With ILIAS 10, the migrations `ilMDLOMConformanceMigration` and
`ilMDCopyrightMigration` can be deleted. To clean up the migrations, the
following table columns should be dropped:

* il_meta_general: coverage, coverage_language
* il_meta_meta_data: meta_data_scheme
* il_meta_requirement: operating_system_name, os_min_version,
os_max_version, browser_name, browser_minimum_version,
browser_maximum_version
* il_meta_educational: learning_resource_type,
intended_end_user_role, context
* il_md_cpr_selections: copyright, language, costs, cpr_restrictions,
migrated

### Location Type

Check whether the field 'location_type' in the table 
'il_meta_location' can be removed. Location type can be set in
the old MD editor, but is not part of the LOM standard. It
would be nice to get rid of it, should it not be used anywhere
else in ILIAS.

### Replace Generic Generators With Custom Iterators

A lot of generic generators are used throughout the component, along
with quite a few usages of `iterator_to_array`. These should be gradually
replaced by bespoke iterator classes.

### Make Greater Use of Null Objects

`null` as a return type should be replaced by proper null objects.
A good starting point might be `Tags` from `Dictionaries`.

### Change default behavior for copyright

Currently, all objects without an entry for copyright in `il_meta_rights`
are treated like having the default copyright. This special behavior
has to be implemented in a variety of places (xml generation,
copyright API, database search, ...), and makes supporting filtering for
the default copyright in Lucene-search prohibitively complicated.

Requiring default copyright in new LOM sets would alleviate these
problems, but would also mean more redundant information in
the MetaData tables. It should be investigated how to progress here.

### Stricter formatting of 'format' and 'entity'

The fields technical>format and the various entities should conform
to different standards (e.g. entities should be vcards). This could
be supported better in ILIAS, currently any string is valid.

### Simplify Copyright Handling

Currently, the API leaves it to consumers to handle custom copyright
and copyright presets differently from each other. It should be
investigated how those two cases can be unified within the API, so that
consumers don't need to worry about the difference, without making a
mess.

The custom copyright string could e.g. act as the analogue of title,
full name, and identifier of presets, but these concepts shouldn't
get watered down too much.

### Unify Presentation of Copyright

We are in danger of having too many modes of presentation of copyright
in ILIAS. It should be investigated how presentation can be unified,
while still being able to fit into the required slots in the KS (data
table, properties in item, ...).

### Allow `INDEX` path filters in search

It should be investigated, how path filters of type `INDEX` can be
taken into account in the search, to allow for search queries like
'Find objects where the **first** author is Dr. No'.

These filters make translating the search queries to SQL much more
complex, so the cost might outweigh the use.

### Allow manipulation of LOM sets during derivation

The `Derivator` in the API could be expanded to contain methods like
`prepareOmit` and `prepareAddOrChange` to allow changes to the derived
LOM set before it is persisted. The repository would need to take into
account more types of markers/scaffolds in `transferMD`.

### Customizable LOM Digest

Customizing of LOM Digest could be made possible for plugins, in
order to tailor the screen better to every installations configuration.

### Clean up Elements Folder

Currently, the Elements folder does not have its own service, so
creation of the factories contained therein is not centralized.

For the factories for elements and structure elements, centralization
does not make much sense: those elements only make sense when created
in bulk and corss-referenced (sub- and super-elements), but the
factories only offer creation of single objects. The actual creation of
elements in context is done by higher order infrastructure such as the
repository. Those factories should thus only be created for that specific
part of the infrastructure and not reused.

Factories for scaffolds, ressource IDs and data on the other hand
can be reused just fine, and should be offered through a service.
Markers are a special case, I'm not sure whether they are needed outside
of the manipulator.

### Internationalization of LangStrings

Elements consisting of a string and a language could be allowed to
contain multiple such tuples, such that e.g. translations of the title
can be stored in LOM.

This would need expansive changes to the database structure, and a new
input element for multilangual text input.

### Clean up Administration

`Settings` should be refactored: namespacing should be introduced,
`Services` should be used properly, etc.

### Improve Unit Test Coverage

The following classes are not yet covered by unit tests:

- everything in `Editor`, except `Vocabulary`
- everything in `Settings` except `Vocabularies\Import`
- `GlobalScreen/ilMDKeywordExposer`
- `Manipulator/ScaffoldProvider`
- everything in `Paths`
- everything in `Repository`,
`Repository/Utilities/Queries/DatabaseSearcher`, and
`Repository/Utilities/Queries/Paths`
- `Services\InternalServices` (along with all `Services` used by it),
also all methods in `Services\Services` that don't do anything except
lazily instantiate an object
- `Vocabularies\Standard\Assignment`, `Vocabularies\Controlled`, `Vocabularies\Manager`,
and everything in `Vocabularies\Slots` except `ElementHelper`
- `XML/Copyright`, `XML/Links`, `XML/Dictionary`, `XML/Writer/SimpleDC`,
and `XML/Reader/Standard/Legacy`
- `OERExposer/OAIPMH/HTTP`
- `OERHarvester/RepositoryObjects`, `OERHarvester/Settings`,
`OERHarvester/CronJob/Results`, `OERHarvester/Export`,
`OERHarvester/ControlCenter`
