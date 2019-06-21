GlobalScreen Service
======================================

# Purpose
The GlobalScreen service offers ILIAS components and plugins the possibility to
contribute elements to the layout of the page. The service should not be
confused with the UI service that controls the actual display of elements. The
GlobalScreen service offers abstractions of specific and unique page elements,
such as entries in the MetaBar or the MainBar. 

A component or a plugin can offer such elements via so-called providers.
Collectors collect these elements at a point in time x and have them rendered
with UI elements of the UI service. More about the collectors below.

GlobalScreen elements therefore mostly do not contain HTML or other forms of
visualization at any time, but merely mediate between a component and the point
that renders and places these elements in the correct place using the UI
service.

# Scopes
There are several scopes that the GlobalScreen service serves. Currently these are:
- MainBar (MainMenu)
- MetaBar
- tool
- layout

A scope addresses one area in the global layout. The components and plug-ins can 
contribute "content" via these scopes.

A scope usually has its own collector and definitions of items and providers.

## Scope MainBar
All items available in the MainBar come from this scope. 

## Scope MetaBar
Analogous to the MainBar, the definitions of items and providers for the MetaBar 
come from this scope.

## Scope Tool
The Scope Tools has a lot in common with the Scope MainBar, because the items are 
rendered in almost the same place. The items as well as the providers as such are 
very different from the Scope MainBar.

# How to use it

## Providers
Suppose one of the badges component wants to provide an entry for the MainBar. 
It implements an `ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider`
with the methods `getStaticTopItems()` and `getStaticSubItems()`.

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

`->withParent()` defines the default parent (e.g. another MainBar-Item). 
However, depending on the configuration in the installation, this will be overwritten 
later by the configured parent.

### Static vs. Dynamic
For the StaticMainMenuProvider - but probably also for other GlobalScreen
components - there are two different types of providers. Most components will
use StaticProviders. These provide elements for the GlobalScreen (e.g. MainBar-Items), 
which are collected once during installation or an update of ILIAS and are stored 
"statically" in the database with their identifiers. These GlobalScreen elements 
are thus always statically available to ILIAS, whether they are displayed depends 
on various other properties (see withAvailableCallable, withVisibilityCallable). 
The static elements can also be adapted via a configuration in the ILIAS 
Administration, e.g. by renaming or changing the order.

DynamicProviders, on the other hand, provide GlobalScreen elements that are only 
available at a certain point in time, such as the Tools that are displayed 
context-dependently. 

### How to implement your provider
All providers for all Scopes will be collected whenever you perform a `composer install` or a `composer dump-autoload` (which is already needed for the autoloading). This is done by the ArtifactBuilder-Service. 

```
As many providers as desired can be registered. These can implement one 
of the available provider interfaces, e.g..:
```php
use ILIAS\GlobalScreen\Provider\DynamicProvider\DynamicToolProvider;

class ilBadgeGlobalScreenProvider implements StaticMainMenuProvider {
...
}
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

# Identification
## Core
Most elements in the GlobalScreen service must be identifiable for the supplying
components mentioned above. The GlobalScreen service uses this Identification,
for example, for parent/child relationships. The Identification is also
forwarded to the UI service or to the instance that then renders the
GlobalScreen elements. This means that the Identification can be used there
again, for example, to generate unique IDs for the online help.

Identifications can be retrieved in a provider as follows, for example:

```php
// assuming $this is a provider
$id = $this->gs->identification()->core($this)->identifier('my_internal_id');
```

## Plugins
There is a special Identification for Plugins which can be get as follows:

```php
// assuming $this is a provider and $pl is a ilPlugin-child
$id = $this->gs->identification()->plugin($pl, $this)->identifier('my_internal_id');
```
