# UI-Plugin Manipulations

## Table of Contents

* [Introduction](#basics)
* [Basics](#basics)
* [UIFactory Manipulations](#uifactory-manipulations)
* [UIRenderer Manipulations](#uirenderer-manipulations)
* [Custom UI Components](#custom-ui-components)

## Introduction

ILIAS offers every plugin, which wants to make changes to the UI, an interface to do so.
This interface can be used within a plugins base class (`classes/class.il<PLUGIN_NAME>Plugin.php`) through the
functions:

- `ilPlugin::exchangeUIRendererAfterInitialization()`
- `ilPlugin::exchangeUIFactoryAfterInitialization()`

While these functions offer a lot of opportunities, they also come with a lot of responsibility, since it allows the
developers to perform major interventions in the build and rendering process. For example a developer can...

- prohibit certain plugins from doing changes
- remove entire components from the ILIAS structure
- introduce completely new components
- create a lot of workload with loops or non-breakable recursions
- ...

Therefore, it is important to be fully aware of the impact such changes have, as well as having an understanding of the
process in general.

## Basics

The first step on the way to make a UI change is to ask the following question:

**Is the desired change structural or visual?**

Structural changes affect the UI by restructuring component without affecting the inner HTML of the components
themselves. These kinds of changes should be made inside the `ilPlugin::exchangeUIFactoryAfterInitialization()`
function. Proper examples are:

- You want to rearrange a list or collection of items
- You want to remove or append a menu entry dynamically
- You want to exchange an entire component with an improved one
- ...

Visual changes affect the rendering of a component itself.
These kinds of changes should be made inside the `ilPlugin::exchangeUIRendererAfterInitialization()` function. Proper
examples are:

- You want change the design (including the HTMl structure) of a component
- You want append a content element in a inaccessible scope
- You want to extend a view (e.g. a list) with complex external data

*Notice that there is not always a strict rule to sort a change into the first or the second section.*

## UIFactory Manipulations

If your desired change is structural your plugin should implement the function `exchangeUIFactoryAfterInitialization()`.
This function gets a key as first parameter which is the key of a factory e.g. `ui.factory.button`. With that key you
can select the factory you want to exchange:

```php
public function exchangeUIFactoryAfterInitialization(string $dic_key, \ILIAS\DI\Container $dic) : Closure
    if($key == "ui.factory.nameOfFactory"){
        return function(\ILIAS\DI\Container  $c): \ILIAS\UI\Factory {
            return new CustomFactory($c['ui.signal_generator'],$c['ui.factory.maincontrols.slate']);
        };
    }
}
```

Then, you can make your changes inside your custom factory by inheriting the original factory or create a whole new one.

**WARNING**

**Be aware that, unlike for render manipulations, there is no chaining which merges all UI-factory manipulations.
A factory can only be exchanged by one plugin, and exchanges are always complete and never partial.
This may cause significant correlation problems with other plugins and therefore should be used with caution and
documented in detail**

## UIRenderer Manipulations

If your desired change is visual, your plugin should implement the function `exchangeUIRendererAfterInitialization()`.
Depending on the use-case of your scenario, there are 3 possible options. Before we dive into them, you need to
understand the logic we want plugin developers to embrace:

#### Understanding the rendering chain

ILIAS allows plugins to fully exchange the default renderer used for rendering all the frameworks components. Because a
plugin can only exchange the entire renderer, the responsibility for properly handling all previous renderer exchanges
lies within each plugin. To help plugin developers tackle this issue, and to reduce conflicts between plugins doing so,
we have introduced a `ILIAS\UI\Implementation\Render\DecoratedRenderer`, which can be used to create a
**rendering chain**.

Every implementation of the `DecoratedRenderer` can be thought of as a Matryoshka doll, which may be wrapped by another
`DecoratedRenderer` or wrap another one itself. The idea is, that every plugin, which wants to perform manipulations,
takes the previous renderer and wraps it inside of such an implementation. The first plugin will wrap the original
renderer and the last one an entire stack of renderers put into each other. Rendering a component will now pass the
component through this chain, where every renderer may manipulate it and pass it on to the wrapped one.

If you are inside a concrete implementation of the `DecoratedRenderer`, you can render the component by letting all
wrapped renderers manipulate it, which ultimately starts a recursive call where the first output HTML will be generated
by the original renderer. Returning any HTML inside the current renderer means, that you pass it on to the renderer
wrapping the current one, until no renderer is left and the HTML is finally returned.

#### Simple manipulations

This is the most common approach. It is recommended to be used by plugins that simply want to exchange, append or
prepend to a UI-element, without having to worry about render stacks or the whole progress in general. For that purpose,
ILIAS provides the `ILIAS\UI\Implementation\Render\DecoratedRenderer`, from which these plugins' renderer can inherit.
The `DecoratedRenderer` takes full care of the rendering progress and chaining and merging of all UI-plugin
manipulations.

The plugin simply needs to implement the abstract method `manipulateRendering()` of this renderer where the
manipulations can be made. If the plugin should handle asynchronous rendering separately, you may also implement
the function `manipulateAsyncRendering()` for that. Inside the function you can select your target component and
exchange it with your own HTML. If you just want to make an addition, you can use the default rendering progress with
the function `renderDefault()`.

See the [example](code-examples/ui-exchange/rendering-manipulations/ExampleRenderer.php) for more details.

When you finished your manipulation, all you have to do is to provide your renderer to the chain with
the `exchangeUIRendererAfterInitialization()` function.

```php
    public function exchangeUIRendererAfterInitialization(Container $dic): Closure
    {
        $renderer = $dic->raw('ui.renderer');
        return function () use ($dic, $renderer): \ILIAS\UI\Renderer {
            return new ExampleRenderer($renderer($dic));
        };
    }
```

See the [example](code-examples/ui-exchange/rendering-manipulations/ExamplePlugin.php) for more details.

With that done all manipulations made by your plugin will be integrated and merged with all other plugin manipulations.

*Be aware that there can still be conflicts with other plugins, e.g. if 2 plugins perform major changes on the same
component.*

#### Introducing custom components

Another common use-case for `exchangeUIRendererAfterInitialization()` is to introduce custom UI components to the
rendering chain. **Please note this only works while the rendering chain is kept alive**. At any point, where another
plugin might break out of this chain by e.g. using an approach similar to the one described in the next chapter,
custom components composed into existing ones could lead to errors, due to the missing renderer in the chain.

Custom components can be very useful because they can be incorporated into already existing ones and still be rendered
by the renderer provided by the plugin. For example, you are missing an additional input, but still want to use it
inside UI forms. This can be achieved, by using an own implementation, which will be handled in the provided renderer.

See the [example](code-examples/ui-exchange/custom-components) for more details.

#### Altering the rendering chain

In some minor cases, you need full control over the rendering process of ILIAS within your plugin. This might be the
case when you want to suppress other plugins from manipulating components as long as your plugin is active, or if you
want to resolve a conflict with another plugin in favour of yours.

If that's the case, you might take the same approach as within the factory exchange, by simply overwriting the complete
ILIAS core renderer with your own. Please note that it's still recommended to inject the possibly exchanged renderer
from the DI container into your custom one, so you can delegate rendering calls which do not concern your manipulation
to the correct renderer, instead of the default one.

```php
    public function exchangeUIRendererAfterInitialization(Container $dic): Closure
    {
        return function () use ($dic): \ILIAS\UI\Renderer {
            return new CustomRenderer($dic);
        };
    }
```

Within your renderer you than might exchange the basic `render()` function or even `getRendererFor()` to perform your
desired change.

**WARNING**

**Be aware that this approach has the same effect as the factory exchange. This can only be made by one plugin and only
with a complete exchange. _The handling of correlations with other plugin is in full responsibility of your plugin
after this, because not using the simple approach breaks the rendering-chain_. There is also no guarantee, that your
renderer is exchanged last, so suppressing other plugins may not work.**
