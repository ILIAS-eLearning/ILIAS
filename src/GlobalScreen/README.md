GlobalScreen Service
====================

- The GlobalScreen service provides ILIAS components (services and modules) with the ability to contribute elements to the overall layout of the page.
- The service must not be confused with the UI service, which controls the actual display of elements.
- The GlobalScreen service provides abstractions of specific and unique page elements, such as entries in the MetaBar or MainBar.

A component or plugin can offer such `Items` through so-called `Providers`. Collectors collect these items at a time x throughout the system. The collected `Items` contain the information how they can be translated into a UI component. More about the `Collector` can be found [below](#collector).

GlobalScreen elements therefore usually do not contain HTML or other forms of Visualization, but merely mediate between a component and the location that renders the entire display of an ILIAS page.

# Scopes

There are several scopes served by the GlobalScreen service. Currently these are:

- MainBar (main menu)
- MetaBar
- Tool
- Notification
- Layout

A scope refers to an area on an ILIAS page. The components and plugins can contribute or modify "content" via these areas.

A scope usually has its own `Collector` and `Factory` definitions of items.

## Scope MainBar

All `Items` available in the MainBar come from this area.

For more information see [Scope/MainMenu/README.md](Scope/MainMenu/README.md).

## Scope MetaBar

Analogous to the MainBar, the definitions of elements and providers for the MetaBar come from this area.

For more information see [Scope/MetaBar/README.md](Scope/MetaBar/README.md).

## Scope Tool

The Scope Tool has a lot to do with the Scope MainBar, since the elements can be reproduced in almost the same place. However, both the `Items` and the `Providers` as such are .

For more information see [Scope/Tools/README.md](Scope/Tool/README.md).

# Scope Notifications

For more information, see [Scope/Notification/README.md](Scope/Notification/README.md).

## Scope Layout

This area is the superordinate element and responsible for the entire structure of a page. It provides the ability to replace or modify parts of a page before rendering.

For more information, see [Scope/Layout/README.md](Scope/Layout/README.md).

# How to use the service

## Provider

Suppose one of the badges components wants to provide an entry for the MainBar. It implements an `ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider`. with the methods `getStaticTopItems()` and `getStaticSubItems()`.

```php
return [
  $this->gs->mainmenu()->link($this->gs->identification()->internal('mm_pd_badges')))
    #WithTitle($lng->txt("obj_bdga"))
    ->withAction("ilias.php?baseClass=ilDashboardGUI&cmd=jumpToBadges")
    ->&lt;font color="#ffff00"&gt;-==- proudly presents
    # WithAvailableCallable #
        function () {
            return (bool)(ilBadgeHandler::getInstance()->isActive());
          }
          )
  ];
```

`->withParent()` defines the default parent (i.e. another MainBar item). Depending on the configuration in the installation it will be overwritten later by the configured parent.

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

# Use in Plugins

All Plugin types in ILIAS are able to use the GlobalScreen service.

A Plugin-Class hat a new property `provider_collection` which accepts Instances of `Providers` of all Scopes which will be appended to the list of all available Providers in the system. E.g.:

```php
    public function __construct()
    {
        parent::__construct();

        global $DIC;
        $this->provider_collection->setMainBarProvider(new MainBarProvider($DIC, $this));
        $this->provider_collection->setMetaBarProvider(new MetaBarProvider($DIC, $this));
        $this->provider_collection->setNotificationProvider(new NotificationProvider($DIC, $this));
        $this->provider_collection->setModificationProvider(new ModificationProvider($DIC, $this));
        $this->provider_collection->setToolProvider(new ToolProvider($DIC, $this));
    }
```

# A note on Items/TypeInformation and rendering

Providers yield Items for a certain Scope. Those Items carry all information necessary to finally "translate" them into UI Components. Therefore those `TypeInformation` will naturally have similiar or identical atttributes as the UI Component - they are, however, part of the GS and safeguard consistency and functionality of page parts. One type of Item SHOULD result in one type of UI Component.

On the other hand, alternative options for the UI Component are feasible if they manifest similar principles and can be derived from the same information. An example for this is the construction of a DrilldonwMenu based on ListItems, which will work fine for one level of items, but will not suffice for deeper structures as ListItems cannot provide those.

In summary, you are generally warned about changing UI-Components for Items on GS-Provider level. Furthermore, your are encouraged to discuss your plans with people at the JourFixe _before_ embarking on a project. Finally, you MUST get the JourFix's approval for changing UI-Components for specific GS Items; this also holds true for changing appearance or behavior of the currently used Component. Probably it is best to actually create a new Item.
