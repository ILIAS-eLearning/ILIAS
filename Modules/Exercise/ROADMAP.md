# Roadmap

Note: Some of the rules listed in this roadmap may be superseded by general ILIAS rules in the future.

## Short Term

### Deletion Process

The deletion process needs to be checked. Some data is left behind, if exercises are finally removed from the system (assignment, member data).

### Web Directory Access for Portfolios and Blogs

- Assignment Types will get a supportsWebDirAccess()

### Put Assignment Member State BL under Tests

- The business logic of the different assignment phases (assignment member state) should be separated more strongly and put under unit tests.

## Mid Term

### Clariy use of table exc_returned

Current situation in ilExSubmission/exc_returned table
- exc_returned entries are used for text and blog/portfolios submissions, too!
  - filetitle is the wsp_id for blog/portfolios, the ref_id for wikis now!
  - getFiles() also returns entries for text
  -> This is confusing.
- FUTURE: exc_returned entries should be refactored in a more general concept "Submission Items" (files, text,
  wsp objects, repo objects, ...)

### Fix ilExcCriteria

The ilExcCriteria class does stuff on application and gui level and should be divided into multiple interfaces.

### Split up large classes (ongoing)

- Especially ilExAssignment should be split up in several repository / manager classes.

### Directory structure (Mostly done)

Subdirectories for domain concepts SHOULD be located directly under the `Exercise` main directory. The main `classes` subdirectory SHOULD only contain code has to be located in this directory due to rules of other components (e.g. the Object service).

### Introduce stronger Tutor Concept

### Introduce Repository Pattern (ongoing)

The Repository Pattern should be introduced to decouple the persistence layer.

### Use Data Objects

Data objects should usually be returned by the repository layer. Factories for these objects should be made available through a service object of the component.

### Move to Data, Repo, Domain, GUI architecture (ongoing)

- Layers should separate responsibilities. Structure should be integrated into an internal service managing dependecies.
- Domain layer should implement business logic without UI dependencies (including permission checks).

### Dependency Management / Interfaces

- Move more dependencies from implementation to interface dependencies.
- Move instantiation upwards in the service factory chain.

### Refactor filesystem access

The new filesystem services should be introduced. Especially the submission files should be organised in a more semantic and explicit way.

### Artefact reader / assignment types

The assignment types should be collect by using the artefact reader and defined interfaces. Code that checks for specific assignment types (if ilExAssignment::TYPE_UPLOAD) needs to be eliminated as far as possible and replaced by "feature-sensitive" checks via a common interface for the types.

### Replace Accordion view by KS Listing Panels

The accordions should be replaced by KS elements. Most probably a Listing Panel with Items that links to specialised assignment type specific views (e.g. Panels) in a second UI level instead of the legacy InfoScreenGUI.

## Long Term
