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

### Enaged Buttons (advanced, ~4h)

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

### Examples on Main Page (Beginner, ~4h)

We want to have examples on the main pages of some components family of the 
documentation displayed in ILIAS System Styles Section. E.g. there should
also be examples on the "Buttons" Page for the complete Buttons family.

### Abstraction for Actions (advanced, ~8h)
Create an abstraction for Actions that could be used instead of stringy links.
It would be strongly related to ilCtrl and probably should be an ILIAS library.
  
## Long Term

### All UI-Elements

The UI-Framework attempts to be the source for all visual elements in ILIAS and
thus supersede the current templating. The challenge is two-fold: on the one hand
the required elements need to be implemented in the UI-framework, on the other
hand the components need to use the UI-framework for their actual rendering. 

## Ideas and Food for Thought

* The names `Triggerer` and `Signal` for client side interaction in the UI-framework
  are confusing. Currently a valid sentence would be "the button triggers click on
  some registered signals". Being able to say something like "the button sends a
  click-signal to some registered receivers" seems to be more intelligible.
  
* Create a mechanism to wire less-files to delos.less that is more automatic than
  'do it manually'.


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
