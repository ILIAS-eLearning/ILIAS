## Introduction

The Legacy-Table-Service has been lingering and causing problems for user experience, accessibility, updatability, and consistency for a long while now. To avoid this state going on forever Services/Table is marked as depricated and will be removed with ILIAS 10. Please make sure you have moved your UI to the [Table of the UI-Framework](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/src/UI/Component/Table) until then.

## Further process
* Presentation of the project to remove Services/Table at the Jour Fixe for big projects for ILIAS 9 in February 2022.
* Appointment of a Project Manager through the Technical Board.
* Collection of missing functionality in Services/Table by responsible maintainers and Project Manager until September 30th 2022.
* Organization by Project Manager of crowdfunding to finance the implementation of the missing functionality of Services/Table and to migrate Components.
* Planing of implementation of missing functionality by Project Manager. The implementation MUST be finalized by Feature Freeze for ILIAS 11.
* Migration of Components away from Services/Table until Coding Complete for ILIAS 11.

## Rules and Guidelines
* If a feature should be implemented in a component still relying on the Table-Service, this reliance MUST be removed first.
* There will be no ILIAS 11 with the Services/Table in it. If a component cannot be moved, it MUST be abandoned.