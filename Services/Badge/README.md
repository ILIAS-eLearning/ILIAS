# Service "Badge"

## In General

Basic concepts are 
* badge type, 
* badge instance (badge type + object instance) and 
* badge assignment (badge instance + user).

Each module or service can provide badges. To enable the badge service for a component add a "badges"-tag to the respective xml.
The badge service will look for a badge provider class for each enabled component when reloading the control structure. 
It has to reside in the classes folder, implement the "ilBadgeProvider"-interface and simply return all available badge types of that module or service.

## Administration

After the control structure is rebuilt all current badge types will be listed in the badge administration. They can be globally (de-)activated.
If badges are available for certain object types the object settings will include the badges service as additional feature. 
It has to be activated for each object instance and will add a "badges"-tab, where the complete badge (instance) administration takes place.

## Development

Each badge instance has a type and can be manually or automatically awarded, it can be tied to certain object types or be installation-wide (object type "bdga").
Programmatically each badge instance is an application class and - if a custom configuration is needed - a GUI class. 
The respective interfaces are ilBadgeType, ilBadgeTypeGUI and ilBadgeAuto.

The auto badges supply an "evaluate"-method which decides if a badge assignment has to be done and is triggered by the event handler (see ilBadgeAppEventListener).

Examples of badges can be found in /Modules/Course/ and /Services/User/.

## Export

Exporting badges to an Open Badges Backpack (Mozilla) is supported, while importing from that backpack - or anywhere else - is not. 
The export generates static badge files according to the open badges specification. It is not badge specific.

Useful resources:
* https://github.com/mozilla/openbadges-backpack
* https://openbadgespec.org/
* https://backpack.openbadges.org/
