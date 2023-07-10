# UX Guide: Best practices for properties and actions displayed on repository objects


When displaying properties and actions on repository objects, we recommend
following an overarching UX strategy and mindful Information Architecture that
puts the user intent and familiar mental models first. This helps the user to
understand and operate ILIAS intuitively, while avoiding confusion and
frustration.

The following document explains what these UX concepts mean and outlines how
they could be applied within the context of ILIAS UI components. Good UX
strategies enable users to work with many objects in large repositories without
getting overwhelmed and distracted by information and actions irrelevant to
their intent.

The target audience for the document are people who want to work on new UI
components, especially those which need to display a lot of information or
huge collections of items.

This project and our research focuses on properties displayed on repository
objects specifically, but many points directly apply to content structure and
object hierarchy in general.

## What is Information Architecture?

Information Architecture describes how data is organized across multiple pages
and objects of an app.

Good Information Architecture guides a user through layers, branches or funnels
(with varying information density) to the content or action they intended to
find. Relevant data is provided when and where it is expected, while data
irrelevant to the current user intent is minimized.

While it should heavily influence the interface design of pages and UI
components, it focuses purely on semantic grouping, hierarchies and the current
context of data.

## Why revise the Information Architecture of item properties?

As we begin to [transition the Legacy Container Object to the UI Standard
Listing Panel](https://docu.ilias.de/goto_docu_wiki_wpage_6409_1357.html) (and
the UI item it contains) many requests have come up to replicate all features of
this legacy object, especially with regards to the properties shown.

A feature request to [Streamline Object
Properties](https://docu.ilias.de/goto_docu_wiki_wpage_7399_1357.html) already
exists. It contains an extensive collection of properties that the Legacy
Container Items will display when the conditions are met with little to no
regard for the context or user intent.

There is also a proposal to create a [Catalogue Presentation for repository
items](https://docu.ilias.de/goto_docu_wiki_wpage_6990_1357.html) that displays
even more properties.

Feature requests like these demonstrate that there is a lot of activity and
requirements around object properties and their placement. However, many
discussions often focus on single, specific use cases.

With this document, we would like to propose general guidelines for the
Information Architecture of such properties that can be applied as a starting
point for organizing object properties in many situations.

This guideline aims to make decisions about displaying object properties easier
for concept designers and developers. It provides a more focused and
contextually sensitive user experience when browsing through a repository.

## User intent should guide data presentation

When a user interacts with an interface, they approach it with methods they
learned from previous experiences. This is often referred to as the user's
mental model. The user intent describes the goal, the mental model describes how
they think they will get there.

For example, if the user intent is to pick a course from a category that
interests them, they might apply the mental model of navigation they know from a
file manager: They expect that they can distinguish categories from other
objects and that clicking on the category's title brings them one level deeper
in the hierarchy.

Ideally, the mental model of the user aligns with the interface model provided.
Issues arise when these models clash - e.g. an important button or information
cannot be found when and how the user expects it.

Because ILIAS offers many different user roles, dozens of different object
types, and a variety of vastly different possible actions, guessing the current
user intent, and matching their mental model at a given moment can be
challenging.

Therefore, many places in ILIAS try to accommodate many possible user intents at
once. This often leads to screens and objects filled with so many properties and
options that finding a specific one might feel cumbersome and overwhelming,
especially to new users.

Consequently, we have explored if and when we can meet the user intent a bit
more closely, ideally reducing the amount of properties shown without
accidentally cutting out important information.

First, we conducted a workshop collecting the experiences from product managers
and programmers at Concepts and Training GmbH, with a focus on why they (or
users they know of) would struggle with the presentation of objects in different
parts of ILIAS. The biggest issues reported were:

* large amounts of irrelevant information when looking for a specific property
* empty space (in particular wide repository items with few or zero displayed
  properties)
* failed communication through irrelevant pictures (in UI Cards) and unknown
  iconography (the empty UI Progress Meter)

When comparing ILIAS to other web apps the participants were making the
following observations (this doesn't necessarily mean that ILIAS needs these
features):

* when dealing with large amounts of objects, many apps offer a very condensed,
  customizable table view (resembling a file manager)
* in some apps you can inspect/edit properties and even open objects in a
  dedicated area without leaving the current page (Jira, Trello, Outlook Online)
* there are no object drag and drop or right-click actions in the ILIAS
  repository

Based on this input and general research on mental models and information
architecture (see Further Reading & Sources), we identified 3 general types of
models, where controlling the selection of information according to the user
intent seems feasible in ILIAS:

* Making a quick choice
* Comparing before choosing
* Managing multiple objects

### Making a quick pick

In this case, a user intent is so focused that they only want to **quickly make
one clear choice for one expected object** by glancing at one or two of its
properties. The item action shown as the most prominent is usually the reason
why the user came to this view.

A good example is the contact or member gallery in ILIAS. If a user sets out
with the intent to get in touch with a specific person they already know, a name
and profile picture of that person is all they need to make the choice to
initiate contact.

When a user expects to be making such a quick choice, a high number of
irrelevant properties and actions are especially frustrating. A visual priority
given to the element that allows the quickest identification is very welcomed.

However, the reduction of properties can also prove problematic - e.g. if the
targeted user in the member gallery isn't identifiable just by the name and
profile picture.


### Comparing before choosing

Sometimes a user wants to **compare a selection of relevant properties of
multiple objects before making a choice between them.** The properties seen as
relevant help the user to make the decision for whichever action is presented as
the most prominent.

For example, a user might be offered a choice of sessions to attend. Besides the
session title, they will most likely be interested in several other properties
like the time and date, the description, the location, and the remaining seats
available. Ideally, irrelevant information (like the creation date of the
session) wouldn't be shown at all.

When a user expects to compare objects, they dislike finding the relevant
details only displayed in a subsequent view that they would have to repeatedly
switch in and out of. 

Also, from the array of potentially most relevant properties, some can become
more important than others (e.g. if there are no more seats available for a
session, all other properties are no longer important).

### Managing multiple objects

It's mostly administrators or users with a special role who want to **collect,
compare, and modify multiple objects and their properties for sorting and bulk
processing actions.**

For example, the user management table in the ILIAS admin area supports many
filtering, sorting and view options, so the administrator can choose dynamically
which relevant properties to display and what (bulk) actions to perform.

The quality of this user experience is mostly based on how intuitive and quick
the provided sorting, view controls, and filter systems are. When working with
such an intent, a pre-filtered and visually weighted presentation is often seen
as an unnecessary obstacle.

## UI components to match user intent

We already have a range of different UI components that each lean towards
different user intents:

A UI Card with an appropriately selected image offers the simplicity and quick
identification that a user wishing to make a quick choice would like to see.

The legacy repository object, the UI item, and the UI Presentation Table offer
room for some selected properties and bits of content to guide an informed
decision when a user prefers to explore and compare.

The legacy table and the UI Data Table are meant for dealing with large amounts
of data and offer functions for dynamic filtering and processing, that a person
managing multiple objects requires.

However, there are instances where those UI components are not embracing the
user intent that they are best suited for, which sometimes forces the mental
model to shift. For example, some repository items can end up displaying so many
properties that finding the relevant ones to compare becomes time consuming. In
this case, one might wish for a table view with customizable property columns
instead, or a carefully set up UI Presentation Table which hides certain details
when collapsed.

With the three distinct types of user intent presented here, we would like to
make identifying mismatches easier, and encourage further discussions and
exploration of how the ILIAS UI can guide the user with minimal friction and
distraction.

## General strategies to meet user intent

There are multiple ways to accommodate a user's wish to focus on specific data
properties or operations that seem especially promising for further improving
the UX in ILIAS. The following list goes from the strategy that requires the
least knowledge about the user intent to the one that requires the most:

* Structured patterns: The user directs their attention to a section where they
  expect to consistently find the currently relevant data and actions.
* User adjusted views: The user sets filters and sorting according to their
  current intent, leaving only (or mostly) the data they wish to work with.
* Curated views: Someone builds a view (or a sequence of views) choosing to
  include, exclude, deemphasize, and highlight data to support the user by
  anticipating a single (or a few) selected intents and needs.

### Structured patterns

When there is no way to build the current screen around a set of selected,
specific user intents, we can still support the user by giving them a specific
location to look for. When properties and actions are clearly grouped and
segmented, the user learns where to find the information that is currently of
interest. For example, all community interactions like star ratings and comments
could be in the bottom right corner of an item, and all organizational
meta-data, such as file size and version number, in the bottom left.

Before we dove deeper into organizing the properties shown on various ILIAS
objects, we investigated what different formats, functionalities, and use cases
for properties exist. The table on the feature request [Streamline Object
Properties](https://docu.ilias.de/goto_docu_wiki_wpage_7399_1357.html) was
tremendously helpful to explore the many different types of elements.

We propose to offer multiple consistent locations on UI components to make
decisions about splitting up properties easier. Instead of one property section,
an item could have multiple distinct ones. Possible semantic groups could be:

* community interactions (star rating, comment number)
* event information (next appointment date, available number of seats)
* meta-data (creation date, file size, version number)

Distinct groups will help users to quickly find the information relevant to them
in multi-purpose views.

If screens are built around a specific user intent, the most important property
could be pulled to a featured position. Different UI components might have
different kinds of featured positions. The already existing Leading Text of the
UI Item and Important Fields of the UI Presentation Table are good examples for
this.

In places where we know the user intent to some extent, some (or all) irrelevant
properties could be hidden. For example, the number of recent posts in a forum
object is a relevant representation of activity, while the number of recent
items in a category is usually not of interest.

In some instances we can avoid repeating redundant information. For example,
when the repository shows all categories grouped together, it might suffice to
put the the category icon only once into the group panel headline instead of on
every single object.

We have yet to explore if and how we want to incorporate other elements of the
interface to help display or hide information of an object outside of the item
itself. For example, many file managers can display additional information and
actions in a sidebar. Maybe the slate can be filled with new tools and features
to aid in analyzing, comparing, and managing repository items.

We also might want to more clearly and consistently decide on how a property is
displayed - e.g. as a key text & value text, key text & icon, icon & value text,
just the value text, or just as an icon. For example in "Status: Offline", the
word "Status" seems to be redundant, as the word "Offline" alone communicates
the same amount of information without the leading key.

### Personalised views

A user with the intent to manage multiple objects would like to use filters,
display properties, and sorting to shape the view to match their current focus.
Currently, in the repository, there is no way for the user to (temporarily)
choose a specific set of of properties to be displayed, while hiding others. We
might want to consider adding a table view, where managing users can quickly
show and hide property columns of a container, and can benefit from all the
filter and sorting options they know from other table views.

To declutter the content area, we might want to consider utilizing the slate for
some of the filter and sorting tools. This, on the one side, keeps them in view
while scrolling, and on the other side, gives an option to hide them for users
who don't wish to use them.

### Curated views

Very often in ILIAS, we have higher user roles managing content for lower user
roles. As part of this, the higher user roles craft landing pages with carefully
selected repository items on them, combined with information added through the
page editor. If the managing user would have more control over which properties
are shown, they could build very focused views around the anticipated user
intent.

For example, a landing page for new users offering the only three mandatory
beginner courses could be completely reduced to a UI Card with just a thumbnail
and the course title. A catalogue of all available courses, on the other hand,
could use the UI Presentation Table with focus on event dates to assist in
scheduling the learner's upcoming months.

We have seen managing users building such views manually with the page editor,
faking a deck of cards that only contains the desired information and
highlights. While this is a valid workaround, these fake items do not update
when the object they represent is updated. Adding, updating, and removing many
such items can turn into a laborious process, and is prone to user error.

When concept designers and developers build views around a specific focus, they
are creating a curated view as well. For example the badge, certificate, and
contacts pages utilize a deck of cards with a carefully chosen selection of
properties and actions.

## Next steps

We hope that this document provides some concepts to clarify user intent and
Information Architecture when discussing, developing, and improving UI
components, views, and other features in ILIAS.

We propose to extend the information on recommended use cases in the Kitchen
Sink Documentation of the UI Item, UI Presentation Table, and UI Card with
regards to which user intents they serve best.

We believe the feature request to [transition the Legacy Container Object to the
UI Standard Listing
Panel](https://docu.ilias.de/goto_docu_wiki_wpage_6409_1357.html) could benefit
from a semantic segmentation and order of properties and actions inside the UI
item, as outlined in the chapter Structured Patterns.

In connection to the ongoing discussion about which object can and should
display which properties (e.g. here: [Streamline Object
Properties](https://docu.ilias.de/goto_docu_wiki_wpage_7399_1357.html)), we also
might want to evaluate how each property is displayed by default (e.g. key and
value, just the value, just an icon, or key and icon?), as their purpose and
context, as well as the user's expectations, may have changed and grown over the
years since some objects were implemented.

In some selected contexts, we recommend considering taking advantage of the
space around the objects. For example, some kind of a property sidebar in the
slate that users know from other web apps like Nextcloud, Trello, and Jira could
be worth exploring.

A roadmap and detailed feature requests are yet to be determined and will be
linked in this document as undertakings around the item properties develop.

## Further Reading & Sources

* Young, I. (2008). Mental Models - Aligning Strategy with Human Behavior.
  Rosenfeld Media, LLC
* Spencer D. (2010). A Practical Guide to Information Architecture. UXmastery.
  [Link](https://maadmob.com.au/wp-content/uploads/2021/03/PracticalGuideToInformationArchitecture.pdf)
* Prof. Mahyar, N. (2019). Mental Models - Conceptual Models and Design. Lecture
  at the University of Massachusetts Amherst.
  [Link](https://groups.cs.umass.edu/nmahyar/wp-content/uploads/sites/8/2019/03/690A-10-ConceptualModels.pdf)
* Babich N. (2020). The Beginner’s Guide to Information Architecture in UX.
  Adobe XD Website.
  [Link](https://xd.adobe.com/ideas/process/information-architecture/information-ux-architect/)
