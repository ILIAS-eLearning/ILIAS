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

### All UI-Elements Step 1

The UI-Framework attempts to be the source for all visual elements in ILIAS and
thus supersede the current templating. The challenge is two-fold: on the one hand
the required elements need to be implemented in the UI-framework, on the other
hand the Components need to use the UI-framework for their actual rendering. 

Not all Components have the same priority. Highest on our list are Components that 
are needed on every screen to render ILIAS with an empty content section (such as Tabs). 
Next are Components, that are needed in many use cases and therefore have a very high
grade of re-use (such as the Toolbar). Due to major work done for ILIAS 6, such as the Standard Layout 
Component, Meta Bar and Main Bar we are now able to promote further Components to our list of short 
term tasks, see below. Note that the names given here, are only the names of the task and must not necessarily 
reflect the names of the resulting Components. 

Further note that we desperately search developers and UX designers willing
to work creating those missing Components. If you are interested, drop a mail to the
coordinators, so they can get you geared up for the work. For all Components we highly
recommend starting with a Workshop with the UI Components coordinators to align various visions
and concepts of the given Component. The following items have the highest priority on 
our list of short term tasks.

#### Outer Content (advanced, variable)
This Component is basically what could be hooked into the [Standard Layout](Component/Layout/Page/Factory.php) as
content (currently provided as array of Legacy Components). Most Probably it should be able to hold the title section 
(not yet part of the UI Components, see below), the Tabs (not yet Part of the UI Components, see below) and the
Inner Content holding the workspace for the current context (not yet Part of the UI Components, see below).

Note; One important aspect here, will be to clarify at some point the relation to the [Global Screen](../GlobalScreen). 

#### Title Section (advanced, variable)
This Component will probably hold the Icon, title, description and the actions (maybe along with the used glyphs) of the 
current context. Note that a major part of the work for this components will be to setup a comprehensive set of rules on 
when to provide an Icon, restrictions of the Title (lengths, nouns vs verbs etc.), restrictions of the description 
(lengths, when to use etc.) and nature, amount of the actions etc. Note that there is pre-existing work on those 
subjects: [Feature Wiki](https://docu.ilias.de/goto_docu_wiki_wpage_6080_1357.html). 
However, this has not been decided yet and is thus most certainly up for discussion.

Note; One important aspect here, will be to clarify at some point the relation to the [Global Screen](../GlobalScreen). 

#### Tabs and Sub Tabs (advanced, variable)
Note that a major part of the work for this Components will be to setup a comprehensive set of rules on the naming of 
Tabs and Sub Tabs (noun vs verbs, length, amount of words etc.) and rules for the usage of Tabs vs Sub Tabs vs Sections
in Forms shown in Tabs. Also, one would have to look into the issue that currently "<-- Back" actions are mixed into 
the Tabs. We will need to decide, whether we will still use this concept in the future.

Note; One important aspect here, will be to clarify at some point the relation to the [Global Screen](../GlobalScreen). 

#### Inner Content
This will most probably mainly contain an array of Components used in the Content Section. An interesting 
point here will be the question, whether this Component should also offer something like withToolbar, to make sure 
only one or no Toolbar can be provided and whether there would be different types of Inner Content Components (such as 
one with a Sidebar).

#### Toolbar
This Component will probably need to be designed as new Input Container. An important part of the work here will be to 
devise a set of rules of what the toolbar should be used for and what not.

### Simple usage of demo-page in examples  (beginner, ~4h)
To show how a UI-Component looks like in the page context (esp. for 
Components from the MainControls) a simple "framework" to use a Demo-Page
in the examples would be helpful.

### Improving FileInput and Dropzones (advanced, variable)
General Dropzone functionality:
ILIAS 6 introduced a new library for drag & drop together with FileInput. This library was not used for all D&D users. This MUST be done and standardized with ILIAS 7.

With ILIAS 7, FileInput (or specific variants thereof) should have at least the following functions and properties:
- Upload several files at once
- Displaying existing files
- Restriction to file extensions
- Restriction to MimeTypes
- use `Data\DataSize` for file size info

With ILIAS 7 or later versions, FileInput (or specific variants thereof) can have the following functions and properties:
- Preview of uploaded images
- Crop functionality for images

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
* `Group::withOnUpdate` can use `parent::withOnUpdate` then.

New inputs must already implement the methods.

### Create a `Group`-family in `Input\Field` (beginner, ~2h)

Currently `Input\Field` contains various group inputs, where the different inputs
are created with methods that share the "group"-suffix. This is a exemplary case
for the introduction of a new 'Group` family within `Input\Field`, with its own
description, factory, renderer, directory...

### Improvement of Persistent Node States in `Tree` (advanced)

Currently there is no centralized approach to enhance `TreeRecursion` instances or
`Node` elements with persistence capabilities for the purpose of storing the state
(collapsed/expanded) of a `Node`.
With ILIAS 6 a first low-level approach was introduced in
[`ilTreeExplorerGUI`](../../Services/UIComponent/Explorer2/classes/class.ilTreeExplorerGUI.php#L444),
which enables derivatives to
[specify a `ilCtrl` route](../../Services/Mail/classes/class.ilMailExplorer.php#L87) used
as action for node state HTTP POST requests. This requests can be processed in the
client/consumer and delegated to the `ilTreeExplorerGUI` by calling
[`toggleExplorerNodeState`](../..Services/Mail/classes/class.ilMailGUI.php#L288)
on the explore instance.
This should be moved to a centralized position, e.g. Services/UI.

### Remove Snake Cases Functions for Tests (beginner, ~2h)

There are several tests still using snake cases as function names, remove it.
See also: https://github.com/ILIAS-eLearning/ILIAS/pull/2299

### Slates only accept string for titles (beginner, ~2h)

In some cases (e.g. see Item Slate aggregates) it would be good for slate titles
to also accept buttons. We should extend that.

### Footer should not use an input (beginner)

In the footer's template, an input-tag in cconunction with some inline-js is 
used to display the perma-link. This should be substituted by a non-input 
block-element, respectively an UI-Component on its own.

### Turn View Controls into View Control Inputs (advanced)

View Controls actually are more like Inputs and should be treated that way.
They accordingly should be implemented as Input\ViewControl\ViewControl.
Finally, when consumers are adapted, ViewControl can be removed from UI's root
entirely.

#### View Control Inputs to be created (moved):
* Mode View Control Input
* Pagination View Control Input
* Section View Control Input
* Sortation View Control Input

### Enforce (Aria-)Labels for Icons and Glyphs (beginner)
In src/GlobalScreen/Scope/MainMenu/Factory/hasSymbolTrait.php, e.g., as well as in
other files in src/GlobalScreen/Scope, an exception is being thrown for icons/glyphs
configured with an empty (Aria-)Label.
The components themselves should take care of this.

### Get rid of < div > under < body > element in Standard Page template (beginner)
In the template of the Standard Page, one level under the < body > element,
a < div > element is used. This level seems redundant and not giving any advantages
over just starting with < body >. We should remove the < div > element, but must
keep the functionalities, which are coupled to the "class"-attribute of the element.

### Complete rendering-tests for Inputs (beginner):
The UI Inputs do not all have a rendering test.
Add, where missing, and refine existing.

### Make date/time input accessible (advanced)
Date/Time pickers are currently implemented using a third party library. The solution suffers from accessibility issues. Even native pickers seem not always to be easy accessible. See https://mantis.ilias.de/view.php?id=29816#bugnotes. We should evaluate different solutions to tackle this.

### Remove wrapping DIVs in Mainbar
Top items in the mainbar are wrapped in a <div class="il-mainbar-triggers">;
We should get rid of this wrapper and have <ol>/<li> only for "menu-items",
directly under the <nav>-tag.


## Long Term

### Make Constraint in Tag Input Field work again

In the commit where this entry was added, a check in Tag Input Field was removed.
Currently, the Tag Input Field won't check if the Tags supplied by a user are
indeed allowed. For Tag Input Fields where user created tags are not allowed, we
would need to check if the supplied tags are indeed contained in the available options.
If user created tags are allowed, we would not need to do so. However, since we currently
cannot remove transformations and the default is that user created tags are not allowed,
we could not remove that check when a consumer allows user created tags. Fixing this would
require some rework of the form processing internals, so this is a reminder to look into
the tags again after such a rework.

### All UI-Elements Step 2

As mentioned above, the UI-Framework attempts to be the source for all visual elements in ILIAS and
thus supersede the current templating. There is much work remaining, even if the components listed above are implemented. 

### Glyphs as Toggle

Currently, the Notification Glyph (and maybe others) is used to toggle the activation
of the notification service at individual objects. The activity then is indicated
by color only, which violates the general accessibility rule that ["Color MUST not be
used as the only visual means of conveying information"](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/accessibility.md).
However, a quick fix seems not to be possible atm, because there also is no other
means to convey the notion of (in-)activity for a general Glyph, or even only the
specific Notification Glyph.

### Tooltips and Tooltippable

Tooltips are currently not yet implemented as UI components. Since 
probably many UI components have or will have tooltips, the introduction
 of a tooltippable interface should be discussed. This interface can
  easily receive tooltips (either as a UI component or much simpler as
   text) and can be implemented for all relevant UI components.

### Remove special case for UI-demo in `Implement\Layout\Page\Renderer::setHeaderVars`

Currently `Implement\Layout\Page\Renderer::setHeaderVars` contains a special
case for if it is used in the context of the Kitchen Sink. This is due to the
fact, that the demo for the complete page provides its own entry point, which
requires adjustments in the paths to javascript files. A special case like
this, however, is clunky and should be removed if possible. This seems to require
adjustments in the way that javascript is included and a base paths for the
current script is set. It might also be advisable to build the complete page
demo in another way.


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

### Introduce Redux-JS-Pattern from Mainbar into more UI Components
We suspect the Redux-Pattern used in the mainbar to be of value for multiple UI Components. One such suspect
is the keyboard navigation in the Tree Component. We aim to make a broader use of in upcoming developments.

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

### Introduce proper Notification Center (Expert)

The term "Notification Center" has not bee properly defined yet in the ILIAS context. 
This leads to several issues. E.g. there is no notification center UI Component,
laying (too much) work on the shoulders of Global Screen, Notification Slate and Items.
However, just building such a UI Component, would not do the trick. This needs
to go hand in hand with a proper discussion on what a Notification Center should be
and do for us. Current state, see: [FR: Notification Center](https://docu.ilias.de/goto_docu_wiki_wpage_5118_1357.html).


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
