# System Styles ROADMAP

##High Prio
* Refactor the Public interface. Introduce a proper factory along with facade to show
and document the public interface. Especially refactor/get rid of the singleton pattern
  for ilStyleDefinition and ilSystemStyleSettings
* Unit Test the public interface.

##Medium Prio
* Further replace legacy ui components with UI Components, namely:
  * Settings Form
  * Table in the Overview (Data Table needed)
  * Creation dialogs (Some accordion like structure needed)
  * Inputs with Coler for Icons (Color Input needed)
  * Toolbar (Toolbar needed)
  * Code Preview in Examples (Component to show text as code needed)
* Further extend testing coverage for GUI classes (usage of UI componentes needed)
* Improve DIC handling. Currently this is all handled through constructors, making constructors large.
maybe introduce a local DIC container, as done in study programe.

##Low Prio
* Introduce the File System Service for file access for this component. Note that for
this, a new Kontext (templates) ist needed for the file system service.

    

    
