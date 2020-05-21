# ilObjStudyProgrammeSettingsGUI

* The settings form currently performs side effects in the transformation of the
form. This is not allowed and thus needs to be removed. We could use the data objects
from the models instead and simply set a new settings object to the programme object. 
* Remove inline html from GUI's.
* Replace the default -1 value with null in prg_sttings
* Refactoring of DIC. Split in multiple DIC with object or without. Perhaps it is possible to switch to trait
* Remove all setObject and getObject Methods in classes
* Refactoring subtab structure for settings