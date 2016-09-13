# Service "Badge"

Each module or service can provide badges. To enable the badge service for a component add a "badges"-tag to the respective xml.
The badge service will look for a badge provider class for each enabled component when reloading the control structure. 
It has to reside in the classes folder, implement the "ilBadgeProvider"-interface and simply return all available badge types of that module or service.

After the control structure is rebuilt all badge types will be listed in the badge administration. They can globally (de-)activated.
If badges are available for certain object types the object settings will include the badges service as additional feature. 
It has to be activated for each object instance and will add a "badges"-tab, where the complete badge (instance) administration takes place.

Each badge instance has a type and can be manually or automatically awarded, it can be tied to certain object types or be installation-wide.
Programmatically each badge instance is an application class and - if a custom configuration is needed - a GUI class. 
The respective interfaces are ilBadgeType, ilBadgeTypeGUI and ilBadgeAuto.

The activity (or auto) badges supply an "evaluate"-method which decides if a badge assignment has to be done and is triggered by the event handler (see ilBadgeAppEventListener).

Examples of badges can be found in /modules/course/ and /services/user/.