User Experience Service (Working-Title)
======================================

# Purpose
The GlobalScreen service offers ILIAS components and plugins the possibility to
contribute elements to the layout of the page. The service should not be
confused with the UI service that controls the actual display of elements. The
GlobalScreen service offers abstractions of specific and unique page elements,
such as entries in the main menu. 

A component or a plugin can offer such elements via so-called providers.
Collectors collect these elements at a point in time x and have them rendered
with UI elements of the UI service. More about the collectors below.

GlobalScreen elements therefore do not contain HTML or other forms of
visualization at any time, but merely mediate between a component and the point
that renders and places these elements in the correct place using the UI
service.

# How to use it

## Providers
Suppose one of the badges component wants to provide an entry for the main
menu. It implements an `ILIAS\GlobalScreen\Provider\StaticProvider` with the
methods `getStaticSlates()` and `getStaticEntries()`.

Since the component does not return its own slates, an empty array can be
returned in `getStaticSlates()`. `getStaticEntries()`, however, returns a new
entry in the form:


```php

return
[$this->gs->mainmenu()->link($this->gs->identification()->internal('mm_pd_badges'))
->withTitle($lng->txt("obj_bdga"))
->withAction("ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToBadges")
->withParent(...[get the identification of the PD-TopParentItem]...)
->withAvailableCallable(
function () {
return (bool)(ilBadgeHandler::getInstance()->isActive());
}
)];
```
`->withParent()` defines the default parent (e.g. a slate). However, depending
on the configuration in the installation, this will be overwritten later by the
configured parent.

### Static vs. Dynamic
For the StaticMainMenuProvider - but probably also for other GlobalScreen
components - there are two different types of providers. Most components will
use StaticProviders. These provide elements for the GlobalScreen (e.g. slates
and menu entries), which are collected once during installation or an update of
ILIAS and are stored "statically" in the database with their identifiers. These
GlobalScreen elements are thus always statically available to ILIAS, whether
they are displayed depends on various other properties (see
withAvailableCallable, withVisibilityCallable). The static elements can also be
adapted via a configuration in the ILIAS Administration, e.g. by renaming or
changing the order.

DynamicProviders, on the other hand, will in future provide GlobalScreen
elements that are only available at a certain point in time, such as the tools
that are displayed context-dependently. The documentation for tools is only
added when the tools are implemented.

### How to implement your provider
Whether a component has GlobalScreen providers is determined by entries in
`service.xml` or `module.xml`. The following entry is added, e.g.:
```xml
<gsproviders>
<mainmenu class_name="ilBadgeGlobalScreenProvider"/>
</gsproviders>
```
As many providers as desired can be registered. These can implement one or more
of the available provider interfaces, e.g..:
```php
use ILIAS\GlobalScreen\Provider\DynamicProvider\DynamicMainMenuProvider;
use ILIAS\GlobalScreen\Provider\StaticProvider\StaticMainMenuProvider;

class ilBadgeGlobalScreenProvider implements StaticMainMenuProvider,
DynamicMainMenuProvider {
...
}
```

## Identification
### Core
All elements in the GlobalScreen service must be identifiable for the supplying
components mentioned above. The GlobalScreen service uses this identification,
for example, for parent/child relationships. The identification is also
forwarded to the UI service or to the instance that then renders the
GlobalScreen elements. This means that the identification can be used there
again, for example, to generate unique IDs for the online help.

Identifications can be retrieved in a provider as follows, for example:
```php
// assuming $this is a provider
$id = $this->gs->identification()->core($this)->identifier('my_internal_id');
```
### Plugins
There is a special Identification for Plugins which can be get as follows:
```php
// assuming $this is a provider and $pl is a ilPlugin-child
$id = $this->gs->identification()->plugin($pl,
$this)->identifier('my_internal_id');
```

## Collectors
In most cases, you won't need to implement a collector. For the
StaticMainMenuProvider, for example, the necessary collectors (MainMenuMainCollector-Collector,
which combines all necessary elements from the collectors "Plugins" and "Core")
are already implemented in GlobalScreen\Collector\MainMenu.

# Usage in Plugins
All Plugin-types in ILIAS are capable of using the GlobalScreen-Service 
(currently Mainmenu-Items). All activated Plugins are asked for their providers, 
an empty provider is returned per default. If you want to provide items and even 
types, just override the method promoteGlobalScreenProvider() in your 
Plugin-Class, e.g.:
```php
public function promoteGlobalScreenProvider(): AbstractStaticPluginMainMenuProvider {
		global $DIC;

		return new ilGSDProvider($DIC, $this);
	}
```
A working sample can be found at https://github.com/studer-raimann/GlobalScreenDemo.git
This is not a new Plugin-Slot, this is just an addition to all existing Plugin-Slots.