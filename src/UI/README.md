# The ILIAS UI-Framework

The ILIAS UI-Framework helps you to implement GUIs consistent with the guidelines
of the Kitchen Sink.

## Use Kitchen Sink Concepts

The ILIAS UI-Framework deals with the concepts found in the Kitchen Sink. In fact,
this framework and the Kitchen Sink are heavily related. You won't need to think
about HTML if you're using this framework. You also won't need to think about
the implementation you are using, the device your GUI is displayed on or the
CSS-classes you need to use. You will be able to talk to other people (like users
or designers) using the same concepts and problem space as they do. This is also
not a templating framework.

## Compose GUIs from Simple Parts

In the ILIAS UI-Framework, GUIs are described by composing large chunks from
smaller components. The available components and their possible compositions are
described in the Kitchen Sink. The single components only have little  configuration,
complex GUIs emerge from simple parts. You also won't need to modify existing
components, just use them as provided.

## Correctness by Construction and Testability

The design of the ILIAS UI-Framework makes it possible to identify lots of
guideline violations during the construction of a GUI and turn them into errors
or exceptions in PHP. This gives you the freedom to care about your GUI instead
of the guidelines it should conform to. You also can check your final GUI for
Kitchen Sink compliance using the procedures the framework provides for Unit
Testing.

## Using the Framework

As a user of the ILIAS UI-Framework your entry point to the framework is provided
via the dependency injection container `$DIC->ui()->factory()`, which gives you
access to the main factory implementing ILIAS\UI\Factory.

### How to Discover the Components in the Framework?

The factories provided by the framework are structured in the same way as the
taxonomy given in the [KS-Layout](http://www.ilias.de/docu/goto_docu_wiki_wpage_3852_1357.html#ilPageTocA11).
The main factory provides methods for every node or leaf in the `Class`-Layer
of the Kitchen Sink Taxonomy. Using that method you get a sub factory if methods
corresponds to a node in the layout. If the method corresponds to a leaf in the
layout, you get a PHP representation of the component you chose. Since the Jour
Fixe decides upon entries in the Kitchen Sink, the factories in the framework
only contain entries `Accepted` by the JF. Creating a component with the
framework thus just means following the path from the `Class` to the leaf you
want to use in your GUI.

The entries of the Kitchen Sink are documented in this framework in a machine
readable form. That means you can rely on the documentation given in the
interfaces to the factories, other representations of the Kitchen Sink are
derived from there. This also means you can chose to use the [documentation of the
Kitchen Sink in ILIAS](http://www.ilias.de/docu/goto_docu_wiki_wpage_4009_1357.html)
to check out the components.

### How to Use the Components of the Framework?

With the ILIAS UI-Framework you describe how your GUI is structured instead of
instructing the system to construct it for you. The main principle for the description
of GUIs is composition.

You declare you components by providing a minimum set of properties and maybe
other components that are bundled in your component. All compents in the framework
strive to only use a small amount of required properties and provide sensible
defaults for other properties.

Since the representation of the components are implemented as immutable objects,
you can savely reuse components created elsewhere in your code, or pass your
component to other code without being concerned if the other code modifies it.

[Example 1](examples/Glyphs/envelope.php)
[Example 1](examples/Glyphs/attachment_with_counters.php)

## Implementing Elements in the Framework

As an implementor of components in the ILIAS UI-Framework you need to stick to
some [rules](doku/rules.md), to make sure the framework behaves in a uniform and
predictable way accross all components. Since a lot of code will rely on the
framework and the Kitchen Sink is coupled to the framework, there also are processes
to introduce new components in the framework and modify existing components.

### How to Introduce a New Component?

New components are introduced in the UI-Framework and the Kitchen Sink in
parallel to maintain the correspondence between the KS and the UI-Framework.

An entry in the Kitchen Sink passes through three states:

* **To be revised**: The entry is still being worked on. Just use a local copy
  or a fork of the ILIAS repository and try out what ever you want.
* **Proposed**: The entry has been revisited and is proposed to the Jour Fixe,
  but has not yet been decided upon. To enter this state, create a pull request
  against  the ILIAS trunk containing your proposed component and take it to the
  Jour Fixe. You need to provide a (mostly) complete definition of the component
  but an implementation is not required at this point. Your will have better
  chances if you also bring some visual representation of your new component,
  you may use the ILIAS edge branch for that.
* **Accepted**: The entry has been accepted by the JF. This, as allways, might
  need some iterations on the component.

These states are represented by using functionality of git and GitHub. After
acceptance, the new entry is part of the Kitchen Sink as well as part of the
source code in the trunk.

### How to Implement a Component?

TODO: write me
STEPS:

* Create a test for a factory if your component requires a new factory.
* Create an interface for the factory and fill in kitchen sink information.
* Create and empty interfaces for the component you want to implement.
* Work on your yaml definitions until they pass your newly written test.
  Remember that yaml does not use tabs for indentation. You could also use
  the $kitchen_sink_info of the factory test to tell the test which should
  rules you disregard.
* Add your newly created factory methods to the implementatio of the factory
  and make sure it throw an \ILIAS\UI\NotImplementedException.
* Make sure you didn't break other tests by running all UI tests.
* Your good to go for your first commit.
* Know you need to model the component you want to introduce by defining its
  interface and the factory method that constructs the component. To make your
  component easy to use, it should be creatable with a minimum of parameters
  and use sensible defaults for the most of its properties. Also think about the
  use cases for your component. Make typical use cases easy to implement and
  more special use cases harder to implement. Put getters for all properties on
  your interface. Make sure you understand, that all UI components should be
  immutable, i.e. instead of defining setters `setXYZ` you must define mutators
  `withXYZ` that return copies of your component with changed properties. Try
  to use as little mutators as possible and try to make it easy to maintain the
  invariants defined in your rules when mutators will be used.
* Write some tests on your component.
* Wire the implementation of new factories, if you needed to create new factories.

### How to Change an Existing Component?

TODO: write me

## FAQ

### There are so many rules, is that really necessary?

The current state of the art in ILIAS GUI creation was dubbed "The GUI Anarchy"
by some smart person. The introduction of the ILIAS UI framework aims at bringing
more structure in the GUIs of ILIAS. As one (or two) maintainers for all things
GUI of ILIAS is no option for several reasons and the current state (without rules)
is anarchy, rules seem to be the only sensible option to get some structure. All
exisiting rules have a purpose, but there might be a more terse way to explain
them. If you have found it, we'll be glad to accept your PR.

### I don't understand that stuff, is there anyone who can explain it to me?

Yes. Ask Richard Klees <richard.klees@concepts-and-training.de> or Timon Amstutz
 <timon.amstutz@ilub.unibe.ch>.
