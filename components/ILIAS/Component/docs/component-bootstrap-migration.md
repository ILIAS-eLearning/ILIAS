# How to migrate to the component bootstrap mechanism

This document describes how an ILIAS component can be migrated from the legacy initialisation to the new component
bootstrap mechanism. Its purpose is to aid developers and 
[authorities who sign off on code changes](../../../../docs/development/maintenance.md#authorities) who are tasked with
the migration of their component to this new mechanism, or want to introduce new ones while the "component revision"
big project is still ongoing.

The described practises and required steps for a successful migration are put in two categories: one category is for
instructions, those are patterns, steps and practises which must be followed to the best of each individuals ability;
the other category is for recommendations, which should be considered by each individual but are shaped by personal
preference and might not be suitable for all components.

More information about the component revision and the new repository- and component-structure can be found here:

* [Repository- and component-structure](../../../../docs/development/components-and-directories.md)
* [Component Revision (Big Project)](https://docu.ilias.de/go/wiki/wpage_7295_1357)

## Instructions

This chapter holds instructions which must be followed to the best of each developers ability.

### Starting point (and bad practices)

The current state of most ILIAS components – former modules and services – is heavily reliant on the global dependency
injection container (known as `global $DIC`), which is used in any place at any time to service-locate something from
somewhere. What we are trying to say here is that most of the components do not yet fully embrace dependency injection
(DI) like its meant to be and therefore don't use it to its full potential. While the first part of the previous
sentence may be achieved more easily, the latter is still not fulfilled by the sheer implementation of injecting
something into something else. It needs to be refined in most cases; we must not simply inject entire subsystems of
ILIAS if we are only going to use a few aspects of it. DI needs careful consideration and the migration towards the
component bootstrap mechanism is the ideal time to reconsider how your objects and facilities are managed – internally
but also externally.

```php
class InternalThingy
{
    // this is not real DI, we still need to service-locate:
    public function __construct(\ILIAS\DI\Container $dic)
    {
        $this->dep1 = $dic->user();
    }
}
```

Having said that, in addition to the above ILIAS components its facilities are also initialised inside
`ilInitialisation` which belongs to the Init component. There are currently two main practises being used to initialise
a component as a whole:

- The entire initialisation code is implemented inside the `ilInitialisation` class directly and is invoked at the right
time given the right circumstances (context and availability of dependencies).
- The initialisation code is implemented in a dedicated class, which receives the current dependency injection container
as an argument. The classes then initialise and expose stuff by defining it as an offset inside the container.

Both practices end up in a similar result, where facilities which are required/used by other components are exposed
inside an instance of the `ILIAS\DI\Container` which is globally available. **We frequently observe that internal
facilities are also exposed inside the container, even though other components should not use it directly.** This has
already led to many locations where some specific offset of the container is accessed directly, without the
`ILIAS\DI\Container` offering a concrete method to fetch it (or its entry point). This is not properly encapsulated and
makes other components rely on internal implementation details, which may not be known or considered during a possible
refactoring. This is another thing which is best improved during the migration of a component.

```php
public function init(\ILIAS\DI\Container $dic): void
{
    $dic['public.thingy'] = static fn () => new PublicThingy(
        $dic['internal.thingy'],
    );
    // this is still public, any component can access this offset:
    $dic['internal.thingy'] = static fn () => new InternalThingy(
       $dic->user()
    );
}
```

The way the initialisation is currently orchestrated, mainly because often times it is unclear what dependency is
available at what time, we have embraced a pattern which allows the lazy-loading of objects, where instances are not
created immediately, but are wrapped inside an anonymous function / arrow function / first class function and stored in
the `ILIAS\DI\Container` instead. This way, if an actual instance of a service is requested by accessing the offset, it
triggers sort of a chain reaction where the functions are invoked recursively because dependencies are also fetched from
the same container. This pattern is still used inside the new bootstrap mechanism for the same reason, while determining
the appropriate order of dependencies during build-time (amongst some other things).

At this point you should have a vague (or clear if we did a good job here) picture about what patterns have been used,
which mostly avoid proper DI and expose more functionality than probably necessary during the initialisation of
components. You should also know where to find most of this code, so we have established your starting point.

### New component wiring and proper encapsulation

The component bootstrap mechanism offers a total of 
[four different ways to wire components](../../../../docs/development/components-and-directories.md#types-of-dependencies-and-integration-strategies)
with one another. Please take a look at the linked document before starting your work on the migration of a component.
In the previous chapter we have explained how some components expose more functionality to the rest of the system than
probably necessary. These new initialisation and integration strategies offer a great way to properly encapsulate your
component from the rest of the system and define precise ways of interactions.

```php
class SomeComponent implements Component
{
    // types of dependencies and integration strategies:
    public function init(
        array | \ArrayAccess &$define,
        array | \ArrayAccess &$implement,
        array | \ArrayAccess &$use,
        array | \ArrayAccess &$contribute,
        array | \ArrayAccess &$seek,
        array | \ArrayAccess &$provide,
        array | \ArrayAccess &$pull,
        array | \ArrayAccess &$internal,
    ) : void {
    }
}
```

There are also a lot of classes where an exact instance of something is exposed globally. This may make sense for some
things, but there are a lot of things which could highly benefit from refactoring into subsystems. For this we recommend
to implement the facade pattern, for which in a first step one must extract an interface from the existing class, so we
can later on swap out its parts without breaking the consumers and migrate this gradually if need be.

We also do not or rarely use namespaces. This was mostly due to the fact that `ilCtrl` did not support this, this has
been fixed in the meantime though. So, same goes here as well, now is the best time to embrace namespaces as well.

### What needs to be touched?

Since there are multiple places and components which need to be touched, especially if your component happens to utilise
a lot of other components which are unmigrated yet, it makes sense to iterate over the most common ones (abstract ones 
but also concrete ones) in this chapter.

- `\ilInitialisation` and optionally your wrapper (like `Init\Dependencies\InitHttpServices`): to take a look at your
initialisation and operate it out of its old place (and improve it!).
- Your component class (`<Component>.php`): to implement the initialisation using the new dependency types and
integration strategies.
- Other's component classes: there are two scenarios in which other components need to be touched as well. Its important
we do this, because if we simply move the initialisation code to the new system and solely rely on the compatibility
layer explained in the next chapter, we will break the whole system once we remove the layer when every component is
migrated. Scenarios:
  - a) the other component is unmigrated and needs a compatibility layer for the new system
  - b) the component being migrated was already touched during the migration of another and it contains a compatibility
  layer already. Now you can amend the usage inside the other component and drop the layer if it was the only usage.
  - c) there are scalar dependencies, like an object-id (as `int`) passed to the constructor. The new system does not
  support such trivial wiring, it needs to be remodeled and put behind an abstraction layer. This layer potentially
  needs implementation in another component than yours. The chapter on established patterns will tell you more about it.
- Your component entry points: because there is a new way of doing things, you will need to migrate your endpoints as
well. Since a few components already migrated, its very possible that this is already done. If not, check out the entry
points chapter for guidance.

### Setting clear boundaries!

That's not just important in real life, but also for this project and during a migration of an ILIAS component. If you
are migrating a component to the bootstrap mechanism, you will quickly notice how you depend on A which in turn depends
on B which yet again depends on C and D, who both depend on E, F and G – all of which are not migrated. If you would
migrate such a component and migrate all facilities of different components which are needed at some point in your
component, you would probably complete half the component revision for all of us. I mean, feel free to do so, we would
all highly appreciate this ofc, but in reality this will most likely not be possible =).

For this reason its important to set clear boundaries. What components should be migrated at the same time highly
depends on the concrete scenario. Maybe you have been contracted to migrate all of your components, so the components of
yours depending on one another can probably be migrated in one go. However, lets assume for this chapter you are
migrating exactly one component and one component only. In this scenario, you will most likely run into some kind of
dependency on another part of the system. If this part is not yet migrated, we need to introduce a compatibility layer
so you can continue with your work and make the component compatible with the unmigrated part of the system.

This is achieved by using the proxy pattern which hides the fact a component may not be migrated yet. It mimics the
behaviour of the facility in question, while it delegates all logic to the unmigrated implementation. This is important
because during the build process of ILIAS we need some bare-minimum implementation of some facility, so it can produce
the bootstrap artifact and dependency graph correctly. How this is achieved is explained in one of the following
chapters.

### Contributing public assets

During the first iteration of the component revision we have moved the publicly available assets into a dedicated folder
named `public/`. This is primarily done for security reasons but it also gives us a good grasp over what assets and
access to business logic is actually provided to the client (browser).

This directory is managed by the Component ILIAS component and is (currently) rebuilt every time the application is
built. To contribute your assets to this directory you need to follow the code snippet below. It utilises the "seek" and
"contribute" integration strategy of the component wiring, where the responsible machinery seeks for implementations of
a given interface by looking for explicit contributions of it.

```php
class SomeComponent implements Component
{
    public function init(
        // ...
        array | \ArrayAccess &$contribute,
        // ...
    ) : void {
        // contribute a different public asset like so:
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/SomeFunctionality.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentCSS($this, "css/some-stylesheet.css");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\NodeModule("@vendor/library/dist/index.js");
    }
}
```

Make sure your assets are in the right place; the machinery will look in `<component>/resources/` for it. Inspect the
respective kind of asset implementation for a more detailed location. If you need to provide an endpoint (like
`ilias.php`), check out the corresponding chapter of this guide.

### Backwards compatibility

At this point it might have occurred to you, that we live in an entirely new and different world now, and asked
yourself how you make things available for the unmigrated part of the system. If that's not the case, don't worry, we
will explain it to you anyways. This chapter covers how we maintain backwards compatibility during the gradual
migration of ILIAS components during the progression of the "component revision" big project.

As mentioned above, the bootstrap mechanism is something completely different than how we previously managed things. The
bootstrap mechanism creates an artifact during build-time, which contains a compiled script where all dependencies are
initialised in the appropriate order and fashion, while the types of dependencies and different integration strategies
are respected. This means, after migrating a component and removing its initialisation from `ilInitialisation`, calling
`ilInitialisation::initILIAS()` is no longer an option.

Since we do not want to maintain a duplicate initialisation for the same component, only to provide it for migrated and
unmigrated components at the same time, we have introduced a legacy initialisation bridge. This is a structured way to
expose migrated components and its facilities inside the legacy environment. While this adds some overhead, it is the
appropriate tool for the job and exposes the true ugliness of the service locator in the first place. Here's what you
do:

```php
// step 1: migrate your component and implement its initialisation:
class SomeComponent implements Component
{
    public function init(
        array | \ArrayAccess &$define,
        array | \ArrayAccess &$implement,
        // ...
    ) : void {
        // stumble over some legacy thing which is required by unmigrated components:
        $define[] = LegacyThingyInterface::class;
        $implement[LegacyThingyInterface::class] = static fn() => new SomeLegacyThingy();
    }
}

// step 2: find the \ILIAS\Init\AllModernComponents class and extend it:
class AllModernComponents implements EntryPoint
{
    // use DI to retrieve your implementation inside the bridge:
    public function __construct(
        protected LegacyThingyInterface $legacy_thingy,
    ) {
    }
    
    // the method you are looking for in order to 'bridge' them:
    protected function populateComponentsInLegacyEnvironment(\Pimple\Container $DIC): void
    {
        // expose your implementation using its legacy offset:
        $DIC['public.legacy_offset'] = fn() => $this->legacy_thingy;
    }
}

// step 3: create the wiring between the legacy initialisation and your component: 
class Init implements Component
{
    public function init(
        // ...
        array | \ArrayAccess &$use,
        array | \ArrayAccess &$contribute,
        // ...
    ) : void {
        $contribute[Component\EntryPoint::class] = static fn() =>
            new AllModernComponents(
                // inject your implementation into the bridge using DI:
                $use[LegacyThingyInterface::class],
            );
    }
}
```

Thats it. Obviously the list of components and the list of properties of the bridge above is already quite long and will
become very long in the end. But this actually shows us the unfiltered and complete list of dependencies which are used
throughout the system.

### Combining old with new in endpoints

Now for those of you who provide endpoints to the system, like the famous `ilias.php`, there is a new way to initialise
ILIAS and make your endpoint viable.

There is a new interface called `\ILIAS\Component\EntryPoint`, which is used to define entry-points of the system. This
is important, because your endpoint should most likely interact with these things. While an endpoint is contributed to
the system using an `\ILIAS\Component\Resource\Endpoint` (public asset) class, your implementation is actually decoupled from all
this. It will be a 'standalone' PHP script, which needs to take care of the proper initialisation (if this is what you
need). We cannot use some fancy mechanic here, because the script is the starting point of the whole system. We cannot
inject stuff because its a simple PHP script which is responsible to kick-off the whole thing. Thats why in this chapter
we show you how to implement and contribute your endpoint and initialise ILIAS properly so the old unmigrated components
work amongst the new migrated components all the same.

```php
class SomeComponent implements Component
{ 
    public function init(
        // ...
        array | \ArrayAccess &$contribute,
        // ...
    ) : void {
        // contribute your endpoint to the system:
        $contribute[Component\Resource\PublicAsset::class] = static fn() =>
            new Component\Resource\Endpoint($this, "endpoint.php");
    }
}
```

Make sure your endpoint is in the right place; ILIAS will look inside `<component>/resources/` for it. Inside your
actual endpoint (the PHP script), you must implement the following lines if you want to initialise ILIAS:

```php
// load composer's autoloader:
require_once __DIR__ . '/../vendor/composer/vendor/autoload.php';
// include the artifact produced by the component bootstrap mechanism:
require_once __DIR__ . '/../artifacts/bootstrap_default.php';
// enter the \ILIAS\Init\AllModernComponents entry point like so:
entry_point('ILIAS Legacy Initialisation Adapter');

// after this, our legacy service-locator should be ready:
/** @var $DIC \ILIAS\DI\Container */
global $DIC;
// $DIC->ctrl()->callBaseClass();
```

For someone who is interested in implementing their own entry-point, which e.g. does not fully initialise ILIAS or
performs entirely different actions, you can update the code above in a way, where `entry_point(...)` is provided with
the name returned by `ILIAS\Component\EntryPoint::getName()`. The function itself is loaded by the bootstrap artifact
and provides the way of entering one of the existing entry points of the system.

### Newly established patterns

The following patterns have emerged during the first migrations. The ones that need more than a sentence are described
in a dedicated sub-chapter below:

- Backwards compatibility layer, explained by the previous chapter.
- Proxy pattern for unmigrated components, compatibility layer between migrated/unmigrated.
- Scalar dependencies put behind configuration interfaces, defined and used by the requiring system, implemented by the
providing system.
- Split read and write access to functionality on a programming level already (interfaces).

The following are not patterns in the strict sense, but conventions you should follow nonetheless:

- `static fn` vs `fn`: think about when to use which one, the smaller scope improves performance.
- Avoid anonymous classes. Introduce dedicated classes, even for the most trivial interface implementations, and don't
be lazy about it. There is currently one known exception where this is hard to avoid, see the caveat on the dependence
on artifacts below.
- Avoid constants. They are global state and we really don't want global state; use the configuration interfaces
described below instead. Be aware that this is the target state and not something every migration can fully achieve
today: a number of constants defined by `ilInitialisation` (`ILIAS_ABSOLUTE_PATH`, `ILIAS_DATA_DIR`, `CLIENT_ID`, …)
are currently still the only way to obtain these values during early bootstrap, before the services that would replace
them are wired. Do not introduce new ones, and replace the existing ones wherever the wiring already allows it.

#### Proxy pattern for a compatibility layer

Assume the following scenario for the code example below:

- Component A is being migrated
- Component A has dependency on Component B (the B class)
- Component B is not migrated

The procedure we currently established is the following:

- Because we do not want to or have the capacity or authority to migrate the other component (B) we depend on, we set a
clear boundary here. We achieve this by looking into what is actually used by our component (A) and extract a new
interface from this (if there isn't one we should use instead).
- Assume the B class is not namespaced and abstracted in any way yet and lives within the `classes/` folder, we will
want to extract an interface, ideally with the required functionality only, from this class and implement it inside the
`src/` directory. The B class then implements this new interface, which allows us to create the proxy now. If there
already is a usable interface we can use, this step can be skipped ofc.
- The proxy is a bare-minimum implementation of this interface, which delegates all method calls directly to the actual
B class, which is retrieved by `global $DIC`. This works because the proxy does not require a constructor, which
makes it compatible with the build and bootstrap process without `$DIC`, while it is still functional in the web context
because its legacy implementation will be invoked at a later point which initialises the B class in this container.


```php
// ComponentB/classes/B.php:
class B
{
    public function functionality(): void
    {
        // ...
    }
}

// ComponentB/src/BInterface.php:
interface BInterface
{
    public function functionality(): void;
}

// ComponentB/src/BLegacyProxy.php:
final class BLegacyProxy implements BInterface
{
    public function functionality(): void
    {
        // delegate actual call to the appropriate B class
        global $DIC;
        $DIC->B()->functionality();
    }
}

// ComponentB/ComponentB.php
class ComponentB implements Component
{
    public function init(
        array | \ArrayAccess &$define,
        array | \ArrayAccess &$implement,
        // ...
    ) : void {
        // define the abstraction, make it known to the system:
        $define[] = BInterface::class;
        // implement the abstraction using the legacy proxy, so other components can use it:
        $implement[BInterface::class] = static fn() => new BLegacyProxy();
    }
}
```

```php
// ComponentA/ComponentA.php
class ComponentA implements Component
{
    public function init(
        // ...
        array | \ArrayAccess &$use,
        // ...
        array | \ArrayAccess &$internal,
    ) : void {
        // use the legacy proxy implementation here:
        $internal[A::class] = static fn() => new A($use[BInterface::class]);
    }
}
```

#### Abstraction of scalar data-types

In some cases there will be dependencies to scalar data-types. For these cases we need an established pattern, which
will be covered in this chapter, because the new component bootstrap mechanism prefers to work with classes rather than
arbitrary offsets which hold an anonymous function returning some hardcoded value.

While the primary target of this abstraction may be for scalar data-types, it can also
be applied to other scenarios, where a more complex object is passed along. When and when not to use this pattern may
differ across contexts, but as a rule of thumb this pattern should not be used for more complex things than data-
transfer-objects (DTO). There could be exceptions to this where some sort of builder pattern is applied to create a
service, but this should be very limited; we ought follow correctness on construction to the best of our ability.

We currently address this problem by looking at it from a configuration perspective. In most cases, such scalar or
trivial object data-types are used during initialisation for configuring the behaviour of some implementation. A good
example for this are INI-values, which are set by system administrators. But these can also be currently hard-coded
values like the `UI\Component\Progress\AsyncRefreshInterval`, where this is still a configuration for which we currently
do not provide any interaction.

This is a good time to think about coupling, especially because there is one caveat which needs to be considered here:
**Configuration values MUST NOT be accessed inside the constructor, ever**. The bootstrap mechanism will not be able to
build its artifact if your object depends on the implementation of something else, because the wiring does not exist at
this point. Therefore, we recommend to introduce dedicated configuration interfaces that will return exactly what is
needed by an appropriate getter – ideally without any arguments. Why is this actually a blessing in disguise? So glad
you ask, because this pattern will ultimately loosen the coupling between your component and another, by hiding the
mechanism which is ultimately used to retrieve the desired value. This will make it very easy to switch mechanism,
location or even underlying business logic in order to retrieve this value, without ever having to touch your component
again. This refactoring could even be tackled by someone else entirely, because you have just decoupled your component
so kindly.

Let's say you have something like this inside your legacy initialisation:

```php
// SomeComponent/src/SomeService.php:
class SomeService
{
    public function __construct(
        protected readonly int $some_config_value,
    ) {
    }
    
    public function functionality(): void
    {
        $this->some_config_value; //...
    }
}

$some_config_value = 0; // possibly retrieved by $DIC as well though
$DIC['some_component.some_service'] = static fn($DIC) => new SomeService($some_config_value);
```

Then you would refactor it according to the pattern to the following structure:

```php
// SomeComponent/src/SomeService.php:
class SomeService
{
    public function __construct(
        protected SomeConfigInterface $some_config,
    ) {
    }
    
    public function functionality(): void
    {
        // notice how retrieval is deferred now, do not store in property please!
        $this->some_config->getValue();
    }
}

// SomeComponent/src/SomeConfigInterface.php:
interface SomeConfigInterface
{
    public function getValue(): int;
}

// SomeComponent/SomeComponent.php:
class SomeComponent implements Component
{
    // types of dependencies and integration strategies:
    public function init(
        array | \ArrayAccess &$define,
        // ...
        array | \ArrayAccess &$use,
        // ...
        array | \ArrayAccess &$internal,
    ) : void {
        // define a new interface for your configuration and make it known to the system:
        $define[] = SomeConfigInterface::class;
        // use the implementation made by some other component for your config:
        $internal[SomeService::class] = static fn() => new SomeService($use[SomeConfigInterface::class]);
    }
}

// OtherComponent/OtherComponent.php:
class OtherComponent implements Component
{
    // types of dependencies and integration strategies:
    public function init(
        // ...
        array | \ArrayAccess &$implement,
        // ...
    ) : void {
        // implement the defined interface of SomeComponent to provide its value:
        $implement[SomeConfigInterface::class] = static fn() => new SomeConfig();
    }
}

// OtherComponent/src/SomeConfig.php:
class SomeConfig implements SomeConfigInterface
{
    // ...
}
```

As you can see we make use of the "define", "implement" and "use" component wiring. We define an interface for the
configuration put over the scalar or trivial object data-types and use it for the initialisation. Then we search for an
appropriate component to implement this defined interface, which may be the same component ofc, and add its
implementation in the right place.

**In many cases this will be a legacy-proxy as well, described in the previous chapter, who delegates the method call in
the web context to some method of `$DIC`.**

#### Restricted access on a programming level

According to the interface segregation principle, an object should only rely on methods it also really needs. In ILIAS
most of the time an entire service is simply injected with all of its functionality for free. This is a bad habit; as
already explained inside the chapter that gives an overview of the current situation, we should be more cautious when
implementing our DI. Doing proper DI can already limit the set of available methods and narrow the used
methods down to the actually used ones quite a bit. However, we noticed that many places could benefit from a
segregation of their methods that mutate stuff and methods that only return some calculated result.

Doing so will allow us to restrict access to functionality on a programming level already. Assume we have the following
object without any abstraction:

```php
class GodObjectThatDoesItAll
{
    public function setValueX(mixed $x): void
    {
        // ...
    }
    
    public function getValueX(): mixed
    {
        // ...
    }
    // ...
}
```

We could introduce separate interfaces for our getters and setters here, or in a more abstract sense one for the
methods mutating state and another for the ones only reading it. The object could stay the same, we only need to
extract interfaces of the corresponding methods. The migration to the new component bootstrap mechanism is a great time
to think about this as well, especially if we need to introduce a new abstraction layer anyways, i.e. due to the need
of a legacy-proxy.

```php
interface WriteActions
{
    public function setValueX(mixed $x): void;
    // ...
}

interface ReadActions
{
    public function getValueX(): mixed;
    // ...
}

class GodObjectThatDoesItAll implements WriteActions, ReadActions
{
    // ...
}
```

This also pairs well with the configuration pattern we have established as an abstraction layer put over the scalar and
trivial object data-types during initialisation.

### Known caveats

This chapter holds a list of known caveats which should ideally be updated anytime some new issue is discovered that
does not only concern one concrete component. These are things like missing concepts, patterns or issues with existing
ones from this document.

- Migration of controllers: `ilCtrl` is very old-fashioned and is not yet migrated to the bootstrap mechanism itself.
This creates a problem for any component that offers functionality via GUI (the browser) and relies on the `ilCtrl`
for routing – which ofc are all. This means we need to find a viable solution for this fast, otherwise only components
formerly known as services will be able to migrate to this mechanism. This should also be a memento to the fact that we
need a more modern routing, which should be tackled by the progression of the
["static routing" big project](https://docu.ilias.de/go/wiki/wpage_8780_1357), which aims for a solution where routes
and corresponding logic are determined at build-time and stored in some kind of artifact, which should make it possible
to generate proper wiring between components and use proper DI as well.
- Context-specific logic: using the legacy initialisation it was possible to define very granularly in what context what
things should be initialised. With the new bootstrap mechanism this is a tiny bit more cumbersome, because we actually
don't differentiate between these contexts anymore. Everything is determined during build-time and different artifacts
are produced for different contexts instead. What the implications of this are is unknown at the moment. This is
probably something we need to analyse and discuss when first components that heavily rely on this mechanic are migrated.
- Dependence on artifacts: currently one needs to use `BuildArtifactObjective::PATH()` to get the path of an artifact
for its inclusion. We currently lack a facility which properly manages this stuff and can provide artifact paths / data
in a structured manner. This leads to potential anonymous classes or artifacts which cannot yet be properly injected. We
probably need to find a solution for this in early stages of this project too.
- Purged `public` directory: the current machinery which manages what assets will ultimately end up in our isolated web
root directory currently purges all files and moves contributed assets into the directory every time the application is
built. This makes it impractical for system administrators who need/want to provide additional assets to this directory,
like a `robots.txt` or if some other application potentially runs there (in a sub directory for example). We probably
want to create some sort of diff which we then compare in order to update only the changed files and basically not purge
the entire directory on every build.
- Contribute configuration screens: we currently lack a sophisticated concept or integration mechanism for components to
contribute configuration screen. A first step will be to migrate the main menu orchestrated by the GlobalScreen so these
entries can be collected inside the bootstrap mechanism and used by both ILIAS and third-party components. In the long
run we may want to improve this concept so dedicated routes/configurations/storage mechanisms can be contributed.
- Contribute translations: the Language component still relies on translation files inside the `./lang` directory. While
this mechanism still works for ILIAS components, third-party components will run into issues because they can no longer
contribute translations of their own. This will best be tackled by splitting up the translation files to their
respective components, so they can be contributed to the system using the appropriate tooling, which would also work for
third-party components.
- Refactorings initiated by single persons/institutions: there will be many use-cases where one person or institution
tackles a migration of some component or specific mechanism, where it will most likely be expected that usages inside
other components are amended. This will lead to a lot of cases where the best-practices described by this guideline will
become a huge overhead. We need to define how we should treat these cases and how the expected workload should be shared
between initiators and consumers of something. It could be that we have to establish special practises for individual
refactorings, all of which SHOULD be documented here to maintain an overview.

### The process as a whole

> **TODO:** this chapter has not been written yet. It is meant to describe how a migration works in terms of process,
> not in terms of actual implementation. We will provide a guide or a separate document about this in a later
> iteration.

The questions it needs to answer:

- who acquires funding?
- who is responsible for a concrete migration?
- who is responsible for all migrations (as an overview)?
- who is authoring the migration?
- who is reviewing the result of a migration (QA)?
- who needs to give approval and when?
- how should the result be published?

## Recommendations

This chapter contains recommendations which should be considered when migrating a component. These are shaped by
preference, but they still might add something valuable to your component as well.

### Fully qualified domain names (FQDN)

The larger a component initialisation becomes, the longer, wider and more cumbersome becomes the list of internal and
external facilities needed. The UI framework is a great example for this. In order for everybody to easily understand
and see what facilities are used in what locations, it is highly recommended to work with FQDNs only and access them
using the `::class` constant. This is great for two reasons:

- we no longer need a `use` statement and prevent possible naming conflicts that would require aliased imports (`as`)
- we can always see directly from which exact location a facility is used and can tell whether its internal or external.

### Consistency for discoverability

Since it will become increasingly important to know what other ILIAS components offer what facilities, and even more
importantly what facilities are only defined but require 'external' implementation, its important to get a quick grasp
over the most important requirements each initialisation has to the system. Thats why it makes sense to streamline this
as good as possible, so this task which is repeated often, is made easy. Thats why it is recommended to follow the order
of parameters as listed by the `Component::init()` method when defining/exposing/setting its facilities into the
appropriate container to achieve its desired goal. 

## I don't understand this and/or need help

If you feel like you don't fully understand any of the above aspects, or you do and have some constructive criticism
about it, or you simply need some help because there is an edge case or something else we haven't thought about
happening inside your component – contact us.

Who are we you ask? While the brains behind the conceptual work and the first iteration of the component revision was
Richard Klees (from concepts and training gmbh), Thibeau Fuhrer and Fabian Schmid (from sr solutions ag) have taken over
his work and authorities of the Component ILIAS component, since he has left the ILIAS community.

Feel free to contact us on Discord, via Email, or simply ping us on GitHub:

- Thibeau Fuhrer <thibeau@sr.solutions>
- Fabian Schmid <fabian@sr.solutions>
