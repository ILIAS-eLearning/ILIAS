# Roadmap

## Short Term

### User Setting Config class

Centralise all fields/properties configuration settings in ilUserSettingsConfig.

## Mid Term

### User Service in DIC

Currently the user service is only represented by an ilObjUser object of the current user in the DIC. However ilObjUser is not a real interface for other components, since it reveals lots of internals that should be hidden.

A decent user service interface needs to be defined that should fit the needs of other components through a well defined interface in the future.

- Properties
- Custom properties
- Preferences

### User Actions vs. Contact Widget

The user actions and the contact widgets activation are a source of confusion, see e.g. bug #27266. This should be conceptually fixed.


## Long Term

### Replace UDF with Custom Metadata

The user defined fields and custom metadata concepts share a lot of similar functionality. It would be easier to maintain only one of these approaches by moving the UDF data to user custom metadata.
