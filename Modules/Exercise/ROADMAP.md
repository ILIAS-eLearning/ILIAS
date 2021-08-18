# Roadmap

Note: Some of the rules listed in this roadmap may be superseded by general ILIAS rules in the future.

## Short Term

### Web Directory Access for Portfolios and Blogs

- Assignment Types will get a supportsWebDirAccess()

### Stronger Typisation

- All methods/properties should get a strong typisation with PHP 7.4 minimum support.

### More robust request handling

- GET/POST/SESSION request should move to the request service. Parameters should be stronger typed and checked for validity.

### Put Assignment Member State BL under Tests

- The business logic of the different assignment phases (assignment member state) should be separated more strongly and put under unit tests.

## Mid Term

### Split up large classes

- Especially ilExAssignment should be split up in several repository / manager classes.

### Directory structure

Subdirectories for domain concepts SHOULD be located directly under the `Exercise` main directory. The main `classes` subdirectory SHOULD only contain code has to be located in this directory due to rules of other components (e.g. the Object service).

### Introduce stronger Tutor Concept

### Introduce Repository Pattern

The Repository Pattern should be introduced to decouple the persistence layer.

### Use Data Objects

Data objects should usually be returned by the repository layer. Factories for these objects should be made available through a service object of the component.

### Introduce Action Layer

Action interfaces/classes should implement Frontend independent process / application logic. This should be extracted from current GUI controllers or "ilObject"-like classes. Intefaces and class should get an `Action` suffix. This layer should

- Perform permission checks consistently
- Make use of repository layer to change state

Currently the top Frontend layer will be reponsible to inject these dependencies.

### UI controller

All business logic should be removed from GUI classes to the action layer. These tasks should be forwarded to the Action Layer instead. Permission checks should only be made for UI decisions here.

### Refactor filesystem access

The new filesystem services should be introduced. Especially the submission files should be organised in a more semantic and explicit way.

### Artefact reader / assignment types

The assignment types should be collect by using the artefact reader and defined interfaces. Code that checks for specific assignment types (if ilExAssignment::TYPE_UPLOAD) needs to be eliminated as far as possible and replaced by "feature-sensitive" checks via a common interface for the types.

### Replace Accordion view by KS Listing Panels

The accordions should be replaced by KS elements. Most probably a Listing Panel with Items that links to specialised assignment type specific views (e.g. Panels) in a second UI level instead of the legacy InfoScreenGUI.

## Long Term
