# System Styles
The client interface of the service is defined in ilStyleDefinition and ilSystemStyleSettings,
sadly mostly through static method.

Note that especially the classes ilStyleDefinition and ilSystemStyleSettings need
refactoring to make them properly testable and define a cleaner interfaces of this service.
For the time being this has been omitted, to keep the interface stable for the moment (see Roadmap).

## Terminology
The following terminology is used:
* System Style:
    A style that can be set as system style for the complete ILIAS installations. This includes, less
    css, fonts, icons and sounds as well as possible html tpl files to overide ILIAS templates.
* Stystem sub Style:
    A sub style can be assigned to exactly one system style to be displayed for a set of categories.
* Skin:
    A skin can hold multiple style. A skin is defined by it's folder carrying the name of the skin and the
    template.xml in this exact folder, listing the skins styles and substyles. Mostly a skin caries exactly one style.
    Through the GUI in the administration it is not possible to define multiple style per skin. It is however possible
    to define multiple sub styles for one style stored in one skin.
* template:
    The template is the xml file of the skin storing the skin styles and sub styles information.

Skins, styles ans stub styles are always used globally (not client specific).

## ilStyleDefinition
ilStyleDefinition acts as a wrapper of style related actions. Use this class to get the systems current style.
Currently some of the logic is not clearly separated from ilSystemStyleSettings. This is due to legacy reasons.
In a future refactoring, this class might be completely merged with ilSystemStyleSettings. 

An instance of this class is currently saved in $DIC as styleDefinition. See plublic static methods of the
class to see the interface. Note that this will be changed in the near future.

## ilSystemStyleSettings
This class acts as Model for all system styles settings related settings
* such as activated or default system styles etc, be it in database or inifile.
* Do not use this class to get the current system style, use ilStyleDefinition insteaed.