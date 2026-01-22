# UICore Privacy

Disclaimer: This documentation does not guarantee completeness or correctness. Please report any missing or incorrect
information using the ILIAS issue tracker or contribute a fix via Pull Request (
docs/development/contributing.md#pull-request-to-the-repositories).

## General Information

This component must not be confused with the UI-framework, -components, or anything semantically related to those. The
name stems from the fact that this component provides the templating engine used by other components such as the UI-
framework to actually implement the UI and put it together. Unfortunately, putting the page together results in many
other components being employed here in order to retrieve singular assets, resources, and other information.
In addition, this component also provides the routing mechanism which is ultimately responsible to map URL-addresses
requested by e.g. the browser to specific (other) components of ILIAS. This component is therefore strongly entangled
with any other component of ILIAS offering some kind of functionality via URL-addresses and/or render some sort of HTML.

## Integrated Components

The UICore component employs the following components, please consult the respective `PRIVACY.md`:

* Administration: for the installation short-title setting, system-folder title
* Authentication: for storing values inside the session
* [Cache](../Cache/PRIVACY.md): for caching HTML templates, blocks and variables
* [Contact](../Contact/PRIVACY.md): for JavaScript resources
* [Component](../Component/PRIVACY.md): for plugin information and UI-Hook plugin manipulations
* DI: for the global dependency injection container
* [File](../File/PRIVACY.md): for rendering file-upload dropzones
* [GlobalScreen](../GlobalScreen/PRIVACY.md): for page layout 
  modifications, JavaScript and CSS resources
* [HTTP](../HTTP/PRIVACY.md): for request information and request handling
* Language: for translations
* Notification: for rendering notifications
* OnScreenChat: for JavaScript resources
* PermanentLink: for generating the permanent-link URL
* [Refinery](../Refinery/PRIVACY.md): to fetch request information and 
  transform it
* [Setup](../Setup/PRIVACY.md): to provide a build objective
* [UI](../UI/PRIVACY.md): for rendering message boxes
* UIComponent: for rendering the toolbar
* Utilities: for helper functions like sanitation
* jQuery: for JavaScript resources

## Data being stored

This component does not store any personal data.

## Data being presented

This component (itself) does not present any personal data. It may be used by other components to do so, which would be
reflected in their respective `PRIVACY.md`.

## Data being deleted

This component does not delete any personal data.

## Data being exported

This component does not export any personal data.
