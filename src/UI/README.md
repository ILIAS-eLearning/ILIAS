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

[Learn more](doku/use_ks_concepts.md)

## Compose GUIs from Simple Parts

In the ILIAS UI-Frameworks, GUIs are described by composing large chunks from
smaller components. The available components and their possible compositions are
described in the Kitchen Sink. The single components only have little  configuration,
complex GUIs emerge from simple parts. You also won't need to modify existing
components, just use them as provided.

[Learn more](doku/composition.md)

## Correctness by Construction and Testability

The design of the ILIAS UI-Framework makes it possible to identify lots of
guideline violations during the construction of a GUI and turn them into errors
or exceptions in PHP. This gives you the freedom to care about your GUI instead
of the guidelines it should conform to. You also can check your final GUI for
Kitchen Sink Compliance using the procedures the framework provides for Unit
Testing.

[Learn more](doku/correctness.md)


## Using the Framework

As a user of the ILIAS UI-Framework your entry point to the framework is provided
via the dependency injection container `$DIC->UIFactory()`, which gives you
access to the main factory implementing ILIAS\UI\Factory.

### How to Discover the Components in the Framework?

The factories provided by the framework are structured in the same way as the
taxonomy given in the [KS-Layout](http://www.ilias.de/docu/goto_docu_wiki_wpage_3852_1357.html#ilPageTocA11).
The main factory provides methods for every node or leaf in the `Class`-Layer
of the Kitchen Sink Taxonomy. Using that method you get a sub factory if methods
corresponds to a node in the factory. If the method corresponds to a leaf in the
KS-Layout you get a PHP representation of the component you chose. Since the Jour
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

[Example](doku/examples.md#discovery)

### How to Use the Components of the Framework?

With the ILIAS UI-Framework you describe how your GUI is structured instead of
instructing the system to construct it for you. The main principle for the description
of GUIs is composition. There are two types of components, corresponding to the
`Aggregation`-Layer in the [KS-Layout](http://www.ilias.de/docu/goto_docu_wiki_wpage_3852_1357.html#ilPageTocA11):

* **Elements**: These are atomic components of the GUI, i.e. components that
  are not made from smaller parts.
* **Collections**: These bundle elements or other collections into larger chunks
  of your GUI.

You declare you components by providing a minimum set of properties and,
if using collections, the bundled components. All compents in the Framework
strive to only use a small amount of required properties and provide sensible
defaults for other properties.

Since the representation of the components are implemented as immutable objects,
you can savely reuse components created elsewhere in your code, or pass your
component to other code without being concerned if the other code modifies it.

[Example 1](doku/usage_examples.md#example_1)
[Example 2](doku/usage_examples.md#example_2)
[Example 3](doku/usage_examples.md#example_3)

## Implementing Elements in the Framework

As an implementor of components in the ILIAS UI-Framework you need to stick to
some rules, to make sure the framework behaves in a uniform and predictable way
accross all components. Since a lot of code will rely on the framework and the
Kitchen Sink is coupled to the framework, there also are processes to introduce
new components in the framework and modify existing components.

### How to Introduce a New Component?

New components are introduced in the UI-Framework and the Kitchen Sink in
parallel to maintain the correspondence between the KS and the UI-Framework.

An entry in the Kitchen Sink passes through three states:

* **To be revised**: The entry is still being worked on.
* **Proposed**: The entry has been revisited and is proposed to the JF, but has
  not yet been decided upon.
* **Accepted**: The entry has been accepted by the JF.

These states are represented by using functionality of git and GitHub. After
acceptance, the new entry is part of the Kitchen Sink as well as part of the
source code in the trunk.

[Learn how to propose a Kitchen Sink Entry](doku/processes.md#introduce_ks_entry)

### How to Model a Kitchen Sink Component?

### How to Change an Existing Component?


* example for implementation
* layout of Internal and test


## To be discussed

* How is the KS-Layout related to this Lib?
	* Which components can be instantiated? Leafs only?
* It would be nice to enumerate the rules, we could refer to them in tests than.
* How could we make sure, that documentation and tests match up as much
  as possible? Could we generate tests from comments directly? How would one
  do that?
* Should the UI elements be immutable? (currently the tests say yes)
* It does not seem to make sense to implement to_html_string on Counter, as we
  never render a counter on its own. What to do about that?
* How are the comments formatted? (YAML?)
* How to deal with to_html_string?
* How to implement to_html_string? Should we already aim for the 'correct'
  interpreter approach or implement it with a naive recursion?
* How to layout the tests/UI-folder?
* Is there any need for an "(Non)Interactive"-Interface? Does this node in
  the KS-Taxonomy mean anything for the implementation?
* Do we need a glossary?

## ToDos:

* Create some more meaningful tests on counter and glyph.
* Make the KS-Layout accessible via link. Maybe just pull it into this repo?