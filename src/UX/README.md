User Experience Service (Working-Title)
======================================

# Purpose
The US service offers ILIAS components and plugins the possibility to contribute elements to the layout of the page. The service should not be confused with the UI service that controls the actual display of elements. The UX service offers abstractions of page elements, such as entries in the main menu. 

A component or a plugin can offer such elements via so-called providers. Collectors collect these elements at a point in time x and have them rendered with UI elements of the UI service. More about the collectors below.

UX elements therefore do not contain HTML or other forms of visualization at any time, but merely mediate between a component and the point that renders and places these elements in the correct place using the UI service.

# How to use it

## Providers
Suppose one of the badges component wants to provide an entry for the main menu. It implements an `ILIAS\UX\Provider\StaticProvider` with the methods `getStaticSlates()` and `getStaticEntries()`.
