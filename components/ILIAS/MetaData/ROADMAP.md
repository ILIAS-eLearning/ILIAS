# Roadmap

### Organize Constants

Reused constants should be collected into bespoke classes as 
appropriate, instead of being scattered across the component.

### Metadata Schema

Check whether renaming the currently incorrect preset schema
'LOM v 1.0' can be replaced with the correct 'LOMv1.0'. From
a quick search for the old value it seems to be in use in a few 
places.

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