# Component Privacy

Disclaimer: This documentation does not guarantee completeness or correctness. Please report any missing or incorrect
information using the ILIAS issue tracker or contribute a fix via Pull Request (
docs/development/contributing.md#pull-request-to-the-repositories).

## General Information

This component boostraps the rest of the system, logically and at runtime. It defines how the system is initialized and
how components integrate with each other.

## Integrated Components

The UICore component employs the following components, please consult the respective `PRIVACY.md`:

* AccessControl: for checking permissions
* Administration: for settings
* [Data](../Data/PRIVACY.md): for representing structured data
* DI: for the global dependency injection container
* [Database](../Database/PRIVACY.md): for storing technical information
* [HTTP](../HTTP/PRIVACY.md): for request information and request handling
* ILIASObject: to implement settings for components and plugins
* [Init](../Init/PRIVACY.md):: for ini-files and settings, raising errors
* Language: for translations
* [Refinery](../Refinery/PRIVACY.md):: for retrieving and transforming data
* [Setup](../Setup/PRIVACY.md):: to proivide build objectives
* [UI](../UI/PRIVACY.md): for rendering the plugin information
* UIComponent: for rendering tabs
* [UICore](../UICore/PRIVACY.md): for routing and asambling the HTML page

## Data being stored

- name of person or institution responsible for a plugin
- email of person or institution resonsible for a plugin

## Data being presented

- name of person or institution responsible for a plugin
- email of person or institution resonsible for a plugin

## Data being deleted

- name of person or institution responsible for a plugin
- email of person or institution resonsible for a plugin

## Data being exported

This component does not export any personal data.
