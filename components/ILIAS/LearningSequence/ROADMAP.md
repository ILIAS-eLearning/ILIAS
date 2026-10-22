# Roadmap

## Component Revision

The condition types are contributed via the component bootstrap (`ConditionType`, `ConditionTypes`) and
exposed as `$DIC[ConditionTypes::class]` for the legacy environment. The repositories for boundaries, item path,
visits and the condition services are provided by `ilLSDI`. The following steps are still open:

### Conditions without service location

`AbstractCondition` retrieves the whole `ILIAS\DI\Container` via `global $DIC` in its constructor, stores it in
`$this->dic` and reads from the database as soon as an id is given. All concrete conditions depend on it
(database, user, ctrl, refinery, language, UI factory). Conditions are instantiated by `ConditionFactory` with
`new $class()`, so nothing can be injected.

* Separate the persistence of a condition (generic `lso_conditions` row and payload tables) from the condition
  itself and move it into repositories.
* Let `ConditionType` create the condition instead of `ConditionFactory` instantiating the class name, so the
  dependencies of a condition can be wired where it is contributed.
* Never read from the database in a constructor.
* Remove the `ReflectionClass::newInstanceWithoutConstructor()` / `ReflectionProperty` workarounds in the tests
  once the dependencies can be injected.

### Legacy proxies for other components

The conditions and the learning map evaluate the learning progress via `ilLPStatus::_lookupStatus()` /
`_hasUserCompleted()` and resolve objects via `ilObject::_lookup*()`, `ilObjectFactory` and `ilLink`.

* Introduce narrow interfaces for the learning progress and object lookups used by the Learning Sequence and
  implement them as legacy proxies (see the proxy pattern in
  `components/ILIAS/Component/docs/component-bootstrap-migration.md`).
* Reuse `LSLearnerItem::getLearningProgressStatus()` where the learner items are already at hand.

### Remaining `global $DIC`

Still located via `global $DIC`, among others:

* `ilObjLearningSequenceConditionsGUI`, `ilObjLearningSequenceConditionConfigurationGUI`,
  `ilLearningSequenceConditionsRetrieval` (condition types, database, refinery, tabs, toolbar)
* `ilObjLearningSequence::renderLearningMap()` (UI factory, renderer, template)
* `ilLSPlayer` (access check for the rating)
* the classes which already existed before the adaptive mode (`ilObjLearningSequenceGUI`, members, importer,
  exporter, app event listener, ...)

Since `ilCtrl` is not yet part of the component bootstrap, the GUI classes need the legacy environment for now;
their dependencies should nevertheless be passed in by the creating class instead of being located inside.

### Constants

`ILIAS_HTTP_PATH` is used in `AbstractCondition`, `ilObjLearningSequenceConditionsGUI`, the XML writer and
`ilLSLocalDI`. It should be replaced by the URI builder / static URL service.

## Conditions

### Relation to the Conditions component

The adaptive mode introduces an own condition framework (`lso_conditions`, `lso_condition_types`, `lso_c_*`),
while the linear mode still uses the post conditions (`post_conditions`) and `ilConditionHandler` for the
operators. The relation to the Conditions component should be clarified and documented, ideally both modes use
one condition system.

### Database schema of the conditions

`ilLearningSequenceConditionsSyncedObjective` creates `lso_condition_types`, `lso_conditions` and the payload
tables from `AbstractCondition::migrate()` instead of `ilDatabaseUpdateSteps`. Changes to existing columns can
therefore not be expressed.

* Create the generic tables with regular database update steps.
* Define how payload tables of contributed condition types are created and updated in a versioned way.
* Add indices on `lso_conditions.lso_ref_id` / `obj_ref_id` and a unique index on
  `lso_condition_types.condition_name`.

### Duplicated code

* The linear and the adaptive content management are parallel class hierarchies (`LSOSequentialContent` /
  `LSOAdaptiveContent`, tables, filters); `getSpecificActions()` differs in its signature and is not part of
  `LSOContentController`.
* `LSOContentDeletion.php` contains a trait and the class `LSOTableActionHandler`.
* The navigation situations (`end`, `straight`, `branch`, `deadend`, `blocked`) are compared as strings in
  `ilLSPlayer` although `LSOLearningMapPosition` defines constants; an enum would be preferable.

## User Interface

* The learning map (`tpl.lso_learning_map.html`, `lso_learning_map.js`, `lso_learning_map.css`) is a custom
  component outside of the UI framework and outside of the SCSS/Delos pipeline. It should be proposed as UI
  component or at least use the Delos variables.
* `lso_player.css` overrides the rendering of UI cards (`.il-card`) for the choice page.
* The choice page, the dead end page and the map modal are assembled from `legacy()->content()` fragments.
* `lso_learning_map.js` should follow the JS guidelines (ES6 module, bundled via rollup, JSDoc, tests). The
  layered graph layout is implemented from scratch; a library (e.g. dagre or elk) should be evaluated.

## Data and Privacy

* `lso_item_boundaries`, `lso_item_path` and `lso_item_visits` are not removed when a learning sequence is
  deleted.
* `lso_item_path` and `lso_item_visits` are not removed when a user account is deleted.
* `PRIVACY.md` does not yet describe the visit log (`lso_item_visits`) and the learner path (`lso_item_path`).

# ilLearningSequenceFilesystem

* parent class is deprecated
* method 'deleteFile' doesn't exists anymore
* make use of the new filesystem
