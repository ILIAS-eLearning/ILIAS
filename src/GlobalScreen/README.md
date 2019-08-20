GlobalScreen Service
====================

- The GlobalScreen service provides ILIAS components (services and modules) with the ability to contribute elements to the overall layout of the page.
- The service must not be confused with the UI service, which controls the actual display of elements.
- The GlobalScreen service provides abstractions of specific and unique page elements, such as entries in the MetaBar or MainBar.

A component or plugin can offer such `Items` through so-called `Providers`.
Collectors collect these items at a time x throughout the system. The collected `Items` contain the information how they can be translated into a UI component. More about the `Collector` can be found [below](#collector).

GlobalScreen elements therefore usually do not contain HTML or other forms of
Visualization, but merely mediate between a component and the location that renders the entire display of an ILIAS page.

# Scopes
There are several scopes served by the GlobalScreen service. Currently these are:
- MainBar (main menu)
- MetaBar
- Tool
- Layout

A scope refers to an area on an ILIAS page. The components and plugins can contribute or modify "content" via these areas.

A scope usually has its own `Collector` and `Factory` definitions of items.

## Scope MainBar
All `Items` available in the MainBar come from this area.

For more information see [Scope/MainMenu/README.md](Scope/MainMenu/README.md).

## Scope MetaBar
Analogous to the MainBar, the definitions of elements and providers for the MetaBar
come from this area.

For more information see [Scope/MetaBar/README.md](Scope/MetaBar/README.md).

## Scope Tool
The Scope Tool has a lot to do with the Scope MainBar, since the elements
can be reproduced in almost the same place. However, both the `Items` and the `Providers` as such are .

For more information see [Scope/Tools/README.md](Scope/Tool/README.md).

## Scope Layout
This area is the superordinate element and responsible for the entire structure of a page. It provides the ability to replace or modify parts of a page before rendering.

For more information, see [Scope/Layout/README.md](Scope/Layout/README.md).

# How to use the service

## Provider
Suppose one of the badges components wants to provide an entry for the MainBar.
It implements an `ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider`.
with the methods `getStaticTopItems()` and `getStaticSubItems()`.

```php
return [
  $this->gs->mainmenu()->link($this->gs->identification()->internal('mm_pd_badges')))
    #WithTitle($lng->txt("obj_bdga"))
    ->withAction("ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToBadges")
    ->&lt;font color="#ffff00"&gt;-==- proudly presents
    # WithAvailableCallable #
        function () {
            return (bool)(ilBadgeHandler::getInstance()->isActive());
          }
          )
  ];
```

`->withParent()` defines the default parent (i.e. another MainBar item).
Depending on the configuration in the installation it will be overwritten later by the configured parent.

### Static vs. Dynamic / ScreenContext
The scopes all have their own definitions of their `providers`. The providers differ on the one hand in what they can return. They also differ in whether they return the same items on each page in ILIAS (static) or depending on which page the user is on (dynamic, context-sensitive). For example, `Tools` are context-sensitive, i.e. their providers are `ScreenContextAwareProviders`.

For further information see [ScreenContext/README.md](ScreenContext/README.md).

### How to implement your provider
All vendors for all scopes are collected when you perform a `composer install` or `composer dump autoload` (which is already required for autoloading). This is done through the ArtifactBuilder service.

Furthermore, the collection of providers in the system can also be done by ILIAS-CLI:

```
php setup/cli.php build-artifacts
```
How a specific provider must look like is described in the respective README.md files of the scopes.

## [](#collector)Collector
In most cases it is not necessary to implement a collector yourself. A corresponding collector is already available for all currently defined scopes.

# Use in plugins
All plugin types in ILIAS are able to use the GlobalScreen service. All activated plugins will be asked for their `Providers`,
an empty provider is returned by default.
