# Roadmap

In May 2024 [fneumann (Databay AG)](https://docu.ilias.de/goto_docu_usr_1560.html) and [lscharmer (Databay AG)](https://docu.ilias.de/go/usr/87863) got the authorities for the administration component.

We want to continue refactoring the administration to create a modern component integration and further resolve historical dependencies. We also see the potential for extensions to make configuration easier for ILIAS operators.

## Current Status (ILIAS 10)

Although very prominently visible in the ILIAS menu, after past refactorings the administration is now a small component that mainly provides access to other components. When reviewing the code, we found the following responsibilities:

* Administration menu in the MainBar with grouping of settings and jump to the GUI classes of their nodes in the system folder
* Base class for the control flow to the settings GUIs of the components
* Linking of settings between components on their forms (e.g. for privacy and security)
* Administration view of the magazine tree with recycle bin
* Container for 'Restored Objects'
* System folder nodes for third-party tools
* Storage of global settings (ilSettings)
* Retrieval of information from Git

## Short Term

In the short term, we want to eliminate existing dependencies that are easy to resolve:

* The use of the BaseGUIRequest trait from the Repository component should be replaced by a more general solution.
* The configuration of the maps should be moved to the Maps component.
* The 'Third Party Software' node should be dissolved and the configurations of Maps, MathJax and WOPI should be provided as separate nodes under 'Extending ILIAS'.
* It should be possible to use the SettingsFactory not only in the setup but also in the running system and it should be provided via the component integration.
* The container for 'Restored Objects' fits better in the 'SystemCheck' component as it is also filled by this component.

## Mid Term

In the medium term, the mechanism for cross-linking settings must be converted to UI framework-based forms. The administration component can provide an integration according to the 'Contribute and Seek' scheme.

## Long Term

In the long term, we would like to make configuration easier for ILIAS administrators. We have a few ideas for this:

* The relationship between the storage of settings and their configuration in the forms or via the setup is still very loose and solved individually in the components. In cooperation with the setup component, the components could be given the opportunity to maintain a hierarchical 'catalog' of their settings and to save and retrieve their settings accordingly.
* Settings could thus be exported for an entire installation or for individual components in the format of the configuration file for the setup. 
* The administration can give components the option of registering their configuration pages and relating the UI elements on them to the settings in the catalog. This would enable a full-text search for a setting with a 'deep link' to the configuration page and ideally to the setting on it, as is known from PHPStorm, for example.
* Changes to settings can be automatically logged and commented on in order to be able to trace who changed something when and for what purpose in an installation.
