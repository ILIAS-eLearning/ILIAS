# Roadmap of the Learning Sequence

## ILIAS 6.0

### Use Dependency Injection correctly

Currently the `ilObjLearningSequence` contains methods that build required
objects for subcomponents of the LearningSequenceObject (e.g `getSettingsDB`).
These caused [#25007](https://mantis.ilias.de/view.php?id=25007), which also
is a layer violation (similar to "only GUIClasses can depend on: IlTemplate").
In fact, the `ilObjLearningSequence` does not use these dependencies but only
requires them to build subcomponents. The Learning-Sequence-Component should
introduce a local DI-container that builds and connects the subcomponents
to remove that responsibility from `ilObjLearningSequence`.
