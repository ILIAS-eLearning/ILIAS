# Roadmap

Note: Some of the rules listed in this roadmap may be superseded by general ILIAS rules in the future.

## Short Term

### Web Directory Access for Portfolios and Blogs

- Assignment Types will get a supportsWebDirAccess()
- 

## Mid Term

### Directory structure

Subdirectories for domain concepts SHOULD be located directly under the `Exercise` main directory. The main `classes` subdirectory SHOULD only contain code has to be located in this directory due to rules of other components (e.g. the Object service).

### Introduce stronger Tutor Contept

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

All process logic should be removed from GUI classes to the action layer. These tasks should be forwarded to the Action Layer instead. Permission checks should only be made for UI decisions here.

## Long Term
