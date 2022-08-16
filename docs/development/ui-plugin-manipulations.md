# UI-Plugin Manipulations

## Table of Contents
* [Introduction](#basics)
* [Basics](#basics)
* [UIFactory Manipulations](#uifactory-manipulations)
* [UIRenderer Manipulations](#uirenderer-manipulations)

## Introduction

ILIAS offers every UIHookPlugin, which want to make changes to the UI, an interface to do so.
This interface can be accessed withing the plugins base class `classes/class.il<PLUGIN_NAME>Plugin.php` through the
functions:
- `exchangeUIRendererAfterInitialization()`
- `exchangeUIFactoryAfterInitialization()`

While these offers a lot of opportunities the usage also comes within a lot o responsibilities since it allows the
developer major interventions in the build and rendering progress. For example the developer can...
- prohibit other plugins from doing changes
- remove entire components from the ILIAs structure
- create a lot of workload with loops or non-breakable recursions
- ...

Therefore, it is important to be fully aware of the impact of made changes as well as to have an understanding of the
process in general.

## Basics

The first step on the way to make a UI change is to ask the following question:

**Is the desired change structural or visual?**

Structural changes do affect the UI by restructuring component without affecting the inner HTMl od the components themselves.
This kind of changes should be made inside the **exchangeUIFactoryAfterInitialization** function.
Proper examples are:
- You want to rearrange a list or collection of items
- You want to remove or append a menu entry dynamically
- you want to exchange a whole component with an improved one

Visual changes affect the rendering of a component itself.
This kind of changes should be made inside the **exchangeUIRendererAfterInitialization** function.
Proper examples are:
- You want change the design (including the HTMl structure) of a component
- You want append a content element in a inaccessible scope
- You want to extend a view (e.g. a list) with complex external data

*Notice that there is not always a strict rule to sort a change inside the first or the second section.*

## UIFactory Manipulations

If your desired change is structural your plugin should implement the function **exchangeUIFactoryAfterInitialization()**
This function gets a key as first parameter which is the key of a factory e.g. `ui.factory.button`.
With that key you can select the factory you want to exchange:
```php
public function exchangeUIFactoryAfterInitialization(string $dic_key, \ILIAS\DI\Container $dic) : Closure
    if($key == "ui.factory.nameOfFactory"){
        return function(\ILIAS\DI\Container  $c){
            return new CustomFactory($c['ui.signal_generator'],$c['ui.factory.maincontrols.slate']);
        };
    }
}
```

Then you can make your changes inside your custom factory by inheriting the origin factory or create a whole new factory.

**WARNING**

**Be aware that, otherwise than in the render manipulations, there is no chaining which merges all UI-factory manipulations.
A factory only can be exchanged by one plugin and exchanges are always complete and never partially.
This may cause heave correlation problems with other plugins and therefore should be used with care and should be documented detailed!**

## UIRenderer Manipulations

If you desired change is visual your plugin should implement the function **exchangeUIRendererAfterInitialization()**
Depending on the complexity of your change there are 2 recommended approaches.

#### Simple Approach

This is the most common approach. It is recommended to be used by plugins that simply just want to exchange, append or prepend
to a UI-element without having to worry about render stacks or the whole progress in general. For that purpose ILIAS provides the **DecoratedRenderer** from which these plugins' renderer can inherit from.
The **DecoratedRenderer** takes full care of the entire rendering progress and the chaining and merging of all UI-plugins manipulations.

The plugin simply needs to implement the abstract method 'manipulateRendering()' of this renderer where the manipulations can be made.
If the plugin also should handle asynchronous rendering separately you may also implement the function 'manipulateAsyncRendering()' for that.
Inside the function you can select you target component and exchange it with your own HTML.
If you just want to make an addition you can the default rendering progress with the function `renderDefault()`.

See the [example](code-examples/ui-exchange/00_base_classes/ExampleRenderer.php) for more details.

If you finished your manipulation all you have to do is to provide your renderer to the chain within the **exchangeUIRendererAfterInitialization()** function.

```php
    public function exchangeUIRendererAfterInitialization(Container $dic): Closure
    {
        $renderer = $dic->raw('ui.renderer');
        return function () use ($dic, $renderer) {
            return new ExampleRenderer($renderer($dic));
        };
    }
```

See the [example](code-examples/ui-exchange/00_base_classes/ExamplePlugin.php) for more details.

With that done all manipulations made by your plugin will be integrated and merged with all other plugin manipulations

*Be aware that there still can be conflicts with other plugins e.g. when 2 plugins do too major changes on the same component*


#### Complex Approach

In some minor cases you might have full control over the entire rendering progress of ILIAS withing your plugin.
This might be the case when you want to suppress other UI-plugins as long as your plugin is active or want to resolve a conflict
with another plugin in favour of yours.

When that's the case you might take the same approach as withing the factory exchange by simply overwriting the complete
ILIAS core renderer with your own.

```php
    public function exchangeUIRendererAfterInitialization(Container $dic): Closure
    {
        return function () use ($dic) {
            return new CustomRenderer($dic);
        };
    }
```

Within your renderer you than might exchange the basic `render()` function or even `getRendererFor()` to perform your desired change.

**WARNING**

**Be aware that this has the same effect as inside the factory exchange. This can only be made by one plugin and only
with a complete exchange. The handling off correlations with other plugin are in full responsibility of your plugin then.**

## Contributors
* Ingmar Szmais, Databay AG, Aachen, Germany
