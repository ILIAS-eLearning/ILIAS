# Roadmap

## Introduction

The Legacy-UIComponents-Service has been lingering and causing problems for user experience, accessibility, updatability, and consistency for a long while now. It also causes a lot of work as many changes in the UI need to be backported here. To avoid this state going on forever the whole UIComponents-Service is marked as depricated and will be removed with ILIAS 12. Parts of the UIComponents already marked as depricated on January 1st 2022 will be removed with ILIAS 9, 10 or 11. Please make sure you have moved your UI to the [UI-Framework](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/src/UI) until then.

## Further process
* Presentation of the project to remove the UIComponents-Service at the Jour Fixe for big projects for ILIAS 9 in February 2022 and at the same meetings for later releases .
* Appointment of a Project Manager through the Technical Board.
* Collection of missing UI-Elements in UI-Service by responsible maintainers and Project Manager until April 30th 2023.
* Organization by Project Manager of crowdfunding to finance the creation of the missing UI-Elements and to migrate Components.
* Migration of Components relying on already deprecated UIComponents until Coding Complete for ILIAS 12.
* Planing of implementation of missing UI-Elements by Project Manager. The implementation is ongoing and MUST be finalized by Coding Completed for ILIAS 12.
* Migration of Components away from UIComponents-Service until Coding Complete for ILIAS 12.

## Rules and Guidelines
* If a feature should be implemented in a component still relying on the UIComponents-Service, this reliance MUST be removed first.
* There will be no ILIAS 12 with the UIComponents in it. If a component cannot be moved, it MUST be abandoned.

## Removal

### ILIAS 9
* Character Selector
* Checkbox List Overlay

### ILIAS 10
* Glyph
* Lightbox
* Modal
* Overlay
* Panel
* Tooltip

### ILIAS 11
* –

### ILIAS 12
* Confirmation & Confirmation Table
* Explorer & Explorer2
* Nested List
* Progress Bar
* Syntax Highlighter (only used in in Page Editor, move there?)
* Tabs
* Text Highlighter
* Toolbar
