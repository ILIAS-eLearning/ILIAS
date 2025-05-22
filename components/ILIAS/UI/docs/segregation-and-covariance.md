# Interface segregation and covariance in the UI framework

The UI framework follows the interface segregation principle by exposing only the methods necessary for consumers to
interact with our UI components. To achieve this, we maintain two distinct namespaces:

- `ILIAS\UI\Component`: contains the public interface definitions that consumers interact with.
- `ILIAS\UI\Implementation`: contains the internal implementations of these interfaces, which include
  additional methods and logic used internally by the UI framework.

These namespaces enforce a clear boundary between the public and internal contexts, allowing the framework to provide a
more extensive set of methods internally without exposing them to our consumers.

The public interface definitions are exposed to our consumers through an instance of the `ILIAS\UI\Factory`, which can
be thought of as the root node of the hierarchical component structure. When accessing this object, chained method calls
can also be thought of as a path, where a component would be accessed like `$factory->parent()->child()->leaf()`. An
instance of this object can be obtained by the new
[component bootstrap mechanism](../../../../docs/development/components-and-directories.md), or the legacy service
locator (`$DIC`).

The internal interface, or implementation details, are hidden from our consumers. We achieve this by only ever declaring
public interface definitions as return types. However, internally, we can access a more extensive set of methods by
leveraging [covariance](https://www.php.net/manual/en/language.oop5.variance.php#language.oop5.variance.covariance), or
the Liskov Substitution Principlee as such.

Following this principle, if `S` is a subtype of `T`, objects of type `T` can be replaced with objects of type `S`
without affecting the program's behaviour. Covariance in PHP is just that: it allows us to declare more specific return
types in our implementation than declared on our public interfaces. This means we can return concrete implementations
(subtype `S`) instead of the public interface definition (type `T`) in our implementation, without altering the programs
behaviour.

This should sound very familiar to a PHP developer. An important detail which might be missed though, is the additional
layer we create by this: the UI framework can now declare the concrete implementation as a type, through which we gain
access to the hidden implementation details - without exposing them on a public interface level.

### Example of covariance

Let's look at an example:

```php
namespace ILIAS\UI\Component;

interface ComponentInterface {}

interface ComponentFactoryInterface
{
    public function component(): ComponentInterface;
}

interface UIFactoryInterface
{
    public function components(): ComponentFactoryInterface;
}
```

```php
namespace ILIAS\UI\Implementation\Component;

class Component implements ComponentInterface {}

class ComponentFactory implements ComponentFactoryInterface
{
    /** returns subtype S instead of type T */
    public function component(): Component
    {
        return new Component();
    }
}

class UIFactory implements UIFactoryInterface
{
    /** returns subtype S instead of type T */
    public function components(): ComponentFactory
    {
        return new ComponentFactory();
    }
}
```

In this example, the `UIFactoryInterface` serves as the public entry point to the UI framework, similar to the
`ILIAS\UI\Factory`. When an instance of this factory is obtained, by e.g. a function that declares the return type as
the interface, we ensure that only the public context (`T`) of the UI framework is exposed. But as you can see, the
implementation refers to the more concrete object (`S`), which exposes our internal context.

If we take this one step further, and consider the mechanism to
[exchange UI factories](../../../../docs/development/ui-plugin-manipulations.md#uifactory-manipulations), we must treat
the subtype `S` as the effective interface of the internal context. In this scenario, `S` effectively becomes `T` for
anyone interacting with a factory.

This means that plugin developers must always extend from the subtype and not the original type (the public interface),
since the UI framework has specific expectations to its internals. In order to exchange the implementation using the new
component bootstrap mechanism as well, we needed to introduce another interface called
`ILIAS\UI\Implementation\FactoryInternal`. **Please note that the public and internal factory COULD be exchanged
seperately, but replacing the internal factory will also replace the public factory by default.**

### Crossing the line with composite UI components

The previous chapter explained how the UI framework manages a public and internal context for the creation and
interaction with our UI components. We have also established the existence of two interface definitions: the public
interface and the internal interface, represented by `ILIAS\UI\Factory` and `ILIAS\UI\Implementation\FactoryInternal`.

However, there are certain UI components which blur the line between these contexts. These are composite components that
accept other UI components, either during construction or through mutators. At this point, the UI framework can no
longer rely on the internal interface, since the provided component may be less concrete - therefore custom. **This
means we have to be careful when adding our type declarations, so we do not set expectations which cannot be fulfilled
at runtime.**

In other words the UI framework loses access to the internal specifics of a UI component provided this way. It must
interact with the component solely through the public interface definition, **which means developers of the UI framework
MUST only use injected components according to the public interface definition, or render them. It also means the UI
framework MUST only ever render concrete implementations, and never less concrete (custom) ones, checking the instance
as closely as possible.**

### The valley of uncertainty

So in summary, we have learned that there is a public and internal context, and that composite UI components blur the
line between these two. We also learned that component renderers must only ever render the concrete implementation of
their component(s), so the UI framework does not accidentally handle less concrete (custom) ones.

This leaves us with the uncertainty, that some UI components may not be handled altogether, creating a valley between
the public and internal context. This is where custom components live. However, this is only true if someone actively
decides to introduce custom components, and **MUST therefore take full responsibility over the proper handling of
them.**

If the component indeed is less concrete (custom), the mechanism to
[exchange the default renderer](../../../../docs/development/ui-plugin-manipulations.md#uirenderer-manipulations) will
take effect and render the custom component by leveraging the rendering chain.
