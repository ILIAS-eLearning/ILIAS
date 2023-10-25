# Roadmap

### Organize Constants

Reused constants should be collected into bespoke classes as 
appropriate, instead of being scattered across the component.

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

### Refactor ilMDCopyrightSelectionEntry

The class ilMDCopyrightSelectionEntry could need some refactoring
(get rid of static functions, etc.). Maybe it would make sense
to roll this into the custom vocabularies.

This might also apply to related classes (e.g. 
ilOerHarvesterSettings).

### Replace Generic Generators With Custom Iterators

A lot of generic generators are used throughout the component, along
with quite a few usages of `iterator_to_array`. These should be gradually
replaced by bespoke iterator classes.

### Make Greater Use of Null Objects

`null` as a return type should be replaced by proper null objects.
A good starting point might be `Tags` from `Dictionaries`. 

### Query Smarter

Currently, every metadata element is queried separately, even if
they are persisted in the same table. This should be optimized.

Furthermore, the methods constructing the bespoke queries for
the MD elements in ilMDLOMDatabaseDictionary are a bit of
a mess, with a lot of overlap between them. This can be done in 
a more elegant way.

### Stricter formatting of 'format' and 'entity'

The fields technical>format and the various entities should conform
to different standards (e.g. entities should be vcards). This could
be supported better in ILIAS, currently any string is valid.

### Vocabularies

Allow adding other vocabularies than LOM. This could be implemented
along similar lines as the 'copyright' tab in the administration
settings for MD.

To this end, 'source' fields have to be introduced for every
vocabulary field in the database.

Note that the usage of non-LOMv1.0 sources for
vocabularies in a MD set means that also the element 'metadataSchema'
has to be appendend, see the LOM standard.

### Abandon the old backend

All ILIAS components using MD should at some point only use the
new classes as the new MD editor does.

### Customizable LOM Digest

Allow customizing what elements are part of the LOM Digest in
administration settings. It would also be worth thinking about 
how to implement multilinguality in the Digest.