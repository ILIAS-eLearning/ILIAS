# The ILIAS UI-Framework

The ILIAS UI-Framework helps you to implement GUIs consistent with the guidelines
of the Kitchen Sink.

## Talk about Kitchen Sink Concepts

The ILIAS UI-Framework deals with the concepts also found in the Kitchen Sink.
In fact, this framework and the Kitchen Sink are heavily related. You won't need
to think about HTML if you're using this framework. You also won't need to think
about the implementation you are using, the device your GUI is displayed on or
the CSS-classes you need to use. You will be able to talk to other people (like
users or designers) using the same concepts and problem space as they do. This is
also not a templating framework.

[Learn more](doku/talk_about_ks_concepts.md)

## Compose GUIs from Simple Parts

In the ILIAS UI-Frameworks, GUIs are constructed by composing large chunks from
smaller components. The available components and their possible compositions are
described in the Kitchen Sink. The single components only have little  configuration,
complex GUIs emerge from simple parts. You also won't need to modify existing
components, just use them as is.

[Learn more](doku/composition.md)

## Correctness by Construction and Testability

The design of the ILIAS UI-Framework makes it possible to identify lots of
guideline violations during the construction of a GUI and turn them into errors
or exceptions in PHP. This gives you the freedom to care about your GUI instead
of the guidelines it should conform to. You also can check your final GUI for
Kitchen Sink Compliance using the procedures the frameworl provides for Unit
Testing.

[Learn more](doku/correctness.md)

## Using Elements of the Framework

### How to Discover the Components in the Framework?

* layout of public interface

### How to use the Components of the Framework?


## Implementing Elements in the Framework

### How to Introduce a New Component?

* process
* requirements

### How to Change an Existing Component?

### How to Model a Kitchen Sink Component?

* example for implementation
* layout of Internal and test



# UI-Framework for ILIAS

## What's this?

* goal
* main principles

## For Developers using this Framework

* How to discover KS-components in this framework?
* Examples for usage.
* layout of public interface

## For Developers implementing Components for the Framework

* How to introduce a component?
	* Process
	* Requirements
* How to change an existing component?
* How to model KS-components in this framework.
* Example for implementation.
* layout of Internal and test
* explain division public interfaces/private implementation

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

## ToDos:

* Create some more meaningful tests on counter and glyph.