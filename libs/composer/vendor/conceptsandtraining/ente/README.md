[![Build Status](https://travis-ci.com/conceptsandtraining/lib-ente.svg?token=4shwrT94iPQfpaEX1GWY&branch=master)](https://travis-ci.com/conceptsandtraining/lib-ente)
# ente.php

*An entity component framework for ILIAS.*

**Contact:** [Richard Klees](https://github.com/klees)


## Model

[Entity component model or system is an architectural pattern](https://en.wikipedia.org/wiki/Entity%E2%80%93component%E2%80%93system)
developed for and mostly used in games. Still, the problems solved with that
pattern are discovered in other situations, i.e. the development of interconnected
functionality in [ILIAS](https://github.com/ILIAS-eLearning/ILIAS).

The general problem that is solved could be described as such: One has a system
with some (or often a lot of) entities in it. An entity can be understood as an
object that has an identity and exhibits some continuity over time and space.
Within the system, there are a lot of subsystems that act upon these entities.

> **Example:**
> In a real time strategy game, entities would be units of the armies in the game
> as well as objects in the landscape and maybe other things. Systems acting
> upon these objects would be e.g. a physics system, a path finding system, a
> sound system, ...

> **Example:**
> In ILIAS an entity could basically be identified with all descendants of `ilObject`
> although there are possibly more things that could be called entities, e.g.
> questions in the test. Systems acting upon these entities would then e.g. be the
> RBAC system, the Learning Progress System, the Tree/Repository, ...

Implementing that kind of system with inheritance could lead to some problems that
could also be observed in ILIAS:

* The classes that are used to build entities aqcuire a lot of code to be able to
  serve all systems that are interested in them.
* It is difficult to share functionality between the different classes of entities.
* It is cumbersome to define classes of entities where the objects might or might
  not take part in that system, depending on whatever.
* For languages that are not memory managed (e.g. C) that strategy for implementation
  might also be bad for caching in CPU caches.

Entity component model solves these problems by employing the principle to "favour
composition over inheritance". The entities on their own are understood to just
supply the required identity and continuity, i.e. each entity is basically an id.
Components that serve the needs of different subsystems can then be attached to
the entity to make them take part in that system.

> **Example:**
> A entity in the RTS game could be extended with a "physics" component that
> knows a location and other physical metrics of the entity to make it appear
> on the playground.

> **Example:**
> The implementation of the learning progress in ILIAS actually follows that
> model. Instead of providing some interface that all `ilObjects` with learning
> progress need to implement, one needs to build a separate object that takes
> care of the learning progress of the original `ilObject`.

This pattern for solving the described problem has some virtues over the naive
inheritance based approach:

* Instead of one huge class that contains code for all kind of subsystems the
  entity is split up into distinct classes that each serve only some subset
  of all systems.
* There is an obvious way to share functionality between entities: just use
  the same components or implementation for the component.
* If an entity should not be processed by a subsystem just don't add the
  according component to the entity. This could easily be changed at runtime
  and requires no code change.
* C-programmes and the like could take care about memory locality more easily.
  A similar benefit regarding the database could apply in the PHP scenario.

There are definetly also disadvantages in using the entity component model. For
the sake of the argument they are not written down here. Finding them is left as
an exercise to the reader.


## Implementation

This implementation is meant to work in the context of ILIAS, where Plugins should
be able to define components for `ilObjects`. This library therefore knows four
kinds of objects, where the according interfaces could be found in the base directory
of the lib:

* `Entity` provides the basic means to identify an object. That is, an entity
  needs to be able to provide some comparable id, where objects with the same id
  are indeed the very same object. This is the thingy known from the pattern.
* A `Component` is the thingy from the pattern that provides information and
  implementations for a certain subsystem. Since the library doesn't know about
  these subsystems, the basic interface just provides a method to get to know
  the entity the component belongs to. This will be one main extension point
  for this library.
* This library should be able to work in the context of `ilObjects`, plugins and
  the ILIAS tree. The plugins this framework will be used for are mainly there
  to enhance the ILIAS Course with different functionality. Plugins then need
  to be able to extend the course in different directions, thus a notion of
  sources for components of an entity is required. A `Provider` thus can provide
  different types of components for a fixed entity. 
* All this stuff needs to be tied together, that is what the `Repository` does.
  It is the source to query for `Providers` and `Components`.

A simple implementation (without ILIAS) can be found in `src/Simple`. It defines
two Components `AttachInt` and `AttachString` that both have one `*Memory`
implementation.

An ILIAS based implementation can be found in `src/ILIAS`. It contains two more
noteworthy objects, that act as a plumbing between the simple model presented
above and the factual ILIAS world.

* The `UnboundProvider` accounts for the fact, that ILIAS repository objects can
  actually have different locations in the tree. An object providing components
  could thus provide for different branches of the tree. To still allow for some
  common machinery to store and load providers, repository objects need a way to
  talk about their possibilities to provide components without actually knowing
  which entity they are bound to.
* The `ProviderDB` is that common machinery to store and load providers. Objects
  providing components must create `UnboundProviders` via that object. The ILIAS
  implementation of the model then uses the tree to turn the `UnboundProviders`
  to real providers for the objects in the tree.

The usage of both facilities is showcased in two examples.


## Example

The `example` folder contains two plugins, one that provides a component, one that
uses handles the provided component. Note that both plugins do not know each other.
Their common denominator is this library and the `AttachString` component they both
use. Both plugins try to showcase this framework only and are no good showcases for
general plugin development!

The `AttachString` component is a very dumb component that allows to attach a string
to an entity. We most probably won't use such a simple component in a real world
problem.

To check out what the plugins are doing, copy both of them to the directory for
RepositoryPlugins in an ilias installation, `composer install` them and activate
them within ILIAS. You should be provided with two new types of repo objects: 
`Component Handler Example` and `Component Provider Example`. Create a course and
an object for each plugin within the course.

First open the Provider-object. You should get a simple form where you can add
multiple strings the object will provide via the `AttachString` component. Do that
now (and don't forget to save the form). Then open the Handler-object. You should
see a simple listing of all strings you added to the provider. You can also
add some more providers to the course. The handler will collect the strings
from all providers. You could of course also add some more handlers, but they
won't differ from your first one.

Now have a look at how the provider object is implemented. One thing the plugin
needs to implement is an instance of `UnboundProvider`. This can be found in
`example/ComponentProviderExample/classes/UnboundProvider.php`. This class needs
to tell which components it intents to provide via `componentTypes` and needs to
present a way how instance of these components can be created via `buildComponentsOf`.

The `UnboundProvider` has an `owner`-ilObject, which is used to get the provided
strings from the ILIAS-database. The component interface `AttachString` is used
for the declaration in `componentTypes`, but `buildComponentOf` uses the in-memory
implementation `AttachStringMemory` to create the actual components. 

The provider object needs to define when an `UnboundProvider` is actually created
and the object starts to provide components for some other object. This is done
on creation of the object in `example/ComponentProviderExample/classes/class.ilObjComponentProviderExample.php`. The trait `ilProviderObjectHelper` helps to easily implement that
creation. The object needs to provide a DIC via `getDIC` and then may `createUnboundProvider`
whenever it needs to. To do that, it needs to tell for which objects on its path
it wants to provide components for (`crs` in the example), what the name of the
`UnboundProvider` is and where the according implementation can be found. The object
also needs to take care that the UnboundProviders it created gets destroyed afterwards,
which is done via `deleteUnboundProviders` on object deletion.

The handler object is even simple. In `example/ComponentHandlerExample/classes/class.ilObjComponentHandlerExample.php`
you can see the according implementation for the handler. It uses the `ilHandlerObjectHelper`
trait and also needs to provide a DIC via `getDIC`. It also needs to provide the
`ref_id` of an object it intends to handle components for. We just use the parent
in the example. It then can use `getComponentsOfType` to get all components provided
for its object and do further processing on them.
