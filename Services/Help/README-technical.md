# Online Help Support

The online help is currently supported for the German language only.
 
Within the online help learning modules, help texts are assigned to screens of the ILIAS application. Screens are identified by screen IDs consisting of three parts **Component/Screen/SubScreen**. To set the screen ID for your component, use the global `$DIC->['ilHelp']` object. It provides the following methods:

- `setScreenIdComponent($a_comp);`
- `setScreenId($a_screen);`
- `setSubScreenId($a_subscreen_id);`

These methods should be called in GUI classes of your component. Best practice is to do this only if the corresponding class implements the current screen (handles the current command). A good place is the method that is also responsible for setting the tabs/subtabs.
 
At minimum the **`setScreenIdComponent`** method must be called. If no screen and subcreen IDs are provided, but tabs and or subtags are displayed on the screen. ILIAS will use the IDs of the tabs/subtabs automatically to extend the overall screen ID.