# Roadmap of the UI-Framework

General idea of this roadmap is, that tasks bubble up from the bottom to the top.
That is, people who want to contribute may find immediately actionable tasks in
the [Short Term](#short-term)-list. The [Long Term](#long-term)-list may be a
source for new short term tasks or tasks that are defined but need some kind of
project planning and management. [Ideas and Food for Thought](#ideas-and-food-for-thought)
acts as a notepad for information that comes up during day to day work with the
framework, discussions, etc. It may act as a source for new tasks. The sections
are explained in [Usage](#usage).


## Short Term

### Engaged Buttons (advanced, ~4h)

The [Bulky Button](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/src/UI/Component/Button/Bulky.php)
introduced the notion of an "engaged" button, i.e. a button that somehow indicates
an active state. The general [Buttons](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/src/UI/Component/Button/Button.php)
acquired a similar, but less explicit functionality via the `withAriaChecked` method
due to the [observation that some users of button (i.e. view mode control) need to
indicate which button is "checked"](https://github.com/ILIAS-eLearning/ILIAS/pull/567).
These two functions should be deduplicated in favour of the "engaged"-naming. I.e. it
should be possible to tag buttons `withEngagedState` in general, the aria label should then
be set accordingly without an explicit `withAriaChecked`.

### PHP 7 Typehints (beginner, ~2h)

ILIAS supported PHP 5.6 when the UI-Framework was first introduced. In the meantime
the PHP 5.6 support was dropped and ILIAS now supports PHP 7.0 and 7.1. This means
that we can take full advantage of PHP 7 typehints, i.e. hinting for internal types
and return types. The types are already documented in the docstrings, these should
be transformed to type hints where possible. Also the docstrings should be deleted
if they do not convey additional information, like some description, besides the
type.

### Smoke-Tests for Examples (advanced, ~4h)

While building the UI-framework, a good coverage by unit tests is an important
requirement. This works well in the general implementation of the UI-framework,
but the examples also delivered with the UI-framework currently do not have any
test coverage at all. We need a mechanism that automatically provides a smoke
test for all existing examples, i.e. checks if the example can be executed at
all and delivers a string to be included in the documentation of the UI frame-
work.

### Examples on Main Page (beginner, ~4h)

We want to have examples on the main pages of some components family of the 
documentation displayed in ILIAS System Styles Section. E.g. there should
also be examples on the "Buttons" Page for the complete Buttons family.

### Check if Supplied Data Matches Evaluating Form (advanced, ~8h)

When receiving data from the client we have no mechanism to make sure that the
data is processed by the same form that created the original client-side HTML-
form. This is especially interesting because the consumer of the form from
the UI-Framework does not have control over the naming. When for some reason
(e.g. some configuration in the Advanced Metadata) the fields in the form change,
the naming will change accordingly (as correctly pointed out by @mjansenDatabay
in [#24994](https://mantis.ilias.de/view.php?id=24994)). There could well be
other reasons why the form processing the request is different from the one
rendering the HTML, e.g. because endpoints are changed for some reason.

We thus want to introduce a mechanism that checks if the data supplied by the
client matches the form that is processing it. To implement this check, we want
to introduce a checksum over the inputs in the form, attach that checksum to the
data posted from the client and only evaluate the data when the checksum matches
the processing form. If the checksums do not match, the form should try to show
the data from the client as good as possible by using some heuristic to fill the
data in the existing inputs. It should also show a message that says why the data
was not processed and that the user should check the input again. A mechanism
like this will become even more valuable once we want to process forms asynchronously.

### Propose Context Parameter for Escaping on ilTemplate::setVariable (advanced, ~8h)

Currently there is no generalized way to handle escaping when outputting text.
In the long-term we would like to switch to a templating engine that is aware
of the context in which placeholders are filled. As a short-term improvement we
would like to introduce an context-parameter for `ilTemplate::setVariable`, based
on which `ilTemplate` could determine the required escaping for the output context.
The contexts should e.g. be "html", "html-attribute", "js-string". Depending on
feedback from other devs, we could either default to a very strict context that
escapes a lot, or to a context that does not escape and a dicto-rule.

### Add mutators to Counter (beginner, ~1h)

Currently, counters (for Glyphs, e.g.) are constructed with a numeric value;
there is a getter for this number, but in order to increase the value, one has
to construct a new Counter.
It would be handy to have a "withNumber"-mutator, or something like
"withIncrease/withDecrease"

### Implement `Input::getUpdateOnLoadCode`, `Input::withOnUpdate` and `Input::appendOnUpdate` for every Input (advanced, ~4h)

When introducing [UI Filters](https://github.com/ILIAS-eLearning/ILIAS/pull/1735)
some ends have been left open and need to be implemented properly. Currently
`withOnUpdate` and `appendOnUpdate` do not work on Inputs in the general case and
only work for `Select Field` and `Text Field` in the context of the filter. To
let the promise of `OnUpdate` come true, the following things will need to be done:

* Every Input needs to implement `Input::getUpdateOnLoadCode`.
* Once this is done, the method `Input::getUpdateOnLoadCode` on the base class
should be removed to force new inputs to implement this method properly.
* The usage of the method should be moved from the (specific) `Container\Filter\Renderer`
to the (general) `Field\Renderer` to make `OnUpdate` apply everywhere.
* `Input::withOnUpdate` and `Input::appendOnUpdate` can then be reinstated on the
base class and removed on `Field\Select` and `Field\Text`.

New inputs must already implement the methods.

## Long Term

### Balance or Unify Cards and Items

The Cards were introduced as one of the first elements in the UI-framework to
implement the "Member Galery" in the group or course together with the Deck of
Cards. Key property of the cards seem to that they show chunks of structured data.

The Items on the other hand where introduced in an attempt to start to redesign
the commonly known ListGUIs of ILIAS. Key feature of an item is that it displays
a unique entity within the system.

While the Card seem to focus on a certain format of data, the Items focus on the
semantical coherence of the displayed data set.

ILIAS 5.4 introduces the Repository Card as an element that is rendered like a
Card but actually displays a repository object, which is an entity in the sense
of the Item and also actually used to render a repository view. It thus seems to
be unclear why the element is implemented as a "Repository Card" instead of a
"Cardlike Item". Also the current usage of the Card for displaying users in the
members gallery could well be understood as displaying entities in the sense of
an Item.

This implies that there is a conceptual tension between the two concepts Card
and Item. This tension should be resolved by clearifing the roles of the two
elements Item and Card or unify them into a common concept. This will help
developers to pick the right tool for their job as well as clarify the future
development of the two concepts.


### All UI-Elements

The UI-Framework attempts to be the source for all visual elements in ILIAS and
thus supersede the current templating. The challenge is two-fold: on the one hand
the required elements need to be implemented in the UI-framework, on the other
hand the components need to use the UI-framework for their actual rendering. 


### Define JS-Patterns for the UI-Framework

Currently there is very little common structure in the JavaScript of the various
components that need client side code. With `withAdditionalOnLoadCode` and the
`Triggerer` and `Signal` concepts there is some structure on the server side,
but this only goes so far and doesn't give a definite answer how complex components
interact on the client side. Also, the current wording of `Triggerer` and `Signal`
and the underlying concepts seem to be confusing to at least some developers
(including at least one coordinator of the UI-Framework).

In the future we expect to include components with more interactivity. On the one
hand users expect more interactive applications that don't follow the request-
response cycle of standard webpages. On the other hand, breaking the request-
response cycle allows for applications that feel and possibly also actually are
more performant, since they don't need to load the complete page when users interact.

This hints at questions that cannot be answered by the server-side `Triggerer`/
`Signal` concept. The implementations of client side code are mostly based on events
currently but seem to differ internally. Event-based implementations of GUIs are
known to be hard to understand and developers using these will wake up in a
"Callback Hell" someday.

We need patterns or even a framework for client-side code that gives clear
guidelines how interactive components should be build for the UI-framework and
that integrates with the mechanism we use on the server-side to compose GUIs.


### Introduce Bootstrap 4 and Create a System for SASS-Variables

Currently ILIAS (and hence the UI-Framework) uses Bootstrap 3 as CSS-framework.
In the meantime, [Bootstrap 4](https://getbootstrap.com/docs/4.0/getting-started/introduction/)
was published. It comes with a new language for writing stylesheets (SASS) and
a new system for its SASS-variables.

The UI-Framework should switch to using Bootstrap 4. In this process, a system
to use Bootraps new set of variables together with a possible set of special
variables should be designed, documented and implemented. The switch to Bootstrap 4
needs to be coordinated with the components of ILIAS that currently do use features
of Bootstrap but do not use the UI-Framework.


### Page-Layout and ilTemplate, CSS/JS Header

When rendering the whole page, all needed resources like CSS and JS must be included.
The issue is closely linked to the question of which Service is responsible for
rendering the actual page, i.e. the overall output when calling an ILIAS-URL.

In the present implementation of ILIAS\UI\Implementation\Component\Layout\Page,
a tpl.standardpage.html-Template is acquired via the TemplateFactory, which in
turn makes use of the global template. The resources of global template are then
transported to the page's template (Layout\Page\Renderer::setHeaderVars).

Since the UI Page-Component aspires to be _the_ topmost thing to be rendered,
this should probably be done in a more direct and instructional way, similar to
the already existent template, but more clearly distinguished, like, maybe, in
registries for CSS- and JS-resources. These registries could then be passed to
the page and would turn the aforementioned transportation from ilTemplate obsolete.
In ultimo, there would be exactly one occurence of a line like
"echo $renderer->render($page);exit();" to output the complete UI.


## Ideas and Food for Thought

* Create a mechanism to wire less-files to delos.less that is more automatic than
  'do it manually'.
* Create an abstraction for Actions that could be used instead of stringy links.
  It would be strongly related to ilCtrl and probably should be an ILIAS library.


## Usage

### Short Term

#### Name of the Task (level,effort estimation)

Every item has a name, contains a description of what should be done and some
rationale about why it should be done. The name should be unique to make it
possible to reference the task by it. "level" is there to help contributors
pick a task according to their knowledge and could be "beginner", "advanced" or
"expert". "effort estimation" should give a rough estimate on the time that may
be required (e.g. "~4h", "~30min", "~2d", ...) to help contributors to judge
if they want to attempt the task. If a task is part of a Long Term task or relates
somehow to another task, this is noted inline.

### Long Term

Contains tasks that are actionable but require major reorganisations, have
preconditions, need to be coordinated somehow or have other reasons they cannot
be cleared immediately.

#### Name of the Effort

Like the Short Term tasks, these items contain a name, a description and a rationale.
Since the tasks are not to be done as is, they do not contain a level and an effort
estimation. If Short Term tasks are derived from them, these are referenced inline.

## Ideas and Food for Thought

Contains tasks that are not actionable and need to be refined to be moved to short
or long term goals. Also may contain questions, observations, ... that may lead to
new ideas or actionable tasks. This is just an unordered list.
