# Help Topics in the UI-Framework

This document describes how the UI-Framework integrates various help texts with
the UI components. It starts with a short general overview of the problem space,
continues with some high-level description of the model implemented in the UI-
Framework, and closes with some hints for implementors using that model.

## Problem Space

Currently, (beginning of the year 2023) ILIAS provides two types of text that the
community understands to be help-texts:

* The texts provided via the so-called online help are attached to various screens
  of ILIAS. They provide advice about the usage of interfaces and concepts displayed
  on the screen.
* The online help also provides tooltips for some links and buttons in an installation.

There also are the by-lines in forms that, from some perspective, also could be
understood to be "help texts", but these currently are not provided ny the online
help but directly by developers via the lang file.

There frequently are new requirements for or shortcomings of the online help and/or
the general help system that currently are hard to address. Some examples:

* Currently, help-texts are only provided in German. How can other languages be
  included?
* How can we use the pool of texts from the online help to derive specific help
  texts for a certain installation?
* How can we provide help texts for plugins?
* How can we implement new help scenarios, such as guided tours?

Finally, until now there hasn't been a mechanism to attach help texts to certain
UI components, neither in an abstract form, nor as concrete components, such as
tooltips.

The Help Topics in the UI-Framework provide a model of how to think about help in
conjunction with the UI-Framework and facilities to implement existing requirements
and new scenarios.


## Model

Help texts are categorized along two dimensions in the UI Framework:

* A **Topic** is the matter that help should be a about.
* A **Purpose** is the intended use for a certain help text.

Topics are bound to components that support help functionality. These components
implement the interface `HasHelpTopic`.

During rendering, the UI framework decides in its `Renderer`s how the topics should
be used. A button with a help topic could, e.g., use that topic to display a tooltip
when it is hovered. To determine for which combinations of topics and purposes the
system provides a useful text, the UI framework uses a `HelpTextRetriever`, which
may or may not provide text for a certain combination of topics and purpose.

This means, that topics on one component can be used for multiple purposes. If
there is no text for a tooltip, there might indeed be text for a guided tour that
needs to be bound to the component in a certain way. Currently we only implement
one purpose `PURPOSE_TOOLTIP`, but the model is meant to be extended along this
direction. To ensure uniformity regarding the ways that help texts are used,
purposes are hard-coded and thus need to be added/requested via PR and decision
by the Jour Fixe.

The topics on the other hand basically are just simple texts and can be added to
components as according developers see fit. In fact, the model expects to have
many combinations of purposes and topics not having any fitting help texts. The
`HelpTextRetriever` is deliberately designed to support different implementations,
so developers adding UI components cannot and should not know if some topics
actually result in some help texts. The overall system will be in a good position
to provide help in various situations if many fitting topics are added to as
much UI components as possible.

The `HelpTextRetriever`, finally, has a standard implementation that uses the
existing tooltips from the online help. That retriever is injected into the
UI framework and can be replaced or extended via existing plugin functionality.
Hence, this model supports different strategies for providing help texts and
invites experimentation.


## Implementation Details

The main interfaces and classes relevant for implementors of the UI framework
are:

* `ILIAS\UI\Component\HasHelpTopics`: Components that support help texts in general
  need to implement that interface. To simplify implementation, a trait
  `ILIAS\UI\Implementation\Component\HasHelpTopics` exists. Using these two facilities,
  components can carry help topics.
* `ILIAS\UI\Help\Purpose` encapsulates the existing purposes. Renderes of components
  use these `Purpose`s and the `Topic`s from the components they render to ask
  for help texts via `AbstractComponentRenderer::getHelpText`.
* As of writing this document, only one `PURPOSE_TOOLTIP` exists. A unified renderer
  for Tooltips can be retrieved via `AbstractComponentRenderer::getTooltipRenderer`.
  When other `Purpose`s are created, new unified renderers to implement these
  are expected to emerge. 

The main facilities for implementors of plugins that allow for alternative mechanisms
for help texts are:

* `ILIAS\UI\HelpTextRetriever`: Describes the interface that needs to be implemented
  in order to be able to provide help texts to the UI-framework.
* An instance of that `HelpTextRetriever` is injected into the entry points of the
  UI-Framework, as one can see in `\InitUIFramework`. These entry points can be 
  [exchanged by plugins](docs/development/ui-plugin-manipulations.md).
